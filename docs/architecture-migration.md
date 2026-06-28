# Architecture Migration: SSH Tunnels to Tailscale

## Overview

This document records the decision to replace SSH port-forwarding tunnels with
Tailscale for connecting Laravel to a heterogeneous distributed database cluster.

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

## Database User Setup (All Three DB Servers)

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

## Deployment Steps (app-server)

These steps deploy the Laravel app to app-server. All commands run on
app-server (100.100.123.90) unless noted.

```bash
# 1. SSH into app-server
ssh taufiq@100.100.123.90

# 2. Install PHP 8.3, Nginx, Composer, MariaDB client, and required extensions
sudo apt-get update
sudo apt-get install -y php8.3 php8.3-fpm php8.3-cli php8.3-mbstring php8.3-xml \
  php8.3-curl php8.3-pgsql php8.3-mysql php8.3-gd php8.3-zip php8.3-bcmath \
  php8.3-intl nginx mariadb-client postgresql-client

# 3. Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 4. Clone repo
git clone https://github.com/tttaufiqqq/Animal-Shelter-Workshop.git ~/Animal-Shelter-Workshop
cd ~/Animal-Shelter-Workshop

# 5. Install PHP dependencies
composer install --no-dev --optimize-autoloader

# 6. Create .env and generate app key
cp .env.example .env
# Edit .env: set all DB_USERNAME=workshop_2 and DB_PASSWORD=workshop_2
# Set DB_CONNECTION=reporting (needed for the migrations tracking table)
php artisan key:generate

# 7. Set storage permissions (taufiq owns, www-data can write)
sudo chown -R taufiq:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 8. Allow Nginx to traverse the home directory
chmod o+x /home/taufiq

# 9. Link public storage
php artisan storage:link

# 10. Configure Nginx
# Write config to /tmp first, then sudo-copy it:
cat > /tmp/nginx-animal-shelter.conf << 'EOF'
server {
    listen 80;
    server_name _;
    root /home/taufiq/Animal-Shelter-Workshop/public;
    index index.php index.html;
    charset utf-8;
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    error_page 404 /index.php;
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    location ~ /\.(?!well-known).* { deny all; }
}
EOF
sudo cp /tmp/nginx-animal-shelter.conf /etc/nginx/sites-available/animal-shelter
sudo ln -sf /etc/nginx/sites-available/animal-shelter /etc/nginx/sites-enabled/animal-shelter
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm

# 11. Run migrations
php artisan migrate --force
```

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
