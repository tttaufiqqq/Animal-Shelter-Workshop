# Shelter Module — Use Case Diagram

Only one actor reaches this module today: any authenticated user. `routes/partials/shelter-management.php`
wraps the whole group in `auth` middleware only — there is no `role:` restriction here (unlike, say,
`admin/*` routes), so "Authenticated Staff" below really does mean anyone logged in, not a specific role.

```
                        Shelter Management
                 +---------------------------------------+
                 |                                        |
                 |     ( Manage Sections )                |
                 |      /              \                  |
                 |   <<include>>    <<include>>            |
                 |    /                   \                |
                 |  ( Create      ( Edit/Delete             |
                 |   Section )      Section )               |
                 |                                        |
   +--------+    |     ( Manage Categories )                |
   |Authen- |----|      /              \                    |
   |ticated |    |   <<include>>    <<include>>              |
   |Staff   |    |    /                   \                  |
   +--------+    |  ( Create      ( Edit/Delete               |
       |         |   Category )     Category )                 |
       |         |                                             |
       |         |     ( Manage Slots )                        |
       |         |      /      |        \                      |
       |         |  <<include>> <<include>> <<extend>>          |
       |         |    /         |            \                  |
       |         |  ( Create  ( Edit/Delete  ( View Slot          |
       |         |   Slot )     Slot )         Details )--<<include>>--( View Animal
       |         |                 \                                    Details )
       |         |              <<extend>>
       |         |                   \
       |         |            ( Recompute Slot
       |         |              Status/Capacity )
       |         |
       +---------|     ( Manage Inventory )
                 |      /              \
                 |   <<include>>    <<include>>
                 |    /                   \
                 |  ( Create      ( View Details /
                 |   Inventory )    Edit/Delete )
                 |                                        |
                 +---------------------------------------+
```

Notes on the relationships actually present in the code:

- **"Edit/Delete Slot" `<<extend>>`s "Recompute Slot Status/Capacity"** — every slot update
  recomputes `status` from the live animal count vs. capacity (unless `maintenance` is explicitly
  set), and delete is blocked outright while any animal still occupies the slot. This is the one
  place in the module with real business logic beyond plain CRUD (see `activity-diagram.md`).
- **"View Slot Details" `<<include>>`s "View Animal Details"** — the slot detail modal links out to
  each animal currently assigned to it, which calls the same `getAnimalDetails()` endpoint the
  Animals module uses.
- Sections, Categories, and Inventory have no extra business rules beyond validation — they're
  documented here for completeness, not because they hide any real branching logic.
