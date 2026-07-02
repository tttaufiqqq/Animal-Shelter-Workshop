# Server Hardening Guide

## Overview

This document covers hardening all four machines in the distributed architecture
to ensure services survive VM shutdowns, Proxmox power cuts, and unplanned
restarts without manual intervention. It also restricts each database server to
only accept connections on its Tailscale interface from the application server.

The guiding principle is **defence in depth**: each layer (network interface
binding, OS firewall, DB-level access control, and process auto-start) independently
blocks unauthorized access. If one layer is misconfigured, the others still hold.

### Machine Reference

| Tailscale IP | Hostname | OS | DBMS | DB Port |
|---|---|---|---|---|
| 100.100.123.90 | app-server | Ubuntu 24.04 (Proxmox VM) | — (Laravel) | — |
| 100.78.124.25 | linux-mariadb | Ubuntu 24.04 (Proxmox VM) | MariaDB 10.11 | 3306 |
| 100.113.234.24 | linux-postgres | Ubuntu (Proxmox VM) | PostgreSQL 16 | 5432 |
| 100.115.237.93 | linux-mysql | Ubuntu (Proxmox VM) | MySQL 8.0 | 3306 |
| 100.68.235.121 | msi | Windows 11 (physical) | MySQL 9.5 | 3306 |

### Hardening Goals

1. **Availability** — Services restart automatically after power loss or VM reboot.
   No human intervention required to bring the system back up.
2. **Network isolation** — Each DB server listens only on the Tailscale interface,
   not on the LAN or public network. An attacker on the local network cannot reach
   the database ports at all, even if they know the IP.
3. **Access control at the firewall layer** — Even on the Tailscale interface, only
   the application server's IP (`100.100.123.90`) is allowed to reach the DB ports.
   Any other Tailscale peer is blocked at the OS firewall before the DB engine sees
   the connection.
4. **Startup ordering** — The Proxmox cluster is configured so DB VMs fully
   initialize before the application VM starts, preventing Laravel from attempting
   connections to databases that are not yet ready.

---

## 1. Laravel Application Server (Ubuntu 24.04)

### 1.1 Auto-start Web Server on Boot

**Why:** When a Proxmox VM reboots after a power cut, installed services do not
automatically start unless explicitly enabled in systemd. Without `systemctl enable`,
nginx and PHP-FPM would have to be started manually every time, leaving the app
unreachable until someone logs in.

```bash
sudo systemctl enable nginx
sudo systemctl enable php8.3-fpm
```

Both are already enabled. Verify with:

```bash
systemctl is-enabled nginx php8.3-fpm
# Both should print: enabled
```

### 1.2 Firewall (UFW)

**Why:** The app-server is exposed to the internet (or at least to the Proxmox
network) on port 80. Without a firewall, all ports that happen to be bound are
reachable. UFW enforces an explicit allowlist — only ports 22 (SSH for management),
80 (HTTP), and 443 (future HTTPS) are permitted. Everything else, including any
accidentally-running services or misconfigurations, is silently dropped.

UFW is already enabled with these rules:

```bash
sudo ufw default deny incoming    # drop everything by default
sudo ufw default allow outgoing   # app can initiate outbound connections (to DB servers)

# SSH restricted to Tailscale interface from the admin machine only.
# Binding to tailscale0 means the rule only fires on packets that arrived
# through the encrypted WireGuard tunnel — a spoofed source IP on eth0 cannot
# satisfy this rule. The global `allow 22/tcp` that would expose SSH to the
# public internet is intentionally absent.
sudo ufw allow in on tailscale0 from 100.68.235.121 to any port 22 proto tcp comment 'SSH from msi'

sudo ufw allow 80/tcp             # HTTP for the web app
sudo ufw allow 443/tcp            # HTTPS (when TLS certificate is added)
sudo ufw --force enable
```

Verify:

```bash
sudo ufw status verbose
```

### 1.3 Storage Permissions After Reboot

**Why:** Laravel writes logs, cached views, and uploaded file metadata to
`storage/` and `bootstrap/cache/`. If the user that owns these directories does
not have write access, the app throws 500 errors. After certain system operations
(e.g. ownership changes, restore from snapshot), permissions may revert. This is
the recovery command:

```bash
sudo chown -R workshop:www-data /var/www/animal-shelter/storage \
                                /var/www/animal-shelter/bootstrap/cache
chmod -R 775 /var/www/animal-shelter/storage \
             /var/www/animal-shelter/bootstrap/cache
```

### 1.4 Proxmox — Start at Boot

