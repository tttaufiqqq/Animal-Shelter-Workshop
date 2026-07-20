# Infrastructure as Code — Terraform + Ansible

## Why we use this approach

This project runs a distributed database architecture across 6 machines on a single Proxmox host:
one Laravel app-server, two MySQL hosts (shelter, animals), two MariaDB hosts (reporting, booking),
and one PostgreSQL host (users) — see CLAUDE.md's Server Topology table. **Only 4 of these 6 are
Terraform-managed VMs** (app-server, the original linux-mysql, linux-mariadb, linux-postgres); the
other two (`linux-mysql-2`, `linux-mariadb-2`) are Proxmox **CTs** (containers) created later, when
`shelter`+`animals` and `reporting`+`booking` were each split off a shared physical host onto their
own dedicated machine (2026-07-20 — see `oracle-db-learning-proxmox/docs/12-mysql-shelter-animals-split/`
and `docs/13-mariadb-reporting-booking-split/`). They were provisioned directly via Proxmox/`pct`,
not `terraform apply`, and are configured by their own Ansible playbooks same as everything else —
Terraform's VM count below is accurate for what Terraform itself manages, but don't read it as the
total machine count for the project.

Without IaC, setting up this environment means manually clicking through Proxmox UI, SSHing into each VM,
running commands by hand, and hoping you remember every step when you need to rebuild it.
That works once. It breaks the second time — wrong config, forgotten step, different package version.

**Terraform** solves the creation problem.
It talks to the Proxmox API and creates all 4 of its VMs from a single `terraform apply`.
Every VM is defined in code: its ID, CPU, RAM, disk, network, and the cloud-init script that runs on first boot.
If a VM is deleted or corrupted, `terraform apply` recreates it identically.
The state of what exists is tracked in `terraform.tfstate` — Terraform always knows what it created.
(The 2 CTs above aren't part of this state at all — rebuilding them means re-running their Ansible
playbook against a manually-created CT, not `terraform apply`.)

