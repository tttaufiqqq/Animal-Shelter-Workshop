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