**Why:** By default, Proxmox VMs do not start automatically after a node reboot.
If the Proxmox host loses power and restarts, all VMs remain off until manually
started. Enabling "Start at boot" removes this single point of human dependency.

In Proxmox web UI:
- Select the app-server VM → **Options** → **Start at boot** → **Yes**
- Set **Startup order: 3**, **Startup delay: 30** (seconds — gives DB VMs time to initialize first)

---

## 2. MariaDB (Ubuntu 24.04)

SSH into the MariaDB server (100.78.124.25) and run all of the following.

### 2.1 Bind to Tailscale IP Only

**Why:** By default, MariaDB binds to `0.0.0.0`, meaning it listens on every
network interface — LAN, loopback, and Tailscale. Any machine that can reach the
host's LAN IP could attempt a connection to port 3306. Restricting `bind-address`
to the Tailscale IP (`100.78.124.25`) means the DB port is completely invisible on
the LAN interface. A port scanner on the local network would find nothing on 3306.
Only devices inside the Tailscale mesh can even see the port is open.

Find the config file:

```bash
grep -r "bind-address" /etc/mysql/ 2>/dev/null
# Typically: /etc/mysql/mariadb.conf.d/50-server.cnf
```

Edit the config:

```bash
sudo nano /etc/mysql/mariadb.conf.d/50-server.cnf
```

Add or change under `[mysqld]`:

```ini
bind-address = 100.78.124.25
```

### 2.2 Enable Auto-start on Boot

**Why:** Same reasoning as for nginx above. MariaDB must be enabled in systemd so
it starts automatically after the Proxmox VM boots, whether from a scheduled
restart or an unplanned power cut.

```bash
sudo systemctl enable mariadb
sudo systemctl is-enabled mariadb   # should print: enabled
```

### 2.3 OS Firewall (UFW)

**Why:** Even with `bind-address` set to the Tailscale IP, the OS firewall adds a
second independent layer of enforcement. If `bind-address` is ever accidentally
reverted (e.g. a package update overwrites the config), the firewall still blocks
connections from unauthorized IPs at the kernel level, before they reach MariaDB.
The two layers are independent — both must fail for an attacker to reach the DB.

`ufw allow in on tailscale0` permits Tailscale's own management and key-exchange
traffic, which travels on the `tailscale0` virtual interface. Without this, Tailscale
connectivity can break after UFW is enabled.

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing

# SSH only from the admin machine, only on the Tailscale interface.
# Do NOT use `allow 22/tcp` (global) — that exposes SSH to the LAN and public internet.
sudo ufw allow in on tailscale0 from 100.68.235.121 to any port 22 proto tcp comment 'SSH from msi'

# MariaDB only from app-server, only on the Tailscale interface.
# Do NOT use `allow in on tailscale0` (blanket) — that would allow every Tailscale
# peer to reach port 3306, making this rule pointless. The interface + source IP
# combination is what provides the actual restriction.
sudo ufw allow in on tailscale0 from 100.100.123.90 to any port 3306 proto tcp comment 'MariaDB from app-server'

sudo ufw --force enable
sudo ufw status verbose
```

### 2.4 Restart and Verify

```bash
sudo systemctl restart mariadb
sudo systemctl status mariadb

