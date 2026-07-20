## Distributed Database Architecture

### Server Topology (Tailscale Network)

| Machine | Tailscale IP | Role | DB Engine |
|---|---|---|---|
| app-server | 100.100.123.90 | Laravel application host | — |
| workshop-2 | 100.78.124.25 | reporting connection (Proxmox VM) | MariaDB |
| linux-mariadb-2 | 100.97.35.29 | booking connection (Proxmox CT) | MariaDB |
| linux-mysql | 100.115.237.93 | shelter connection (Proxmox VM) | MySQL 8.0 |
| linux-mysql-2 | 100.123.221.89 | animals connection (Proxmox CT) | MySQL 8.0 |
| workshop-postgres | 100.113.234.24 | users connection | PostgreSQL |

Every one of the 5 connections now has its own dedicated physical machine —
1-database-1-physical-machine, no exceptions. `shelter`/`animals` were split off `msi` on
2026-07-20; `reporting`/`booking` (which previously shared `workshop-2`) were split the same
day, `booking` moving to a new CT. `msi` (this machine) no longer hosts any DB connection for
this project — it remains the Ansible/WSL control node and local dev environment, not part of
the Proxmox-managed fleet.

SSH access is via Tailscale IP directly — SSH keys are pre-configured, no password needed.

Example: `ssh taufiq@100.78.124.25`

**Root/superuser password — unified across all 5 DB servers: `qwertY@1612`** (MySQL/MariaDB
`root`, PostgreSQL `postgres`). Previously split across three different values depending on the
host (`qwertY@1612`, `Password123!`, or unset/peer-auth only); unified 2026-07-20 for consistency —
this is homelab/experiment infrastructure with no real users or data, one shared admin credential
across the fleet is a deliberate simplicity choice, not a production security posture.

### Database Connection Mapping

Each of the 5 servers carries **two** databases — a DBA-style prod/dev split, both introduced
2026-07-20 (the single shared `workshop_2` database + user this table used to list no longer
exists; its data was copied into `workshop_2_prod` and verified row-count-exact before the old one
was dropped):

