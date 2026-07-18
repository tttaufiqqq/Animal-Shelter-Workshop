# Auth Module — Use Case Diagram

Actors: **Guest** (unauthenticated visitor), **User** (any authenticated account — adopter, caretaker,
public user), **Admin** (a User whose role also grants admin privileges — see
`docs/flows/users-admin/use-case-diagram.md` for the admin-side actions that trigger some of these).

```
                         Auth Module
        +---------------------------------------------------------+
        |                                                           |
        |     ( Register )                                         |
Guest---o----------------------------                               |
        |                            \                              |
        |     ( Log In )              \                             |
Guest---o------------+                  \                            |
        |            |<<include>>        \                          |
        |            v                    v                         |
        |   ( Check Account Status )  ( Assign Default Role )        |
        |    (suspended / locked)                                    |
        |                                                            |
        |     ( Request Password Reset Email )                       |
Guest---o------------------------------------                        |
        |                                                            |
        |     ( Reset Password via Emailed Token )                    |
Guest---o--------------------------------------------                |
        |                                                            |
        |     ( Log Out )                                            |
 User---o-------------------------                                   |
        |                                                            |
        |     ( Confirm Password )         (re-auth before a         |
 User---o---------------------------        sensitive action)        |
        |                                                            |
        |     ( Update Own Profile Info )                             |
 User---o------------------------------                              |
        |                                                            |
        |     ( Update Own Password )                                 |
 User---o--------------------------+                                 |
        |                          |<<extend>>                        |
        |                          v                                  |
        |              ( Change Password Under                        |
        |                Forced-Reset Gate )                          |
        |                (same underlying action,                     |
        |                 reached only while flagged)                 |
        |                                                            |
        |     ( Delete Own Account )                                  |
 User---o---------------------------                                 |
        |         |<<include>>                                       |
        |         v                                                  |
        | ( Re-confirm Current Password )                             |
        |                                                            |
        +------------------------------------------------------------+

Admin --------(triggers, see users-admin module)---------> ( Force a User's Password Reset )
                                                              |<<extend>>
                                                              v
                                                  ( User is Gated to /password/change
                                                    on Their Next Request )
```

Notes:
- **"Log In" `<<include>>` "Check Account Status"**: every login attempt checks suspended/locked
  status *after* verifying credentials, not before — a correct password for a suspended account still
  fails, with a distinct error message pointing to the contact page.
- **"Update Own Password" `<<extend>>` "Change Password Under Forced-Reset Gate"**: these are the
  same controller action (`ManagesPassword::updatePassword()`), reached through two different UI
  entry points — the profile page's own password form uses a different action
  (`updateProfilePassword`) entirely; only the forced-reset gate uses this one.
- **"Delete Own Account" `<<include>>` "Re-confirm Current Password"**: deletion requires re-typing
  the current password in the same request, not a separately confirmed session.
- Email verification (`verify-email` routes) exists in `routes/auth.php` but is not a real use case
  today — nothing in the registration flow triggers it, and the `User` model can't record a verified
  timestamp. Left off this diagram since it's not reachable in practice.
