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
    ┌───────────────────────────────────────────────────────────────────────────────────────┐
    │                                                                                        │
    │   app-server (100.100.123.90)                                                         │
    │   ┌────────────────────────────────────────────────────────────────────────────────┐  │
    │   │  php artisan db:backup        (scheduled nightly, 02:00)                       │  │
    │   │                                                                                │  │
    │   │  storage/app/backups/<UTC-run-id>/                                             │  │
    │   │    ├─ mariadb-reporting-workshop2.sql.gz                                       │  │
    │   │    ├─ mariadb-booking-workshop2.sql.gz                                         │  │
    │   │    ├─ mysql-shelter-workshop2.sql.gz                                           │  │
    │   │    ├─ mysql-animals-workshop2.sql.gz                                           │  │
    │   │    ├─ pgsql-workshop2.dump                                                     │  │
    │   │    └─ manifest.json                                                           │  │
    │   └────────────────────────────────────────────────────────────────────────────────┘  │
    │        │              │                  │                  │              │           │
    │   mysqldump      mysqldump          mysqldump           mysqldump      pg_dump         │
    │        │              │                  │                  │              │           │
    └────────┼──────────────┼──────────────────┼──────────────────┼──────────────┼───────────┘
             │              │                  │                  │              │
   ┌─────────▼────────┐ ┌───▼────────────┐ ┌───▼───────────┐ ┌────▼──────────┐ ┌─▼─────────────────┐
   │ workshop-2        │ │ linux-mariadb-2│ │ linux-mysql   │ │ linux-mysql-2 │ │ workshop-postgres  │
   │ 100.78.124.25      │ │ 100.97.35.29   │ │ 100.115.237.93│ │ 100.123.221.89│ │ 100.113.234.24     │
   │ MariaDB            │ │ MariaDB        │ │ MySQL         │ │ MySQL         │ │ PostgreSQL         │
   │ reporting          │ │ booking        │ │ shelter       │ │ animals       │ │ users              │
   └────────────────────┘ └────────────────┘ └───────────────┘ └───────────────┘ └────────────────────┘
```

**Why app-server dumps remotely instead of each DB host dumping locally:** it produces one
coordinated snapshot window across every physical database instead of independent ones, and
means backups land on a machine other than any single DB host, so losing any one DB VM/CT
doesn't take its own backups down with it.

### Why 5 dumps, not 5 — one per connection

Every Laravel connection now has its own dedicated physical host (1-database-1-physical-machine,
`docs/03-db-architecture.md`) since the `reporting`/`booking` split on 2026-07-20 followed the
`shelter`/`animals` split earlier the same day. `App\Services\Backup\BackupTargetResolver` groups
connections by `(driver, host, port, database)` so each physical database is dumped exactly once
— now a 1:1 mapping to connections, since no two connections share a host anymore. The naming
step still disambiguates targets that share a driver (`mariadb-reporting-workshop2` /
`mariadb-booking-workshop2`, `mysql-shelter-workshop2` / `mysql-animals-workshop2`) rather than
colliding on a shared name — the fix that was needed the moment any driver had more than one
target, first hit by the `shelter`/`animals` split.

| Target file prefix | Physical host | Engine | Laravel connection it covers |
|---|---|---|---|
| `mariadb-reporting-workshop2` | 100.78.124.25 (linux-mariadb) | MariaDB | `reporting` |
| `mariadb-booking-workshop2` | 100.97.35.29 (linux-mariadb-2) | MariaDB | `booking` |
| `mysql-shelter-workshop2` | 100.115.237.93 (linux-mysql) | MySQL | `shelter` |
| `mysql-animals-workshop2` | 100.123.221.89 (linux-mysql-2) | MySQL | `animals` |
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
[2] RESOLVE TARGETS — BackupTargetResolver maps 5 connections → 5 targets (1:1, since every
    connection has its own host now)
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
  │     yes → reset the pre-provisioned *_restore_test databases (never the live ones)
  │     no  → restore straight into the live workshop_2_prod databases
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

### One-time setup: provisioning the scratch databases

`RestoreDatabases::SCRATCH_SUFFIX` appends `_restore_test` to whatever the *live* database name
currently is — so the scratch name always tracks the real one automatically. As of the 2026-07-20
prod/dev split (CLAUDE.md's Database Connection Mapping) that's **`workshop_2_prod_restore_test`**,
not `workshop_2_restore_test` — this changed the moment the live database was renamed, and every one
of the 5 servers needs it, granted to the app's live DB credential (`workshop_2_prod`) — **the app's
regular DB user deliberately does not have the privilege to create arbitrary new databases itself.**
This is exactly the same one-time-setup shape as CLAUDE.md's Pre-Migration Checklist, just for a 6th
database name:

```bash
# linux-mariadb (100.78.124.25), linux-mariadb-2 (100.97.35.29), linux-mysql
# (100.115.237.93), and linux-mysql-2 (100.123.221.89) — as root on each:
mysql -u root -p -e "
  CREATE DATABASE IF NOT EXISTS workshop_2_prod_restore_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  GRANT ALL PRIVILEGES ON workshop_2_prod_restore_test.* TO 'workshop_2_prod'@'%';
  FLUSH PRIVILEGES;
"

