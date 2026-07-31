#!/bin/bash
# provision-test-loop.sh — full IaC pipeline for the disposable Terraform
# test loop: capacity check -> staged terraform apply -> CT Tailscale
# bridge -> wait for all 6 hosts online -> resolve current IPs into the
# Ansible inventory -> hand off to ansible-playbook.
#
# MUST run from WSL's native filesystem (e.g. ~/), not /mnt/c — see the
# preflight check below for why.
#
# Real bugs this script works around, each one found running this by hand
# first (full detail: docs/19-devops-practice/13 and 14, homelab meta-repo):
#   - VMs and CTs must apply as two SEPARATE terraform commands. Combining
#     them caused the CT `start` tasks to lose a Proxmox config-lock race
#     against the VMs' heavy clone I/O.
#   - CTs have no cloud-init equivalent. Tailscale has to be bridged in by
#     hand after creation: TUN device workaround, then install, then join.
#   - terraform destroy does NOT deregister a machine from Tailscale, so a
#     recreated host gets a "-N" suffix. Resolved via
#     resolve_tailscale_ips.py (tailscale status --json), never assumed
#     from the plain hostname.
#   - Ansible run from WSL against files on /mnt/c distrusts ansible.cfg
#     entirely (a WSL/NTFS permission quirk) — this script always works
#     from a copy on WSL's native filesystem.
#   - SSH aliases like "proxmox"/"linux-vault" live in Windows'
#     ~/.ssh/config, which WSL does NOT share (its own separate
#     ~/.ssh/config, usually without these aliases at all) — this script
#     uses explicit user@ip targets throughout instead of depending on
#     whichever machine happens to have matching aliases configured.
#
# Usage:
#   AWS_ACCESS_KEY_ID=terraform-asw AWS_SECRET_ACCESS_KEY=... ./provision-test-loop.sh
#
# Nothing else is hardcoded: the Tailscale authkey is read from
# terraform.tfvars (already gitignored there), and the Vault AppRole
# secret_id is generated fresh every run, never persisted.

set -euo pipefail

REPO_ROOT="/mnt/c/Users/taufi/Documents/Dev/Animal-Shelter-Workshop"
TF_DIR="$REPO_ROOT/infrastructure/terraform"
ANSIBLE_SRC="$REPO_ROOT/infrastructure/ansible"
WSL_ANSIBLE_DIR="$HOME/asw-ansible-run"
LOG_FILE="/tmp/provision-test-loop-$(date +%Y%m%d-%H%M%S).log"
CT_IDS=(207 208)

# Explicit user@ip, not SSH-config aliases — see the note above.
PROXMOX_SSH="root@100.97.8.93"
VAULT_SSH="linux-vault@100.112.41.113"

log() { echo "[$(date '+%H:%M:%S')] $*" | tee -a "$LOG_FILE"; }

fail() {
  log "FATAL: $*"
  log "Current state for diagnosis:"
  { ssh "$PROXMOX_SSH" "qm list; pct list"; } 2>&1 | tee -a "$LOG_FILE" || true
  # terraform init is platform-specific (.terraform/providers/ caches a
  # binary for whichever OS last ran init there) - re-init quietly first so
  # this diagnostic doesn't itself fail with an unrelated provider error if
  # the last init happened from a different platform (e.g. Windows git-bash
  # vs this WSL run).
  (cd "$TF_DIR" && terraform init -input=false >/dev/null 2>&1; terraform state list) 2>&1 | tee -a "$LOG_FILE" || true
  log "Full log: $LOG_FILE"
  exit 1
}

# ---- 0. Preflight -----------------------------------------------------------
log "Preflight checks..."

: "${AWS_ACCESS_KEY_ID:?Set AWS_ACCESS_KEY_ID (terraform-asw) before running}"
: "${AWS_SECRET_ACCESS_KEY:?Set AWS_SECRET_ACCESS_KEY before running}"

