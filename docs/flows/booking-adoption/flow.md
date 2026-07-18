# Booking / Adoption — Page Flow

Covers `routes/partials/booking-adoption.php`: the visit list, the two ways a booking gets created,
the 3-step booking modal, and the ToyyibPay payment round-trip. Source: `BookingAdoptionController`
and its `Concerns\BookingAdoption\*` traits (`ManagesVisitList`, `ConfirmsAppointment`, `StoresBooking`,
`ListsBookings`, `ConfirmsBooking`, `CreatesPaymentBill`, `HandlesPayment`, `CompletesBookingPayment`,
`ManagesBookings`, `VerifiesPaymentGateway`, `CalculatesAdoptionFee`).

## 1. Visit list → confirm appointment

The primary path: build a visit list from the Animals module, then confirm it into a booking.

```
 [ Animal detail / matching page ]  (Animals module)
            |
            | POST /visit-list/add/{animal}   (visit.list.add)
            v
 [ Visit List page ]  GET /visit-list  (visit.list -> indexList)
   view: booking-adoption.visit-list
   - lists animals currently on the user's visit list
   - "Remove" button per animal --------------------------+
   - "Confirm Appointment" form (date, time, terms)        |
            |                                              |
            | POST /visit-list/confirm (visit.list.confirm)|
            v                                              |
   [ ConfirmsAppointment::confirmAppointment ]             |
   - visit list must be non-empty                          |
   - every animal must still be on caller's visit list      |
   - appointment date must not be in the past               |
   - terms must be accepted                                 |
   - blocks if any animal already has a Pending/Confirmed   |
     booking for ANOTHER user at the same date+time (names  |
     the other booking's owner in the error)                |
            |                                               |
      success: creates one Pending Booking, attaches all     |
      animals via animal_booking, clears them from the       |
      visit list                                             |
            |                                               |
            v                                               |
 [ redirect -> animal-management.index ]  "success" flash    |
                                                              |
 DELETE /visit-list/remove/{animalId} (visit.list.remove) <--+
   -> back to Visit List page, animal removed
```

## 2. Direct "Book Appointment" modal (skips the visit list)

A second, parallel creation path used by the "Book Appointment" modal seen elsewhere in the app
(e.g. on an animal's detail card) — same shape as above but in one step, no visit list involved.

```
 [ Book Appointment modal ]  (any page that renders it)
            |
            | POST /adoption/book  (adoption.book)
            v
 [ StoresBooking::storeBooking ]
   - same validation family as confirmAppointment (date, terms, conflict check)
   - creates one Pending Booking + animal_booking rows directly
            |
            v
 [ redirect -> animal-management.index ]  "success" flash
```

## 3. My Bookings → the 3-step modal → payment

```
 [ My Bookings page ]  GET /bookings/index  (bookings.index -> ListsBookings::index)
   view: booking-adoption.main  (booking-adoption/main/bookings-table.blade.php)
   - one row per booking owned by the current user
   - "View" button per row: onclick="openBookingModal(id)" (opens, does not navigate)
            |
            v
   [ Booking Modal #bookingModal-{id} ]  (booking-modal-steps/*, public/js/booking-modal.js)
   +--------------------------------------------------------------+
   | Step 1: Details        -> Next -> Step 2: Select -> Next ->  |
   | (booking meta)            (animal checkboxes,       Step 3:  |
   |                            per-animal fee shown)     Confirm |
   +--------------------------------------------------------------+
                                                              |
       Step 3 shows: selected animals, fee breakdown          |
       (populated client-side from each checkbox's own        |
       data-fee/-base-fee/-medical-fee/-vaccination-fee),      |
       and a required "I agree to the terms" checkbox that     |
       enables the "Proceed to Payment" submit button.         |
                                                              |
            | PATCH /bookings/{booking}/confirm (bookings.confirm)
            v
   [ ConfirmsBooking::confirm ]
   - re-validates animal_ids belong to THIS booking + terms accepted
   - recomputes the fee SERVER-SIDE from the selected animals
     (CalculatesAdoptionFee) — a client-supplied total is never trusted
   - marks the booking Confirmed
   - stashes booking_id/adoption_fee/animal_ids in session
            |
            v
   [ CreatesPaymentBill::createBill ]
   - calls the ToyyibPay API, gets back a payment page URL
            |
            v
 [ ToyyibPay's own hosted payment page ]  (off-site, FPX / card)
            |
            +---------------------------------------------+
            | (browser redirect, has a session)            | (server-to-server, no session)
            v                                               v
 GET /payment/status (toyyibpay-status)          POST /payment/callback (toyyibpay-callback)
 [ HandlesPayment::paymentStatus ]               [ HandlesPayment::callback ]
   - re-verifies the gateway's own status           - verifies via isGatewayConfirmed()
     via getBillTransactions(), not just              (not auth/CSRF — this route has neither)
     trusting status_id from the URL                - parses booking id out of the refno
            |                                               |
            +-------------------+---------------------------+
                                v
                [ CompletesBookingPayment::completeBookingPayment ]
                - idempotent on bill_code: a second call for an
                  already-recorded bill is a no-op, returns the
                  existing Transaction
                - atomically: Booking -> Completed, creates
                  Transaction + one Adoption row per animal,
                  updates each animal's slot occupancy
                - promotes the user's role (public user/caretaker
                  -> adopter) on first successful adoption
                                v
                [ redirect -> booking:main, show_payment_modal=true ]
                   My Bookings page shows the payment-result modal
```

## 4. Cancelling a booking

```
 [ My Bookings page ]
            |
            | PATCH /bookings/{booking}/cancel  (bookings.cancel)
            v
 [ ManagesBookings::cancel ]
   - only Pending or Confirmed bookings can be cancelled
   - Completed / already-Cancelled -> rejected with the current status in the error
            |
            v
 [ redirect -> booking:main ]  success or error flash
```

## Admin view

`GET /bookings/all` (`bookings.index-admin` → `ListsBookings::indexAdmin`) is the same table/modal
UI as My Bookings, but unscoped to one user — filterable by user search, booking id, status, and
date range. No separate confirm/cancel/payment logic; it reuses the same routes above.
