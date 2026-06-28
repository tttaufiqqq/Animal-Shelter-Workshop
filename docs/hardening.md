# Server Hardening Guide

## Overview

This document covers hardening all four machines in the distributed architecture
to ensure services survive VM shutdowns, Proxmox power cuts, and restarts without
manual intervention. It also restricts each database server to listen only on its
Tailscale interface.

### Machines

| Machine | Tailscale IP | OS | DB Engine | DB Port |
|---|---|---|---|---|
| app-server | 100.100.123.90 | Ubuntu 24.04 (Proxmox VM) | — | — |
| workshop-2 | 100.78.124.25 | Ubuntu 24.04 (Proxmox VM) | MariaDB | 3306 |
| workshop-postgres | 100.113.234.24 | Ubuntu (Proxmox VM) | PostgreSQL | 5432 |
| msi | 100.68.235.121 | Windows 11 (physical) | MySQL 9.5 | 3306 |

### Principle

Each database server should:
1. Only listen on its Tailscale IP (not `0.0.0.0`)
2. Only accept DB connections from app-server (`100.100.123.90`)
3. Start automatically after a reboot or power restoration

---

## 1. app-server (Ubuntu 24.04 — Proxmox VM)

### Auto-start on Boot (Already Done)

```bash
sudo systemctl enable nginx
sudo systemctl enable php8.3-fpm
```

Both are already enabled and will start automatically after a VM boot.

### Firewall (UFW)

UFW is enabled and configured. Rules applied:

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp   # SSH (Tailscale access)
sudo ufw allow 80/tcp   # HTTP
sudo ufw allow 443/tcp  # HTTPS (when SSL is configured)
sudo ufw enable
```

Verify:

```bash
sudo ufw status verbose
```

### File Permissions After Reboot

If storage permissions break after reboot (nginx 403 errors), re-apply:

```bash
sudo chown -R taufiq:www-data ~/Animal-Shelter-Workshop/storage ~/Animal-Shelter-Workshop/bootstrap/cache
chmod -R 775 ~/Animal-Shelter-Workshop/storage ~/Animal-Shelter-Workshop/bootstrap/cache
chmod o+x /home/taufiq
```

### Proxmox VM — Auto-start

In Proxmox web UI:
- Select the `app-server` VM → Options → Start at boot → **Yes**
- Start/Shutdown order: set a delay so DB VMs start before app-server

---

## 2. workshop-2 (Ubuntu 24.04 — MariaDB)

SSH into workshop-2 and run all of the following.

### 2.1 Bind MariaDB to Tailscale IP Only

Find the config file:

```bash
grep -r "bind-address" /etc/mysql/ 2>/dev/null
# Typically: /etc/mysql/mariadb.conf.d/50-server.cnf
```

Edit the config:

```bash
sudo nano /etc/mysql/mariadb.conf.d/50-server.cnf
```

Change or add under `[mysqld]`:

```ini
bind-address = 100.78.124.25
```

This stops MariaDB from listening on `0.0.0.0` (all interfaces). Only the Tailscale
interface is exposed.

### 2.2 Enable Auto-start on Boot

```bash
sudo systemctl enable mariadb
sudo systemctl is-enabled mariadb   # should print: enabled
```

### 2.3 Firewall (UFW)

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp comment 'SSH'

# Allow MariaDB only from app-server Tailscale IP
sudo ufw allow from 100.100.123.90 to any port 3306 proto tcp comment 'MySQL from app-server'

# Allow Tailscale management traffic
sudo ufw allow in on tailscale0

sudo ufw --force enable
sudo ufw status verbose
```

### 2.4 Restart and Verify

```bash
sudo systemctl restart mariadb
sudo systemctl status mariadb

# Verify it's listening on the right address
ss -tlnp | grep 3306
# Expected output: 100.78.124.25:3306
```

### 2.5 Proxmox VM — Auto-start

In Proxmox web UI:
- Select the `workshop-2` VM → Options → Start at boot → **Yes**
- Set startup order before app-server

---

## 3. workshop-postgres (Ubuntu — PostgreSQL)

SSH into workshop-postgres and run all of the following.

### 3.1 Bind PostgreSQL to Tailscale IP Only

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

This stops PostgreSQL from listening on all interfaces.

### 3.2 Restrict pg_hba.conf to app-server Only

```bash
sudo nano /etc/postgresql/16/main/pg_hba.conf
```

Ensure only app-server can connect remotely. Add/keep only:

```
# TYPE  DATABASE  USER        ADDRESS              METHOD
local   all       all                              peer
host    all       workshop_2  100.100.123.90/32    scram-sha-256
```

Remove or comment out any `host all all 0.0.0.0/0` lines.

### 3.3 Enable Auto-start on Boot

