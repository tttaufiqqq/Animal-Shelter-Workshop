# Backups: coordinated, verified, centralized

This replaces the per-host nightly `mysqldump`/`pg_dump` timers described in the old "Automated
backups" section of `docs/09-production-hardening.md`. That approach had five problems, found while
reviewing it for this pass:

1. **Dumps never left the host they backed up.** Losing a DB VM lost its backups too.
2. **`msi` (100.68.235.121) was never backed up at all** — it hosts `shelter` + `animals`, and it's a
   Windows machine the Linux Ansible playbooks can never manage.
3. **Uncoordinated.** Three independent timers meant a restored set could have e.g. a
   `booking.userID` pointing at a user id that wasn't in that night's `users` dump.
4. **Silent failure.** `set -euo pipefail` exiting non-zero into a systemd unit nobody was watching.
5. **Retention had no floor.** `find -mtime +7 -delete` had no success check — seven failed nights in
   a row would have pruned its way down to zero backups.

The old scripts and systemd units (`templates/backup-{mysql,postgres}.sh.j2`,
`templates/db-backup.{service,timer}.j2`, the `── Backups ──` task blocks in
`playbooks/linux-{mysql,mariadb,postgres}.yml`) are deleted. Everything now runs as one command,
`php artisan db:backup`, on app-server.

## Architecture

```
                              Tailscale network
    ┌───────────────────────────────────────────────────────────────────┐
    │                                                                   │
    │   app-server (100.100.123.90)                                    │
    │   ┌───────────────────────────────────────────────────────────┐  │
    │   │  php artisan db:backup        (scheduled nightly, 02:00)  │  │
    │   │                                                           │  │
    │   │  storage/app/backups/<UTC-run-id>/                        │  │
    │   │    ├─ mariadb-workshop2.sql.gz                            │  │
    │   │    ├─ mysql-workshop2.sql.gz                              │  │
    │   │    ├─ pgsql-workshop2.dump                                │  │
    │   │    └─ manifest.json                                      │  │
    │   └───────────────────────────────────────────────────────────┘  │
    │             │                    │                    │           │
    │        mysqldump             mysqldump             pg_dump        │
    │             │                    │                    │           │
    └─────────────┼────────────────────┼────────────────────┼───────────┘
                  │                    │                    │
      ┌───────────▼─────────┐ ┌────────▼──────────┐ ┌───────▼─────────────┐
      │ workshop-2           │ │ msi (laptop)       │ │ workshop-postgres    │
      │ 100.78.124.25         │ │ 100.68.235.121     │ │ 100.113.234.24       │
      │ MariaDB               │ │ MySQL              │ │ PostgreSQL           │
      │ reporting + booking   │ │ shelter + animals  │ │ users                │
      └───────────────────────┘ └────────────────────┘ └──────────────────────┘
```

**Why app-server dumps remotely instead of each DB host dumping locally:** it's the only design that
covers `msi` at all, and it produces one coordinated snapshot window instead of three independent
ones — both requirements from the original ask. It also means backups land on a 4th machine, so
losing any one DB VM doesn't take its own backups down with it.

### Why 3 dumps, not 5

The app has 5 Laravel connections but only 3 physical databases — `reporting`+`booking` share the
MariaDB server, `shelter`+`animals` share the MySQL server (`docs/03-db-architecture.md`).
`App\Services\Backup\BackupTargetResolver` groups connections by `(driver, host, port, database)` so
each physical database is dumped exactly once, not once per Laravel connection pointed at it.

| Target file prefix | Physical host | Engine | Laravel connections it covers |
|---|---|---|---|
| `mariadb-workshop2` | 100.78.124.25 | MariaDB | `reporting`, `booking` |
| `mysql-workshop2` | 100.68.235.121 (msi) | MySQL | `shelter`, `animals` |
| `pgsql-workshop2` | 100.113.234.24 | PostgreSQL | `users` |

## Nightly flow

