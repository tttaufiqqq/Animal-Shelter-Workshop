# Animals Module — Use Case Diagram

Actors: **Caretaker** (manages animals/clinics/vets/medical records for their own rescues),
**Admin** (same, unrestricted), **Adopter** (views animals and matches). Caretaker and Admin
share the same use cases in this module — the code does not restrict animal/clinic/vet CRUD to
one role over the other beyond requiring authentication, so they're drawn as a generalization.

```
                          Animal Management Module
                    ,-----------------------------------,
                    |                                     |
                    |        ( Browse Animals )           |
                    |               |                     |
                    |        ( View Animal Detail )        |
                    |               |                     |
                    |     <<include>>|                     |
                    |               v                     |
                    |     ( View Medical/Vaccination        |
                    |       History )                      |
                    |                                     |
   .---------.      |        ( Create Animal )            |
   | Adopter |------|               |                     |
   '----+----'      |     <<include>>                     |
        |            |               v                     |
        |            |     ( Upload Animal Images )        |
        |            |                                     |
        |            |        ( Update Animal )            |
        |            |               |                     |
        |            |     <<extend>> (only if slot         |
        |            |               changes)               |
        |            |               v                     |
        |            |     ( Assign / Reassign Slot )       |
        |            |                                     |
        |            |        ( Delete Animal )            |
        |            |               |                     |
        |            |     <<include>>                     |
        |            |               v                     |
        |            |     ( Free Vacated Slot )            |
        |            |                                     |
        |            |        ( Manage Clinics )           |
        |            |                                     |
        |            |        ( Manage Vets )              |
        |            |               ^                     |
        |            |     <<include>> (vet must            |
        |            |     reference a clinic)               |
        |            |                                     |
        |            |     ( Add Medical Record )          |
        |            |     ( Add Vaccination Record )      |
        |            |                                     |
        |     .------------------------.                   |
        +---->| ( Manage Own Adopter    |                   |
        |     |   Profile )             |                   |
        |     '------------------------'                    |
        |            |                                     |
        |     <<include>>                                  |
        |            v                                     |
        +---->( View Computed Matches )                     |
        |            |                                     |
        |     <<extend>> (force refresh)                    |
        |            v                                     |
        +---->( Force-Refresh Matches )                      |
                    |                                     |
                    '-----------------------------------'
                                    ^
                                    |
                    .---------------+---------------.
                    |                                |
              .-----------.                    .---------.
              | Caretaker |                     |  Admin  |
              '-----------'                     '---------'
```

Notes on the real constraints these associations imply:
- "View Computed Matches" always `<<include>>`s "Manage Own Adopter Profile" existing first — the
  server returns a "complete your profile" message rather than a match list if no profile row exists.
- "Manage Vets" `<<include>>`s a valid clinic reference — `sp_vet_create` rejects a `clinicID` that
  doesn't exist.
- "Delete Animal" always `<<include>>`s "Free Vacated Slot" when the animal held one, but does *not*
  check whether an adoption or active booking still references the animal first (a documented,
  intentional gap — see `docs/testing.md`'s "orphan risk" note, not modeled as a use case here since
  nothing in the system currently enforces it).
