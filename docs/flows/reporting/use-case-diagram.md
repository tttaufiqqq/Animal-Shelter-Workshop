# Reporting Module — Use Case Diagram

Actors are drawn from who each route in `routes/partials/stray-reporting.php` is actually
reachable by: any authenticated user for report submission/viewing, and role-gated actions for
the rest (verified against `ManagesReports`/`ManagesRescues`/`UpdatesStatusWithAnimals`, which
scope caretaker actions to `where('caretakerID', Auth::id())` rather than a route-level role
check).

```
                     Stray Reporting Module

   o                                                    o
  /|\   Public /                                        |
   |    Adopter                                        /|\  Admin
  / \   (any authed                                    / \
        user)
   |                        +------------------------------+
   +----------------------->|  Submit Stray Report          |
   |                        +------------------------------+
   |                              |            |
   |                        <<include>>   <<include>>
   |                              v            v
   |                 +----------------+  +----------------+
   |                 | Pin Location   |  | Upload Images  |
   |                 | on Map         |  | (1-5, JPEG/PNG)|
   |                 +----------------+  +----------------+
   |
   |                        +------------------------------+
   +----------------------->|  View My Submitted Reports    |
                            +------------------------------+
                            (shown on the same home page,
                             includes each report's status
                             and, for adopters, animal matches)

                            +------------------------------+
        Admin ------------->|  View All Reports             |
                            +------------------------------+
                                          |
                                          v
                            +------------------------------+
        Admin ------------->|  Assign Caretaker to Report   |
                            +------------------------------+
                                    |
                              <<include>>
                                    v
                            +------------------------------+
                            | Compute Priority From         |
                            | Description                   |
                            +------------------------------+


                     o
                    /|\   Caretaker
                    / \
                     |
                     |            +------------------------------+
                     +----------->|  View My Assigned Rescues     |
                     |            +------------------------------+
                     |               (filterable by priority/status)
                     |
                     |            +------------------------------+
                     +----------->|  Update Rescue Status          |
                     |            +------------------------------+
                     |                    |
                     |              <<extend>>  (only when target status
                     |                    v      is Success or Failed)
                     |            +------------------------------+
                     |            | Provide Remarks (>=10 chars)  |
                     |            +------------------------------+
                     |
                     |            +------------------------------+
                     +----------->|  Complete Rescue With Animals  |
                                  +------------------------------+
                                    |                    |
                              <<extend>> of         <<include>>
                              "Update Rescue              v
                              Status" (the          +------------------------------+
                              Success-with-          | Create Animal Record          |
                              animal-payload         | (hands off to the Animals     |
                              variant)               |  module - slot assignment,    |
                                                      |  medical records, matching)   |
                                                      +------------------------------+
```

**Notes on constraints that shape this diagram** (see `activity-diagram.md` for the full decision
logic): a caretaker can only view/update rescues where `caretakerID` matches their own id — there
is no "view any rescue" use case for a caretaker, by design. "Complete Rescue With Animals" is
gated behind both the `shelter` and `animals` connections being reachable, since it writes to both
in one transaction alongside `reporting`. Report submission has no location/image bypass — both
are hard-required by validation, which is also what `tests/Feature/Reporting/ReportSubmissionTest.php`
and `tests/Browser/ReportSubmissionTest.php` pin.
