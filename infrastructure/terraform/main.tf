terraform {
  required_version = ">= 1.5"

  required_providers {
    proxmox = {
      source  = "bpg/proxmox"
      version = "~> 0.66"
    }
  }
}

provider "proxmox" {
  # e.g. https://192.168.1.10:8006 — Proxmox web UI URL
  endpoint  = var.proxmox_endpoint
  api_token = var.proxmox_api_token

  # Proxmox uses a self-signed cert by default
  insecure = true

  # bpg/proxmox needs SSH access to upload cloud-init snippets
  ssh {
    agent    = false
    username = var.proxmox_ssh_user
    password = var.proxmox_ssh_password
  }
}
