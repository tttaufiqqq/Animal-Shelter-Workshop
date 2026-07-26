variable "name" {
  description = "VM name (also used as the cloud-init hostname)"
  type        = string
}

variable "vmid" {
  description = "Proxmox VM ID"
  type        = number
}

variable "cores" {
  description = "CPU cores"
  type        = number
}

variable "memory" {
  description = "Memory in MB"
  type        = number
}

variable "disk" {
  description = "Disk size in GB"
  type        = number
}

variable "vlan" {
  description = "VLAN tag - vmbr0 is VLAN-aware, untagged traffic has no DHCP/routing"
  type        = number
}

variable "node_name" {
  description = "Proxmox node name"
  type        = string
}

variable "template_id" {
  description = "VM ID of the cloud-init template to clone from"
  type        = number
}

variable "storage_pool" {
  description = "Proxmox storage pool for the VM disk and cloud-init datastore"
  type        = string
}

variable "ssh_public_key" {
  description = "SSH public key injected into the VM's workshop and taufiq users"
  type        = string
}

variable "tailscale_auth_key" {
  description = "Tailscale reusable auth key"
  type        = string
  sensitive   = true
}
