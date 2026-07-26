output "vm_id" {
  description = "The VM's Proxmox VM ID"
  value       = proxmox_virtual_environment_vm.this.vm_id
}

output "ipv4_addresses" {
  description = "IPv4 addresses assigned to the VM (populated after boot)"
  value       = proxmox_virtual_environment_vm.this.ipv4_addresses
}
