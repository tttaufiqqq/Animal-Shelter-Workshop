terraform {
  required_version = ">= 1.5"

  required_providers {
    proxmox = {
      source  = "bpg/proxmox"
      version = "~> 0.66"
    }
  }

  # State lives on the homelab's own self-hosted MinIO (linux-mini-io) instead
  # of a local .tfstate file — S3-compatible, so the standard "s3" backend
  # works against it once pointed at MinIO's endpoint and told to use
  # path-style addressing (MinIO doesn't do virtual-hosted-style buckets).
  # Credentials are a bucket-scoped MinIO user (terraform-asw), not the
  # MinIO root account — same "scope the credential, not just the network"
  # reasoning as the Azure backup SAS token (see docs/devops-plan in the
  # homelab meta-repo). Pass them via AWS_ACCESS_KEY_ID/AWS_SECRET_ACCESS_KEY
  # env vars, never hardcoded here.
  backend "s3" {
    bucket = "animal-shelter-workshop-tfstate"
    key    = "terraform.tfstate"
    region = "us-east-1" # required by the backend, meaningless to MinIO

    endpoints = {
      s3 = "http://100.73.172.85:9000" # linux-mini-io, Tailscale IP
    }

    use_path_style              = true
    skip_credentials_validation = true
    skip_region_validation      = true
    skip_requesting_account_id  = true
    skip_metadata_api_check     = true
    skip_s3_checksum            = true
  }
}

provider "proxmox" {
  # e.g. https://192.168.1.10:8006 — Proxmox web UI URL
  endpoint  = var.proxmox_endpoint
  api_token = var.proxmox_api_token

  # Proxmox uses a self-signed cert by default
  insecure = true

  # bpg/proxmox needs SSH access to upload cloud-init snippets. By default it
  # resolves the node's SSH address from Proxmox's own cluster config, which
  # is the node's LAN IP (10.0.10.2) — unreachable from a control node that
  # only has Tailscale connectivity to the host. Override it to the same
  # Tailscale IP used for the API endpoint.
  ssh {
    agent    = false
    username = var.proxmox_ssh_user
    password = var.proxmox_ssh_password

    node {
      name    = var.proxmox_node
      address = "100.97.8.93"
    }
  }
}
