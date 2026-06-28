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

## Deployment Steps (app-server)

These steps deploy the Laravel app to app-server after the migration:

```bash
# 1. SSH into app-server
ssh taufiq@100.100.123.90

# 2. Install PHP, Nginx, Composer and required extensions
sudo apt-get update
sudo apt-get install -y php8.3 php8.3-fpm php8.3-cli php8.3-mbstring php8.3-xml \
  php8.3-curl php8.3-pgsql php8.3-mysql php8.3-gd php8.3-zip php8.3-bcmath \
  nginx postgresql-client

# 3. Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 4. Clone repo (already done)
cd ~/Animal-Shelter-Workshop

# 5. Install PHP dependencies
composer install --no-dev --optimize-autoloader

# 6. Create .env
cp .env.example .env
php artisan key:generate
# Edit .env to set correct DB passwords and keys

# 7. Configure Nginx
sudo nano /etc/nginx/sites-available/animal-shelter
# Point root to /home/taufiq/Animal-Shelter-Workshop/public
sudo ln -s /etc/nginx/sites-available/animal-shelter /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# 8. Create workshop_2 on PostgreSQL (from workshop-postgres server)
ssh taufiq@100.113.234.24
psql -U postgres -c "CREATE DATABASE workshop_2;"
psql -U postgres -c "CREATE USER workshop_user WITH PASSWORD 'password';"
psql -U postgres -c "GRANT ALL PRIVILEGES ON DATABASE workshop_2 TO workshop_user;"

# 9. Run migrations
cd ~/Animal-Shelter-Workshop
php artisan migrate
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