- **`workshop_2_prod`** / user `workshop_2_prod` / password `workshop_2_prod` — what `app-server`
  connects to (via Vault, see `docs/09-production-hardening.md`'s "Secrets stopped touching disk
  entirely" section — the app never sees this password in plaintext, Vault Agent injects it).
- **`workshop_2_dev`** / user `workshop_2_dev` / password `workshop_2_dev` — what local development
  on this laptop connects to via a plain `.env` file (no Vault involved for local dev, by design —
  simplicity over infrastructure for a single-developer local loop). Empty on creation; each
  developer runs their own `php artisan migrate --force` (and `db:seed` if demo data is wanted)
  against it.

Both databases/users exist side by side on every server — dev work never touches prod data, and
prod deploys never touch dev data.

| Connection name | Module | Server | Driver | Prod DB/user/password | Dev DB/user/password |
|---|---|---|---|---|---|
| reporting | Stray Reporting | workshop-2 (100.78.124.25) | mariadb | workshop_2_prod | workshop_2_dev |
| booking | Booking Adoption | linux-mariadb-2 (100.97.35.29) | mariadb | workshop_2_prod | workshop_2_dev |
| shelter | Shelter Management | linux-mysql (100.115.237.93) | mysql | workshop_2_prod | workshop_2_dev |
| animals | Stray Animal | linux-mysql-2 (100.123.221.89) | mysql | workshop_2_prod | workshop_2_dev |
| users | Users Management | workshop-postgres (100.113.234.24) | pgsql | workshop_2_prod | workshop_2_dev |

(Prod and dev columns both read as `workshop_2_prod`/`workshop_2_dev` for database, username, and
password — e.g. reporting's prod credential is database=`workshop_2_prod`,
username=`workshop_2_prod`, password=`workshop_2_prod`.)

A `workshop_2` user (password `workshop_2`) still exists on the 4 MySQL/MariaDB servers, scoped only
to the pre-existing `workshop_2_test`/`workshop_2_restore_test` databases used by the test suite
(`docs/08-testing.md`) and the backup/restore drill (`docs/10-backups.md`) — unrelated to the
prod/dev split above, not touched by it, still using its original credential.

### Database Ownership by Connection

- **reporting** — reports, rescues, images tables
- **booking** — booking, transaction, adoption, visit_list, animal_booking tables (MariaDB stored procedures + triggers)
- **shelter** — category, inventory, slot, section tables
- **animals** — medical, clinic, vet, vaccination, animal, animal_profile tables
- **users** — users, roles, permissions, adopter_profile, audit_log tables (PostgreSQL)

### Pre-Migration Checklist

Create **both** the `workshop_2_prod` and `workshop_2_dev` databases/users on all 5 DB servers
before running migrations — prod for `app-server`, dev for local development against the same real
servers (see "Database Connection Mapping" above for why there are two). All 4 non-`workshop-2`
hosts are provisioned automatically by `infrastructure/ansible/playbooks/linux-mysql.yml` /
`linux-mysql-2.yml` / `linux-mariadb-2.yml` (see `docs/06-ansible.md`) — the snippets below are what
those playbooks actually run (for `workshop_2_prod` only; `workshop_2_dev` isn't Ansible-managed,
since it's a local-dev-only convenience), useful for a from-scratch rebuild without Ansible. Root
password is `qwertY@1612` on all 5 (unified 2026-07-20 — see the note above).

```bash
# --- Run on all 5 servers (adjust host/port per CLAUDE.md's Server Topology table) ---
# MySQL/MariaDB (workshop-2, linux-mariadb-2, linux-mysql, linux-mysql-2):
mysql -u root -p'qwertY@1612' -e "
  CREATE DATABASE IF NOT EXISTS workshop_2_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE DATABASE IF NOT EXISTS workshop_2_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER IF NOT EXISTS 'workshop_2_prod'@'%' IDENTIFIED BY 'workshop_2_prod';
  GRANT ALL PRIVILEGES ON workshop_2_prod.* TO 'workshop_2_prod'@'%';
  CREATE USER IF NOT EXISTS 'workshop_2_dev'@'%' IDENTIFIED BY 'workshop_2_dev';
  GRANT ALL PRIVILEGES ON workshop_2_dev.* TO 'workshop_2_dev'@'%';
  FLUSH PRIVILEGES;
"
# linux-mysql only: default validate_password policy (MEDIUM) rejects these
# passwords outright (no uppercase/special char) — relax it first:
mysql -u root -p'qwertY@1612' -e "SET PERSIST validate_password.policy = 'LOW'; SET PERSIST validate_password.length = 4;"
# linux-mysql / linux-mysql-2 only (needed for trigger creation):
mysql -u root -p'qwertY@1612' -e "SET PERSIST log_bin_trust_function_creators = 1;"

# --- workshop-postgres (PostgreSQL, 100.113.234.24) ---
psql -U postgres -c "CREATE DATABASE workshop_2_prod;"
psql -U postgres -c "CREATE DATABASE workshop_2_dev;"
psql -U postgres -c "CREATE USER workshop_2_prod WITH PASSWORD 'workshop_2_prod';"
psql -U postgres -c "GRANT ALL PRIVILEGES ON DATABASE workshop_2_prod TO workshop_2_prod;"
psql -U postgres -d workshop_2_prod -c "GRANT ALL PRIVILEGES ON SCHEMA public TO workshop_2_prod;"
psql -U postgres -c "CREATE USER workshop_2_dev WITH PASSWORD 'workshop_2_dev';"
psql -U postgres -c "GRANT ALL PRIVILEGES ON DATABASE workshop_2_dev TO workshop_2_dev;"
psql -U postgres -d workshop_2_dev -c "GRANT ALL PRIVILEGES ON SCHEMA public TO workshop_2_dev;"
# Note: PostgreSQL 15+ no longer grants CREATE on public schema by default
```

### Running Migrations

Migrations on app-server now run automatically on every push to `main`, via
`.github/workflows/deploy.yml` (see `docs/12-cd.md`) — no manual step needed for normal deploys. The
commands below are for local/dev work or manual intervention only.

```bash
# On app-server — run all migrations
php artisan migrate

# Fresh all databases
php artisan db:fresh-all --seed
```

### Stored Procedure Calling Convention (MariaDB)

The `booking` connection uses MariaDB stored procedures. Calling convention via PHP:

```php
// Procedures with OUT parameters — use session variables
DB::connection('booking')->statement(
    'CALL sp_booking_create(?, ?, ?, ?, @o_booking_id, @o_status, @o_message)',
    [$userId, $date, $time, $status]
);
$result = DB::connection('booking')->selectOne(
    'SELECT @o_booking_id AS booking_id, @o_status AS `status`, @o_message AS `message`'
);

// Procedures that return result sets — use select()
$rows = DB::connection('booking')->select('CALL sp_booking_read(?)', [$bookingId]);
```

---

## Rule 1 — Think Before Coding

State assumptions explicitly. Ask rather than guess.

Push back when a simpler approach exists. Stop when confused.



## Rule 2 — Simplicity First

Minimum code that solves the problem. Nothing speculative.

No abstractions for single-use code.



## Rule 3 — Surgical Changes

Touch only what you must. Don't improve adjacent code.

Match existing style. Don't refactor what isn't broken.



## Rule 4 — Goal-Driven Execution

Define success criteria. Loop until verified.

Strong success criteria let Claude loop independently.



## Rule 5 — Use the Model Only for Judgment Calls

Use for: classification, drafting, summarization, extraction.

Do NOT use for: routing, retries, deterministic transforms.

If code can answer, code answers.



## Rule 6 — Token Budgets Are Not Advisory

Per-task: 4,000 tokens. Per-session: 30,000 tokens.

If approaching budget, summarize and start fresh.

Surface the breach. Do not silently overrun.



## Rule 7 — Surface Conflicts, Don't Average Them

If two patterns contradict, pick one (more recent / more tested).

Explain why. Flag the other for cleanup.



## Rule 8 — Read Before You Write

Before adding code, read exports, immediate callers, shared utilities.

If unsure why existing code is structured a certain way, ask.



## Rule 9 — Tests Verify Intent, Not Just Behavior

Tests must encode WHY behavior matters, not just WHAT it does.

A test that can't fail when business logic changes is wrong.



## Rule 10 — Checkpoint After Every Significant Step

Summarize what was done, what's verified, what's left.

Don't continue from a state you can't describe back.



## Rule 11 — Match the Codebase's Conventions, Even If You Disagree

Conformance > taste inside the codebase.

If you think a convention is harmful, surface it. Don't fork silently.



## Rule 12 — Fail Loud

"Completed" is wrong if anything was skipped silently.

"Tests pass" is wrong if any were skipped.

Default to surfacing uncertainty, not hiding it.



## Rule 13 — Commit messages are documentation

When committing, always write:

1. A summary line: `type(scope): what changed and why` (<=72 chars)

2. A body with at minimum: what the problem was, what was tried, what the solution is, and which files changed meaningfully.

Never commit with a one-line message only. The commit history is the project's decision log.

Don't add co-author by Claude in the commit.



## Rule 14 — File Length Limit

No file may exceed 200 lines.

**For new files:** If a file would exceed 200 lines, split it into focused part files before writing.

**For existing files that already exceed 200 lines:** Refactor using the orchestration pattern — convert the file into a thin orchestrator that imports and composes focused part files.
