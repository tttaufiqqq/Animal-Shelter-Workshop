# Dashboard & Notifications — Use Case Diagram

```
                    Dashboard & Notifications
        +--------------------------------------------------------+
        |                                                        |
        |    ( View Booking Analytics Dashboard )                |
        |              |                                         |
        |     <<include>>                                        |
        |              v                                         |
        |    ( Compute Metrics for Selected Year )                |
        |              ^                                         |
        |     <<extend>> (only on dropdown change)                |
        |              |                                         |
        |    ( Change Dashboard Year Filter )                     |
        |                                                        |
        |    ( View Database Connectivity Warning )               |
        |     <<extend>> (only when a connection is down)         |
        |                                                        |
   .-----------.                                                 |
  (   Admin    )-----------------------------.                   |
   '-----------'                              \                  |
        |                                       \                 |
        |    ( View Notification Feed ) <--------'                |
        |              |                                          |
        |     <<include>>                                         |
        |              v                                          |
        |    ( Load Merged Notifications )                        |
        |     <<include>>--( Map Bookings/Rescues/Transactions/    |
        |                     Adoptions to Notification Shape )   |
        |                                                          |
        |    ( Mark One Notification as Read )                    |
        |                                                          |
        |    ( Mark All Notifications as Read )                   |
        |                                                          |
        +--------------------------------------------------------+
```

## Notes

- **Admin** is the only actor — both features live entirely behind `admin.dashboard`'s layout
  (dashboard page + topbar bell on every admin page); neither has any adopter/caretaker/public-facing
  surface.
- "View Database Connectivity Warning" and "Change Dashboard Year Filter" are drawn as `<<extend>>`
  of the base dashboard view because they're both conditional/optional extensions of the same base
  use case, not separate journeys a user starts on their own.
- "Load Merged Notifications" is its own use case (not folded into "View Notification Feed") because
  it can also be triggered indirectly — any component can dispatch a `notificationRead` event that
  the bell listens for, independent of a user actively opening the dropdown.
- No `<<include>>` cycle back to the other modules is drawn here even though the notification feed's
  *data* comes from Bookings (Booking/Adoption), Rescues (Reporting), Transactions/Adoptions
  (Payment) — those are read-only source queries, not use cases this module offers or depends on
  behaviorally; see each of those modules' own diagrams for the use cases that actually create that
  data.
