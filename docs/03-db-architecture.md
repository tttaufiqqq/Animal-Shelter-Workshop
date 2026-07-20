# Database Architecture

## Overview

This application uses a **heterogeneous distributed database** spread across five physical
servers connected via a Tailscale VPN mesh. There is no shared database server — each module
owns its data on an independent machine with a different DB engine, and (since 2026-07-20) every
one of the five connections has its own dedicated physical machine — no machine hosts more than
one connection. The Laravel application running on `app-server` opens five named connections and
routes queries explicitly per module.

`app-server`, `workshop-2`, `linux-mariadb-2`, `linux-mysql`, `linux-mysql-2`, and
`workshop-postgres` are not dedicated to this project — they're VMs/CTs on a shared personal
Proxmox homelab that also runs Oracle, SQL Server, and MongoDB instances for other learning
projects. The host inventory, how each was provisioned, and the Tailscale/DNS mesh they all ride
on are documented from the infrastructure side in
[taufiq's homelab repo](https://github.com/tttaufiqqq/oracle-db-linux-proxmox) — this doc only
covers the application's view of those same machines. Two connection pairs used to share a
physical host and were split apart on 2026-07-20: `shelter`+`animals` shared MySQL on `msi` (a
separate physical laptop, not part of that homelab's Proxmox inventory), and `reporting`+
`booking` shared MariaDB on `workshop-2`. See the homelab repo's migration docs —
[`docs/12-mysql-shelter-animals-split/`](https://github.com/tttaufiqqq/oracle-db-learning-proxmox/blob/main/docs/12-mysql-shelter-animals-split/mysql-shelter-animals-split.md)
and
[`docs/13-mariadb-reporting-booking-split/`](https://github.com/tttaufiqqq/oracle-db-learning-proxmox/blob/main/docs/13-mariadb-reporting-booking-split/mariadb-reporting-booking-split.md)
— for why and how.

---

## Server Topology

```
app-server (100.100.123.90)   <-- Laravel application host
     |
     |-- [Tailscale VPN] --+
                           |
         workshop-2 (100.78.124.25)        MariaDB 10.x
         linux-mariadb-2 (100.97.35.29)    MariaDB 10.x
         linux-mysql (100.115.237.93)      MySQL 8.0
         linux-mysql-2 (100.123.221.89)    MySQL 8.0
         workshop-postgres (100.113.234.24)  PostgreSQL 15
```

All five DB servers expose their native port (3306 / 5432) directly on the Tailscale
interface. No SSH tunnels are used. SSH keys are pre-configured on all machines.

---

## Connection Map

| Laravel connection | Module owner | DB engine | Host (Tailscale) | Database |
|---|---|---|---|---|
| `reporting` | Eilya — Stray Reporting | MariaDB | 100.78.124.25 | workshop_2 |
| `booking` | Danish — Booking & Adoption | MariaDB | 100.97.35.29 | workshop_2 |
| `shelter` | Atiqah — Shelter Management | MySQL | 100.115.237.93 | workshop_2 |
| `animals` | Shafiqah — Animal & Medical | MySQL | 100.123.221.89 | workshop_2 |
| `users` | Taufiq — User Management | PostgreSQL | 100.113.234.24 | workshop_2 |

No native FK crosses either split pair — `reporting`↔`booking` or `shelter`↔`animals` (see
`docs/04-foreign-keys.md`) — which is what made splitting each pair onto separate physical
machines possible without any application code changes. Both pairs previously shared one
physical server each; each connection now has its own dedicated host.

All connections use credentials `workshop_2 / workshop_2`. Config lives in
`config/database.php` under the five named keys above.

---

## Table Ownership

### `reporting` connection (MariaDB — workshop-2)

| Table | Purpose |
|---|---|
| `report` | Stray animal sighting reports submitted by users |
| `rescue` | Rescue operations assigned from reports |
| `image` | Images attached to reports, animals, or clinics |

### `booking` connection (MariaDB — linux-mariadb-2)

| Table | Purpose |
|---|---|
| `booking` | Shelter visit appointments |
| `transaction` | Payment records for adoption fees |
| `adoption` | Final adoption records linking booking + transaction + animal |
| `visit_list` | A user's personal list of animals they want to visit |
| `visit_list_animal` | Pivot table (also called a junction or bridge table) — animals on a visit list |
| `animal_booking` | Pivot table — animals attached to a booking appointment |

### `shelter` connection (MySQL — linux-mysql)

| Table | Purpose |
|---|---|
| `section` | Physical sections of the shelter building |
| `slot` | Storage/housing slots within a section |
| `category` | Inventory item categories |
| `inventory` | Inventory items (food, medicine, supplies) |

### `animals` connection (MySQL — linux-mysql-2)

| Table | Purpose |
|---|---|
| `animal` | Core animal records |
| `animal_profile` | Matching attributes for the adoption algorithm |
| `clinic` | Veterinary clinics |
| `vet` | Veterinarians linked to clinics |
| `medical` | Medical treatment records for animals |
| `vaccination` | Vaccination records for animals |

### `users` connection (PostgreSQL — workshop-postgres)

| Table | Purpose |
|---|---|
| `users` | User accounts |
| `adopter_profile` | Adopter matching attributes for the adoption algorithm |
| `roles` / `permissions` | Spatie permission tables |
| `model_has_roles` | Role assignments |
| `audit_logs` | User action audit trail |

---

## Database-Level Features per Engine

### MariaDB (`reporting` + `booking`)

- Stored procedures with `IN`/`OUT` parameters
- Triggers (`BEFORE INSERT`, `BEFORE UPDATE`, `AFTER INSERT`, `AFTER UPDATE`, `BEFORE DELETE`)
- `SIGNAL SQLSTATE '45000'` for application-level constraint errors
- Session variables (`@o_var`) for returning OUT parameter values to PHP

### MySQL (`shelter` + `animals`)

- Stored procedures
- Triggers with same syntax as MariaDB
- `log_bin_trust_function_creators = 1` required on both `linux-mysql` and `linux-mysql-2` for
  trigger creation by a non-SUPER user — set automatically by
  `infrastructure/ansible/playbooks/linux-mysql.yml` / `linux-mysql-2.yml`

### PostgreSQL (`users`)

- Regular views (`CREATE OR REPLACE VIEW`)
- Materialized views (`CREATE MATERIALIZED VIEW`) for dashboard statistics
- `REFRESH MATERIALIZED VIEW` called via a PL/pgSQL helper function
- `STRING_AGG` for role aggregation
- `FILTER (WHERE ...)` aggregate syntax
- `::INTEGER`, `::NUMERIC` casts

---

## Migration Execution

Migrations are run on `app-server`. Each migration file explicitly specifies
`Schema::connection('...')` or `DB::connection('...')` — no migration targets the default
connection. Laravel's migration runner issues one DDL statement per connection per migration;
the five connections are all open simultaneously during `php artisan migrate`.

```bash
# Run all migrations
php artisan migrate

# Fresh wipe + seed
php artisan db:fresh-all --seed
```

The migration table itself lives on the default connection (SQLite locally, or whichever
`DB_CONNECTION` is set to in `.env`).
