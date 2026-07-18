# Auth Module — Activity Diagrams

## Login attempt

```
                    (start)
                       |
                       v
          +-------------------------+
          | validate credentials     |
          | (LoginRequest::authenticate) |
          +------------+--------------+
                        |
              <invalid> / \ <valid>
                  /       X       \
                 v        X        v
   +---------------------+X+---------------------------+
   | increment failed-     |  | user = Auth::user()       |
   | login-attempts (SQL   |  +--------------+--------------+
   | function); may trip a |                 |
   | DB trigger that auto- |                 v
   | locks the account     |     <isSuspended()?>
   +-----------+------------+        /          \
               |                 yes/            \no
               v                    v              v
    back to /login with    logout guard,    <isLocked()?>
    error message           throw "account       /       \
               |            suspended" error   yes/       \no
               |                    |            v         v
               +--------------------+    logout guard,  reset failed-
                                          throw "account   login-attempts
                                          locked until X"  (SQL function)
                                                |               |
                                                +-------+--------+
                                                        |        |
                                                        v        v
                                              back to /login   regenerate
                                              with error        session,
                                                                audit-log
                                                                'login_success'
                                                                     |
                                                                     v
                                                          <user hasRole('admin')?>
                                                             /              \
                                                          yes/                \no
                                                            v                  v
                                                     redirect->intended    redirect
                                                     (falls back to        -> welcome
                                                      /dashboard)             |
                                                            \                /
                                                             v              v
                                                                  (end)
```

## Forced password-change gate (runs on every request, global middleware)

```
              (start: any incoming request)
                          |
                          v
              <Auth::check() — logged in?>
                 /                    \
              no/                      \yes
               v                        v
       continue to route      <user->require_password_reset?>
       normally (end)              /                  \
                                 no/                    \yes
                                  v                       v
                          continue to route   <request route name in
                          normally (end)       {password.change,
                                                 password.update, logout}?>
                                                    /              \
                                                 yes/                \no
                                                   v                  v
                                       continue to route   redirect -> GET
                                       normally (end)       /password/change
                                                             with warning flash
                                                                   |
                                                                   v
                                                                 (end)
```

This check runs before *every* request for an authenticated user — not a one-time redirect on login.
A flagged user cannot reach `/dashboard`, `/profile`, `/bookings/index`, or anything else until the
password is actually changed (or they log out).

## Registration

```
        (start)
           |
           v
  <validate name/email/password/city/state/address/phoneNum>
           |
      invalid / \ valid
          /       \
         v         v
  back to form   UserProcedureService::createUser()
  with errors    (fn_user_create — enforces unique email
                  on the users.users table)
                        |
              failed (e.g. duplicate email) / \ succeeded
                    /                          \
                   v                            v
        back to form with         assign "public user" role
        the DB's error message     via UserProcedureService
                                             |
                                             v
                                   Auth::login($user)
                                             |
                                             v
                                  redirect()->intended(welcome)
                                             |
                                             v
                                           (end)
```