**Ansible** solves the configuration problem.
After Terraform creates its 4 VMs and they join Tailscale, Ansible SSHes into each one — plus the 2
manually-created CTs — and installs the right software: MySQL on linux-mysql/linux-mysql-2, MariaDB
on linux-mariadb/linux-mariadb-2, PostgreSQL on linux-postgres, PHP + Nginx + Composer on
app-server. It also creates the `workshop_2_prod` database and user on each of the 5 DB servers (a
`workshop_2_dev` database/user also exists on every server for local development, but that one isn't
Ansible-managed — see CLAUDE.md's Database Connection Mapping), sets the correct permissions, and
configures remote access — exactly as defined in the playbooks. Running the same playbook twice
produces the same result (idempotent).

**The split between them is deliberate:**
- Terraform owns what exists, for the 4 VMs it manages (disks, network)
- Ansible owns what's inside every one of the 6 machines (packages, databases, config files)

This means if you only change a database config, you run Ansible — no need to touch Terraform.
If you need another Terraform-managed VM, add it to Terraform — Ansible picks it up automatically
via inventory. A manually-created CT (like the 2 above) just needs its own Ansible playbook and an
`inventory.yml` entry; Terraform is never involved for those.

**Tailscale is the network layer.**
Every machine — Terraform VM or manually-created CT — joins the Tailscale network on first boot
(via cloud-init for the VMs). This means they can all talk to the Laravel app and to each other
using stable hostnames (`linux-mysql`, `linux-mariadb`, `linux-mysql-2`, `linux-mariadb-2`, etc.)
regardless of what local IP they get. No port forwarding, no VPN config, no firewall rules to
maintain.

---

## Ansible — Configuration Management

Ansible runs from your WSL Ubuntu instance and configures all 6 machines (Terraform's 4 VMs plus
the 2 manually-created CTs). No agent or extra VM is needed — it connects over SSH via Tailscale.

**SSH usernames are not uniform across these 6 machines** — cloud-init creates a `workshop` user on
the 4 Terraform VMs, but in practice each machine ended up with its own convention over time
(`workshop-2` for the reporting host, `workshop-mysql` for shelter, `workshop-postgres` for users,
`linux-mysql-2`/`linux-mariadb-2` matching their own hostname for the 2 CTs, and `taufiq` for
app-server specifically — see below). `ssh workshop@<host>` will fail on most of these; the actual
per-host username is recorded in `~/.ssh/config` (Host blocks with an explicit `User` line) — check
that file before assuming a username, don't guess from this doc.

## One-time WSL setup

```bash
# 1. Install Ansible and pip dependencies
sudo apt update && sudo apt install -y ansible python3-pip

# 2. Install community collections (MySQL, PostgreSQL, Vault modules) + the
#    Vault Python client the hashi_vault collection needs
cd /mnt/c/Users/taufi/Documents/Dev/Animal-Shelter-Workshop/infrastructure/ansible
ansible-galaxy collection install -r requirements.yml
pip3 install hvac

# 3. Set up your SSH key in WSL
#    Option A: generate a new key
ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519

#    Option B: copy your existing Windows key into WSL
cp /mnt/c/Users/taufi/.ssh/id_ed25519 ~/.ssh/
cp /mnt/c/Users/taufi/.ssh/id_ed25519.pub ~/.ssh/
chmod 600 ~/.ssh/id_ed25519

# 4. Make sure the PUBLIC KEY in ~/.ssh/id_ed25519.pub matches what you set
#    in terraform.tfvars → ssh_public_key before running terraform apply.
#    If they don't match, Ansible won't be able to SSH in.
```

## Workflow after `terraform apply`

```
terraform apply
    └── Terraform's 4 VMs created and boot
            └── cloud-init runs:
                    ├── creates 'workshop' user with your SSH key
                    └── joins Tailscale (hostname = VM name)

# The 2 manually-created CTs (linux-mysql-2, linux-mariadb-2) aren't part of
# this flow at all — they're created directly via Proxmox/pct, then given
# their own SSH user matching their hostname, then configured by Ansible
# exactly like the Terraform VMs are.

# Wait ~60s for cloud-init to finish, then verify connectivity (check
# ~/.ssh/config for the real per-host username first — see above):
tailscale status                              # confirm all 6 machines appear
ssh linux-mysql "echo ok"                     # uses ~/.ssh/config's mapped user

# app-server.yml deploys as 'taufiq', not 'workshop' — it targets the real,
# hand-configured box, which has a 'taufiq' user. A truly fresh Terraform VM
# only gets 'workshop' from cloud-init, so app-server.yml won't run to
# completion against one until a 'taufiq' user exists there too. See
# docs/09-production-hardening.md's "Known follow-ups".

# Every playbook that reads asw_secrets (all 5 DB playbooks + app-server.yml)
# needs a scoped Vault AppRole in the environment first — see
# oracle-db-learning-proxmox/docs/11-vault-approle-app-integration/ for how
# asw-deploy was created. Never use the lab's Vault root token here.
export VAULT_ROLE_ID="<asw-deploy role_id>"
export VAULT_SECRET_ID="<asw-deploy secret_id>"

# Then run Ansible
cd /mnt/c/Users/taufi/Documents/Dev/Animal-Shelter-Workshop/infrastructure/ansible

# Provision all 6 machines at once (5 DB playbooks + app-server, in
# dependency order — see site.yml)
ansible-playbook playbooks/site.yml

# Or provision a single machine
ansible-playbook playbooks/linux-mysql.yml
ansible-playbook playbooks/linux-mysql-2.yml
ansible-playbook playbooks/linux-mariadb.yml
ansible-playbook playbooks/linux-mariadb-2.yml
ansible-playbook playbooks/linux-postgres.yml
ansible-playbook playbooks/app-server.yml
```

## Inventory — updating Tailscale IPs

If Tailscale MagicDNS is enabled, hostnames resolve automatically and `inventory.yml` works as-is.

If not, get the Tailscale IPs after `terraform apply`:
```bash
tailscale status
```

Then edit `inventory.yml` and replace `ansible_host` values with the actual IPs:
```yaml
linux-mysql:
  ansible_host: 100.x.x.x   # from tailscale status
```

## What each playbook does

| Playbook | Host | Installs / Does |
|---|---|---|
| `linux-mysql.yml` | linux-mysql | MySQL 8.0, creates workshop_2_prod DB + user, enables remote + triggers, UFW |
| `linux-mysql-2.yml` | linux-mysql-2 | MySQL 8.0, creates workshop_2_prod DB + user, enables remote + triggers, UFW |
| `linux-mariadb.yml` | linux-mariadb | MariaDB, creates workshop_2_prod DB + user, enables remote, UFW |
| `linux-mariadb-2.yml` | linux-mariadb-2 | MariaDB, creates workshop_2_prod DB + user, enables remote, UFW |
| `linux-postgres.yml` | linux-postgres | PostgreSQL, creates workshop_2_prod DB + user, pg_hba + schema grants, UFW |
| `app-server.yml` | app-server | PHP 8.3 + extensions, Nginx, Node 20, Composer, UFW; clones repo to `/home/taufiq/Animal-Shelter-Workshop`, deploys `.env` from Vault (`asw_secrets`, re-rendered every run), builds frontend assets, runs `migrate --force` + a first-deploy-only `db:seed` — **never** `db:fresh-all` (see `docs/09-production-hardening.md`) |
| `site.yml` | all 6 | `import_playbook`s the 5 DB playbooks above then app-server.yml, in that order |

## Verifying DB setup

Usernames below match `~/.ssh/config`'s actual mapping — verify against that file, don't assume:

```bash
# MySQL
ssh linux-mysql "mysql -u workshop_2_prod -pworkshop_2_prod -e 'SHOW DATABASES;'"
ssh linux-mysql-2 "mysql -u workshop_2_prod -pworkshop_2_prod -e 'SHOW DATABASES;'"

# MariaDB
ssh linux-mariadb "mysql -u workshop_2_prod -pworkshop_2_prod -e 'SHOW DATABASES;'"
ssh linux-mariadb-2 "mysql -u workshop_2_prod -pworkshop_2_prod -e 'SHOW DATABASES;'"

# PostgreSQL
ssh linux-postgres "psql -U workshop_2_prod -d workshop_2_prod -c '\l'"

# PHP on app-server
ssh linux-app-server "php -v && nginx -v && composer --version"
```

## Full infrastructure flow

```
1. terraform init && terraform apply        → Terraform's 4 VMs created on Proxmox
   (the 2 manually-created CTs, linux-mysql-2/linux-mariadb-2, already exist
   separately — see "Why we use this approach" above)
2. VMs boot, cloud-init joins Tailscale     → all 6 machines appear in tailscale status
3. ansible-playbook playbooks/site.yml      → software installed, DBs configured,
                                               app deployed, migrations + seed run
```

`app-server.yml` handles the full application deployment end-to-end:
- Clones the repo to `/home/taufiq/Animal-Shelter-Workshop` (as `taufiq` user)
- Deploys `.env` from `templates/env-app.j2`, sourcing every credential (5 DB passwords,
  `APP_KEY`, Cloudinary, ToyyibPay, SMTP) from Vault via `asw_secrets` — re-rendered on
  every run, not just the first
- Runs `composer install`, `npm ci && npm run build`
- Deploys the Nginx site config from `templates/nginx-app.conf.j2` (plain HTTP on `:80` —
  TLS is terminated by the Cloudflare Tunnel, not certbot, unless `-e use_certbot=true`
  is passed explicitly)
- Runs `migrate --force` (always, idempotent) and `db:seed --force` (first deploy only,
  gated on `storage/.provisioned`) — never `db:fresh-all`

After the first run, re-running `ansible-playbook playbooks/app-server.yml` is safe — all tasks are idempotent, including `.env`: it re-renders from Vault every time but produces the same content unless a secret actually changed.
