# Users / Admin — Page Flow

Covers `routes/partials/admin.php`'s `admin/audit/*`, `admin/users/*`, and `admin/caretaker/*` groups
(all gated by `['auth', 'role:admin']`). `admin/shelter-management/*` in the same file belongs to the
Shelter module, documented separately.

Two things worth knowing before the diagrams: there is **no dedicated "user list" page** — suspend/
lock/unlock/force-reset are all triggered from a modal opened on top of the audit log dashboard (a log
row's "Manage User" button), not from a standalone user-management screen. And every user-management
action is a **JSON/AJAX endpoint** (`UserManagementController`) — the "page flow" for those is really
"stay on the same page, modal opens/closes, a JS-rendered result appears in it."

## A — Audit dashboard navigation

```
                        admin/audit/  (AuditController::index)
                        +---------------------------------+
                        | Overview: total/today/failed     |
                        | logs, category counts,           |
                        | last 20 recent log rows          |
                        +---------------------------------+
                            |        |        |       |
              +-------------+  +-----+  +-----+  +----+--------+
              |                |        |               |
              v                v        v               v
   admin/audit/all   admin/audit/    admin/audit/   admin/audit/
   (.all)             authentication  payments        animals
   +-------------+    (.authentication)(.payments)    (.animals)
   | every log,  |    +------------+  +------------+ +------------+
   | filterable  |    | + suspicious|  | + total    | | filter by  |
   | by date/    |    | -user       |  | revenue    | | animal_id  |
   | category/   |    | detection   |  | filter by  | +------------+
   | action/     |    | (repeat     |  | booking_id/|
   | status/     |    | failed      |  | amount     |       |
   | search      |    | logins,     |  | range      |       v
   +-------------+    | many IPs)   |  +------------+  admin/audit/rescues
        |             +------------+        |         (.rescues)
        |                   |                |         filter by rescue_id/
        |                   |                |         priority
        v                   v                v               |
   admin/audit/export/{category}  <-------------------------- +
   (.export) — streams a CSV, same filters as whichever
   category view it was triggered from

   Any log row with a metadata.correlation_id also links to:
   admin/audit/timeline/{correlationId}  (.timeline)
   — every log sharing that id, grouped by source database, in
     chronological order (traces one action across 5 databases)
```

## B — Managing a user (from within the audit log table)

```
   Any admin/audit/* log-table row
   +--------------------------------------------------+
   | ... | user@example.com | [Manage User] button     |
   +--------------------------------------------------+
                |  onclick="openUserManagementModal(id, email)"
                v
   +--------------------------------------------------+
   |  User Management Modal (client-rendered)          |
   |  GET admin/users/{id}/activity  (.activity)        |
   |  -> login/failed-login counts, unique IPs,          |
   |     last 20 auth events, suspicious-pattern flags,  |
   |     can_manage (false if target is self or admin)   |
   +--------------------------------------------------+
        |            |            |              |
        v            v            v              v
   [Suspend]     [Lock]       [Unlock]     [Force Password Reset]
   POST          POST         POST         POST
   admin/users/  admin/users/ admin/users/ admin/users/
   {id}/suspend  {id}/lock    {id}/unlock  {id}/force-password-reset
        |            |            |              |
        v            v            v              v
   JSON success/error, modal shows the result inline — no page navigation
```

## C — Creating a caretaker account

```
   admin/caretaker/  (CaretakerController::index)
   +----------------------------------+
   | Paginated list of users with the |
   | 'caretaker' role, + a create form |
   +----------------------------------+
              |
              | POST admin/caretaker/store
              v
   +----------------------------------+
   | Validate (unique email, confirmed |
   | password, required contact fields)|
   +----------------------------------+
          |                    |
     validation fails      succeeds
          |                    |
          v                    v
   redirect back with    User::create() + assignRole('caretaker')
   errors (named bag      -> redirect back with success message
   'caretaker') + input
```
