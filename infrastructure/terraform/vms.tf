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
