# Dashboard & Notifications — Activity Diagrams

## 1. Dashboard Render / Year Change

Both the initial `render()` and a `yearChanged` event run the same metric-computation activity —
only the selected year differs.

```
                    ( start )
                        |
                        v
              [ Is the 'booking' connection reachable? ]
                    /              \
                 no                  yes
                  |                    |
                  v                    v
       [ Return zeroed booking   [ Compute total/successful/
         metrics — totals,        cancelled bookings, success
         success rate, charts     rate, repeat-customer rate,
         all show empty/zero ]    top-animals-by-revenue,
                  |                status breakdown, monthly
                  |                trend, volume-vs-value ]
                  |                    |
                  +---------+----------+
                            v
              [ Is the 'users' connection reachable? ]
                    /              \
                 no                  yes
                  |                    |
                  v                    v
       [ Return zeroed admin     [ Compute admin user-
         stats — no user           management stats +
         counts shown ]            audit summary grouped
                  |                 by category, with a
                  |                 computed success rate ]
                  |                    |
                  +---------+----------+
                            v
              [ Render DatabaseWarningBanner if
                either connection was unreachable ]
                            |
                            v
              [ Pass all computed values as props
                to the 8 sub-components ]
                            |
                            v
                         ( end )
```

## 2. Notification Feed Load

`LoadsNotifications::loadNotifications`, run on dropdown-open.

```
                    ( start )
                        |
                        v
        [ For each source (bookings, rescues,
          transactions, adoptions): is that
          source's connection reachable? ]
                        |
              +---------+---------+
              |                   |
             no                    yes
              |                     |
              v                     v
   [ Record a database    [ Query recent rows from
     error for that         that source (bookings via
     source; skip it —      the same-connection
     one bad connection     animalBookings pivot, never
     doesn't blank the      the cross-server animals()
     whole feed ] --+       relation) ]
                     |               |
                     |               v
                     |     [ Map each row to a notification
                     |       shape via MapsNotificationTypes
                     |       (icon, color, message, time) ]
                     |               |
                     +-------+-------+
                             v
              [ Merge all sources' notifications,
                sort by timestamp descending,
                cap at 20 ]
                             |
                             v
              [ Recompute unreadCount from the
                merged, capped list ]
                             |
                             v
                          ( end )
```
