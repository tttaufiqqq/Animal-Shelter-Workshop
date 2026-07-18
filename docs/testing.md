# Testing

343 tests across 68 files, run with `php artisan test --env=testing`. This document explains what's
covered, why each layer exists, and where to look for a given feature. For *how* the harness works
(distributed-DB transactions, procedure self-commit workarounds, the browser driver's quirks), see
the comments in `tests/Concerns/*.php` and `tests/Pest.php` — this file is about coverage, not plumbing.

## Why this suite exists

The app writes to **5 real databases across 3 engines** (MariaDB, MySQL, PostgreSQL) over Tailscale —
see `db-architecture.md`. Laravel's usual `RefreshDatabase` only refreshes one connection, so without
deliberate setup, tests either silently pass while hitting nothing, or actually write to production
data. Every test in this suite runs against real `_test` copies of all 5 databases, wrapped in
transactions that roll back (or, for stored-procedure routes that self-commit, explicit truncation).
That's *why* the tests are trustworthy: a green test here means the real cross-database write path
actually ran, not a mock of it.

## The shape of the coverage

| Layer | Suite | What it proves | Files | Tests |
|---|---|---|---|---|
| Pure logic | `tests/Unit` | Business rules with no I/O — matching algorithm, fee formula, circuit breaker | 7 | 29 |
| HTTP + DB | `tests/Feature` (excl. Livewire) | A real request through routes → middleware → controller → real DB, asserted end-to-end | 39 | 161 |
| SQL layer | `tests/Procedures` | Stored procedures/triggers/views on all 4 engines, called directly — independent of whether any PHP code currently calls them | 15 | 112 |
| Server-side UI state | `tests/Feature/Livewire` | Livewire component behavior (polling, filters, computed stats) without a browser | 4 | 34 |
| Real browser | `tests/Browser` | Actual rendered pages, real clicks/typing, via headless Chromium | 3 | 7 |

Backend (Unit + Feature + Procedures) is the bulk of the suite — every write path, cross-database
join, and security boundary is proven at the HTTP/DB level. Frontend (Livewire + Browser) is
deliberately narrow: only the 4 Livewire components with actual server-side reactive state, and only
the handful of user flows where something happens **in the browser** that an HTTP-level test can't
see (client-side validation, JS-computed fee display, a real login redirect).

---

## Backend

### Auth (`Feature/Auth`, `Feature/ProfileTest`)
Registration, login/logout, password confirm/update/reset, profile edit and account deletion — the
standard Breeze flows, run against the real `users` (Postgres) connection. This is the module that
turned up the most real bugs (see below): registration and password reset were both completely
broken before this suite existed.

### Users / Admin (`Feature/Users`, `Procedures/User*`)
Suspend/unsuspend, lock/unlock (with duration and auto-expiry), forced password reset, and the audit
log dashboard (category counts, search, CSV export) — all admin actions that can lock someone out of
their own system if they're wrong. `Procedures/User*` tests the underlying `fn_user_*` Postgres
functions and views directly, independent of the controller layer.

### Animals (`Feature/Animals`, `Procedures/Animal*`)
CRUD, slot assignment (including freeing a previous slot on reassignment), medical/vaccination record
creation, and clinic/vet management — the `animals` (MySQL) connection. `Procedures/*` covers the
`sp_animal_*`/`sp_clinic_*`/`sp_vet_*` stored procedures and their validation triggers directly.

### Matching (`Unit/Matching`, `Feature/Animals/MatchingTest`)
The adopter-to-animal scoring algorithm — species, energy level, housing type, size, children/pets
compatibility — tested as pure functions (no DB) for every scoring branch, plus one Feature test
proving cache invalidation actually happens when an adopter's profile changes.

### Shelter (`Feature/Shelter`, `Procedures/SectionCategory*`, `Procedures/SlotInventory*`)
Section/category/slot/inventory CRUD and the capacity rules that keep a slot's status in sync with
occupancy — the `shelter` (MySQL) connection.

### Booking / Adoption (`Feature/Booking`, `Procedures/Booking*`, `Unit/Fees`)
Visit list management, appointment confirmation (with conflict detection — including *whose* name a
conflict error should and shouldn't reveal), cancellation, and the adoption fee formula (species base
+ per-medical-record + per-vaccination-record) as a pure unit-tested function. `Procedures/Booking*`
covers the MariaDB stored procedures and all 6 triggers directly.

### Reporting (`Feature/Reporting`, `Procedures/Report*`, `Procedures/Rescue*`, `Procedures/Image*`)
Stray report submission (including the image-upload path via a faked Cloudinary), caretaker
assignment with priority mapping, rescue status transitions, and completing a rescue by creating
animal records — the `reporting` (MariaDB) connection, including its self-committing procedures.

### Payment (`Feature/Payment`)
The highest-stakes module: the fee is always recomputed server-side and a client-supplied
`total_fee` is ignored; the ToyyibPay gateway callback is verified against the gateway's own status
(not just trusted); duplicate webhook deliveries and browser-return races produce exactly one
transaction, not two; and a forced mid-transaction failure proves the booking/animals/payment
records roll back together rather than leaving animals marked Adopted with no payment on record.

### Security (`Feature/Security`)
Cross-cutting, not tied to one module: every admin route actually requires the `admin` role; a user
can't confirm or cancel someone else's booking; unauthenticated requests are rejected before they can
write anything; and a reflection-based smoke test confirms every routed controller action still
exists (catches a renamed/deleted method before it 404s in production).

### Cross-database concerns (`Feature/CrossDb`)
Specific to this app's distributed architecture: every *logical* foreign key (a reference across
connections with no real DB constraint) is checked against whatever actually guards that write path;
what happens when an animal is deleted while something else still references it (an intentional,
documented gap — pinned, not silently accepted); and graceful degradation — the app keeps serving
readable pages (empty lists, 503s, hidden columns) instead of 500ing when one of the 5 databases is
unreachable.