```bash
sudo systemctl enable postgresql
sudo systemctl is-enabled postgresql   # should print: enabled
```

### 3.4 Firewall (UFW)

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp comment 'SSH'

# Allow PostgreSQL only from app-server Tailscale IP
sudo ufw allow from 100.100.123.90 to any port 5432 proto tcp comment 'PostgreSQL from app-server'

# Allow Tailscale management traffic
sudo ufw allow in on tailscale0

sudo ufw --force enable
sudo ufw status verbose
```

### 3.5 Restart and Verify

```bash
sudo systemctl restart postgresql
sudo systemctl status postgresql

# Verify listening address
ss -tlnp | grep 5432
# Expected: 100.113.234.24:5432
```

### 3.6 Proxmox VM — Auto-start

In Proxmox web UI:
- Select the `workshop-postgres` VM → Options → Start at boot → **Yes**
- Set startup order before app-server

---

## 4. msi (Windows 11 — MySQL 9.5)

msi is a physical Windows machine. It does not depend on Proxmox, so power-cut
resilience is handled by Windows itself (auto-start service).

### 4.1 MySQL Windows Service — Auto-start (Already Set)

The `MySQL95` Windows service is already set to `StartType: Automatic`.
It will start automatically when Windows boots.

Verify via PowerShell (run as Administrator):

```powershell
Get-Service -Name 'MySQL95' | Select-Object Name, Status, StartType
```

### 4.2 Bind MySQL to Tailscale IP Only

Open `C:\ProgramData\MySQL\MySQL Server 9.5\my.ini` as Administrator.

Under the `[mysqld]` section, after the `port=3306` line, add:

```ini
# Bind only to Tailscale IP — prevents exposure on LAN/public interfaces
bind-address=100.68.235.121
```

Restart MySQL service (as Administrator in PowerShell):

```powershell
Restart-Service -Name 'MySQL95'
```

Verify:

```powershell
mysql -u root -ppassword -e "SHOW VARIABLES LIKE 'bind_address';"
# Expected: bind_address = 100.68.235.121
```

### 4.3 Windows Firewall — Restrict Port 3306

Run in PowerShell as Administrator:

```powershell
# Block all inbound MySQL by default
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

# Allow localhost (for local admin access)
netsh advfirewall firewall add rule `
  name="MySQL Allow localhost" `
  protocol=TCP dir=in localport=3306 `
  remoteip=127.0.0.1 `
  action=allow
```

Verify existing rules:

```powershell
netsh advfirewall firewall show rule name="MySQL Allow app-server"
```

---

## 5. Proxmox Node Startup Order

To ensure DB VMs are ready before the app-server connects, configure startup
ordering in Proxmox for each VM:

| VM | Start at Boot | Startup Order | Startup Delay |
|---|---|---|---|
| workshop-2 | Yes | 1 | 0s |
| workshop-postgres | Yes | 2 | 0s |
| app-server | Yes | 3 | 30s |

The 30-second delay on app-server gives MariaDB and PostgreSQL time to fully
initialize before Laravel attempts database connections on first request.

Configure in Proxmox UI: VM → Options → Start/Shutdown order.

---

## 6. Verification Checklist

After applying all hardening, run these checks from app-server:

```bash
# --- From app-server (100.100.123.90) ---

# MariaDB (workshop-2) — should connect
mysql -h 100.78.124.25 -u workshop_2 -pworkshop_2 -e "SELECT 'workshop-2 ok';"

# MySQL (msi) — should connect
mysql -h 100.68.235.121 -u workshop_2 -pworkshop_2 -e "SELECT 'msi ok';"

# PostgreSQL (workshop-postgres) — should connect
PGPASSWORD=workshop_2 psql -h 100.113.234.24 -U workshop_2 -d workshop_2 -c "SELECT 'workshop-postgres ok';"

# App should still be serving HTTP
curl -s -o /dev/null -w "%{http_code}" http://localhost/
# Expected: 200

# UFW status
sudo ufw status verbose
```

Also verify each DB server is NOT reachable on port 3306/5432 from a machine
that is not app-server (confirm the firewall is blocking correctly).

---

## 7. What Each Change Protects Against

| Threat | Protection |
|---|---|
| VM reboot / power cut | `systemctl enable` + Proxmox "Start at boot" |
| App-server restarts before DB | Proxmox startup order with 30s delay |
| DB port exposed on all interfaces | `bind-address` restricted to Tailscale IP |
| Unauthorized DB connections | Firewall (UFW / Windows Firewall) limits port to `100.100.123.90` only |
| Broad `pg_hba.conf` access | Restrict to `100.100.123.90/32` only |
| Windows MySQL exposed on LAN | Windows Firewall block-all + allow-specific rules |
