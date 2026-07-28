locals {
  # This is the DISPOSABLE TEST LOOP (VM IDs 201/204/205/206), not the real
  # production fleet — those are separate resources in `production-vms.tf`
  # and `containers.tf`, adopted via `terraform import`, not created by this
  # module. Keys here are prefixed `test-` specifically so
  # `terraform state list` reads unambiguously now that real production
  # resources exist alongside these (see docs/19-devops-practice/12 in the
  # homelab meta-repo — the two used to share the same bare names, which
  # `docs/12-mysql-shelter-animals-split` flagged as confusing, even though
  # it never caused a real collision since Ansible targets by Tailscale
  # hostname, not VM ID).
  #
  # vlan matches the production VM on the same role - vmbr0 is VLAN-aware
  # (vlan_filtering 1) and untagged traffic lands on a VLAN with no
  # DHCP/routing, so a VM without the right tag never gets an IP.
  vms = {
    "test-app-server" = {
      vmid   = 201
      cores  = 2
      memory = 2048
      disk   = 20
      vlan   = 40
    }
    "test-mysql" = {
      vmid   = 204
      cores  = 2
      memory = 2048
      disk   = 20
      vlan   = 20
    }
    "test-mariadb" = {
      vmid   = 205
      cores  = 2
      memory = 2048
      disk   = 20
      vlan   = 20
    }
    "test-postgres" = {
      vmid   = 206
      cores  = 2
      memory = 2048
      disk   = 20
      vlan   = 20
    }
  }
}

module "vm" {
  source   = "./modules/proxmox-vm"
  for_each = local.vms

  name   = each.key
  vmid   = each.value.vmid
  cores  = each.value.cores
  memory = each.value.memory
  disk   = each.value.disk
  vlan   = each.value.vlan

  node_name           = var.proxmox_node
  template_id         = var.vm_template_id
  storage_pool        = var.storage_pool
  ssh_public_key      = var.ssh_public_key
  tailscale_auth_key  = var.tailscale_auth_key
}

# --- CT creation proof: test-mysql-2 / test-mariadb-2 ---
# The 4 VMs above prove Terraform can build a VM from scratch (via the
# module + cloud-init). Terraform in this project has never actually
# CREATED a CT from scratch, though — every real CT (linux-mysql-2,
# linux-mariadb-2, linux-vault, linux-gh-runner) was adopted via
# `terraform import`, hand-built first. These two prove the missing half:
# that `proxmox_virtual_environment_container` can build a working CT
# directly from the vztmpl template, not just track one that already
# exists.
#
# Sized small (512MB/1 core, matching the earlier scratch-CT dry run from
# docs/19-devops-practice/12) since this only needs to prove creation
# works, not carry real load.
#
# KNOWN GAP, deliberately not solved here yet: Tailscale needs the TUN
# device workaround (the two raw lxc.* config lines), which can't be
# declared through this resource's schema at all — same lesson as
# containers.tf's adopted CTs. A bash/shell script to apply that fix and
# install/join Tailscale after creation is a planned follow-up, to be
# documented once built. Until then these two CTs will boot but won't
# join Tailscale on their own.

resource "proxmox_virtual_environment_container" "test_mysql_2" {
  node_name    = var.proxmox_node
  vm_id        = 207
  unprivileged = true
  started      = true

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
    vlan_id = 20
  }

  initialization {
    hostname = "test-mysql-2"

    dns {
      domain  = "local"
      servers = ["8.8.8.8"]
    }

    ip_config {
      ipv4 {
        address = "dhcp"
      }
    }

    user_account {
      keys = [var.ssh_public_key]
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

  timeout_create = 1800
  timeout_delete = 60
  timeout_update = 1800
}

resource "proxmox_virtual_environment_container" "test_mariadb_2" {
  node_name    = var.proxmox_node
  vm_id        = 208
  unprivileged = true
  started      = true

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
    vlan_id = 20
  }

  initialization {
    hostname = "test-mariadb-2"

    dns {
      domain  = "local"
      servers = ["8.8.8.8"]
    }

    ip_config {
      ipv4 {
        address = "dhcp"
      }
    }

    user_account {
      keys = [var.ssh_public_key]
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

  timeout_create = 1800
  timeout_delete = 60
  timeout_update = 1800
}
