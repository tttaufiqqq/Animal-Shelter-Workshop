# Terraform — Proxmox VM Provisioning

## Overview

Terraform manages the entire live Proxmox fleet now — both the disposable test loop below
(created fresh via `bpg/proxmox`, cloned from a Ubuntu 24.04 cloud-init template, joins Tailscale
on first boot) and the real production VMs/CTs (adopted via `terraform import`, never created
fresh — see the second table below). Only `opnsense` (the network's actual gateway) and the
stopped legacy VMs (102/103/107, plus template 9000) are deliberately left outside Terraform.

### Test loop (`vms.tf`, disposable, `test-` prefixed)

| VM ID | Name | Role |
|---|---|---|
| 201 | test-app-server | Laravel app (PHP + Nginx + Composer) |
| 204 | test-mysql | MySQL 8.0 |
| 205 | test-mariadb | MariaDB |
| 206 | test-postgres | PostgreSQL |

> IDs start at 201 (not 101) because 101–107 are pre-existing manual VMs kept as backups.
> These are proof-of-loop resources, torn down after each proof (see
> `docs/19-devops-practice/01` in the homelab meta-repo) — nothing here is meant to stay running.
> Keys are `test-`-prefixed specifically so `terraform state list` never reads ambiguously
> against the real production resources below, which share the same underlying roles.

### Real production fleet (`production-vms.tf` + `containers.tf`, adopted via `terraform import`)

| VM/CT ID | Name | Role | Type |
|---|---|---|---|
| 101 | app-server | Laravel app | VM |
| 104 | linux-mysql | `shelter` connection, MySQL 8.0 | VM |
| 105 | linux-mariadb | `reporting` connection, MariaDB | VM |
| 106 | linux-postgres | `users` connection, PostgreSQL | VM |
| 109 | linux-mini-io | MinIO — hosts this very Terraform state's S3 backend | VM |
| 112 | linux-mysql-2 | `animals` connection, MySQL 8.0 | CT |
| 113 | linux-mariadb-2 | `booking` connection, MariaDB | CT |
| 100 | linux-k3s | Stage 5 k3s node | CT |
| 108 | linux-mongodb | MongoDB | CT |
| 110 | linux-vault | Vault | CT |
| 111 | linux-gh-runner | CI runner | CT |
| 114 | linux-observability | Prometheus/Grafana/Loki/Alertmanager | CT |

Full drift/gotcha writeup for this batch: `docs/19-devops-practice/12` in the homelab meta-repo.

---

## One-time prerequisites

### 1. Proxmox API token

Proxmox UI → Datacenter → Permissions → API Tokens → Add

- User: `root@pam`
- Token name: `ansible` (or any name)
- Uncheck "Privilege Separation" so it inherits root permissions

Token format in `terraform.tfvars`:
```
proxmox_api_token = "root@pam!ansible=<uuid>"
```

### 2. Ubuntu 24.04 cloud-init template (VM 9000)

Run on the Proxmox host via SSH:

```bash
wget https://cloud-images.ubuntu.com/noble/current/noble-server-cloudimg-amd64.img \
  -O /tmp/ubuntu-24.04-cloud.img

qm create 9000 --name ubuntu-2404-template --memory 2048 --cores 2 \
  --net0 virtio,bridge=vmbr0 --ostype l26

qm importdisk 9000 /tmp/ubuntu-24.04-cloud.img local   # use 'local', not 'local-lvm'

qm set 9000 --scsihw virtio-scsi-pci --scsi0 local:9000/vm-9000-disk-0.raw
qm set 9000 --ide2 local:cloudinit
qm set 9000 --boot c --bootdisk scsi0
qm set 9000 --serial0 socket --vga serial0
qm template 9000
```

### 3. Enable Snippets on local storage

Proxmox UI → Datacenter → Storage → local → Edit → Content → check **Snippets**

Terraform uploads cloud-init YAML files as snippets. Without this, `apply` fails.

### 4. Tailscale reusable auth key

Tailscale admin → Settings → Keys → Generate auth key
- Reusable: **on**
- Ephemeral: **off**
- Expiry: 90 days

Paste the `tskey-auth-...` value into `terraform.tfvars`.

### 5. MinIO state backend credentials

State lives on the homelab's self-hosted MinIO (`linux-mini-io`), not a
local `.tfstate` file — see `main.tf`'s `backend "s3"` block. A
bucket-scoped MinIO user (`terraform-asw`, policy restricted to the
`animal-shelter-workshop-tfstate` bucket only — not the MinIO root
account) needs to already exist; if it doesn't, on `linux-mini-io`:

```bash
mc mb local/animal-shelter-workshop-tfstate
mc admin user add local terraform-asw '<generate-a-secret>'
mc admin policy create local terraform-asw-tfstate /path/to/policy.json
mc admin policy attach local terraform-asw-tfstate --user terraform-asw
```

Then export the credentials before any `terraform init`/`plan`/`apply` —
the backend block deliberately has no credentials in it:

```bash
export AWS_ACCESS_KEY_ID=terraform-asw
export AWS_SECRET_ACCESS_KEY=<the secret from mc admin user add>
```

---

## WSL setup (one-time)

```bash
# Install Terraform (Ubuntu 25.04 resolute — use snap, not apt)
sudo snap install terraform --classic

# Verify
terraform version
```

> The HashiCorp apt repo does not support Ubuntu 25.04 (resolute) yet. Snap is the correct install path.

---

## terraform.tfvars

Copy from example and fill in:

```bash
cd infrastructure/terraform
cp terraform.tfvars.example terraform.tfvars
nano terraform.tfvars
```

```hcl
proxmox_endpoint     = "https://100.97.8.93:8006"   # Proxmox Tailscale IP
proxmox_api_token    = "root@pam!ansible=<uuid>"
proxmox_ssh_user     = "root"
proxmox_ssh_password = "<proxmox root password>"
proxmox_node         = "taufiq"                      # node name from Proxmox UI top-left

vm_template_id = 9000
storage_pool   = "local"

ssh_public_key     = "<contents of ~/.ssh/id_ed25519.pub>"
tailscale_auth_key = "tskey-auth-..."
```

Key points:
- `proxmox_node` is the name shown in Proxmox UI sidebar (was `taufiq`, not `pve`)
- `storage_pool` is `local` (Directory type), not `local-lvm` (LVM-thin doesn't exist here)
- `ssh_public_key` must match the private key Ansible uses (`~/.ssh/id_ed25519`)
  — the key label `iphone-11-taufiq` was the Windows key copied into WSL

---

## Running Terraform

`linux-mini-io` (the MinIO VM state now lives on) isn't always running —
it's not part of the always-on core fleet. Check `qm status 109` on
Proxmox first; `qm start 109` if it's stopped, or every command below
fails to reach the backend at all.

```bash
cd infrastructure/terraform

export AWS_ACCESS_KEY_ID=terraform-asw
export AWS_SECRET_ACCESS_KEY=<the terraform-asw MinIO secret>

# Download provider (bpg/proxmox v0.111.0)
terraform init

# Preview what will be created — review before applying
terraform plan

# Create the VMs
terraform apply
```

`terraform apply` will:
1. SSH into Proxmox and upload 4 cloud-init YAML snippets to `local` storage
2. Clone VM 9000 four times (full clone, not linked)
3. Resize each disk to 20GB
4. Attach cloud-init config and set DHCP networking
5. Boot all 4 VMs — cloud-init runs on first boot, creates a `workshop` user, joins Tailscale
   (in practice, some of these VMs' SSH username has since diverged from the cloud-init default —
   `~/.ssh/config` is the actual source of truth for what to connect as today, not this doc)

---

## CI drift check (`.github/workflows/terraform-drift.yml`, Stage 3)

`terraform plan` also runs automatically — daily at 03:00 UTC, plus manual `workflow_dispatch` — on
`linux-gh-runner`, and posts its output to the run's job summary. It never runs `apply`: creating or
destroying real VMs/CTs unattended was judged too destructive to automate, so this job is
deliberately read-only. Its purpose is just to make drift visible without someone remembering to run
`terraform plan` locally and check.

This needs its own credentials on the runner, separate from everything deploy.yml uses:

- **`TF_VAULT_TOKEN`** — a distinct Vault token in `~/actions-runner/.env` (alongside the existing
  `VAULT_TOKEN`), scoped to a dedicated `asw-terraform-cd` policy that can only read
  `secret/asw-terraform-cd`. This path holds everything a `terraform plan` needs: all of
  `terraform.tfvars`' fields (including the Proxmox hypervisor's own root SSH password and API
  token) plus the MinIO backend's `terraform-asw` credentials. Kept in its own path/policy/token,
  never merged into `secret/asw-cd`, so a compromised app-deploy step can never read
  hypervisor-root-level credentials, and vice versa.
- **`vault-tf-token-renew.timer`** (`roles`-adjacent, defined in `playbooks/linux-gh-runner.yml`) —
  renews `TF_VAULT_TOKEN` daily, mirroring the existing `vault-token-renew.timer` pattern but pointed
  at the separate token.
- The workflow exports everything as `TF_VAR_<name>` env vars (Terraform's own convention for
  variable overrides) plus `AWS_ACCESS_KEY_ID`/`AWS_SECRET_ACCESS_KEY` for the MinIO backend — no
  `terraform.tfvars` file is ever written on the runner.

`terraform plan -detailed-exitcode` distinguishes "no drift" (0) from "plan errored" (1) from "changes
present" (2) — only exit code 1 fails the job. Exit code 2 (drift/pending changes found) is reported
in the summary, not treated as a CI failure, since the point is to surface it, not block on it.

---

## Lessons learned during setup

### Storage: `local` vs `local-lvm`

This Proxmox node has a single **Directory** storage called `local`, not an LVM-thin pool.
The example used `local-lvm` which doesn't exist — caused `qm importdisk` to fail and would
have caused `terraform apply` to fail.

Fix: set `storage_pool = "local"` in tfvars and `file_format = "qcow2"` in `vms.tf`
(Directory storage requires `qcow2`, not `raw`).

### cloud-init drive datastore

The `initialization` block in `bpg/proxmox` defaults `datastore_id` to `local-lvm`.
Must be explicitly set to `var.storage_pool` to match actual storage:

```hcl
initialization {
  datastore_id      = var.storage_pool   # ← required, otherwise defaults to local-lvm
  user_data_file_id = proxmox_virtual_environment_file.cloud_init[each.key].id
  ...
}
```

### Node name

The default in `terraform.tfvars.example` was `pve`. The actual node name is `taufiq`
(visible in Proxmox UI top-left under the datacenter tree).

### VM IDs 201–206 (not 101–106)

101, 104, 105, 106 already existed as manually created VMs.
Terraform would have failed with "VM ID already in use" if we used the same IDs.
Decision: use 200-series IDs for Terraform-managed VMs, keep 100-series as fallback backups.

### SSH key mismatch

WSL initially had a freshly generated key (`taufiq-ansible@MSI`).
The Windows SSH key (`iphone-11-taufiq`) was then copied into WSL, overwriting the generated key.
The `ssh_public_key` in `terraform.tfvars` must match the **private key that Ansible will use**,
which is whatever is at `~/.ssh/id_ed25519` on the WSL control node.

### Terraform install on Ubuntu 25.04

The HashiCorp apt repo (`apt.releases.hashicorp.com`) does not have packages for Ubuntu 25.04
(codename `resolute`). Running the standard HashiCorp install script fails silently.
Fix: `sudo snap install terraform --classic`

### Importing the real production VMs/CTs: three `proxmox_virtual_environment_vm` defaults that silently diverge from reality

Found while adopting `app-server`/`linux-mysql`/`linux-mariadb`/`linux-postgres`/`linux-mini-io`
into `production-vms.tf` (full writeup: `docs/19-devops-practice/12` in the homelab meta-repo).
Unlike the CT resource (`containers.tf`), the VM resource has its own set of attributes that
default away from an imported host's real state unless declared explicitly:

- `on_boot` and `started` both default to `true` when undeclared — none of the 4 DB/app VMs
  actually have `onboot` set in real life, so leaving these undeclared would have had Terraform
  try to enable auto-start on every one of them on the very first `apply`.
- `scsi_hardware` defaults to `"virtio-scsi-pci"`; every real host here actually uses
  `"virtio-scsi-single"`.
- `cdrom` can never be read back on import (same category as the CT resource's
  `operating_system.template_file_id` — Proxmox just doesn't persist it in a form the provider's
  read populates into state), so it **always** shows as an add on the first `plan` after import,
  regardless of whether it matches reality. Declaring it explicitly with `interface = "ide2"`
  (the real one — the provider's own default is `"ide3"`, which doesn't match and would leave a
  second, unrelated cdrom device rather than manage the real one) makes that one-time apply a
  genuine no-op against the API, not a real change — confirmed via `qm config` byte-identical
  before/after on every host.

### A stopped container/VM must be imported with `started = false`

`linux-k3s` (CT 100) and `linux-mongodb` (CT 108) were genuinely powered off at import time.
Both `containers.tf` and `production-vms.tf`'s `started` attribute must match the real current
power state, not just assume `true` — otherwise the very first `apply` boots a host that was
deliberately left off.

---

## After `terraform apply`

```bash
# Confirm VMs appear on Tailscale
tailscale status

# Wait ~60s for cloud-init, then test SSH — check ~/.ssh/config for the real
# per-host username first (confirmed live: "workshop" fails on linux-mysql,
# linux-mariadb, and linux-postgres today — the actual usernames are
# workshop-mysql / workshop-2 / workshop-postgres respectively)
ssh linux-mysql "echo ok"

# Then run Ansible to install software
cd infrastructure/ansible
ansible-playbook playbooks/site.yml
```
