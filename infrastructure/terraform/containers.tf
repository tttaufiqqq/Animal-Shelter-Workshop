# linux-mysql-2 (CT 112) and linux-mariadb-2 (CT 113) are LIVE PRODUCTION
# database hosts (the `animals` and `booking` connections — see CLAUDE.md's
# Server Topology) that already existed, hand-provisioned, before Terraform
# managed anything. These blocks were written to match their exact current
# config and adopted via `terraform import`, never created fresh — do not
# `terraform destroy` either of these without understanding that it deletes
# a real database with real data.
#
# Both containers have two custom raw LXC config lines (visible via
# `pct config 112` / `pct config 113` on the Proxmox host) that make
# Tailscale's TUN device work inside an unprivileged container:
#   lxc.cgroup2.devices.allow: c 10:200 rwm
#   lxc.mount.entry: /dev/net/tun dev/net/tun none bind,create=file
# bpg/proxmox's `device_passthrough` block does NOT represent these — it
# maps to Proxmox's newer, different `devN:` native passthrough mechanism,
# not the legacy raw lines. Declaring `device_passthrough` here would add a
# second, redundant passthrough path rather than manage the existing one.
# The correct choice, validated against a disposable scratch container
# before touching either of these for real (`terraform plan` showed zero
# diff, and `pct config` was confirmed byte-identical before/after apply):
# leave device_passthrough undeclared entirely, so Terraform never touches
# those two lines at all.

resource "proxmox_virtual_environment_container" "linux_mysql_2" {
  node_name    = var.proxmox_node
  vm_id        = 112
  unprivileged = true
  started      = true
  start_on_boot = true

  cpu {
    cores = 2
  }

  memory {
    dedicated = 2048
    swap      = 512
  }

  disk {
    datastore_id = "local"
    size         = 20
  }

  network_interface {
    name    = "eth0"
    bridge  = "vmbr0"
    vlan_id = 20
  }

  initialization {
    hostname = "linux-mysql-2"

    dns {
      domain  = "local"
      servers = ["8.8.8.8"]
    }

    ip_config {
      ipv4 {
        address = "dhcp"
      }
    }
  }

  console {
    enabled   = true
    tty_count = 2
    type      = "tty"
  }

  features {
    nesting = true
  }

  operating_system {
    template_file_id = "local:vztmpl/ubuntu-24.04-standard_24.04-2_amd64.tar.zst"
    type              = "ubuntu"
  }

  # template_file_id isn't persisted by Proxmox after creation, so it always
  # reads back unset on an imported container — without this, every plan
  # would show a forced replacement. See the file-level comment above.
  lifecycle {
    ignore_changes = [operating_system]
  }

  timeout_clone  = 1800
  timeout_create = 1800
  timeout_delete = 60
  timeout_update = 1800
}

resource "proxmox_virtual_environment_container" "linux_mariadb_2" {
  node_name    = var.proxmox_node
  vm_id        = 113
  unprivileged = true
  started      = true
  start_on_boot = true

  cpu {
    cores = 2
  }

  memory {
    dedicated = 2048
    swap      = 512
  }

  disk {
    datastore_id = "local"
    size         = 20
  }

  network_interface {
    name    = "eth0"
    bridge  = "vmbr0"
    vlan_id = 20
  }

  initialization {
    hostname = "linux-mariadb-2"

    dns {
      domain  = "local"
      servers = ["8.8.8.8"]
    }

    ip_config {
      ipv4 {
        address = "dhcp"
      }
    }
  }

  console {
    enabled   = true
    tty_count = 2
    type      = "tty"
  }

  features {
    nesting = true
  }

  operating_system {
    template_file_id = "local:vztmpl/ubuntu-24.04-standard_24.04-2_amd64.tar.zst"
    type              = "ubuntu"
  }

  lifecycle {
    ignore_changes = [operating_system]
  }

  timeout_clone  = 1800
  timeout_create = 1800
  timeout_delete = 60
  timeout_update = 1800
}

# The five hosts below are Stage 1's "bring the rest of the fleet in" pass
# (docs/19-devops-practice/12 in the homelab meta-repo). All adopted via
# `terraform import`, never created fresh — same "prove zero drift on a
# scratch container first, then import for real" discipline as above.
# Per-host `started`/`start_on_boot`/network `firewall` values are copied
# exactly from each host's real `pct config` at import time, not assumed —
# linux-k3s and linux-mongodb were genuinely powered off when imported, and
# `firewall=1` is only present on some of these hosts' `net0`, not all.

# linux-k3s (CT 100) — Stage 5's k3s node. STOPPED at import time (not part
# of the always-on core fleet); `started = false` preserves that instead of
# booting it as a side effect of import.
resource "proxmox_virtual_environment_container" "linux_k3s" {
  node_name     = var.proxmox_node
  vm_id         = 100
  unprivileged  = true
  started       = false
  start_on_boot = true

  cpu {
    cores = 1
  }

  memory {
    dedicated = 3072
    swap      = 768
  }

  disk {
    datastore_id = "local"
    size         = 8
  }

  network_interface {
    name     = "eth0"
    bridge   = "vmbr0"
    vlan_id  = 30
    firewall = true
  }

  initialization {
    hostname = "linux-k3s"

    dns {
      servers = ["8.8.8.8"]
    }

    ip_config {
      ipv4 {
        address = "dhcp"
      }
    }
  }

  console {
    enabled   = true
    tty_count = 2
    type      = "tty"
  }

  features {
    nesting = true
    keyctl  = true
  }

  operating_system {
    template_file_id = "local:vztmpl/ubuntu-24.04-standard_24.04-2_amd64.tar.zst"
    type              = "ubuntu"
  }

  lifecycle {
    ignore_changes = [operating_system]
  }

  timeout_clone  = 1800
  timeout_create = 1800
  timeout_delete = 60
  timeout_update = 1800
}

