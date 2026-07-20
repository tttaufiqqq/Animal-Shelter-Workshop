# Architecture Migration: SSH Tunnels to Tailscale

## Overview

This document records two migrations, in chronological order: first, how this project moved from
five team members' own laptops to a single homelab-hosted deployment; second — once it was
homelab-hosted — the decision to replace SSH port-forwarding tunnels with Tailscale for connecting
Laravel to the heterogeneous distributed database cluster.

The mesh itself — join keys, split-DNS via dnsmasq, and the rest of the tailnet these VMs sit on —
is set up and documented at the infrastructure level in
[taufiq's homelab repo](https://github.com/tttaufiqqq/oracle-db-linux-proxmox)'s
`docs/02-dns/dns-setup.md`. This document only covers the decision from the application's side:
why Tailscale over the SSH-tunnel approach that came before it.

---

## Origin: From a Five-Person Team Project to a Solo Homelab Rebuild

### The original team setup (Oct 2025 – Jan 2026)

This project was originally built by a five-person team for BITU3923 Workshop II, each member
owning one module and one database engine (see the README's Development Team table). There was no
dedicated application server and no shared always-on infrastructure — whoever was running or
demoing the app started it on their own laptop, connecting out to the other four members' machines
over manual SSH tunnels, then running `php artisan serve` locally. Reaching the distributed
databases at all depended on whichever teammates happened to have their machine on and tunnel
commands running at the time.

### Picking it back up solo (Jan 2026)

The team project concluded, and the five-person setup along with it — none of the original
teammates are involved in what follows. In January 2026, working through past projects to find
something worth revisiting now that a personal Proxmox homelab
([`oracle-db-linux-proxmox`](https://github.com/tttaufiqqq/oracle-db-linux-proxmox)) already
existed, this project stood out: a genuinely heterogeneous multi-database app was exactly the kind
of infrastructure problem the homelab was built to practice on. From this point on it's a solo side
project, not a continuation of the team's work.

### Why the homelab, specifically

Two reasons, not one. Practically, a single laptop can't run five different database engines at
once — virtualizing each engine as its own Proxmox VM was the direct fix for that constraint, the
same way the homelab's other single-engine VMs (`linux-mariadb`, `linux-postgres`, etc.) already
existed for exactly this reason. Just as importantly, this was also a deliberate choice to learn
how a real multi-database web application gets deployed and kept running across several machines —
something none of the homelab's other, single-engine projects exercise.

### How the rebuild was actually done

Only the GitHub repository — the team's original application code — carried over. No database from
any of the five original team members' machines was migrated or restored; the distributed databases
on the homelab VMs are freshly created and reseeded from this repo's own seeders
(`php artisan db:fresh-all --seed`), not a copy of anything that existed during the team era. Each
database VM was built and configured by hand: SSH in, install the engine, create the `workshop_2`
user, configure the firewall — one command at a time, not from an existing image or script.
Claude Code was used as a development aid throughout this solo rebuild and the improvements that
followed it.

What follows in this document is the *next* step after that rebuild: the app-server's first
attempt at reaching those hand-built VMs (over SSH tunnels again, though this time to fixed
homelab machines rather than teammates' laptops), and why that was replaced with Tailscale.

---

## The Original (Wrong) Approach: SSH Tunnels

### What Was Built

Five separate SSH tunnels were established from the app-server to remote database
hosts. Each tunnel forwarded a local port on app-server to a remote database port:

```
app-server:13306 → ssh → workshop-2:3306    (MariaDB)
app-server:13307 → ssh → msi:3306           (MySQL)
app-server:15432 → ssh → workshop-postgres:5432  (PostgreSQL)
```

Laravel's `config/database.php` then connected to `127.0.0.1` on those local ports:

```php
'booking' => [
    'host' => '127.0.0.1',
    'port' => '13306',   // local tunnel port, not real DB port
    ...
],
```

Tunnel management was done via a shell script (`ssh-tunnels.txt`) that stored
plaintext SSH credentials and had to be run manually before starting the app.

Each tunnel was opened with `ssh -L` (local port forwarding). The commands from
`ssh-tunnels.txt` looked like this:

```bash
# Run on app-server before starting Laravel — all five tunnels required

# MariaDB on workshop-2 → local port 13306
ssh -N -L 13306:localhost:3306 taufiq@192.168.1.101 &

# MySQL (shelter) on msi → local port 13307
ssh -N -L 13307:localhost:3306 taufiq@192.168.1.102 &

# MySQL (animals) on msi — second connection, different local port
ssh -N -L 13308:localhost:3306 taufiq@192.168.1.102 &

# SQL Server on msi → local port 1434
ssh -N -L 1434:localhost:1433 taufiq@192.168.1.102 &

# PostgreSQL on workshop-postgres → local port 15432
ssh -N -L 15432:localhost:5432 taufiq@192.168.1.103 &
```

Flags used:
- `-N` — do not execute a remote command; keep the tunnel open but do nothing else
- `-L local_port:remote_host:remote_port` — forward `local_port` on the local machine to `remote_port` on the remote host through the SSH connection
- `&` — run in the background; all five had to be running simultaneously before `php artisan serve`

To verify a tunnel was alive you would run `ss -tlnp | grep 13306` and check that the local port was bound. If a tunnel had crashed there was no error — the port would simply be missing and the app would fail with a "Connection refused" database error at runtime.

### Why It Failed

**1. Operational fragility**
Tunnels are processes. They crash, timeout, and must be restarted. Every app
restart or server reboot required manually re-establishing tunnels. There was no
supervisor process, no health-checking, and no auto-reconnect.

**2. Plaintext credentials stored in the repository**
`ssh-tunnels.txt` contained SSH usernames, hosts, and key references in plain
text, committed to version control.

**3. False port mapping**
The app was configured to talk to `127.0.0.1:13306` — a local port that only
existed if the tunnel was running. This hid the real topology and made the system
impossible to reason about without knowing the tunnel state.

**4. Deployment impossibility**
Any new server had to replicate the exact same tunnel setup before the app could
start. This made horizontal scaling or failover impractical.

**5. SQL Server driver mismatch**
The `booking` connection was written for SQL Server (`sqlsrv` driver, T-SQL syntax,
`EXEC` calls, `@param OUTPUT`, `[bracketed identifiers]`) but the actual database
was MariaDB. The app could never have run against the real DB.

**6. Named after people, not modules**
Connection names (`taufiq`, `eilya`, `danish`, `shafiqah`, `atiqah`) were named
after team members rather than the modules they serve. This made the codebase
opaque to anyone unfamiliar with the team.

---

## The New Approach: Tailscale

### What Changed

Tailscale was installed on all machines in the cluster. Each machine receives a
stable, private Tailscale IP that never changes, regardless of physical location
or network topology.

```
app-server       100.100.123.90   Laravel host
workshop-2       100.78.124.25    MariaDB (reporting + booking modules)
msi              100.68.235.121   MySQL   (shelter + animals modules)
workshop-postgres 100.113.234.24  PostgreSQL (users module)
```

Laravel now connects directly to those static IPs — no tunnels, no local port
forwarding, no wrapper scripts:

```php
'booking' => [
    'driver' => 'mariadb',
    'host'   => env('DB4_HOST', '100.78.124.25'),   // real IP, always reachable
    'port'   => env('DB4_PORT', '3306'),             // real port
    'database' => env('DB4_DATABASE', 'workshop_2'),
    ...
],
```

### Why Tailscale Works Here

| Property | SSH Tunnels | Tailscale |
|---|---|---|
| Connection stability | Tunnel process can crash | Always-on mesh VPN |
| Startup requirement | Must run tunnel script first | Nothing — just connect |
| Credential exposure | Plaintext in repo | No credentials — WireGuard keys |
| Port mapping | Fake local ports | Real ports on real hosts |
| Deployability | Manual per-server setup | Install Tailscale, done |
| Firewall requirement | Open SSH port only | Tailscale handles NAT traversal |

### Verifying Tailscale Connectivity with Ping

Before running migrations or starting the app, confirm that every DB server is
reachable from app-server over Tailscale. The tool for this is `ping`.

#### Why ping, not a direct DB connection test?

A database connection failure can fail for several independent reasons:

1. The Tailscale tunnel itself is down (network layer)
2. The DB port is firewalled or the service is not running (transport layer)
3. Wrong credentials or missing database (application layer)

`ping` isolates **reason 1** from reasons 2 and 3. If ping fails, there is no
point troubleshooting credentials or port bindings — the machines cannot see each
other at all. Fix the network first, then move down the stack.

If ping succeeds but the DB connection still fails, you know the Tailscale mesh is
healthy and the problem is at the DB level (firewall, service not started, wrong
password, missing `workshop_2` database). This narrows the diagnosis significantly.

#### Commands

Run these from app-server (`ssh taufiq@100.100.123.90`) before any migration:

```bash
# Ping each DB server — expect <10ms round-trip inside Tailscale mesh
ping -c 4 100.78.124.25    # workshop-2  (MariaDB — reporting + booking)
ping -c 4 100.68.235.121   # msi         (MySQL   — shelter + animals)
ping -c 4 100.113.234.24   # workshop-postgres (PostgreSQL — users)
```

Expected output (all three must succeed):

```
PING 100.78.124.25 (100.78.124.25) 56(84) bytes of data.
64 bytes from 100.78.124.25: icmp_seq=1 ttl=64 time=3.21 ms
64 bytes from 100.78.124.25: icmp_seq=2 ttl=64 time=2.87 ms
...
--- 100.78.124.25 ping statistics ---
4 packets transmitted, 4 received, 0% packet loss
```

**0% packet loss and sub-10ms latency** = Tailscale mesh is healthy, proceed.

If a host does not respond:

```bash
# Check Tailscale status on app-server
tailscale status

# If the target machine shows "offline", the Tailscale daemon on that VM
# may not be running. Log into that VM and run:
sudo systemctl start tailscaled
sudo tailscale up
```

#### After ping passes — verify the DB port is open

Once the network layer is confirmed, check that the DB process is actually
listening on its port:

```bash
# MariaDB on workshop-2 (port 3306)
nc -zv 100.78.124.25 3306

# MySQL on msi (port 3306)
nc -zv 100.68.235.121 3306

# PostgreSQL on workshop-postgres (port 5432)
nc -zv 100.113.234.24 5432
```

`nc -zv` (netcat zero-I/O verbose) attempts a TCP connection without sending data.
A `Connection succeeded` response means the DB service is up and the port is
reachable — safe to run `php artisan migrate`.

---

## MariaDB Migration (booking connection)

The `booking` connection was previously configured for SQL Server with T-SQL syntax.
All stored procedures and triggers were rewritten for MariaDB.

### Why SQL Server Was Replaced

The project runs on a homelab Proxmox cluster with a constrained RAM budget shared
across four virtual machines and one physical host. SQL Server's minimum idle
footprint is approximately **1 GB** per instance — that alone would claim a
significant share of available memory before Laravel, Nginx, or any DB server for
the other modules had even started.

MariaDB idles at **under 100 MB** and provides the same procedural SQL features the
booking module was built on: stored procedures with `IN`/`OUT` parameters,
`BEFORE`/`AFTER` triggers, `START TRANSACTION` / `ROLLBACK`, and `SIGNAL` for
application-level errors. The rewrite required no changes to the PHP application
layer — only the SQL dialect inside the migration files changed.

**The heterogeneous distributed database architecture is fully preserved.** The system
still integrates three distinct database engines across four separate machines:

| Engine | Version | Module connections |
|---|---|---|
| MariaDB | 10.11 | `reporting`, `booking` |
| MySQL | 9.5 | `shelter`, `animals` |
| PostgreSQL | 16 | `users` |

MariaDB and MySQL share lineage but are independent products with separate release
schedules, default configurations, and behavioural differences (e.g. MariaDB's
`RETURNING` clause, default `utf8mb4` charset, and strict mode defaults differ from
MySQL 9.x). Treating them as the same engine would be incorrect — the architecture
is genuinely heterogeneous.

### Key Syntax Changes

| SQL Server | MariaDB |
|---|---|
| `EXEC sp_name @p1, @p2 OUTPUT` | `CALL sp_name(?, @o_var)` + session variable SELECT |
| `IF OBJECT_ID('sp','P') IS NOT NULL DROP PROCEDURE sp` | `DROP PROCEDURE IF EXISTS sp` |
| `BEGIN TRY / BEGIN CATCH` | `DECLARE EXIT HANDLER FOR SQLEXCEPTION` |
| `BEGIN TRANSACTION` | `START TRANSACTION` |
| `GETDATE()` | `NOW()` |
| `SCOPE_IDENTITY()` | `LAST_INSERT_ID()` |
| `@@ROWCOUNT` | `ROW_COUNT()` |
| `NVARCHAR(MAX)` | `TEXT` |
| `STRING_SPLIT(list, ',')` | `FIND_IN_SET(col, list)` or `SUBSTRING_INDEX` loop |
| `INSTEAD OF DELETE` trigger | `BEFORE DELETE` trigger with `SIGNAL SQLSTATE '45000'` |
| `RAISERROR('msg', 16, 1)` | `SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'msg'` |
| `AFTER INSERT, UPDATE` trigger | Separate `BEFORE INSERT` and `BEFORE UPDATE` triggers |
| `[transaction]` square brackets | `` `transaction` `` backticks |
| `CHARINDEX` | `LOCATE` |
| `LTRIM(RTRIM(str))` | `TRIM(str)` |

### MariaDB OUT Parameter Pattern (PHP)

MariaDB does not support PDO `PARAM_INPUT_OUTPUT` binding. The correct pattern is:

```php
// Step 1: CALL with session variable placeholders for OUT params
DB::connection('booking')->statement(
    'CALL sp_booking_create(?, ?, ?, ?, @o_booking_id, @o_status, @o_message)',
    [$userId, $date, $time, $status]
);

// Step 2: SELECT the session variables in a separate query
$result = DB::connection('booking')->selectOne(
    'SELECT @o_booking_id AS booking_id, @o_status AS `status`, @o_message AS `message`'
);
```

For procedures that return result sets (no OUT params):

```php
$rows = DB::connection('booking')->select('CALL sp_booking_read(?)', [$bookingId]);
```

---

## Connection Rename (Person Names to Module Names)

All five database connections were renamed from team member names to module names:

| Old name | New name | Module |
|---|---|---|
| `taufiq` | `users` | Users Management Module |
| `eilya` | `reporting` | Stray Reporting Management Module |
| `shafiqah` | `animals` | Stray Animal Management Module |
| `atiqah` | `shelter` | Shelter Management Module |
| `danish` | `booking` | Booking Adoption Management Module |

This rename was applied across 126 files using PowerShell bulk replacement, and
all service class files were renamed to match:

- `DanishProcedureService.php` → `BookingProcedureService.php`
- `ShafiqahProcedureService.php` → `AnimalProcedureService.php`
- `TaufiqProcedureService.php` → `UserProcedureService.php`
- `TaufiqViewService.php` → `UserViewService.php`
- `AtiqahProcedureService.php` → `ShelterProcedureService.php`
- `EilyaProcedureService.php` → `ReportingProcedureService.php`

---

## ~~Database User Setup (All Three DB Servers)~~ — superseded

**This section is stale and describes a setup that no longer exists — don't follow it.** It predates
both the shelter/animals + reporting/booking host splits (now 5 DB servers, not 3; `msi` isn't a DB
host at all anymore) and the later `workshop_2_prod`/`workshop_2_dev` credential split. Kept only as
a historical record of the very first `workshop_2` user setup done during the Tailscale migration.
**Current, accurate instructions: CLAUDE.md's "Pre-Migration Checklist."**

<details>
<summary>Original content (superseded, do not use)</summary>

Each database server requires a dedicated `workshop_2` user with appropriate
privileges. Do this **before** running migrations.

### MariaDB — workshop-2 (100.78.124.25)

Access via direct console or from a machine with local root access:

```sql
CREATE USER IF NOT EXISTS 'workshop_2'@'%' IDENTIFIED BY 'workshop_2';
GRANT ALL PRIVILEGES ON workshop_2.* TO 'workshop_2'@'%';
FLUSH PRIVILEGES;
```

### MySQL — msi (100.68.235.121)

```sql
CREATE USER IF NOT EXISTS 'workshop_2'@'%' IDENTIFIED BY 'workshop_2';
GRANT ALL PRIVILEGES ON workshop_2.* TO 'workshop_2'@'%';
FLUSH PRIVILEGES;
```

Also enable trigger/procedure creation for non-SUPER users (binary logging is on):

```sql
-- Run as root (local connection) — this persists across restarts in MySQL 8+
SET PERSIST log_bin_trust_function_creators = 1;
```

### PostgreSQL — workshop-postgres (100.113.234.24)

```sql
-- Run as postgres superuser
CREATE DATABASE workshop_2;
CREATE USER workshop_2 WITH PASSWORD 'workshop_2';
GRANT ALL PRIVILEGES ON DATABASE workshop_2 TO workshop_2;

-- Required in PostgreSQL 15+ (public schema no longer open by default)
\c workshop_2
GRANT ALL PRIVILEGES ON SCHEMA public TO workshop_2;
GRANT CREATE ON DATABASE workshop_2 TO workshop_2;
```

</details>

---

## Test Accounts

All accounts are seeded by `database/seeders/UserSeeder.php`. Password for every
account is `password`.

| Role | Email | Access |
|---|---|---|
| admin | admin1@gmail.com | Full system — reports, animals, shelter, bookings, audit logs |
| admin | admin2@gmail.com | Full system — reports, animals, shelter, bookings, audit logs |
| caretaker | caretaker1@gmail.com | Rescue ops, animal management, medical records |
| caretaker | caretaker2@gmail.com | Rescue ops, animal management, medical records |
| public user | taufiq@gmail.com | Submit reports, browse animals, book adoptions |
| public user | shafiqah@gmail.com | Submit reports, browse animals, book adoptions |
| public user | atiqah@gmail.com | Submit reports, browse animals, book adoptions |
| public user | danish@gmail.com | Submit reports, browse animals, book adoptions |
| public user | eilya@gmail.com | Submit reports, browse animals, book adoptions |

> The `adopter` role is not seeded directly. A public user becomes an adopter after
> completing an adoption — the role is assigned by the booking/adoption workflow.

---

## ~~Deployment Steps (app-server)~~ — superseded, and step 9 below is actively dangerous

**Do not follow this section — step 9 (`db:fresh-all --seed` on every deploy) drops every table on
all databases on every run.** That behavior was deliberately removed (see
`docs/09-production-hardening.md`'s "Deploy pipeline no longer destroys data"). This whole section
also predates the real deploy path/user (`/var/www/animal-shelter` as `workshop` — the actual box
uses `/home/taufiq/Animal-Shelter-Workshop` as `taufiq`), `key:generate --force` running every
deploy (removed once `APP_KEY` moved into Vault), and the current Vault Agent secrets flow. Kept
only as a historical record of the very first Ansible deploy design. **Current, accurate
instructions: `docs/06-ansible.md` and `docs/09-production-hardening.md`.**

<details>
<summary>Original content (superseded, do not use — see warning above)</summary>

App-server deployment is fully automated by Ansible. Run from the WSL control node:

```bash
cd infrastructure/ansible
ansible-playbook playbooks/app-server.yml
```

This handled everything in order:
1. Installs PHP 8.3 (all extensions), Nginx, Node 20, Composer
2. Clones the repo to `/var/www/animal-shelter` as user `workshop`
3. Sets ownership `workshop:www-data` and 775 on `storage/` and `bootstrap/cache/`
4. Runs `composer install --no-dev --optimize-autoloader`
5. Deploys `.env` from the `env-app.j2` template (skipped if already exists)
6. Runs `php artisan key:generate --force`
7. Runs `npm ci && npm run build` (skipped if `public/build/manifest.json` exists)
8. Deploys Nginx config to `/etc/nginx/sites-available/animal-shelter`, enables it
9. Runs `php artisan db:fresh-all --seed`

The app lived at `/var/www/animal-shelter`. Nginx served from `/var/www/animal-shelter/public`.

For manual access or debugging:

```bash
# SSH into app-server
ssh workshop@100.100.123.90

# App directory
cd /var/www/animal-shelter

# Re-run migrations (e.g. after a schema change)
php artisan migrate --force

# View logs
tail -f storage/logs/laravel.log
```

</details>

---

## Files Deleted

- `ssh-tunnels.txt` — plaintext SSH credentials and tunnel commands (security risk)

---

## Summary

The SSH tunnel approach introduced unnecessary operational complexity, credential
exposure, and a fragile dependency on running background processes. Replacing it
with Tailscale reduced the database connection configuration to plain static IPs —
the same as connecting to a local database, but across a secure WireGuard mesh.
The app now starts without any pre-flight tunnel setup and connects reliably even
after server reboots.
