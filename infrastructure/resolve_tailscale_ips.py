#!/usr/bin/env python3
"""Resolve the current Tailscale IP for each disposable test-loop host and
patch the scoped Ansible inventory in place.

Why this exists, not a grep/sed one-liner: `terraform destroy` never
deregisters a machine from Tailscale, so recreating a VM/CT with the same
hostname collides with its own stale "offline" device and gets a "-N"
suffix appended to disambiguate. After several destroy+recreate cycles,
`tailscale status`'s human-readable table has multiple entries per hostname
prefix and no reliable way to tell which one is current just by looking —
this bit us for real (see docs/19-devops-practice/13/14 in the homelab
meta-repo). `tailscale status --json` has structured fields (Online,
LastSeen) that make picking the right one deterministic instead of a guess.

Usage:
  resolve_tailscale_ips.py --check-only
      Exit 0 only if every target host is currently ONLINE. Prints status,
      writes nothing. Meant to be polled in a loop before trusting the
      result enough to patch the inventory.

  resolve_tailscale_ips.py --inventory /path/to/inventory.yml
      Resolves every target and patches ansible_host in place. Falls back
      to the most-recently-seen match if nothing is online yet (with a
      loud warning) rather than failing outright.
"""
import argparse
import json
import re
import subprocess
import sys

# Ansible inventory hostname -> Tailscale device hostname this maps to.
# Exact match only, or an exact match with a Tailscale "-<digits>" suffix —
# never a plain substring match, since "test-mysql" is itself a substring
# of "test-mysql-2" (a genuinely different host, not a suffix collision).
TARGETS = {
    "app-server": "test-app-server",
    "linux-mysql": "test-mysql",
    "linux-mysql-2": "test-mysql-2",
    "linux-mariadb": "test-mariadb",
    "linux-mariadb-2": "test-mariadb-2",
    "linux-postgres": "test-postgres",
}


# Explicit user@ip, not an SSH-config alias like "proxmox" — the control
# node running this script (WSL) has its own separate ~/.ssh/config from
# Windows, with no aliases defined at all. Depending on a per-machine
# alias that may not exist is exactly the kind of hidden-state dependency
# a script meant to run from more than one place shouldn't have.
PROXMOX_SSH_TARGET = "root@100.97.8.93"


def get_tailscale_status() -> dict:
    """Fetch structured Tailscale status via the Proxmox host — reachable
    from wherever this script runs, unlike a local `tailscale` binary."""
    result = subprocess.run(
        ["ssh", PROXMOX_SSH_TARGET, "tailscale status --json"],
        capture_output=True, text=True, check=True,
    )
    return json.loads(result.stdout)


def resolve_current_ip(status: dict, prefix: str):
    """Return (ip, matched_hostname, is_online) for the peer that's
    actually the current instance of `prefix`, or (None, None, False) if
    nothing matches at all."""
    peers = list(status.get("Peer", {}).values())
    if "Self" in status:
        peers.append(status["Self"])

    exact_pattern = re.compile(rf"^{re.escape(prefix)}(-\d+)?$")
    candidates = [p for p in peers if exact_pattern.match(p.get("HostName", ""))]

    if not candidates:
        return None, None, False

    online = [p for p in candidates if p.get("Online")]
    if online:
        if len(online) > 1:
            names = [p["HostName"] for p in online]
            print(f"WARNING: multiple ONLINE peers matched '{prefix}': {names} "
                  f"— using the first, verify manually", file=sys.stderr)
        p = online[0]
        return p["TailscaleIPs"][0], p["HostName"], True

    # Nothing online yet — likely still booting/joining. Fall back to the
    # most recently seen, but make it loud that this is a guess.
    candidates.sort(key=lambda p: p.get("LastSeen", ""), reverse=True)
    p = candidates[0]
    print(f"WARNING: no ONLINE peer for '{prefix}' yet — falling back to "
          f"most recently seen ({p['HostName']}, offline). May not be "
          f"booted yet.", file=sys.stderr)
    return p["TailscaleIPs"][0], p["HostName"], False


def patch_inventory(path: str, resolved: dict):
    """resolved: {ansible_hostname: (ip, matched_hostname, is_online)}"""
    with open(path, encoding="utf-8") as f:
        lines = f.readlines()

    out = []
    current_target = None
    key_re = re.compile(r"^(\s*)([A-Za-z0-9_.-]+):\s*$")

    for line in lines:
        m = key_re.match(line)
        if m and m.group(2) in resolved:
            current_target = m.group(2)
            out.append(line)
            continue

        if current_target and "ansible_host:" in line:
            indent = line[: len(line) - len(line.lstrip())]
            ip, matched_name, _ = resolved[current_target]
            if ip is None:
                print(f"ERROR: no Tailscale IP resolved for '{current_target}' "
                      f"— leaving its inventory line untouched", file=sys.stderr)
                out.append(line)
            else:
                out.append(f"{indent}ansible_host: {ip}   # {matched_name}\n")
            current_target = None
            continue

        out.append(line)

    with open(path, "w", encoding="utf-8") as f:
        f.writelines(out)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--inventory", help="Path to the inventory YAML to patch")
    parser.add_argument("--check-only", action="store_true",
                         help="Only check whether every target is ONLINE; write nothing")
    args = parser.parse_args()

    if not args.check_only and not args.inventory:
        parser.error("--inventory is required unless --check-only is given")

    status = get_tailscale_status()

    resolved = {}
    all_online = True
    for ansible_hostname, prefix in TARGETS.items():
        ip, matched_name, is_online = resolve_current_ip(status, prefix)
        resolved[ansible_hostname] = (ip, matched_name, is_online)
        state = "ONLINE" if is_online else ("STALE/OFFLINE" if ip else "NOT FOUND")
        print(f"{ansible_hostname:20s} -> {matched_name or '?':22s} {ip or '':16s} {state}")
        if not is_online:
            all_online = False

    if args.check_only:
        sys.exit(0 if all_online else 1)

    patch_inventory(args.inventory, resolved)

    if not all_online:
        print("\nWARNING: not every host was ONLINE when patched — re-run "
              "once they are, don't trust this result blindly.", file=sys.stderr)
        sys.exit(1)

    print("\nInventory patched successfully — all 6 hosts were ONLINE.")


if __name__ == "__main__":
    main()
