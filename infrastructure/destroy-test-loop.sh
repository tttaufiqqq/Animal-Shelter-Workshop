#!/bin/bash
# destroy-test-loop.sh — tears down the disposable test loop completely:
# terraform destroy (the real infra) AND removes every matching Tailscale
# device via the API, current ones AND stale "-N" leftovers alike. Unlike
# terraform destroy alone, this actually closes the "-N suffix" gap
# documented in docs/19-devops-practice/01, instead of leaving stale
# devices behind for the next create to collide with.
#
# Usage:
#   AWS_ACCESS_KEY_ID=terraform-asw AWS_SECRET_ACCESS_KEY=... \
#   TAILSCALE_API_KEY=tskey-api-... ./destroy-test-loop.sh
#
# TAILSCALE_API_KEY is a DIFFERENT credential from the reusable auth key
# used to join devices — generate one at
# https://login.tailscale.com/admin/settings/keys ("Generate access
# token"), scoped to manage devices.

set -euo pipefail

REPO_ROOT="/mnt/c/Users/taufi/Documents/Dev/Animal-Shelter-Workshop"
TF_DIR="$REPO_ROOT/infrastructure/terraform"
LOG_FILE="/tmp/destroy-test-loop-$(date +%Y%m%d-%H%M%S).log"

log() { echo "[$(date '+%H:%M:%S')] $*" | tee -a "$LOG_FILE"; }

: "${AWS_ACCESS_KEY_ID:?Set AWS_ACCESS_KEY_ID (terraform-asw) before running}"
: "${AWS_SECRET_ACCESS_KEY:?Set AWS_SECRET_ACCESS_KEY before running}"
: "${TAILSCALE_API_KEY:?Set TAILSCALE_API_KEY - generate at https://login.tailscale.com/admin/settings/keys}"

log "Destroying Terraform-managed test loop resources..."
(
  cd "$TF_DIR"
  terraform init -input=false >> "$LOG_FILE" 2>&1
  terraform destroy -auto-approve \
    -target="module.vm" \
    -target="proxmox_virtual_environment_container.test_mysql_2" \
    -target="proxmox_virtual_environment_container.test_mariadb_2" \
    >> "$LOG_FILE" 2>&1
) || log "WARNING: terraform destroy hit an error (see $LOG_FILE) — continuing to Tailscale cleanup anyway"

log "Removing every matching Tailscale device (current + stale leftovers)..."
python3 "$REPO_ROOT/infrastructure/resolve_tailscale_ips.py" --list-all-device-ids 2>&1 \
  | tee -a "$LOG_FILE" \
  | sort -u -k1,1 \
  | while read -r device_id hostname; do
      log "  Deleting ${hostname} (${device_id})..."
      http_code=$(curl -s -o /dev/null -w "%{http_code}" -u "${TAILSCALE_API_KEY}:" \
        -X DELETE "https://api.tailscale.com/api/v2/device/${device_id}" || echo "ERR")
      if [[ "$http_code" == "200" ]]; then
        log "    deleted."
      else
        log "    WARNING: delete returned HTTP ${http_code} for ${hostname} (${device_id})"
      fi
    done

log "Done. Verify with 'qm list'/'pct list' on Proxmox and the Tailscale admin console."
log "Full log: $LOG_FILE"
