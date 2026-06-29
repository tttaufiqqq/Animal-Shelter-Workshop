output "vm_ids" {
  description = "VM IDs of all provisioned VMs"
  value = {
    for k, vm in proxmox_virtual_environment_vm.vms : k => vm.vm_id
  }
}

output "vm_ipv4_addresses" {
  description = "IPv4 addresses assigned to each VM (populated after boot)"
  value = {
    for k, vm in proxmox_virtual_environment_vm.vms : k => vm.ipv4_addresses
  }
}
