# Dashboard & Notifications — Page Flow

Two admin-facing Livewire features that don't have their own multi-page journeys — they're a
single dashboard screen and a bell embedded in every admin page's topbar. Source:
`app/Livewire/Dashboard.php` + `app/Livewire/Dashboard/*.php` (8 sub-components, see their own
`README.md`), `app/Livewire/Notifications.php` + `Concerns/Notifications/{LoadsNotifications,
MapsNotificationTypes,NotificationInteractions}.php`, `routes/web.php`'s `/dashboard` route,
`resources/views/components/admin/topbar.blade.php`.

## 1. Admin Dashboard

```
 [ Any admin page ]
        |
        | GET /dashboard  (route: dashboard, middleware: auth + role:admin)
        v
 [ Dashboard page ]  view: admin.dashboard -> @livewire('dashboard')
   +----------------------------------------------------------------+
   |  DatabaseWarningBanner   (shown only if a connection is down)  |
   |  PageHeader                                                    |
   |  YearFilter  ---dispatch: yearChanged--->  [ Dashboard::        |
   |  (dropdown)                                  yearChanged() ]   |
   |                                                    |            |
   |                                     re-renders every metric    |
   |                                     for the newly selected     |
   |                                     year (same page, no        |
   |                                     navigation — a Livewire    |
   |                                     re-render, not a new URL)  |
   |                                                                |
   |  MetricCard x N        (total bookings, success rate,          |
   |                          repeat-customer rate, ...)             |
   |  RevenueBySpeciesChart  (top animals by adoption revenue)       |
   |  BookingStatusChart     (doughnut: Pending/Confirmed/...)       |
   |  BookingsByMonthChart   (line, whole-year trend)                |
   |  VolumeVsValueChart     (bar+line, last 6 months)               |
   +----------------------------------------------------------------+
```

There is only one page here — the "flow" is a single load followed by in-place re-renders driven by
the year filter. Every chart/metric is computed server-side in `Dashboard.php`'s composed traits and
passed down as props; the sub-components only render what they're given (see the Dashboard
`README.md` for each component's exact prop shape).

## 2. Notification Bell

Not a page — a component embedded in `components/admin/topbar.blade.php`, so it's present on every
admin-layout page at once.

```
 [ Any admin page's topbar ]
        |
        v
 [ Bell icon, showing $unreadCount ]
        |
        | click (Alpine: dropdownOpen = !dropdownOpen)
        v
 <dropdown opening?>
   /            \
 no               yes
  |                 |
  v                 v
[ close,      [ $wire.loadNotifications() ]
  no reload ]        |
                      v
             [ LoadsNotifications::loadNotifications ]
             - pulls recent Bookings (+ animalBookings pivot,
               NOT the cross-server animals() relation),
               Rescues, Transactions, Adoptions
             - each mapped to a notification shape by
               MapsNotificationTypes (icon, color, message,
               timestamp)
             - merged, sorted by time descending, capped at 20
                      |
                      v
             [ Dropdown shows the merged feed ]
                      |
        +-------------+--------------------+
        |                                  |
        v                                  v
 [ click one notification ]      [ click "Mark all as read" ]
 wire:click="markAsRead(id)"     wire:click="markAllAsRead"
        |                                  |
        v                                  v
 [ NotificationInteractions ]    [ NotificationInteractions ]
 - flags that one as read        - flags all as read
 - unreadCount decrements        - unreadCount -> 0
```

Any other component can also flag one notification read by dispatching a `notificationRead` browser
event — the component listens for it (`protected $listeners = ['notificationRead' => 'markAsRead']`)
independent of the dropdown being open.
