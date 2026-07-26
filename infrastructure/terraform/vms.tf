locals {
  # vlan matches the production VM (101/104/105/106) on the same role -
  # vmbr0 is VLAN-aware (vlan_filtering 1) and untagged traffic lands on a
  # VLAN with no DHCP/routing, so a VM without the right tag never gets an IP.
  vms = {
    "app-server" = {
      vmid   = 201
      cores  = 2
      memory = 2048
      disk   = 20
      vlan   = 40
    }
    "linux-mysql" = {
      vmid   = 204
      cores  = 2
      memory = 2048
      disk   = 20
      vlan   = 20
    }
    "linux-mariadb" = {
      vmid   = 205
      cores  = 2
      memory = 2048
      disk   = 20
      vlan   = 20
    }
    "linux-postgres" = {
      vmid   = 206
      cores  = 2
      memory = 2048
      disk   = 20
      vlan   = 20
    }
  }
}

# Upload a cloud-init snippet per VM to the Proxmox "local" datastore.
# Prerequisite: enable "Snippets" content type on the "local" storage in Proxmox UI
# (Datacenter > Storage > local > Edit > check Snippets).
resource "proxmox_virtual_environment_file" "cloud_init" {
  for_each = local.vms

  content_type = "snippets"
  datastore_id = "local"
  node_name    = var.proxmox_node

  source_raw {
    file_name = "cloud-init-${each.key}.yml"
    data = templatefile("${path.module}/cloud-init.yml.tftpl", {
      hostname           = each.key
      ssh_public_key     = var.ssh_public_key
      tailscale_auth_key = var.tailscale_auth_key
    })
  }
}

resource "proxmox_virtual_environment_vm" "vms" {
  for_each = local.vms

  name      = each.key
  vm_id     = each.value.vmid
  node_name = var.proxmox_node

  # Full clone from your Ubuntu 24.04 template
  clone {
    vm_id = var.vm_template_id
    full  = true
  }

  cpu {
    cores = each.value.cores
    type  = "host"
  }

  memory {
    dedicated = each.value.memory
  }

  disk {
    datastore_id = var.storage_pool
    size         = each.value.disk
    interface    = "scsi0"
    file_format  = "qcow2"
    discard      = "on"
  }

  network_device {
    bridge  = "vmbr0"
    model   = "virtio"
    vlan_id = each.value.vlan
  }

  # Inject cloud-init
  initialization {
    datastore_id      = var.storage_pool
    user_data_file_id = proxmox_virtual_environment_file.cloud_init[each.key].id

    ip_config {
      ipv4 {
        address = "dhcp"
      }
    }
  }

  # Don't re-run cloud-init on every apply
  lifecycle {
    ignore_changes = [initialization]
  }

  depends_on = [proxmox_virtual_environment_file.cloud_init]
}
