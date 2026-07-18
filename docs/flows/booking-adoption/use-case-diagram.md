# Booking / Adoption — Use Case Diagram

```
                         Booking / Adoption Module
        +--------------------------------------------------------------+
        |                                                              |
        |     ( Add Animal to Visit List )                             |
        |              ^                                               |
        |              |                                               |
        |     ( Remove Animal from Visit List )                        |
        |              ^                                               |
        |              |                                               |
        |     ( Confirm Appointment )----<<include>>---( Check Booking  |
        |              ^                                 Conflict )    |
        |              |                                               |
        |     ( Book Appointment Directly )--<<include>>--(same check) |
        |              ^                                               |
        |              |                                               |
   .-----------.       |                                               |
  ( Adopter /  )--------                                               |
  ( Public User)                                                       |
   '-----------'       |                                               |
        |              v                                               |
        |     ( View My Bookings )                                     |
        |              ^                                               |
        |              |                                               |
        |     ( Confirm Booking for Payment )--<<include>>--( Recompute |
        |              ^                                       Fee     |
        |              |                                       Server- |
        |     ( Pay via ToyyibPay Gateway )--<<extend>>-->     Side )  |
        |              ^            (only when confirmed)              |
        |              |                                               |
        |     ( Cancel Booking )                                       |
        |                                                              |
        |                        .------------------.                 |
        |                       ( ToyyibPay Gateway  )                 |
        |                       ( (external actor)   )                 |
        |                        '------------------'                 |
        |                                |                             |
        |                                v                             |
        |                       ( Return Browser to                    |
        |                         Payment Status Page )                |
        |                                |                             |
        |                       ( Send Server-to-Server                |
        |                         Payment Callback )                   |
        |                                |                             |
        |                                v                             |
        |                       ( Complete Booking Payment )           |
        |                       --<<include>>--( Create Transaction +  |
        |                                          Adoption Records )  |
        |                       --<<include>>--( Update Slot Occupancy)|
        |                       --<<extend>>-->( Promote User to        |
        |                          (first adoption)  Adopter Role )    |
        |                                                              |
        |     ( View All Bookings (unscoped) )  <----.                 |
        |                                             \                |
   .-----------.                                       \               |
  (   Admin    )----------------------------------------'              |
   '-----------'                                                       |
        +--------------------------------------------------------------+
```

## Notes

- **Adopter/Public User** is the primary actor for every booking-creation and payment use case.
  "Public User" and "Caretaker" both start out able to book; a successful first adoption promotes
  either toward the "adopter" role (see `HandlesPayment::paymentStatus`).
- **Admin** only gets one extra use case here — an unscoped view of every booking
  (`bookings.index-admin`) — everything else (confirm/cancel/pay) is identical to the adopter flow
  and not admin-specific.
- **ToyyibPay Gateway** is a genuine external actor, not part of this system: it receives a bill
  request, hosts its own payment page, then independently drives two different re-entry points back
  into this system (browser redirect + server callback) — see `activity-diagram.md`'s "Complete a
  Payment" activity for why both converge on the same idempotent operation.
- "Check Booking Conflict" is `<<include>>`d by both booking-creation use cases because
  `ConfirmsAppointment` and `StoresBooking` both run the same same-animal/same-slot conflict check —
  it's shared logic, not two separate features.
- "Pay via ToyyibPay Gateway" is drawn as `<<extend>>` from "Confirm Booking for Payment" because it
  only happens conditionally — a booking staying Pending/Confirmed without ever reaching payment is
  a valid, unremarkable end state (nothing forces the user to complete checkout).