```
routes/console.php: Schedule::command('db:backup')->dailyAt('02:00')
  │
  ▼
[1] PREFLIGHT — DatabaseConnectionChecker::checkAll()
  │     any connection offline?  ──yes──▶  ABORT. No run directory is created.
  │     no                                  Mail sent, Cache + log record 'failed'.
  ▼
[2] RESOLVE TARGETS — BackupTargetResolver collapses 5 connections → 3 targets
  ▼
[3] DUMP each target  (DatabaseDumper)
  │     mysqldump --single-transaction --routines --triggers --events | gzip
  │     pg_dump    --format=custom --clean --if-exists
  │     dump fails, or file < 100 bytes?  ──yes──▶  delete run dir, ABORT, email sent
  │     no
  ▼
[4] INTEGRITY AUDIT  (LogicalForeignKeyAudit)
  │     12 cross-DB checks from docs/04-foreign-keys.md
  │     any orphans found?  ──yes──▶  status = "degraded" (backup is still kept)
  │     no                    status = "ok"
  ▼
[5] WRITE manifest.json  (BackupManifest) — sha256 + bytes per file, orphan
  │     counts, duration, status. This file, not mtime, is the source of truth
  │     for retention and the UI.
  ▼
[6] Cache::forever('backup_last_status', ...)  →  feeds the admin UI panel
  ▼
[7] PRUNE old runs  (BackupRetention)
  ▼
[8] degraded or failed?  ──yes──▶  email via Mail::to(config('mail.backup_alert_to'))
```

### Why "abort" instead of "write what you can"

If one of the 5 connections is unreachable at the start, the command deletes nothing it hasn't
written and produces no run directory at all — a partial set of 2-out-of-3 dumps is not a coordinated
backup, and a partially-written run sitting in the backups directory looking like a normal run is more
dangerous than no run, because someone could restore from it without knowing it's incomplete.

### Why "degraded" is not the same as "failed"

A logical FK orphan (e.g. a `booking.animalID` pointing at a deleted animal — see
`docs/04-foreign-keys.md`'s "Orphan Risk" section, which already documents this as a known,
non-catastrophic possibility) doesn't mean the dump is bad. The 3 files are still complete, checksummed,
valid backups of what's actually in the databases right now. Marking the run `failed` would throw away
a perfectly good backup over a pre-existing data-quality issue unrelated to the backup itself. Instead
it's flagged loudly (email + red UI panel + orphan list) so it's investigated, but still counted as a
successful run for retention purposes.

### Why credentials never appear in a process argument list

`mysqldump -p<password>` (and similar) is visible to any other user on the box via `ps aux` while it
runs. `App\Services\Backup\MysqlCredentialsFile` writes a `chmod 0600` temporary
`--defaults-extra-file` instead, deleted immediately after the dump/restore finishes. PostgreSQL's
client tools take the password via the `PGPASSWORD` environment variable instead, which isn't visible
in `ps` either.

## Retention

`App\Services\Backup\BackupRetention` keeps the last **7 daily** runs plus the last **4 weekly**
(newest run of each ISO week) runs, reading `manifest.json` status — not file age — to decide what
counts:

- A run with `status: failed` is never counted as a survivor and is never protected by the floor
  below. (There should never be a `failed` run directory in practice, since a failed run is deleted
  before a manifest is even written — but the check exists in case ordering changes.)
- No prune ever runs at all while 3 or fewer successful runs exist — a bad week can't shrink the
  backup set to zero, unlike the old `find -mtime +7 -delete`, which had no such floor.

```
newest ─────────────────────────────────────────────────────────► oldest
 [today] [d-1] [d-2] [d-3] [d-4] [d-5] [d-6] [wk-2] [wk-3] [wk-4] [wk-5] ...
   ▲───────────── keep: last 7 daily ─────────────▲ ▲── keep: 4 weekly ──▲
                                                                          ✗ pruned
```

## Restore

```
php artisan db:restore <run> [--into-scratch] [--force]
  │
  ▼
[1] Read storage/app/backups/<run>/manifest.json
  │     missing?  ──▶  FAIL — nothing to restore
  ▼
[2] Recompute sha256 of every dump file, compare to the manifest
  │     mismatch or missing file?  ──▶  REFUSE — a corrupted dump is never restored
  ▼
[3] Confirm (unless --force)
  ▼
[4] --into-scratch ?
  │     yes → DROP + CREATE the *_restore_test databases first (never the live ones)
  │     no  → restore straight into the live workshop_2 databases
  ▼
[5] DatabaseRestorer: mysql / pg_restore --clean --if-exists, per target
  ▼
[6] Re-run LogicalForeignKeyAudit
  │     --into-scratch → audited connections are temporarily repointed at
  │                       *_restore_test (registered as `<connection>_scratch`
  │                       in config() for the duration of this command only)
  │     otherwise       → audits the databases just restored
  ▼
[7] Print orphan counts for comparison against the manifest's own audit result
```

