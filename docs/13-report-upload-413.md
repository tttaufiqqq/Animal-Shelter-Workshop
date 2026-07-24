# Report Submission Stuck on "Submitting Report..." — 413 Nobody Saw

**Date:** 24 July 2026

## What I Built

Raised three upload-size limits that had never matched the report form's own validation rule (up to 5 images, 5MB each):

| Setting | Where | Before | After |
|---|---|---|---|
| `client_max_body_size` | `infrastructure/ansible/templates/nginx-locations.conf.j2` | unset (nginx default: 1MB) | 30M |
| `upload_max_filesize` | `/etc/php/8.3/fpm/php.ini`, via a new Ansible task in `app-server.yml` | 2M (package default) | 8M |
| `post_max_size` | same | 8M (package default) | 30M |

Both the live VM and the Ansible source got the fix, so a future `ansible-playbook app-server.yml` won't regress it.

## Why I Built It

Reported as: submitting a stray report hangs on "Submitting Report..." indefinitely, and the site felt generally slower. This came in right after an unrelated homelab network segmentation project (VLANs, a new OPNsense router) finished on the Proxmox side, so the first hypothesis was that the network changes broke something — DNS, database reachability, Vault secret injection, outbound bandwidth to Cloudinary. All of that got tested directly and came back healthy: DB connections under 70ms, Vault agent actively rotating every credential (`DB1_PASSWORD` through `DB5_PASSWORD`, `CLOUDINARY_*`, `APP_KEY`) every few minutes with zero errors, ~180+ Mbps outbound throughput, Cloudinary reachable. The network work was, in the end, not the cause — but ruling it out properly is what led to actually finding the real bug.

## What Broke, How I Found It, and How I Recovered

**Broke:** User reproduced it live while I tailed `/var/log/nginx/access.log` and `error.log` in real time. The moment they submitted:
```
[error] client intended to send too large body: 3929516 bytes ...
request: "POST /reports HTTP/1.1"
"POST /reports HTTP/1.1" 413 594
```
nginx rejected the ~3.9MB image almost instantly with `413 Payload Too Large`. The "stuck forever" feeling wasn't the server being slow at all — it was the frontend never handling a 413 response, so the submit button just stayed in its loading state indefinitely instead of showing an error.

**Found it:** Once the real error was visible in the log, checking nginx's and PHP's actual configured limits made the root cause obvious — nginx had no `client_max_body_size` override at all (silently defaulting to nginx's stock 1MB), and PHP's own `upload_max_filesize` (2M) and `post_max_size` (8M) were both still at Ubuntu's package defaults too. None of the three had ever been raised to match the report form's own "up to 5 images, 5MB each" validation rule — this had presumably been broken since the form was first built, and just happened to surface today because someone finally uploaded a photo bigger than 1MB.

**Recovered:** Raised all three limits (30M/8M/30M — see table above), applied live on the VM (`nginx -t && systemctl reload nginx`, `systemctl restart php8.3-fpm`) and committed the same fix into the Ansible template/playbook so it survives the next deploy. Verified with a real 4MB POST to `/reports`: went from `413` to `419` (CSRF mismatch, expected without a real logged-in session/token) — confirming nginx and PHP both now accept the payload and the request reaches Laravel.

## Where Things Stand

Fixed and verified end-to-end at the infrastructure level. Still open, and worth a follow-up: the frontend should handle a failed submission (413, 419, validation errors, etc.) by showing an actual error message and re-enabling the submit button, instead of spinning forever — that's what made a fast, obvious server-side rejection *feel* like a slow hang. Not fixed here since it's a frontend code change, not an infra one.
