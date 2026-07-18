# Reporting Module — Activity Diagrams

Decision logic behind the page flow in `flow.md`. Source: `ManagesReports::store()`,
`ManagesRescues::{assignCaretaker,updateStatusCaretaker}()`, `UpdatesStatusWithAnimals::updateStatusWithAnimals()`.

## Submit a stray report

```
                    ( start )
                        |
                        v
            validate lat/lng, address,
            city, state, description,
            1-5 images (each <=5MB, jpg/png/gif)
                        |
                   <validation OK?>
                    /         \
                  no           yes
                   |            |
        return errors      call sp_report_create
        inline (no          (status = Pending)
        uploads attempted)        |
                   |          <insert OK?>
                   |           /       \
                   |         no        yes
                   |          |          |
                   |     throw ------>  for each uploaded image:
                   |     exception       upload to Cloudinary,
                   |          |          call sp_image_create
                   |          |               |
                   |          |          <every image OK?>
                   |          |           /          \
                   |          |         no            yes
                   |          |          |              |
                   |          +--> destroy already--     |
                   |               uploaded Cloudinary    |
                   |               images, show error      |
                   v                    |                  v
              ( end: form            ( end: error    ( end: redirect "/"
                re-shown              shown )          "Report submitted
                with errors )                           successfully!" )
```

## Assign / reassign a caretaker

```
                    ( start )
                        |
                        v
              validate caretaker_id present,
              load Report, load caretaker
              from the 'users' connection
                        |
                <caretaker found and
                 users DB reachable?>
                  /              \
                no                yes
                 |                 |
        show error,       priority = getPriorityFromDescription(
        keep existing     report.description)
        assignment          - 3 fixed "critical" descriptions
                 |           - 4 fixed "high" descriptions
                 |           - anything else -> "normal"
                 |                 |
                 |         call sp_rescue_assign_caretaker
                 |         (report.id, caretaker.id, priority)
                 |                 |
                 |         <report already had a rescue?>
                 |           /                    \
                 |     reassignment           first assignment
                 |     (keeps same rescue      (creates a new
                 |      row, swaps             Rescue row,
                 |      caretakerID)           status = Scheduled)
                 |           |                      |
                 |     audit: caretaker_       audit: caretaker_
                 |     reassigned              assigned
                 |           \                      /
                 |            \                    /
                 |             v                  v
                 |        DB trigger syncs Report.report_status
                 |        Pending -> Assigned (this is a MariaDB
                 |        trigger, not application code)
                 |                    |
                 v                    v
        ( end: error           ( end: redirect back,
          shown )                "Caretaker assigned
                                  successfully!" )
```

## Transition a rescue's status (without adding animals)

```
                    ( start )
                        |
                        v
        target status in {Scheduled, In Progress,
        Success, Failed}?
                  |            |
                 no           yes
                  |            |
        422 validation   <target is Success
        error            or Failed?>
                          /          \
                        yes           no
                         |             |
                remarks required   (no remarks
                (min 10 chars)?    required)
                  /        \            |
                no         yes          |
                 |          |           |
          422 error    <target is Success?>     <---+
                          /        \                |
                        yes         no               |
                         |           |                |
              <shelter AND animals   apply the        |
               DBs both online?>     status update -->+
                /          \         (rescue owned
              no           yes       by this caller,
               |            |        via .where('caretakerID',
        error: cannot   apply the    Auth::id()))
        complete,       update
        DBs offline          |
               |        audit: status_updated, plus a
               |        second sync-note audit entry when
               |        landing on In Progress / Success / Failed
               |             |
               |        <landed on Success?>
               |          /           \
               |        yes            no
               |         |              |
               v    redirect to      redirect back,
        ( end: error   animal-        "Rescue status
          shown, no    management     updated"
          write made)  .create,
                       prefilled
                       rescue_id
                            |
                            v
                     ( end: caretaker now
                       adds the animal via
                       the Animals module )
```

## Complete a rescue with animals (the atomic, cross-connection path)

```
                    ( start )
                        |
                        v
        shelter AND animals AND reporting
        all reachable?
              |              |
             no             yes
              |              |
        503, nothing    BEGIN transaction on
        written         'reporting' AND 'animals'
              |               |
              |          validate status=Success,
              |          remarks (>=10 chars), animals
              |          (JSON, non-empty)
              |               |
              |          <validation OK, and
              |           rescue belongs to caller?>
              |             /            \
              |           no             yes
              |            |              |
              |      rollback both   Rescue.status = Success,
              |      connections,    remarks saved; Report
              |      404/422         .report_status = Completed
              |            |              |
              |            |         for each animal in payload:
              |            |           Animal::create (rescueID,
              |            |           optional slotID)
              |            |           -> upload each image to
              |            |              Cloudinary, create Image row
              |            |              |
              |            |         <any create/upload step throws?>
              |            |           /                    \
              |            |         yes                    no
              |            |          |                      |
              |            |   rollback BOTH          COMMIT BOTH
              |            |   connections, destroy   connections
              |            |   any Cloudinary images        |
              |            |   already uploaded this        |
              |            |   request                       |
              |            v          |                      v
              +----->( end: error, rescue    ( end: redirect to rescues.show,
                       state unchanged )       "N animal(s) added" - each new
                                                animal is now live in the
                                                Animals module )
```
