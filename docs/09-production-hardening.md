# Production Hardening

This app was originally deployable but not production-grade for unattended, single-user homelab
operation — a redeploy could wipe production data, password reset was silently broken, timestamps
on the `users` connection were off by ~8 hours, and there was no TLS, no backups, and no runtime
services beyond the web request/response cycle. This document records what changed, why, and what's
still a deliberate follow-up rather than something silently left undone.

**What "production" means for this project**: this is a portfolio deployment — the point is for
other people to see the engineering work, not to serve real users or hold real data. There is no
real user base and no real data to protect. That scope deliberately caps how far some of this
hardening goes: e.g. the payment gateway stays on ToyyibPay's sandbox permanently (see the ToyyibPay
bullet below) rather than ever switching to live payments, since there's no real money or real
adopters involved. Treat "production-grade" throughout this doc as "handled the way a real deploy
would be," not "actually serving real users."

## What changed

### Deploy pipeline no longer destroys data

`infrastructure/ansible/playbooks/app-server.yml` used to run `php artisan db:fresh-all --seed` on
**every** deploy — that command drops every table on all 5 databases and reseeds from scratch. Any
redeploy after go-live would have wiped real data. It's now: `optimize:clear` → `migrate --force`
(always, idempotent) → `db:seed --force` gated on a `storage/.provisioned` flag file that only gets
created once → `config:cache`/`route:cache`/`view:cache`/`event:cache` → `storage:link`.
`db:fresh-all` is no longer called anywhere in the playbook. The `git` clone task also now uses
`update: true` so a redeploy actually pulls new commits, and `key:generate` only runs on a genuinely
fresh `.env` (regenerating `APP_KEY` on every deploy would invalidate every existing session).

The two migrations that had only ever run against the `_test` databases —
`0001_01_01_000001_create_password_reset_tokens_table` and
`2026_01_13_000002_add_remember_token_to_users_table` — were applied to the real production
`workshop_2` databases as a one-time catch-up. Password reset was silently broken in production
until this ran.

### Timezone alignment

`config/app.php`'s timezone is `Asia/Kuala_Lumpur`, but none of the 5 DB connections set a session
timezone, so every PHP-side timestamp write disagreed with the database by ~8 hours (`audit_logs`,
Eloquent `updated_at`, `User::isLocked()`'s comparison against `locked_until`). `config/database.php`
now sets `'timezone'` on all 5 connections — `'Asia/Kuala_Lumpur'` for `users` (PostgreSQL, which has
its own tzdata), `'+08:00'` for the 4 MySQL/MariaDB connections (the named zone isn't loaded on those
servers; Malaysia has no DST, so a fixed offset is exact and never drifts). This also fixed a
documented time-of-day-dependent flake in `tests/Procedures/BookingTriggersTest.php`.

### Session/cache/queue decoupled from the `users` database

Production's `.env` had `CACHE_STORE=database` and `SESSION_DRIVER=database`, both of which resolve
to the `users` (PostgreSQL) connection — meaning a single Postgres outage would have taken down every
page's session along with it, undermining the graceful-degradation behavior the app otherwise tests
for. Both are now `file`, matching what the app's own DB circuit breaker already assumes internally.
`QUEUE_CONNECTION` is now `sync` — there are no `Jobs`/`Mail`/`Notifications` classes and no
`ShouldQueue` usage anywhere in `app/`, so a database-backed queue needing its own worker process was
unused complexity.

### Scheduler runs via systemd

`routes/console.php` schedules `db:refresh-status` (every minute) and `taufiq:refresh-stats` (every 5
minutes), but nothing ever invoked `php artisan schedule:run` on a recurring basis in production —
both had been silently dead in every deployment so far. A systemd timer
(`infrastructure/ansible/templates/laravel-scheduler.timer.j2`, fires every minute) now drives a
oneshot service that runs `schedule:run`. No queue worker was added — see above.

### TLS via nginx + Let's Encrypt

nginx used to listen on `:80` only. The playbook now requires `app_domain` and `certbot_email`
variables (fails early with a clear message if either is missing — a public DNS record for
`app_domain` and inbound `:443` must already be in place), obtains a certificate via certbot's
webroot challenge, and serves `:443` with HSTS, `X-Frame-Options`, `X-Content-Type-Options`,
`Referrer-Policy`, and `X-XSS-Protection`. `:80` redirects to `:443` once a certificate exists.
`APP_URL` is now `https://{{ app_domain }}`, `SESSION_SECURE_COOKIE`/`SESSION_ENCRYPT` are both
`true`, and `AppServiceProvider::boot()` calls `URL::forceScheme('https')` in production (nginx
terminates TLS and proxies plain HTTP to php-fpm, so Laravel never sees `https://` directly).

**Deliberately not added yet: a Content-Security-Policy.** The app loads several external CDNs
(Tailwind, Font Awesome, Leaflet, Nominatim for reverse geocoding) — a CSP needs its own scoped pass
to enumerate every legitimate external origin without breaking the map/geocoding flow. Flagged here
as a follow-up, not silently skipped.

### Trusted proxies

The public request path is `Cloudflare (real TLS termination) → cloudflared tunnel → nginx
(localhost:80) → php-fpm`. `cloudflared` and nginx both run on the same app-server box, so every
request php-fpm actually sees arrives from `127.0.0.1` — the real client's IP and the fact the
original request was HTTPS only exist in the `X-Forwarded-For`/`X-Forwarded-Proto` headers Cloudflare
attaches. Laravel trusts none of those headers by default (a proxy you don't control could otherwise
have a client forge them), so `$request->ip()` returned the proxy's own address and
`$request->isSecure()` returned `false` even on a real HTTPS visit — `URL::forceScheme('https')` (see
above) was already masking the symptom for generated links, but anything reading `isSecure()`
directly still saw plain HTTP.

Fixed in `bootstrap/app.php` — `$middleware->trustProxies(at: ['127.0.0.1'])`. `127.0.0.1` specifically
(not `'*'`) because that's the one fixed, known hop in this chain (cloudflared → nginx are always
local to each other); no other proxy sits between the internet and this box.

