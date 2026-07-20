# Terraform — Proxmox VM Provisioning

## Overview

Terraform provisions 4 VMs on a single Proxmox node using the `bpg/proxmox` provider.
Each VM is cloned from a Ubuntu 24.04 cloud-init template and automatically joins Tailscale on first boot.

### VMs managed by Terraform

| VM ID | Name | Role |
|---|---|---|
| 201 | app-server | Laravel app (PHP + Nginx + Composer) |
| 204 | linux-mysql | MySQL 8.0 |
| 205 | linux-mariadb | MariaDB |
| 206 | linux-postgres | PostgreSQL |

> IDs start at 201 (not 101) because 101–107 are pre-existing manual VMs kept as backups.
> VM names match the Ansible inventory and Tailscale MagicDNS hostnames exactly — no prefix.

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

```bash
cd infrastructure/terraform

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
