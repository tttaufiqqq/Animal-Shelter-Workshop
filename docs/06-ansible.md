# Infrastructure as Code — Terraform + Ansible

## Why we use this approach

This project runs a distributed database architecture across 4 VMs on a single Proxmox host.
Each VM has a specific role: one for the Laravel app, one for MySQL, one for MariaDB, one for PostgreSQL.

Without IaC, setting up this environment means manually clicking through Proxmox UI, SSHing into each VM,
running commands by hand, and hoping you remember every step when you need to rebuild it.
That works once. It breaks the second time — wrong config, forgotten step, different package version.

**Terraform** solves the creation problem.
It talks to the Proxmox API and creates all 4 VMs from a single `terraform apply`.
Every VM is defined in code: its ID, CPU, RAM, disk, network, and the cloud-init script that runs on first boot.
If a VM is deleted or corrupted, `terraform apply` recreates it identically.
The state of what exists is tracked in `terraform.tfstate` — Terraform always knows what it created.

**Ansible** solves the configuration problem.
After Terraform creates the VMs and they join Tailscale, Ansible SSHes into each one and installs
the right software: MySQL on linux-mysql, MariaDB on linux-mariadb, PostgreSQL on linux-postgres,
PHP + Nginx + Composer on app-server. It also creates the `workshop_2` database and user on each DB server,
sets the correct permissions, and configures remote access — exactly as defined in the playbooks.
Running the same playbook twice produces the same result (idempotent).

**The split between them is deliberate:**
- Terraform owns what exists (VMs, disks, network)
- Ansible owns what's inside (packages, databases, config files)

This means if you only change a database config, you run Ansible — no need to touch Terraform.
If you need a 5th VM, you add it to Terraform — Ansible picks it up automatically via inventory.

**Tailscale is the network layer.**
All VMs join the Tailscale network on first boot via cloud-init. This means the Proxmox VMs
can talk to the Laravel app and to each other using stable hostnames (`linux-mysql`, `linux-mariadb`, etc.)
regardless of what local IP they get. No port forwarding, no VPN config, no firewall rules to maintain.

---

## Ansible — Configuration Management

Ansible runs from your WSL Ubuntu instance and configures the 4 VMs after Terraform creates them.
No agent or extra VM is needed — it connects over SSH via Tailscale.

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
    └── VMs created and boot
            └── cloud-init runs:
                    ├── creates 'workshop' user with your SSH key
                    └── joins Tailscale (hostname = VM name)

# Wait ~60s for cloud-init to finish, then verify connectivity:
tailscale status                              # confirm all 4 VMs appear
ssh workshop@linux-mysql "echo ok"            # test SSH

# app-server.yml now deploys as 'taufiq', not 'workshop' (see the table
# below) — it targets the real, hand-configured box, which has a 'taufiq'
# user. A truly fresh Terraform VM only gets 'workshop' from cloud-init, so
# app-server.yml won't run to completion against one until a 'taufiq' user
# exists there too. See docs/09-production-hardening.md's "Known follow-ups".

# Every playbook that reads asw_secrets (the 3 DB playbooks + app-server.yml)
# needs a scoped Vault AppRole in the environment first — see
# oracle-db-learning-proxmox/docs/11-vault-approle-app-integration/ for how
# asw-deploy was created. Never use the lab's Vault root token here.
export VAULT_ROLE_ID="<asw-deploy role_id>"
export VAULT_SECRET_ID="<asw-deploy secret_id>"

# Then run Ansible
cd /mnt/c/Users/taufi/Documents/Dev/Animal-Shelter-Workshop/infrastructure/ansible

# Provision all 4 VMs at once
ansible-playbook playbooks/site.yml

# Or provision a single VM
ansible-playbook playbooks/linux-mysql.yml
ansible-playbook playbooks/linux-mariadb.yml
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
| `linux-mysql.yml` | linux-mysql | MySQL 8.0, creates workshop_2 DB + user, enables remote + triggers, UFW |
| `linux-mariadb.yml` | linux-mariadb | MariaDB, creates workshop_2 DB + user, enables remote, UFW |
| `linux-postgres.yml` | linux-postgres | PostgreSQL, creates workshop_2 DB + user, pg_hba + schema grants, UFW |
| `app-server.yml` | app-server | PHP 8.3 + extensions, Nginx, Node 20, Composer, UFW; clones repo to `/home/taufiq/Animal-Shelter-Workshop`, deploys `.env` from Vault (`asw_secrets`, re-rendered every run), builds frontend assets, runs `migrate --force` + a first-deploy-only `db:seed` — **never** `db:fresh-all` (see `docs/09-production-hardening.md`) |
| `site.yml` | all | Runs the 4 above in order |

## Verifying DB setup

```bash
# MySQL
ssh workshop@linux-mysql "mysql -u workshop_2 -pworkshop_2 -e 'SHOW DATABASES;'"

# MariaDB
ssh workshop@linux-mariadb "mysql -u workshop_2 -pworkshop_2 -e 'SHOW DATABASES;'"

# PostgreSQL
ssh workshop@linux-postgres "psql -U workshop_2 -d workshop_2 -c '\l'"

# PHP on app-server
ssh workshop@app-server "php -v && nginx -v && composer --version"
```

## Full infrastructure flow

```
1. terraform init && terraform apply        → VMs created on Proxmox
2. VMs boot, cloud-init joins Tailscale     → all 4 VMs appear in tailscale status
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
