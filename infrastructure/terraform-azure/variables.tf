variable "subscription_id" {
  description = "Azure subscription ID (Azure for Students)"
  type        = string
}

variable "location" {
  description = "Azure region"
  type        = string
  default     = "southeastasia"
}

variable "vm_size" {
  # Standard_B1s/B1ms/B2s (the cheap burstable tier the plan originally
  # called for) all failed with SkuNotAvailable/Capacity Restrictions in
  # southeastasia on this subscription at apply time — a real, live regional
  # capacity limit on Azure for Students, not a config mistake. Standard_D2s_v3
  # (a much more universally-available general-purpose size) is what actually
  # applied successfully. Costs more per hour if left running, irrelevant
  # here since this VM is proof-then-destroy, never meant to stay up.
  description = "VM size — D2s_v3, not the originally-planned B-series (see comment)"
  type        = string
  default     = "Standard_D2s_v3"
}

variable "admin_username" {
  description = "SSH admin username on the VM"
  type        = string
  default     = "taufiq"
}

variable "ssh_public_key" {
  description = "SSH public key injected into the VM — same key used everywhere else in this homelab"
  type        = string
}

variable "allowed_ssh_source_ip" {
  description = "Single IP allowed to reach port 22 — scoped to this machine's current public IP, not 'Internet'"
  type        = string
}