### Mail

Production had no `MAIL_*` configuration at all, so it silently used Laravel's own `log` mailer
default — password reset emails were written to `storage/logs` instead of being sent.
`infrastructure/ansible/templates/env-app.j2` now has a full SMTP block (`MAIL_MAILER`, `MAIL_HOST`,
`MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`),
templated the same way as the existing Cloudinary/ToyyibPay blocks. **This is the one setting that
needs a real external secret** — supply working SMTP credentials (a Gmail app password, SendGrid,
Mailgun, or a homelab relay) via `-e` or a vaulted vars file before deploying, or password reset will
continue to silently do nothing.

### Automated backups — superseded, see docs/10-backups.md

This section originally described a per-host `mysqldump`/`pg_dump` nightly timer on each of the 3 DB
VMs (`templates/backup-{mysql,postgres}.sh.j2`, `/var/backups/workshop_2` on each host). That approach
had exactly the gaps called out below (no off-VM copy, no restore drill) plus two more found later: it
never covered `msi` at all (a Windows machine, unreachable by these Linux playbooks) and had no
cross-database consistency check, despite the app having 12 undeclared logical foreign keys across
3 servers (`docs/04-foreign-keys.md`).

It has been replaced with a single coordinated backup orchestrated from app-server
(`php artisan db:backup`) that dumps all 3 physical databases, verifies the 12 logical foreign keys
still resolve, checksums and centrally retains the result, and alerts on failure. **See
`docs/10-backups.md` for the current architecture, retention policy, and restore runbook** — the
per-host scripts and systemd units referenced above no longer exist.

### Animal delete now blocks on real adoption/booking references

`ManagesAnimals::destroy()` called the delete procedure with no check for referencing rows on the
`booking` connection (`adoption`, `animal_booking`) — a different physical server, so no real foreign
key could enforce this. Deleting an adopted or booked animal silently orphaned those rows (this was
already proven by `tests/Feature/CrossDb/OrphanRiskTest.php`, which asserted the orphan happening).
The controller now checks for any referencing row first and blocks the delete with a clear error if
one exists — matching the guard pattern already used in `ManagesReports::destroy()`. It fails closed
if the `booking` connection itself is unreachable, rather than proceeding without being able to
verify. `OrphanRiskTest.php` was inverted to assert the block; verified red-then-green.

### Logging

`LOG_STACK` was never set in production, so `LOG_CHANNEL=stack` silently fell back to `'single'` — an
unbounded `storage/logs/laravel.log`. It's now `LOG_STACK=daily`, using `config/logging.php`'s own
14-day default retention. nginx's own access/error logs don't need a new logrotate entry — Ubuntu's
`nginx` package already ships one via `nginx-common`.

**Existing health surfaces, now worth pointing a monitor at**: Laravel's built-in `/up`
(`bootstrap/app.php`) and this app's own `/api/database-status` (`routes/web.php`), which reports
per-connection reachability. External error tracking (Sentry/Flare) is intentionally out of scope for
a single-user homelab — the daily log is the sink.

