# Booking / Adoption — Activity Diagrams

Decision logic behind the page flow in `flow.md`. Three activities: confirming an appointment
(creating a booking), confirming a booking for payment, and completing a payment.

## 1. Confirm Appointment (visit list → Pending booking)

`ConfirmsAppointment::confirmAppointment` / `StoresBooking::storeBooking` share this shape.

```
                    ( start )
                        |
                        v
              [ Read visit list / request animals ]
                        |
                        v
                   <is it empty?>
                    /         \
                 yes            no
                  |               |
                  v               v
        [ Error: "select at    [ Validate: date not in the
          least one animal" ]    past, terms accepted ]
                  |                       |
                  v                       v
              ( end )            <all validations pass?>
                                    /            \
                                 no                yes
                                  |                  |
                                  v                  v
                        [ Show validation   [ For each animal: check
                          errors ]            Pending/Confirmed booking
                            |                 at same date+time by
                            v                 ANOTHER user ]
                          ( end )                    |
                                                      v
                                          <any conflict found?>
                                            /              \
                                          yes                no
                                           |                  |
                                           v                  v
                              [ Reject — name the        [ Create Pending Booking ]
                                other booking's owner ]        |
                                   |                            v
                                   v                  [ Attach animals via
                                 ( end )                 animal_booking ]
                                                                |
                                                                v
                                                    [ Remove animals from
                                                      visit list (visit-list
                                                      path only) ]
                                                                |
                                                                v
                                                    [ Redirect with success ]
                                                                |
                                                                v
                                                             ( end )
```

## 2. Confirm Booking for Payment

`ConfirmsBooking::confirm` — reached from the modal's Step 3 submit.

```
                    ( start )
                        |
                        v
        [ Validate: animal_ids present, terms accepted ]
                        |
                        v
                <validation passes?>
                 /               \
              no                   yes
               |                     |
               v                     v
        [ 422 errors ]      [ Load booking; is caller
               |               the booking's owner? ]
               v                     |
             ( end )         <owner matches?>
                               /          \
                            no              yes
                             |                |
                             v                v
                    [ 403 Forbidden ]  <status is Pending
                             |            or Confirmed? >
                             v              /        \
                           ( end )       no             yes
                                          |               |
                                          v               v
                                [ Reject: current   [ Do the requested
                                  status shown ]      animal_ids actually
                                          |            belong to this
                                          v            booking? ]
                                        ( end )               |
                                                    <all valid?>
                                                     /        \
                                                  no             yes
                                                   |               |
                                                   v               v
                                         [ Reject: invalid   [ Recompute fee
                                           animal selection ]  SERVER-SIDE per
                                                   |            animal (species
                                                   v            base + medical +
                                                 ( end )         vaccination) ]
                                                                       |
                                                                       v
                                                          [ Mark booking Confirmed;
                                                            stash fee/animal_ids
                                                            in session ]
                                                                       |
                                                                       v
                                                          [ Create ToyyibPay bill,
                                                            redirect to gateway ]
                                                                       |
                                                                       v
                                                                    ( end )
```

## 3. Complete a Payment

`CompletesBookingPayment::completeBookingPayment` — the shared final step reached from **both**
`paymentStatus` (browser return) and `callback` (gateway server-to-server webhook), which is exactly
why it has to be idempotent: either one can arrive first, or both can arrive for the same payment.

```
                    ( start )
                        |
                        v
              <animal_ids list empty?>
                 /              \
              yes                 no
               |                    |
               v                    v
        [ No-op: return null ]  <a Transaction already
               |                  exists for this bill_code?>
               v                    /              \
             ( end )             yes                  no
                                   |                    |
                                   v                    v
                        [ Idempotent no-op:      [ Begin transactions on
                          return the existing       booking + animals (+
                          Transaction ]              shelter, if reachable) ]
                                   |                         |
                                   v                         v
                                 ( end )           [ Mark booking Completed ]
                                                              |
                                                              v
                                                  [ For each animal: create
                                                    Adoption row, update its
                                                    slot's occupancy ]
                                                              |
                                                              v
                                                  [ Create the Transaction
                                                    record (amount, bill_code,
                                                    reference_no) ]
                                                              |
                                                              v
                                                   <any step failed?>
                                                     /            \
                                                  yes                no
                                                   |                  |
                                                   v                  v
                                        [ Roll back ALL open   [ Commit all open
                                          connections — no       connections ]
                                          animal ends up                |
                                          Adopted with no                v
                                          payment recorded ]   [ Promote caller's
                                                   |             role toward
                                                   v             "adopter" if
                                                 ( end )         not already ]
                                                                        |
                                                                        v
                                                                     ( end )
```
