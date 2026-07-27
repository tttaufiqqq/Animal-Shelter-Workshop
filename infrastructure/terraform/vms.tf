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
