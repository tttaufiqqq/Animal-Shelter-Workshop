terraform {
  required_version = ">= 1.5"

  required_providers {
    azurerm = {
      source  = "hashicorp/azurerm"
      version = "~> 4.0"
    }
  }
}

# Auth via the Azure CLI's existing login (`az login`, already done this
# session) — no service principal needed for a one-off proof-then-destroy
# VM. Same pattern as Stage 1's Proxmox provider, just a different cloud.
provider "azurerm" {
  features {}
  subscription_id = var.subscription_id
}
