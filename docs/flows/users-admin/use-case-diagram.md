# Users / Admin — Use Case Diagram

Single actor: every route in this module is gated by `['auth', 'role:admin']` — there is no separate
"moderator" or "support" role in `admin/audit/*`, `admin/users/*`, `admin/caretaker/*`. The one
recurring constraint (an admin can't act on themself or on another admin) is modeled as a note on the
relevant use cases rather than a second actor, since the system — not a different actor — enforces it.

```
                              +-------------------------------------------+
                              |              Users / Admin                |
                              |                                           |
      +-------+               |   (o View Audit Dashboard)                |
      |       |-------------->|        |                                  |
      |       |               |        | <<extend>>                       |
      |       |               |        v                                  |
      |       |               |   (o View Category Logs)                  |
      |       |               |     [authentication / payments /          |
      |       |               |      animals / rescues / all]             |
      |       |               |        |                                  |
      |       |               |        | <<include>>                     |
      |       |               |        v                                  |
      |       |               |   (o Filter Logs)                         |
      |       |               |        |                                  |
      |       |               |        | <<extend>>                      |
      |       |               |        v                                  |
      |       |               |   (o Export Logs to CSV)                  |
      |       |               |                                           |
      |       |-------------->|   (o View Correlation Timeline)           |
      |       |               |     (trace one action across 5 DBs        |
      |       |               |      via a log's correlation_id)          |
      |       |               |                                           |
      | Admin |-------------->|   (o Manage User Account) <---+           |
      |       |               |        ^        ^        ^    |          |
      |       |               |        |        |        |    |          |
      |       |               |  <<include>><<include>><<include>>       |
      |       |               |        |        |        |    |          |
      |       |               |  (o Suspend)(o Lock) (o Unlock)          |
      |       |               |                                          |
      |       |               |   (o Force Password Reset) --------------+
      |       |               |     <<include>> View User Activity        |
      |       |               |     (auth stats + suspicious-pattern       |
      |       |               |      detection, feeds the "can_manage"     |
      |       |               |      guard the modal itself checks)        |
      |       |               |                                            |
      |       |-------------->|   (o Create Caretaker Account)             |
      +-------+               |                                            |
                              +--------------------------------------------+

  Constraint (not a UML relation — a business rule enforced in
  ManagesUserAccounts, not the diagram): Suspend, Lock, and Force
  Password Reset all refuse to act when the target user is the acting
  admin themself, or when the target holds the 'admin' role.
```
