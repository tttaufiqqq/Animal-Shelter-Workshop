# Database Architecture

## Overview

This application uses a **heterogeneous distributed database** spread across three physical
servers connected via a Tailscale VPN mesh. There is no shared database server — each module
owns its data on an independent machine with a different DB engine. The Laravel application
running on `app-server` opens five named connections and routes queries explicitly per module.

`app-server`, `workshop-2` (MariaDB), and `workshop-postgres` are not dedicated to this project —
they're VMs on a shared personal Proxmox homelab that also runs Oracle, MySQL, SQL Server, and
MongoDB instances for other learning projects. The host inventory, how each VM was provisioned,
and the Tailscale/DNS mesh all three ride on are documented from the infrastructure side in
[taufiq's homelab repo](https://github.com/tttaufiqqq/oracle-db-linux-proxmox) — this doc only
covers the application's view of those same three machines. `msi` is a separate physical laptop,
not part of that homelab's Proxmox inventory.

---

## Server Topology

```
app-server (100.100.123.90)   <-- Laravel application host
     |
     |-- [Tailscale VPN] --+
                           |
         workshop-2 (100.78.124.25)      MariaDB 10.x
         msi       (100.68.235.121)      MySQL 8.x
         workshop-postgres (100.113.234.24)  PostgreSQL 15
```

All three DB servers expose their native port (3306 / 5432) directly on the Tailscale
interface. No SSH tunnels are used. SSH keys are pre-configured on all machines.

---

## Connection Map

| Laravel connection | Module owner | DB engine | Host (Tailscale) | Database |
|---|---|---|---|---|
| `reporting` | Eilya — Stray Reporting | MariaDB | 100.78.124.25 | workshop_2 |
| `booking` | Danish — Booking & Adoption | MariaDB | 100.78.124.25 | workshop_2 |
| `shelter` | Atiqah — Shelter Management | MySQL | 100.68.235.121 | workshop_2 |
| `animals` | Shafiqah — Animal & Medical | MySQL | 100.68.235.121 | workshop_2 |
| `users` | Taufiq — User Management | PostgreSQL | 100.113.234.24 | workshop_2 |

`reporting` and `booking` share the same physical MariaDB server and the same database
(`workshop_2`), but they are separate Laravel connections with independent credentials and
PDO handles. This means no cross-schema JOINs are possible between them at the SQL level —
they must be resolved in application code like any other cross-DB pair.

`shelter` and `animals` share the same physical MySQL server and database name for the same
reason.

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

### `booking` connection (MariaDB — workshop-2)

| Table | Purpose |
|---|---|
| `booking` | Shelter visit appointments |
| `transaction` | Payment records for adoption fees |
| `adoption` | Final adoption records linking booking + transaction + animal |
| `visit_list` | A user's personal list of animals they want to visit |
| `visit_list_animal` | Pivot table (also called a junction or bridge table) — animals on a visit list |
| `animal_booking` | Pivot table — animals attached to a booking appointment |

### `shelter` connection (MySQL — msi)

| Table | Purpose |
|---|---|
| `section` | Physical sections of the shelter building |
| `slot` | Storage/housing slots within a section |
| `category` | Inventory item categories |
| `inventory` | Inventory items (food, medicine, supplies) |

### `animals` connection (MySQL — msi)

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
- `log_bin_trust_function_creators = 1` required on msi for trigger creation by non-SUPER user

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
