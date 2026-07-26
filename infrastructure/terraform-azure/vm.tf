# Dedicated resource group, separate from homelab-stage8 (Storage/Function
# App live there long-term) — this whole group is meant to be created and
# destroyed as one unit, proof-then-teardown, same discipline as Stage 1's
# own 200-series test VMs.
resource "azurerm_resource_group" "stretch" {
  name     = "homelab-stage8-terraform-stretch"
  location = var.location
}

resource "azurerm_virtual_network" "stretch" {
  name                = "asw-stretch-vnet"
  address_space       = ["10.99.0.0/24"]
  location            = azurerm_resource_group.stretch.location
  resource_group_name = azurerm_resource_group.stretch.name
}

resource "azurerm_subnet" "stretch" {
  name                 = "asw-stretch-subnet"
  resource_group_name  = azurerm_resource_group.stretch.name
  virtual_network_name = azurerm_virtual_network.stretch.name
  address_prefixes     = ["10.99.0.0/26"]
}

resource "azurerm_public_ip" "stretch" {
  name                = "asw-stretch-pip"
  location            = azurerm_resource_group.stretch.location
  resource_group_name = azurerm_resource_group.stretch.name
  allocation_method   = "Static"
  sku                 = "Standard"
}

# SSH from exactly one IP (this machine's current public IP), not "Internet"
# — same least-privilege habit as every UFW rule in the rest of this homelab.
resource "azurerm_network_security_group" "stretch" {
  name                = "asw-stretch-nsg"
  location            = azurerm_resource_group.stretch.location
  resource_group_name = azurerm_resource_group.stretch.name

  security_rule {
    name                       = "AllowSSHFromMe"
    priority                   = 100
    direction                  = "Inbound"
    access                     = "Allow"
    protocol                   = "Tcp"
    source_port_range          = "*"
    destination_port_range     = "22"
    source_address_prefix      = "${var.allowed_ssh_source_ip}/32"
    destination_address_prefix = "*"
  }
}

resource "azurerm_network_interface" "stretch" {
  name                = "asw-stretch-nic"
  location            = azurerm_resource_group.stretch.location
  resource_group_name = azurerm_resource_group.stretch.name

  ip_configuration {
    name                          = "internal"
    subnet_id                     = azurerm_subnet.stretch.id
    private_ip_address_allocation = "Dynamic"
    public_ip_address_id          = azurerm_public_ip.stretch.id
  }
}

resource "azurerm_network_interface_security_group_association" "stretch" {
  network_interface_id     = azurerm_network_interface.stretch.id
  network_security_group_id = azurerm_network_security_group.stretch.id
}

resource "azurerm_linux_virtual_machine" "stretch" {
  name                = "asw-stretch-vm"
  location            = azurerm_resource_group.stretch.location
  resource_group_name = azurerm_resource_group.stretch.name
  size                = var.vm_size
  admin_username      = var.admin_username
  network_interface_ids = [azurerm_network_interface.stretch.id]

  admin_ssh_key {
    username   = var.admin_username
    public_key = var.ssh_public_key
  }

  os_disk {
    caching              = "ReadWrite"
    storage_account_type = "Standard_LRS"
  }

  source_image_reference {
    publisher = "Canonical"
    offer     = "ubuntu-24_04-lts"
    sku       = "server"
    version   = "latest"
  }

  disable_password_authentication = true
}
