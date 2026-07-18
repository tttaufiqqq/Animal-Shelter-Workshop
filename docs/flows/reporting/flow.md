# Reporting Module — Page Flow

Source: `routes/partials/stray-reporting.php`, `StrayReportingManagementController` (+ its
`Concerns/StrayReporting/{ManagesReports,ManagesRescues,UpdatesStatusWithAnimals}` traits),
`resources/views/stray-reporting/*`. Connection: `reporting` (MariaDB), with a hand-off to
`shelter`/`animals` when a rescue completes.

## 1. Public: submit a stray report

The report form is a **modal on the home page**, not a standalone page —
`stray-reporting.create` is `@include`d directly into `welcome.blade.php`, alongside
`stray-reporting.my-submitted-report` (the same page also lists the logged-in user's own past
reports and their animal matches).

```
                              GET /  (route: welcome)
                                   |
                                   v
                    +---------------------------------+
                    |   Welcome page (StrayReporting-  |
                    |   ManagementController@indexUser)|
                    |  - "Submit Stray Report" button  |
                    |  - My Submitted Reports list      |
                    |  - Animal match list (if adopter) |
                    +---------------------------------+
                                   |
                          click "Submit Stray Report"
                                   v
                    +---------------------------------+
                    |  Report Modal (stray-reporting/  |
                    |  create.blade.php)                |
                    |  - pin location on map (lat/lng)  |
                    |  - address / city / state         |
                    |  - situation/urgency dropdown      |
                    |  - upload 1-5 images               |
                    +---------------------------------+
                                   |
                        POST /reports  (reports.store)
                                   |
                   +---------------+---------------+
                   |                               |
           validation fails                 validation passes
                   |                               |
                   v                               v
        back to modal, errors        sp_report_create (status: Pending)
        shown inline, images NOT           |
        uploaded yet                  each image -> Cloudinary upload
                                       -> sp_image_create (reportID set)
                                             |
                                    any upload/insert step fails?
                                       |                |
                                      yes               no
                                       |                |
                              roll back uploaded    redirect back to "/"
                              Cloudinary images     with success message
                              already sent,         (report now shows in
                              show error            "My Submitted Reports")
```

## 2. Staff: report → assign caretaker → rescue in progress → complete

```
GET /reports/all (reports.index, admin)      GET /rescues (rescues.index, caretaker - own only)
        |                                              |
        v                                              v
+------------------------+                  +--------------------------------+
| All Reports list        |                 | My Rescues list                 |
| (stray-reporting.index)|                  | (index-caretaker.blade.php)     |
+------------------------+                  | - filter by priority/status     |
        |                                    +--------------------------------+
  click a report                                        |
        v                                          click a rescue
+------------------------+                                v
| Report Detail            |                  +--------------------------------+
| (reports.show)           |                  | Rescue Detail (rescues.show,     |
| - caretaker dropdown     |                  | show-caretaker.blade.php)       |
+------------------------+                  |  - status update form           |
        |                                    |  - "Success" reveals add-animal |
   PATCH /reports/{id}/assign-caretaker      |    rows (only if shelter+       |
   (reports.assign-caretaker)                |    animals DBs are online)      |
        |                                    +--------------------------------+
        v                                              |
  sp_rescue_assign_caretaker                  PATCH /rescues/{id}/update-status
  (creates Rescue, priority auto-             (rescues.update-status) - any status
  mapped from description text)              except a Success that carries animal
        |                                     data (see below)
  DB trigger syncs Report.report_status              |
  Pending -> Assigned                          status = In Progress / Failed /
        |                                       Success-with-no-animals-yet
        v                                              |
  back to Report Detail,                         redirect back to Rescue Detail
  "Caretaker assigned successfully!"             (Success also redirects to
  (assigning again reassigns rather              animal-management.create if no
  than duplicating the rescue)                   animal payload was sent this way)
```

## 3. Completing a rescue with animals (hands off to the Animals module)

This is the one action that writes across three connections in one request — `reporting` (rescue +
report), `animals` (new Animal rows), and reads `shelter` (available slots) — and it's the real
"Success" path from the rescue detail page (distinct from a plain status-only update above).

```
        Rescue Detail page, status = Success, animal rows filled in
        (name/species/gender/age/weight/health/slot, optional images each)
                                   |
              fetch PATCH /rescues/{id}/update-status-with-animals
              (rescues.update-status-with-animals, AJAX - see
              public/js/rescue-status-update.js)
                                   |
                    shelter AND animals DBs online?
                          |                |
                          no               yes
                          |                |
                 503 JSON error    BEGIN transaction on
                 shown inline,     'reporting' AND 'animals'
                 nothing written          |
                                   Rescue.status = Success,
                                   remarks saved; Report.report_status
                                   = Completed
                                          |
                                   for each animal entry:
                                     Animal::create(rescueID, slotID?)
                                     -> each image uploaded to Cloudinary
                                     -> Image::create(animalID)
                                          |
                              any step throws?
                                |              |
                               yes             no
                                |              |
                    ROLLBACK both        COMMIT both connections
                    connections,         redirect to rescues.show
                    undo any             ("N animal(s) added to
                    Cloudinary           the shelter") - each new
                    uploads already      Animal now also appears in
                    sent                 the Animals module (slot
                                         assignment, medical records,
                                         adopter matching, etc.)
```