case "$(pwd -P)" in
  /mnt/c/*) fail "Run this from WSL's native filesystem, not /mnt/c (ansible.cfg trust issue — docs/19-devops-practice/13)" ;;
esac

ssh "$PROXMOX_SSH" "true" 2>/dev/null || fail "Cannot reach Proxmox host over SSH"
ssh "$PROXMOX_SSH" "qm status 109" 2>/dev/null | grep -q running \
  || fail "linux-mini-io (Terraform's state backend) isn't running — qm start 109 first"

FREE_MB=$(ssh "$PROXMOX_SSH" "free -m | awk '/^Mem:/{print \$7}'")
log "Host has ${FREE_MB}MB available."
if (( FREE_MB < 9216 )); then
  read -r -p "Only ${FREE_MB}MB available (want ~9GB for 4 VMs + 2 CTs). Continue anyway? [y/N] " ans
  [[ "$ans" == "y" || "$ans" == "Y" ]] || fail "Aborted — free up capacity first (see docs/19-devops-practice/13's capacity notes)"
fi

TAILSCALE_AUTHKEY=$(grep -oP '(?<=tailscale_auth_key = ")[^"]+' "$TF_DIR/terraform.tfvars") \
  || fail "Could not read tailscale_auth_key from terraform.tfvars"

command -v python3 >/dev/null || fail "python3 not found in this WSL environment"

# ---- 1. Terraform: the 4 VMs ------------------------------------------------
log "Applying the 4 VMs..."
(
  cd "$TF_DIR"
  terraform init -input=false >> "$LOG_FILE" 2>&1
  terraform apply -auto-approve -target="module.vm" >> "$LOG_FILE" 2>&1
) || fail "terraform apply (VMs) failed — see $LOG_FILE"

# ---- 2. Terraform: the 2 CTs, staged separately -----------------------------
log "Applying the 2 CTs (separate command — this is the lock-timeout fix)..."
(
  cd "$TF_DIR"
  terraform apply -auto-approve \
    -target="proxmox_virtual_environment_container.test_mysql_2" \
    -target="proxmox_virtual_environment_container.test_mariadb_2" \
    >> "$LOG_FILE" 2>&1
) || fail "terraform apply (CTs) failed — see $LOG_FILE"

# ---- 3 & 4. CT-only bridge: TUN device + reboot, then install/join Tailscale,
# both idempotent — safe to re-run against an already-fixed CT.
for ct_id in "${CT_IDS[@]}"; do
  if ssh "$PROXMOX_SSH" "pct exec ${ct_id} -- tailscale ip -4" &>/dev/null; then
    log "CT $ct_id already has Tailscale up — skipping the bridge entirely."
    continue
  fi

  log "CT $ct_id: TUN device fix..."
  ssh "$PROXMOX_SSH" "
    grep -q 'lxc.cgroup2.devices.allow: c 10:200 rwm' /etc/pve/lxc/${ct_id}.conf || \
      echo 'lxc.cgroup2.devices.allow: c 10:200 rwm' >> /etc/pve/lxc/${ct_id}.conf
    grep -q 'lxc.mount.entry: /dev/net/tun' /etc/pve/lxc/${ct_id}.conf || \
      echo 'lxc.mount.entry: /dev/net/tun dev/net/tun none bind,create=file' >> /etc/pve/lxc/${ct_id}.conf
    pct reboot ${ct_id}
  " >> "$LOG_FILE" 2>&1 || fail "TUN device fix failed for CT $ct_id"

  log "CT $ct_id: waiting for it to come back up..."
  until ssh "$PROXMOX_SSH" "pct exec ${ct_id} -- true" 2>/dev/null; do sleep 3; done

  log "CT $ct_id: installing + joining Tailscale..."
  ssh "$PROXMOX_SSH" "pct exec ${ct_id} -- bash -c 'apt-get update -qq && apt-get install -y -qq curl'" >> "$LOG_FILE" 2>&1
  ssh "$PROXMOX_SSH" "pct exec ${ct_id} -- bash -c 'curl -fsSL https://tailscale.com/install.sh | sh'" >> "$LOG_FILE" 2>&1
  ssh "$PROXMOX_SSH" "pct exec ${ct_id} -- tailscale up --authkey='${TAILSCALE_AUTHKEY}'" >> "$LOG_FILE" 2>&1 \
    || fail "Tailscale join failed for CT $ct_id"
done

# ---- 5. Wait for all 6 hosts online, then resolve IPs into the inventory ----
log "Preparing WSL-native Ansible copy (never run Ansible against /mnt/c)..."
rm -rf "$WSL_ANSIBLE_DIR"
cp -r "$ANSIBLE_SRC" "$WSL_ANSIBLE_DIR"

log "Waiting for all 6 hosts to show ONLINE on Tailscale..."
for i in $(seq 1 30); do
  if python3 "$REPO_ROOT/infrastructure/resolve_tailscale_ips.py" --check-only 2>&1 | tee -a "$LOG_FILE"; then
    break
  fi
  [[ $i -eq 30 ]] && fail "Not all hosts came online within 5 minutes"
  sleep 10
done

log "Resolving current IPs into the inventory..."
python3 "$REPO_ROOT/infrastructure/resolve_tailscale_ips.py" \
  --inventory "$WSL_ANSIBLE_DIR/.scratch-inventory-test-loop.yml" \
  2>&1 | tee -a "$LOG_FILE" \
  || fail "Failed to resolve/patch Tailscale IPs into the inventory"

# ---- 6. Vault: fresh AppRole secret_id, generated every run, never persisted
log "Starting linux-vault and fetching fresh AppRole credentials..."
ssh "$PROXMOX_SSH" "pct status 110" | grep -q running || ssh "$PROXMOX_SSH" "pct start 110"
sleep 10  # boot + auto-unseal

VAULT_ROLE_ID=$(ssh "$VAULT_SSH" "export VAULT_ADDR=http://127.0.0.1:8200; export VAULT_TOKEN=\$(cat ~/.vault-token); vault read -field=role_id auth/approle/role/asw-deploy/role-id") \
  || fail "Could not read Vault role_id"
VAULT_SECRET_ID=$(ssh "$VAULT_SSH" "export VAULT_ADDR=http://127.0.0.1:8200; export VAULT_TOKEN=\$(cat ~/.vault-token); vault write -f -field=secret_id auth/approle/role/asw-deploy/secret-id") \
  || fail "Could not generate Vault secret_id"

# ---- 7. Hand off to Ansible --------------------------------------------------
log "Running ansible-playbook..."
(
  cd "$WSL_ANSIBLE_DIR"
  VAULT_ROLE_ID="$VAULT_ROLE_ID" VAULT_SECRET_ID="$VAULT_SECRET_ID" \
    ansible-playbook -i .scratch-inventory-test-loop.yml playbooks/site.yml \
    -e '{"mysql_family_bootstrap_root_auth": true}'
) 2>&1 | tee -a "$LOG_FILE"

log "Done. Full log: $LOG_FILE"
