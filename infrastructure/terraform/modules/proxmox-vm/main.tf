terraform {
  required_providers {
    proxmox = {
      source = "bpg/proxmox"
    }
  }
}

# Upload a cloud-init snippet for this VM to the Proxmox "local" datastore.
# Prerequisite: enable "Snippets" content type on the "local" storage in Proxmox UI
# (Datacenter > Storage > local > Edit > check Snippets).
resource "proxmox_virtual_environment_file" "cloud_init" {
  content_type = "snippets"
  datastore_id = "local"
  node_name    = var.node_name

  source_raw {
    file_name = "cloud-init-${var.name}.yml"
    data = templatefile("${path.module}/cloud-init.yml.tftpl", {
      hostname           = var.name
      ssh_public_key     = var.ssh_public_key
      tailscale_auth_key = var.tailscale_auth_key
    })
  }
}

resource "proxmox_virtual_environment_vm" "this" {
  name      = var.name
  vm_id     = var.vmid
  node_name = var.node_name

  # Full clone from the Ubuntu 24.04 cloud-init template
  clone {
    vm_id = var.template_id
    full  = true
  }

  cpu {
    cores = var.cores
    type  = "host"
  }

  memory {
    dedicated = var.memory
  }

  disk {
    datastore_id = var.storage_pool
    size         = var.disk
    interface    = "scsi0"
    file_format  = "qcow2"
    discard      = "on"
  }

  network_device {
    bridge  = "vmbr0"
    model   = "virtio"
    vlan_id = var.vlan
  }

  # Inject cloud-init
  initialization {
    datastore_id      = var.storage_pool
    user_data_file_id = proxmox_virtual_environment_file.cloud_init.id

    ip_config {
      ipv4 {
        address = "dhcp"
      }
    }
  }

  lifecycle {
    ignore_changes = [
      # Don't re-run cloud-init on every apply
      initialization,
      # A full clone doesn't actually track file_format the way a fresh
      # disk creation does — Proxmox always reports the cloned disk back
      # as "raw" regardless of what's declared here, so this shows a
      # perpetual (harmless) diff on every plan unless ignored.
      disk[0].file_format,
    ]
  }

  depends_on = [proxmox_virtual_environment_file.cloud_init]
}
