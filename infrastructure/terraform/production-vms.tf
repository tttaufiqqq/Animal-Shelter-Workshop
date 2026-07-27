# The production VMs below (app-server, linux-mysql, linux-mariadb,
# linux-postgres, linux-mini-io) are the hand-built VMs that have run this
# app since before Terraform managed anything — NOT the disposable test loop
# in `vms.tf` (which uses different VM IDs entirely, 201/204/205/206, and is
# for spinning up a fresh, throwaway proof of the loop). These blocks were
# written to match each VM's exact current config and adopted via
# `terraform import`, never created fresh — same "prove zero drift on a
# scratch VM first" discipline as `containers.tf` used for the two adopted
# LXCs. Do not `terraform destroy` any of these — they hold real data.
#
# Real specs differ from what the `modules/proxmox-vm` module (used by the
# test loop) assumes: cores/disk vary per host (not a flat 2 cores/20G),
# `cpu.type` is the real CPU model string, not `"host"`, and every host still
# has its original install ISO attached on a `cdrom` block. See
# docs/19-devops-practice/12 for the full drift/gotcha writeup, including
# three VM-resource-specific defaults that silently diverge from reality
# unless declared explicitly: `on_boot` and `started` both default to `true`
# on an undeclared attribute (none of these 4 DB/app VMs have `onboot` set in
# real life), and `scsi_hardware` defaults to `"virtio-scsi-pci"` while every
# real host here uses `"virtio-scsi-single"`.

resource "proxmox_virtual_environment_vm" "linux_mysql" {
  name          = "linux-mysql"
  vm_id         = 104
  node_name     = var.proxmox_node
  started       = true
  on_boot       = false
  scsi_hardware = "virtio-scsi-single"

  operating_system {
    type = "l26"
  }

  cpu {
    cores = 1
    type  = "x86-64-v2-AES"
  }

  memory {
    dedicated = 2048
  }

  disk {
    datastore_id = "local"
    size         = 22
    interface    = "scsi0"
    file_format  = "qcow2"
    iothread     = true
  }

  cdrom {
    file_id   = "local:iso/ubuntu-24.04.4-live-server-amd64.iso"
    interface = "ide2"
  }

  network_device {
    bridge   = "vmbr0"
    model    = "virtio"
    vlan_id  = 20
    firewall = true
  }
}

resource "proxmox_virtual_environment_vm" "linux_mariadb" {
  name          = "linux-mariadb"
  vm_id         = 105
  node_name     = var.proxmox_node
  started       = true
  on_boot       = false
  scsi_hardware = "virtio-scsi-single"

  operating_system {
    type = "l26"
  }

  cpu {
    cores = 1
    type  = "x86-64-v2-AES"
  }

  memory {
    dedicated = 2048
  }

  disk {
    datastore_id = "local"
    size         = 22
    interface    = "scsi0"
    file_format  = "qcow2"
    iothread     = true
  }

  cdrom {
    file_id   = "local:iso/ubuntu-24.04.4-live-server-amd64.iso"
    interface = "ide2"
  }

  network_device {
    bridge   = "vmbr0"
    model    = "virtio"
    vlan_id  = 20
    firewall = true
  }
}

resource "proxmox_virtual_environment_vm" "linux_postgres" {
  name          = "linux-postgres"
  vm_id         = 106
  node_name     = var.proxmox_node
  started       = true
  on_boot       = false
  scsi_hardware = "virtio-scsi-single"

  operating_system {
    type = "l26"
  }

  cpu {
    cores = 1
    type  = "x86-64-v2-AES"
  }

  memory {
    dedicated = 2048
  }

  disk {
    datastore_id = "local"
    size         = 22
    interface    = "scsi0"
    file_format  = "qcow2"
    iothread     = true
  }

  cdrom {
    file_id   = "local:iso/ubuntu-24.04.4-live-server-amd64.iso"
    interface = "ide2"
  }

  network_device {
    bridge   = "vmbr0"
    model    = "virtio"
    vlan_id  = 20
    firewall = true
  }
}

resource "proxmox_virtual_environment_vm" "app_server" {
  name          = "app-server"
  vm_id         = 101
  node_name     = var.proxmox_node
  started       = true
  on_boot       = false
  scsi_hardware = "virtio-scsi-single"

  operating_system {
    type = "l26"
  }

  cpu {
    cores = 2
    type  = "x86-64-v2-AES"
  }

  memory {
    dedicated = 2048
  }

  disk {
    datastore_id = "local"
    size         = 28
    interface    = "scsi0"
    file_format  = "qcow2"
    iothread     = true
  }

  cdrom {
    file_id   = "local:iso/ubuntu-24.04.4-live-server-amd64.iso"
    interface = "ide2"
  }

  network_device {
    bridge   = "vmbr0"
    model    = "virtio"
    vlan_id  = 40
    firewall = true
  }
}

# linux-mini-io (VM 109) — hosts the MinIO instance this very Terraform
# config uses as its S3 state backend (see `main.tf`). Imported LAST of
# everything in this pass, deliberately, so a mistake importing it can't
# jeopardize state access for every other host. Two real disks (the OS disk
# and the actual MinIO data volume) and a different install ISO than the
# other four VMs (22.04, not 24.04) — this VM predates the others.
resource "proxmox_virtual_environment_vm" "linux_mini_io" {
  name          = "linux-mini-io"
  vm_id         = 109
  node_name     = var.proxmox_node
  started       = true
  on_boot       = true
  scsi_hardware = "virtio-scsi-single"

  operating_system {
    type = "l26"
  }

  cpu {
    cores = 2
    type  = "x86-64-v2-AES"
  }

  memory {
    dedicated = 4096
  }

  disk {
    datastore_id = "local"
    size         = 18
    interface    = "scsi0"
    file_format  = "qcow2"
    iothread     = true
  }

  disk {
    datastore_id = "local"
    size         = 32
    interface    = "scsi1"
    file_format  = "qcow2"
    iothread     = true
  }

  cdrom {
    file_id   = "local:iso/ubuntu-22.04.5-live-server-amd64.iso"
    interface = "ide2"
  }

  network_device {
    bridge   = "vmbr0"
    model    = "virtio"
    vlan_id  = 30
    firewall = true
  }
}