### Removed: non-functional email-verification scaffolding

Routes, controllers, and a view for Laravel's standard email-verification flow existed but were never
wired up — `User` didn't implement `MustVerifyEmail`, no migration ever added an `email_verified_at`
column, and registration never fired the event that would trigger a verification email. By explicit
decision (a single-user deployment gets no real value from this), the scaffolding was removed rather
than completed: the 3 controllers, the verification routes, the dead `<form>` in the profile page, and
the now-meaningless `email_verified_at` cast on `User`.

## Known follow-ups (flagged, not silently skipped)

- **Content-Security-Policy** — needs its own pass to enumerate the CDN origins this app actually
  loads without breaking the map/geocoding flow.
- ~~Off-VM backup copies~~ / ~~a real restore drill~~ — **superseded**, see `docs/10-backups.md`:
  backups are now centralized on app-server (closing the off-VM gap) with a documented, drillable
  `--into-scratch` restore path.
- **`TOYYIBPAY_BASE_URL` stays on the ToyyibPay sandbox** (`dev.toyyibpay.com`) **by design, not as an
  open decision.** This project has no real users and no real money moving through it — it exists for
  others to see the engineering work, not to process live payments. Switching to the live gateway is
  explicitly out of scope, not a pending follow-up.
- **Ansible syntax was never run through `ansible-playbook --syntax-check`** — this hardening pass was
  done from a Windows dev machine with no `ansible`/Python installed. Every YAML file was reviewed by
  hand for indentation and brace-balance correctness, but an automated syntax check on the actual
  control node is worth doing before the next real deploy.
- **`app-server.yml`'s deploy path doesn't match the real, live app-server.** The playbook and
  `env-app.j2` assume `/var/www/animal-shelter` (owned by a `workshop` user) — confirmed that path
  doesn't exist on the actual box (only the default `/var/www/html` placeholder does). The real app is
  at `/home/taufiq/Animal-Shelter-Workshop` (owned by `taufiq`), confirmed via nginx's actual `root`
  directive. This box was set up by hand at some point and the two have quietly diverged — a fresh
  playbook run wouldn't update the live site, it'd create an unused parallel copy with no error to
  signal the mismatch. Needs an explicit decision (migrate the live path to match Ansible, or update
  Ansible to match reality), not a silent fix.
- ~~SMTP credentials still needed~~ — **done.** Resend is the provider (`smtp.resend.com`, username
  `resend`), domain `mail.tttaufiqqq.com` verified (SPF/DKIM/DMARC), and the real API key is applied
  directly to app-server's live `.env` (not via the Ansible template — see the path-mismatch finding
  below). `Password::sendResetLink()` returned `passwords.sent`, no mail exception logged, and
  Resend's own dashboard confirms the message was actually relayed to Gmail — it then **bounced**,
  but only because the test recipient (`admin1@gmail.com`) is a seeded demo account
  (`database/seeders/UserSeeder.php`), not a real inbox. A hard bounce from the real destination mail
  server is evidence the relay itself works correctly (Resend accepted it, routed it, Gmail rejected
  the *mailbox*, not the sender) — this was a bad choice of test recipient on my part, not a config
  problem.
- **Still open: confirm delivery to a real inbox.** Re-run the same `Password::sendResetLink()` test
  against an email address that actually exists, and check it arrives (not just that Resend accepted
  it) before calling this fully closed.

## Verification

Everything above was verified as directly as this environment allowed:

- The full Pest suite (`php artisan test --env=testing`) was re-run after every code-level change
  (timezone config, the animal-delete guard, the email-verification removal) — **364 tests passed, 0
  failed** each time, against the real distributed test databases, not mocks.
- The two production migrations were applied and confirmed live via
  `Schema::connection('users')->hasTable('password_reset_tokens')` and `hasColumn('users',
  'remember_token')`.
- The timezone fix was confirmed with a live probe comparing PHP's `now()` against each connection's
  own session clock — the skew dropped from ~8 hours to sub-second.
- The animal-delete guard was verified red-then-green: the updated tests fail without the fix (`git
  stash` on just the controller change) and pass with it.
- The `mysqldump` backup command was run directly against the real local MySQL instance (a safe,
  read-only operation) and produced a valid dump containing real tables, procedures, and triggers.
  `pg_dump` was reviewed by hand — no PostgreSQL client tools are installed on this dev machine.
- What could **not** be verified from this environment: a live systemd timer actually firing, a real
  TLS handshake against a public domain, an actual SMTP delivery, and a real backup restore drill —
  all of these need the real homelab hardware and, in two cases, external secrets/domains this
  session didn't have.
