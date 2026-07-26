output "public_ip_address" {
  description = "Public IP of the stretch-goal VM"
  value       = azurerm_public_ip.stretch.ip_address
}

output "ssh_command" {
  description = "Ready-to-use SSH command"
  value       = "ssh ${var.admin_username}@${azurerm_public_ip.stretch.ip_address}"
}