# Confirm MariaDB is listening only on the Tailscale IP, not 0.0.0.0
ss -tlnp | grep 3306
# Expected: 100.78.124.25:3306
```

### 2.5 Proxmox — Start at Boot

**Why:** DB VMs must boot before the application VM so that Laravel's connection
pool does not fail on startup. This DB server should have a lower startup order
number than app-server.

In Proxmox web UI:
- Select the MariaDB VM → **Options** → **Start at boot** → **Yes**
- Set **Startup order: 1**, **Startup delay: 0**

---

## 3. PostgreSQL (Ubuntu)

SSH into the PostgreSQL server (100.113.234.24) and run all of the following.

### 3.1 Bind to Tailscale IP Only

**Why:** PostgreSQL defaults to `listen_addresses = 'localhost'` on a fresh
install, but many deployment guides change it to `'*'` for convenience. If it is
set to `'*'`, it listens on every interface including the LAN. Restricting it to
the Tailscale IP ensures the database port is only reachable through the encrypted
WireGuard tunnel, not from the bare network.

Find the config:

```bash
sudo find /etc/postgresql -name postgresql.conf
# Typically: /etc/postgresql/16/main/postgresql.conf
```

Edit:

```bash
sudo nano /etc/postgresql/16/main/postgresql.conf
```

Change:

```ini
listen_addresses = '100.113.234.24'
```

### 3.2 Restrict pg_hba.conf to app-server Only

**Why:** PostgreSQL has its own application-level access control file (`pg_hba.conf`)
that is evaluated before any SQL is executed. Even if a connection reaches the port,
`pg_hba.conf` decides whether to allow authentication. Restricting it to
`100.100.123.90/32` means that even a Tailscale peer other than app-server that
somehow bypasses the OS firewall would be rejected by PostgreSQL itself. This is the
database-layer equivalent of the firewall rule — a third independent enforcement layer.

Using `scram-sha-256` instead of `md5` is important: `md5` sends a hash that can
be cracked offline if the challenge-response is intercepted. `scram-sha-256` is a
proper challenge-response protocol that does not expose any crackable data.

```bash
sudo nano /etc/postgresql/16/main/pg_hba.conf
```

Ensure only app-server can connect remotely:

```
# TYPE  DATABASE  USER        ADDRESS              METHOD
local   all       all                              peer
host    all       workshop_2  100.100.123.90/32    scram-sha-256
```

Remove or comment out any broad rules like `host all all 0.0.0.0/0 md5`.

### 3.3 Enable Auto-start on Boot

**Why:** PostgreSQL must be running before app-server attempts its first database
connection. Enabling it in systemd ensures it starts automatically after a Proxmox
host reboot without any manual intervention.

```bash
sudo systemctl enable postgresql
sudo systemctl is-enabled postgresql   # should print: enabled
```

### 3.4 OS Firewall (UFW)

**Why:** Same layered-defence rationale as MariaDB. `pg_hba.conf` and
`listen_addresses` restrict access at the application layer, but UFW enforces
it at the kernel network layer before the connection ever reaches PostgreSQL.
Both layers are independent; neither alone is sufficient.

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing

# SSH only from the admin machine, only on the Tailscale interface.
sudo ufw allow in on tailscale0 from 100.68.235.121 to any port 22 proto tcp comment 'SSH from msi'

# PostgreSQL only from app-server, only on the Tailscale interface.
# Same reasoning as MariaDB: `allow in on tailscale0` (blanket) would allow all
# Tailscale peers to reach 5432 and negate the source-IP restriction entirely.
sudo ufw allow in on tailscale0 from 100.100.123.90 to any port 5432 proto tcp comment 'PostgreSQL from app-server'

sudo ufw --force enable
sudo ufw status verbose
```

### 3.5 Restart and Verify

```bash
sudo systemctl restart postgresql
sudo systemctl status postgresql

# Confirm PostgreSQL is listening on the Tailscale IP only
ss -tlnp | grep 5432
# Expected: 100.113.234.24:5432
```

### 3.6 Proxmox — Start at Boot

In Proxmox web UI:
- Select the PostgreSQL VM → **Options** → **Start at boot** → **Yes**
- Set **Startup order: 2**, **Startup delay: 0**

---

## 4. MySQL (Windows 11 — Physical Machine)

msi is a physical Windows machine, not a Proxmox VM. Power-cut resilience is
handled by Windows service management rather than Proxmox. The firewall approach
uses Windows Firewall instead of UFW.

### 4.1 Windows Service — Auto-start (Already Configured)

**Why:** Windows services can be set to `Manual`, `Automatic`, or `Disabled`.
`Automatic` means Windows starts the service during boot without any user login
required. The `MySQL95` service is already set to `Automatic`, so MySQL restarts
on its own after a power cut or Windows reboot.

Verify via PowerShell (run as Administrator):

```powershell
Get-Service -Name 'MySQL95' | Select-Object Name, Status, StartType
# Expected: StartType = Automatic
```

### 4.2 Bind to Tailscale IP Only

**Why:** MySQL on Windows binds to all interfaces by default. On a Windows machine
connected to both a local network and Tailscale, this means port 3306 is reachable
from the LAN — anyone on the same network segment could attempt brute-forcing MySQL
credentials. Setting `bind-address` to the Tailscale IP (`100.68.235.121`) removes
this exposure completely. The LAN interface will show no open port 3306.

Open `C:\ProgramData\MySQL\MySQL Server 9.5\my.ini` as Administrator (right-click
→ Open with → Notepad, run as administrator).

Under `[mysqld]`, after the `port=3306` line, add:

```ini
# Bind only to Tailscale IP — prevents exposure on LAN/public interfaces.
# Only the encrypted WireGuard tunnel interface is accessible.
bind-address=100.68.235.121
```

Restart the MySQL service (PowerShell as Administrator):

```powershell
Restart-Service -Name 'MySQL95'
```

Verify:

```powershell
mysql -u root -ppassword -e "SHOW VARIABLES LIKE 'bind_address';"
# Expected: bind_address = 100.68.235.121
```