# PostgreSQL (workshop-postgres, 100.113.234.24) — as the postgres superuser:
psql -U postgres -c "CREATE DATABASE workshop_2_prod_restore_test;"
psql -U postgres -c "GRANT ALL PRIVILEGES ON DATABASE workshop_2_prod_restore_test TO workshop_2_prod;"
psql -U postgres -d workshop_2_prod_restore_test -c "GRANT ALL PRIVILEGES ON SCHEMA public TO workshop_2_prod;"
```

Two more grants are needed beyond the ones above — both found live, running a real end-to-end drill
after the credential rename, not designed upfront:

- **Dumping routines** (`mysqldump --routines`) needs a way to see a routine's body when the
  connecting user isn't its `DEFINER` (every routine's `DEFINER` is still whatever user created it
  originally — renaming the app's credential doesn't retroactively change that). MySQL 8
  (`linux-mysql`, `linux-mysql-2`) uses the `SHOW_ROUTINE` dynamic privilege; MariaDB
  (`linux-mariadb`, `linux-mariadb-2`) doesn't implement that privilege at all and needs the classic
  `SELECT` on `mysql.proc` instead:
  ```sql
  -- MySQL 8 hosts:
  GRANT SHOW_ROUTINE ON *.* TO 'workshop_2_prod'@'%';
  -- MariaDB hosts:
  GRANT SELECT ON mysql.proc TO 'workshop_2_prod'@'%';
  ```
- **Restoring** a dump that contains `DEFINER=` clauses (every trigger/procedure/view) for a
  *different* user than the one connecting fails with `Access denied; you need (at least one of) the
  SUPER, SET USER privilege(s)`, unless granted:
  ```sql
  -- MariaDB hosts:
  GRANT SUPER ON *.* TO 'workshop_2_prod'@'%';
  -- MySQL 8 hosts:
  GRANT SET_USER_ID ON *.* TO 'workshop_2_prod'@'%';
  ```
  This is a real, ongoing consequence of renaming the connecting credential without also recreating
  every trigger/procedure/view under the new `DEFINER` — accepted here rather than doing that
  larger, riskier rewrite, since this is a homelab with no real data at stake. In an actual
  production system, backup/restore would more likely use its own dedicated, more-privileged
  credential distinct from the app's regular runtime one, rather than escalating that credential.

On PostgreSQL specifically, one more step is needed **every time the scratch database is
created from a template/copy of a renamed database** (not needed for a from-scratch provision):
`CREATE DATABASE ... WITH TEMPLATE` (and, it turns out, a plain rename/copy of an existing database)
preserves each *table's* individual owner from the source — a database-level `GRANT ALL PRIVILEGES
ON DATABASE` does not cascade to already-existing tables within it. Fix with:
```sql
\c workshop_2_prod_restore_test
REASSIGN OWNED BY <old_role> TO workshop_2_prod;
```

Run the base provisioning once per environment (a fresh homelab rebuild, a new server). This was
done for the live homelab on 2026-07-19, then re-done under the new `workshop_2_prod` naming and
verified with a real, full 5-server `--into-scratch` drill on 2026-07-20 (restore succeeded on all 5,
followed by a clean logical foreign key audit) — see the restore drill result below.

### The restore drill

An untested backup is not a backup. Run this periodically (and after any change to the dump/restore
code) without touching production data:

```bash
php artisan db:restore 20260720_020000 --into-scratch --force
```

This restores into `workshop_2_prod_restore_test` (the current live database name + suffix — see the
provisioning section above for why that name changed) on each physical server, then re-runs the
integrity audit against those scratch copies. Confirm the orphan counts match what the original
run's manifest recorded — if the drill shows *more* orphans than the manifest did, something about
the restore itself introduced a problem, not the data.

**Last drilled:** 2026-07-20, against a fresh real run (`20260720_080021`) on all 5 current servers
(app-server + linux-mariadb + linux-mariadb-2 + linux-mysql + linux-mysql-2 + linux-postgres) — full
pass, run for real after the `workshop_2_prod` rename specifically to confirm the whole pipeline
still worked end-to-end under the new credential, not just the individual grants:

- `db:backup` produced all 5 files after fixing the `SHOW_ROUTINE`/`mysql.proc` grants above (first
  attempt failed: `workshop_2_prod has insufficient privileges to SHOW CREATE PROCEDURE`).
- `db:restore ... --into-scratch --force` failed twice more before succeeding: once because 3 of the
  5 scratch databases had never actually been provisioned at all (only `linux-mariadb` and
  `linux-postgres` ever had one, from the 2026-07-19 drill below — provisioned the missing 3), and
  once on the `SUPER`/`SET_USER_ID` grant above (`DEFINER` mismatch restoring triggers/procedures).
  With all of that in place: restored cleanly on all 5 engines, followed by a clean logical foreign
  key audit (0 orphans).

**Previously drilled:** 2026-07-19, against real run `20260719_180705` on the live homelab as it
existed then (app-server + linux-mariadb + msi + linux-postgres — `msi` is no longer part of this
project's DB topology, see CLAUDE.md) — full pass:

- `db:backup` produced all 3 files; `gunzip -t` and `pg_restore --list` confirmed both are valid,
  non-corrupt archives (Postgres dump: 163 TOC entries); `zgrep -c 'CREATE.*PROCEDURE'` on the MariaDB
  dump found 39 stored procedures, confirming `--routines` actually captured them.
- Integrity audit: 0 orphans across all 12 logical FK checks.
- `db:restore 20260719_180705 --into-scratch --force` restored cleanly on all 3 engines; the
  post-restore audit (run against the `*_restore_test` scratch copies) also found 0 orphans.
- Row counts spot-checked directly against the restored scratch databases matched the live
  originals exactly: `users` 9/9 (Postgres), `animal` 102/102 (MySQL), `booking` 601/601 (MariaDB).

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