# linux-mongodb (CT 108). Also STOPPED at import time — same reasoning as
# linux-k3s above. Unlike k3s, `onboot: 1` IS set in real life, so
# `start_on_boot = true` even though `started = false`.
resource "proxmox_virtual_environment_container" "linux_mongodb" {
  node_name     = var.proxmox_node
  vm_id         = 108
  unprivileged  = true
  started       = false
  start_on_boot = true

  cpu {
    cores = 1
  }

  memory {
    dedicated = 1024
    swap      = 512
  }

  disk {
    datastore_id = "local"
    size         = 8
  }

  network_interface {
    name     = "eth0"
    bridge   = "vmbr0"
    vlan_id  = 20
    firewall = true
  }

  initialization {
    hostname = "linux-mongodb"

    dns {
      domain  = "local"
      servers = ["8.8.8.8"]
    }

    ip_config {
      ipv4 {
        address = "dhcp"
      }
    }
  }

  console {
    enabled   = true
    tty_count = 2
    type      = "tty"
  }

  features {
    nesting = true
  }

  operating_system {
    template_file_id = "local:vztmpl/ubuntu-24.04-standard_24.04-2_amd64.tar.zst"
    type              = "ubuntu"
  }

  lifecycle {
    ignore_changes = [operating_system]
  }

  timeout_clone  = 1800
  timeout_create = 1800
  timeout_delete = 60
  timeout_update = 1800
}

# linux-gh-runner (CT 111) — the CI runner. Running, but not critical if
# briefly restarted (only needed during an actual CI/CD run). No `onboot`
# set in real life, matching `start_on_boot = false` below.
resource "proxmox_virtual_environment_container" "linux_gh_runner" {
  node_name     = var.proxmox_node
  vm_id         = 111
  unprivileged  = true
  started       = true
  start_on_boot = false

  cpu {
    cores = 1
  }

  memory {
    dedicated = 1024
    swap      = 512
  }

  disk {
    datastore_id = "local"
    size         = 10
  }

  network_interface {
    name     = "eth0"
    bridge   = "vmbr0"
    vlan_id  = 30
    firewall = true
  }

  initialization {
    hostname = "linux-gh-runner"

    dns {
      domain  = "local"
      servers = ["8.8.8.8"]
    }

    ip_config {
      ipv4 {
        address = "dhcp"
      }
    }
  }

  console {
    enabled   = true
    tty_count = 2
    type      = "tty"
  }

  features {
    nesting = true
  }

  operating_system {
    template_file_id = "local:vztmpl/ubuntu-24.04-standard_24.04-2_amd64.tar.zst"
    type              = "ubuntu"
  }

  lifecycle {
    ignore_changes = [operating_system]
  }

  timeout_clone  = 1800
  timeout_create = 1800
  timeout_delete = 60
  timeout_update = 1800
}

# linux-observability (CT 114) — Prometheus/Grafana/Loki/Alertmanager.
resource "proxmox_virtual_environment_container" "linux_observability" {
  node_name     = var.proxmox_node
  vm_id         = 114
  unprivileged  = true
  started       = true
  start_on_boot = true

  cpu {
    cores = 1
  }

  memory {
    dedicated = 1536
    swap      = 512
  }

  disk {
    datastore_id = "local"
    size         = 8
  }

  network_interface {
    name    = "eth0"
    bridge  = "vmbr0"
    vlan_id = 80
  }

  initialization {
    hostname = "linux-observability"

    dns {
      servers = ["8.8.8.8"]
    }

    ip_config {
      ipv4 {
        address = "dhcp"
      }
    }
  }

  console {
    enabled   = true
    tty_count = 2
    type      = "tty"
  }

  features {
    nesting = true
    keyctl  = true
  }

  operating_system {
    template_file_id = "local:vztmpl/ubuntu-24.04-standard_24.04-2_amd64.tar.zst"
    type              = "ubuntu"
  }

  lifecycle {
    ignore_changes = [operating_system]
  }

  timeout_clone  = 1800
  timeout_create = 1800
  timeout_delete = 60
  timeout_update = 1800
}

# linux-vault (CT 110) — MOST CRITICAL host in this batch, imported last.
# Everything's secrets flow through this box (AppRole tokens, DB root
# passwords, app secrets via Vault Agent). No `onboot` set in real life
# (a genuine pre-existing gap, not something this import silently fixes —
# see docs/19-devops-practice/12). Its real `pct config` also shows the two
# raw TUN lines duplicated (harmless, noted in the write-up, left alone).
resource "proxmox_virtual_environment_container" "linux_vault" {
  node_name     = var.proxmox_node
  vm_id         = 110
  unprivileged  = true
  started       = true
  start_on_boot = false

  cpu {
    cores = 1
  }

  memory {
    dedicated = 512
    swap      = 512
  }

  disk {
    datastore_id = "local"
    size         = 8
  }

  network_interface {
    name    = "eth0"
    bridge  = "vmbr0"
    vlan_id = 30
  }

  initialization {
    hostname = "linux-vault"

    dns {
      servers = ["8.8.8.8"]
    }

    ip_config {
      ipv4 {
        address = "dhcp"
      }
    }
  }

  console {
    enabled   = true
    tty_count = 2
    type      = "tty"
  }

  features {
    nesting = true
  }

  operating_system {
    template_file_id = "local:vztmpl/ubuntu-24.04-standard_24.04-2_amd64.tar.zst"
    type              = "ubuntu"
  }

  lifecycle {
    ignore_changes = [operating_system]
  }

  timeout_clone  = 1800
  timeout_create = 1800
  timeout_delete = 60
  timeout_update = 1800
}