### Stored procedures, triggers, and views (`Procedures/*`)
A dedicated suite, separate from `Feature`, because these procedures self-commit internally on
MySQL/MariaDB — testing them the normal transactional way would either not roll back (leaking rows)
or silently mask real behavior. Covers every procedure/trigger/view on all 4 database roles
(MariaDB × 2, MySQL, PostgreSQL) directly via `CALL`/`SELECT`, independent of whether the PHP layer
currently calls them — this is what caught procedures with dead/unreachable code paths and validation
that rejected values the real UI actually sends.

---

## Frontend

### Livewire components (`Feature/Livewire`)
The only 4 Livewire components with a server-side API worth testing directly (`Livewire::test()`,
no browser needed): the admin reports table (filtering, pagination, queryString restoration on
page load), the notification bell (merging and mapping bookings/rescues/transactions/adoptions into
one feed), the logged-in user's own report-status tracker (polling for status changes), and the
admin dashboard's computed stats (booking totals, success rate, revenue-by-species, degraded-DB
fallbacks). This is server-side reactive *state*, not rendering — it proves the component's PHP logic
is correct without needing a real browser.

### Browser end-to-end (`tests/Browser`, Pest 4 + Playwright)
Real Chromium, real clicks, real typing, driving the actual rendered app — reserved for the small set
of things that only exist in a live browser:

- **`ForcedPasswordChangeTest`** — a real login through the real form for a user flagged
  `require_password_reset`, proving the redirect-to-`/password/change` gate actually intercepts a
  live session (not just the route), that navigating away while flagged bounces back, and that
  completing the form clears the flag in the database.
- **`ReportSubmissionTest`** — native browser form validation (the browser's own "please fill this
  field" blocking a submit before any JS/fetch runs), which an HTTP-level test can't see at all since
  there's no browser to enforce `required` client-side.
- **`AdoptionJourneyTest`** — the 3-step booking modal's pure client-side JS state: it won't let you
  advance past "select animals" with nothing selected, the pay button stays disabled until you tick
  "I agree to the terms," and the fee total shown on screen (computed from each checkbox's own
  `data-fee` attribute, entirely in JS) actually matches the server-side formula.

None of the browser tests submit the final payment/report form through the browser — file uploads
and full multipart submissions are covered instead by the equivalent `Feature` HTTP test, and the
browser test only proves what genuinely needs a browser (DOM state, client-side validation, JS-driven
UI). This is a deliberate scope decision, not a gap: this Pest/Playwright combination has a real,
current limitation where multipart form bodies never reach the server correctly, so there's nothing
to gain by fighting it for coverage the HTTP-level test already provides.

---

## What testing actually found

This suite wasn't written against already-correct code — building it surfaced real, previously-unknown
production bugs, several of them serious:

- **Registration was completely broken** (500 on every submit) — wrong `Role` model imported, so
  every role lookup ran against the wrong database.
- **Password reset silently never sent an email** — two standard Laravel tables were missing entirely.
- **Email-uniqueness validation checked the wrong database in 3 places** — duplicate signups were
  never actually being caught, in production either.
- **The admin audit dashboard 500'd on every request** — a template file-split error nobody had
  triggered because nothing automated ever rendered those views.
- **A user's own status-tracking feature never worked** — a property needed to be `public`, not
  `protected`, for Livewire to persist it across polls; it silently reset every 15 seconds.
- **Booking notifications broke entirely once a booking had any animal attached** (i.e. almost
  always) — a cross-database join that MySQL/MariaDB can't actually do, caught silently by an outer
  `try/catch`.
- **A payment flow that accepted a client-supplied fee and only checked payment status loosely** —
  now recomputed server-side and cross-checked against the real gateway response.
- **Two adopter-facing dropdown values the database silently rejected** (`hdb` housing, "no size
  preference") — a real trigger validation gap that would have hard-failed real form submissions.
- **A `set_time_limit()` production safety net that broke the test harness itself** once
  browser tests reused one long-lived process — not a DB bug, but worth knowing if this pattern is
  ever reused elsewhere.

Several other things were **found, pinned as current behavior, and deliberately left unfixed** pending
a product decision — e.g. a timezone mismatch on the `users` connection, an unenforced "delete
referencing rows first" convention, and a match-scoring cap that can skip a better candidate. These
are documented at the point they're pinned (`tests/Feature/CrossDb/OrphanRiskTest.php`,
`tests/Unit/Services/DatabaseConnectionCheckerTest.php`, etc.) rather than silently fixed, since the
right fix in each case has a bigger blast radius than a test file should decide on its own.