### The restore drill

An untested backup is not a backup. Run this periodically (and after any change to the dump/restore
code) without touching production data:

```bash
php artisan db:restore 20260720_020000 --into-scratch --force
```

This restores into `workshop_2_restore_test` on each physical server, then re-runs the integrity audit
against those scratch copies. Confirm the orphan counts match what the original run's manifest
recorded — if the drill shows *more* orphans than the manifest did, something about the restore itself
introduced a problem, not the data.

**Last drilled:** _not yet — run the command above against a real backup on app-server and record the
date and result here._

## Alerting

- **Admin UI**: `/admin/backups` (see sidebar → System → Backups) reads `Cache::get('backup_last_status')`
  plus every `manifest.json` under `storage/app/backups/`, and shows the latest run's status
  (`ok`/`degraded`/`failed`) plus a history table.
- **Email**: `App\Mail\DatabaseBackupFailed`, sent on `failed` or `degraded` runs, to
  `config('mail.backup_alert_to')` (env: `BACKUP_ALERT_EMAIL`).

**Open item:** `.env`'s `MAIL_MAILER=log` means any mail sent right now is written to
`storage/logs/laravel.log`, not actually delivered. `BACKUP_ALERT_EMAIL` and a real `MAIL_MAILER`
(matching what `docs/09-production-hardening.md` already set up for password-reset mail — Resend) both
need to be set for the email side of alerting to reach an inbox. Until then, the admin UI panel is the
only alerting surface that actually works.

## manifest.json

```json
{
    "run_id": "20260720_020000",
    "status": "ok",
    "started_at": "2026-07-20T02:00:00+00:00",
    "finished_at": "2026-07-20T02:03:41+00:00",
    "duration_seconds": 221.4,
    "targets": {
        "mariadb-workshop2": { "file": "mariadb-workshop2.sql.gz", "bytes": 184320, "sha256": "…" },
        "mysql-workshop2":   { "file": "mysql-workshop2.sql.gz",   "bytes": 97536,  "sha256": "…" },
        "pgsql-workshop2":   { "file": "pgsql-workshop2.dump",     "bytes": 40192,  "sha256": "…" }
    },
    "orphans": {
        "reporting.report.userID -> users.users.id": 0,
        "booking.adoption.animalID -> animals.animal.id": 0
    }
}
```

## Where things live

| Piece | Path |
|---|---|
| Orchestrator command | `app/Console/Commands/BackupDatabases.php` |
| Restore command | `app/Console/Commands/RestoreDatabases.php` |
| Target grouping (5 connections → 3 physical DBs) | `app/Services/Backup/BackupTargetResolver.php` |
| Dump execution | `app/Services/Backup/DatabaseDumper.php` |
| Restore execution | `app/Services/Backup/DatabaseRestorer.php` |
| manifest.json read/write | `app/Services/Backup/BackupManifest.php` |
| The 12 logical-FK checks | `app/Services/Backup/LogicalForeignKeyAudit.php` |
| Retention/pruning | `app/Services/Backup/BackupRetention.php` |
| Credential file handling | `app/Services/Backup/MysqlCredentialsFile.php` |
| Failure/degraded email | `app/Mail/DatabaseBackupFailed.php`, `resources/views/emails/backup-alert.blade.php` |
| Admin UI | `app/Http/Controllers/Admin/BackupController.php`, `resources/views/admin/backups/index.blade.php` |
| Schedule | `routes/console.php` (`Schedule::command('db:backup')`) |
| Backup storage | `storage/app/backups/<run-id>/` on app-server |
| Ansible: client tools + storage dir | `infrastructure/ansible/playbooks/app-server.yml` |
| Ansible: retired per-host units removed | `infrastructure/ansible/playbooks/linux-{mysql,mariadb,postgres}.yml` |