### 4.3 Windows Firewall — Restrict Port 3306

**Why:** Even with `bind-address` set to the Tailscale IP, adding a Windows
Firewall rule provides the same independent second layer as UFW does on Linux.
The approach uses a deny-all rule for port 3306, then adds specific allow rules
on top. Windows Firewall evaluates allow rules before block rules, so the specific
allows take precedence for matching source IPs while everything else is blocked.

The localhost allow rule is needed because local administrative tools (MySQL
Workbench, `mysql` CLI) connect via `127.0.0.1`.

Run in PowerShell as Administrator:

```powershell
# Block all inbound MySQL connections by default
netsh advfirewall firewall add rule `
  name="MySQL Block All" `
  protocol=TCP dir=in localport=3306 `
  action=block

# Allow only from app-server Tailscale IP
netsh advfirewall firewall add rule `
  name="MySQL Allow app-server" `
  protocol=TCP dir=in localport=3306 `
  remoteip=100.100.123.90 `
  action=allow

# Allow localhost for local admin access (MySQL Workbench, CLI)
netsh advfirewall firewall add rule `
  name="MySQL Allow localhost" `
  protocol=TCP dir=in localport=3306 `
  remoteip=127.0.0.1 `
  action=allow
```

Verify:

```powershell
netsh advfirewall firewall show rule name="MySQL Allow app-server"
```

---

## 5. Proxmox Node — Startup Order

**Why:** If the Proxmox node reboots and all VMs start simultaneously, app-server
may attempt Laravel's database connections before MariaDB or PostgreSQL have finished
initializing. Laravel's connection pool fails fast — it does not retry on startup.
The result is a broken app that appears to be running but serves 500 errors until
manually restarted. Enforcing a startup order and adding a delay on app-server
eliminates this race condition.

| DBMS | Start at Boot | Startup Order | Startup Delay |
|---|---|---|---|
| MariaDB (100.78.124.25) | Yes | 1 | 0s |
| PostgreSQL (100.113.234.24) | Yes | 2 | 0s |
| Laravel app (100.100.123.90) | Yes | 3 | 30s |

The 30-second delay on app-server is a conservative buffer. MariaDB and PostgreSQL
typically initialize in under 10 seconds, but the buffer accounts for slow disk I/O
after a hard shutdown (e.g. journal recovery on ext4/btrfs).

Configure in Proxmox UI: select each VM → **Options** → **Start/Shutdown order**.

---

## 6. Verification Checklist

Run these checks from app-server after all hardening is applied:

```bash
# All three DBs should respond
mysql -h 100.78.124.25 -u workshop_2 -pworkshop_2 -e "SELECT 'MariaDB ok';"
mysql -h 100.68.235.121 -u workshop_2 -pworkshop_2 -e "SELECT 'MySQL ok';"
PGPASSWORD=workshop_2 psql -h 100.113.234.24 -U workshop_2 -d workshop_2 -c "SELECT 'PostgreSQL ok';"

# App should still serve HTTP
curl -s -o /dev/null -w "%{http_code}" http://localhost/
# Expected: 200

# UFW rules on app-server
sudo ufw status verbose
```

To confirm the firewall is actually blocking unauthorized access, attempt a
connection from a machine that is NOT app-server (e.g. from msi directly to
workshop-2's MariaDB port). It should time out or be refused.

---

## 7. Summary — What Each Measure Protects Against

| Hardening Measure | Threat Mitigated |
|---|---|
| `bind-address` = Tailscale IP | DB port invisible on LAN/public interfaces; only reachable inside the encrypted WireGuard mesh |
| UFW / Windows Firewall allow-list | Second independent enforcement layer; blocks all Tailscale peers except app-server from reaching DB ports |
| `pg_hba.conf` restricted to `100.100.123.90/32` | Third PostgreSQL-layer check; rejects auth attempts from any IP not explicitly listed, even if they pass the firewall |
| `scram-sha-256` in pg_hba.conf | Prevents offline cracking of captured authentication challenges (unlike `md5`) |
| `systemctl enable` on all DB services | Services restart automatically after a VM reboot or power cut, no manual login required |
| Proxmox "Start at boot" on all VMs | VMs restart automatically when the Proxmox node itself recovers from a power cut |
| Proxmox startup order (DBs before app, 30s delay) | Eliminates race condition where app-server connects before DB engines finish initializing |
| MySQL `Automatic` Windows service | MySQL restarts on Windows boot without user login, same as `systemctl enable` on Linux |
| Windows Firewall block-all + allow-specific | Provides the same layered protection as UFW for the Windows-hosted MySQL instance |
