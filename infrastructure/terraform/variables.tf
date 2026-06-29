variable "proxmox_endpoint" {
  description = "Proxmox API URL, e.g. https://192.168.1.10:8006"
  type        = string
}

variable "proxmox_api_token" {
  description = "Proxmox API token — format: USER@REALM!TOKENID=UUID"
  type        = string
  sensitive   = true
}

variable "proxmox_ssh_user" {
  description = "SSH user for the Proxmox host (needed to upload cloud-init snippets)"
  type        = string
  default     = "root"
}

variable "proxmox_ssh_password" {
  description = "SSH password for the Proxmox host"
  type        = string
  sensitive   = true
}

variable "proxmox_node" {
  description = "Proxmox node name (shown in Proxmox UI, e.g. 'pve')"
  type        = string
  default     = "pve"
}

variable "vm_template_id" {
  description = "VM ID of the Ubuntu 24.04 cloud-init template to clone from"
  type        = number
}

variable "storage_pool" {
  description = "Proxmox storage pool for VM disks (e.g. 'local-lvm')"
  type        = string
  default     = "local-lvm"
}

variable "ssh_public_key" {
  description = "SSH public key injected into all VMs (paste the full key string)"
  type        = string
}

variable "tailscale_auth_key" {
  description = "Tailscale reusable auth key — generate at https://login.tailscale.com/admin/settings/keys"
  type        = string
  sensitive   = true
}
