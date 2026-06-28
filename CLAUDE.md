## Distributed Database Architecture

### Server Topology (Tailscale Network)

| Machine | Tailscale IP | Role | DB Engine |
|---|---|---|---|
| app-server | 100.100.123.90 | Laravel application host | — |
| workshop-2 | 100.78.124.25 | reporting + booking connections | MariaDB |
| msi (local) | 100.68.235.121 | shelter + animals connections | MySQL |
| workshop-postgres | 100.113.234.24 | users connection | PostgreSQL |

SSH access is via Tailscale IP directly — SSH keys are pre-configured, no password needed.

Example: `ssh taufiq@100.78.124.25`

### Database Connection Mapping

| Connection name | Module | Server | Driver | Database | Username | Password |
|---|---|---|---|---|---|---|
| reporting | Stray Reporting | workshop-2 (100.78.124.25) | mariadb | workshop_2 | root | — |
| booking | Booking Adoption | workshop-2 (100.78.124.25) | mariadb | workshop_2 | root | — |
| shelter | Shelter Management | msi (100.68.235.121) | mysql | workshop_2 | root | password |
| animals | Stray Animal | msi (100.68.235.121) | mysql | workshop_2 | root | password |
| users | Users Management | workshop-postgres (100.113.234.24) | pgsql | workshop_2 | postgres | — |

### Database Ownership by Connection

- **reporting** — reports, rescues, images tables
- **booking** — booking, transaction, adoption, visit_list, animal_booking tables (MariaDB stored procedures + triggers)
- **shelter** — category, inventory, slot, section tables
- **animals** — medical, clinic, vet, vaccination, animal, animal_profile tables
- **users** — users, roles, permissions, adopter_profile, audit_log tables (PostgreSQL)

### Pre-Migration Checklist

Create the `workshop_2` database and user on all 3 DB servers before running migrations.

```bash
# --- workshop-2 (MariaDB, 100.78.124.25) ---
mysql -u root -p -e "
  CREATE DATABASE IF NOT EXISTS workshop_2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER IF NOT EXISTS 'workshop_2'@'%' IDENTIFIED BY 'workshop_2';
  GRANT ALL PRIVILEGES ON workshop_2.* TO 'workshop_2'@'%';
  FLUSH PRIVILEGES;
"

# --- msi/local (MySQL, 100.68.235.121) — local root, password: 'password' ---
mysql -u root -ppassword -e "
  CREATE DATABASE IF NOT EXISTS workshop_2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER IF NOT EXISTS 'workshop_2'@'%' IDENTIFIED BY 'workshop_2';
  GRANT ALL PRIVILEGES ON workshop_2.* TO 'workshop_2'@'%';
  FLUSH PRIVILEGES;
"
# Binary logging is ON on msi — required for trigger creation by non-SUPER user:
mysql -u root -ppassword -e "SET PERSIST log_bin_trust_function_creators = 1;"

# --- workshop-postgres (PostgreSQL, 100.113.234.24) ---
psql -U postgres -c "CREATE DATABASE workshop_2;"
psql -U postgres -c "CREATE USER workshop_2 WITH PASSWORD 'workshop_2';"
psql -U postgres -c "GRANT ALL PRIVILEGES ON DATABASE workshop_2 TO workshop_2;"
psql -U postgres -d workshop_2 -c "GRANT ALL PRIVILEGES ON SCHEMA public TO workshop_2;"
# Note: PostgreSQL 15+ no longer grants CREATE on public schema by default
```

### Running Migrations

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
