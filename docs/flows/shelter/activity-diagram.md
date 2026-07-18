# Shelter Module — Activity Diagrams

## Generic create/edit/delete pattern (Sections, Categories, Inventory, and Slot create)

Every write action in this module (except slot update/delete, which have real extra logic below)
follows the same shape:

```
        (start)
           |
           v
   [ Validate request ]
           |
      -----------
     / valid?    \
    /             \
  no               yes
   |                 |
   v                 v
[ Flash validation   [ Call ShelterProcedureService
  errors, keep         (create/update/delete) ]
  form input ]               |
   |                    -----------
   |                   / success?  \
   |                  /             \
   |                no               yes
   |                 |                 |
   |                 v                 v
   |         [ Flash error ]   [ Flash success ]
   |                 |                 |
   +-----------------+-----------------+
                      |
                      v
                   (end, redirect back to index)
```

## Slot status recomputation (`updateSlot()`)

```
        (start)
           |
           v
   [ Validate request: name, sectionID, capacity, status? ]
           |
      -------------------------
     / status explicitly       \
     \ submitted as             /
      \ 'maintenance'?         /
       -------------------------
        yes            no
         |               |
         v               v
 [ statusToSet =    [ Is the 'animals' DB reachable? ]
   'maintenance' ]         |
         |            -----------
         |           / yes       \ no
         |          /             \
         |         v               v
         |   [ count animals   [ animalCount = 0 ]
         |     where slotID ]         |
         |         |                 |
         |         +--------+--------+
         |                  |
         |                  v
         |         [ animalCount >= capacity? ]
         |             /            \
         |           yes             no
         |            |               |
         |            v               v
         |   [ statusToSet =   [ statusToSet =
         |     'occupied' ]      'available' ]
         |            |               |
         +------------+-------+-------+
                              |
                              v
              [ Persist slot with computed status
                via ShelterProcedureService::updateSlot() ]
                              |
                              v
                           (end)
```

Note: the animal count used here is read live at submit time, from this controller's own query —
it is not informed by whatever the Animals module's own slot-assignment logic just did in the same
request cycle (it isn't the same cycle; assignment happens from a different route entirely). The two
paths can, in principle, disagree momentarily until the next slot edit or animal move recomputes it.

## Slot deletion guard (`deleteSlot()`)

```
        (start)
           |
           v
  [ Look up the slot ]
           |
      -----------
     / found?    \
    no            yes
     |              |
     v              v
 [ Flash    [ Is the 'animals' DB reachable? ]
  "not             |
  found" ]     -----------
     |        / yes       \ no
     |       /             \
     |      v               v
     | [ count animals  [ animalCount = 0,
     |   where slotID ]   log a warning ]
     |      |                 |
     |      +--------+--------+
     |               |
     |               v
     |      [ animalCount > 0 ? ]
     |          /          \
     |        yes           no
     |         |             |
     |         v             v
     |  [ Block: flash   [ Call
     |    "has N          ShelterProcedureService
     |    animal(s)" ]     ::deleteSlot() ]
     |         |             |
     +---------+------+------+
                      |
                      v
                   (end)
```
