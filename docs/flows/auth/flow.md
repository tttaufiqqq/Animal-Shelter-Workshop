# Auth Module — Page Flows

Source: `routes/auth.php`, `routes/web.php` (password/change block), `app/Http/Controllers/Auth/*`,
`app/Http/Controllers/Concerns/Profile/{ManagesPassword,ManagesProfile}.php`,
`app/Http/Middleware/RequirePasswordChange.php`.

## 1. Registration

```
 GET /register                    POST /register
+----------------+   submit    +----------------------------+
| register page  | ----------> | RegisteredUserController    |
| (name, email,  |             | @store                      |
|  password,     |             |                              |
|  city, state,  |             | validate -> create via       |
|  address,      |             | UserProcedureService          |
|  phoneNum)     |             | (fn_user_create) -> assign    |
+----------------+             | "public user" role -> log in  |
       ^                       +--------------+----------------+
       |                                      |
       | validation / duplicate-email error   | success
       |                                      v
       +-------------------------------  redirect -> welcome ("/")
```

No `Registered` event is fired — email verification is scaffolded elsewhere in the app but never
wired up for this flow (no `email_verified_at` column, `User` doesn't implement `MustVerifyEmail`).

## 2. Login

```
 GET /login              POST /login
+------------+   submit  +-----------------------------------------+
| login page | --------> | AuthenticatedSessionController@store    |
+------------+           +----------------+--------------------------+
                                           |
                     credentials invalid  |  credentials valid
                     (increments fail     |
                      count via SQL       v
                      function, may   +-------------------------+
                      auto-lock) --   | suspended? / locked?    |
                          ^           +-----------+--------------+
                          |                       |
                    back to login            no -> reset fail count,
                    with error                    regenerate session,
                                                   audit-log success
                                                   |
                                        +----------+-----------+
                                        | has 'admin' role?    |
                                        +----------+-----------+
                                         yes |            | no
                                             v            v
                                   /dashboard      / (welcome)
```

A suspended or locked account is logged straight back out even though credentials were correct — see
`activity-diagram.md` for the exact branch order.

## 3. Forgot / reset password (email link)

```
GET /forgot-password        POST /forgot-password              email
+-------------------+  submit  +------------------------------+  link  +----------------------+
| forgot-password   | -------> | PasswordResetLinkController   | ----> | user's inbox         |
| page (email)      |          | @store -> Password::sendResetLink()  | (reset-password/{tok})|
+-------------------+          +------------------------------+       +-----------+-----------+
                                                                                    |
                                                                                    v
                                                        GET /reset-password/{token}
                                                        +---------------------------+
                                                        | reset-password page       |
                                                        | (token, email, password,  |
                                                        |  password_confirmation)   |
                                                        +-------------+--------------+
                                                                      |
                                                                POST /reset-password
                                                                      v
                                                        NewPasswordController@store
                                                        Password::reset() -> forceFill
                                                        password + new remember_token
                                                                      |
                                                          valid token |  invalid/expired token
                                                                      v            v
                                                              /login (status)   back to form + error
```

## 4. Forced password change (admin-initiated)

```
   Admin: POST /admin/users/{id}/force-password-reset
   (see docs/flows/users-admin/flow.md) -> sets users.require_password_reset = true
                          |
                          v
   That user's next request, ANY route -----------------------------+
   (RequirePasswordChange middleware, applied globally)              |
                          |                                          |
             route name in {password.change, password.update,       |
                             logout} ?                                |
                    no |                          yes |                
                       v                              v
        redirect -> GET /password/change      request proceeds normally
        ("You must change your password
         before continuing")
                       |
                       v
        +------------------------------+
        | change-password page          |
        | (current_password, password,  |
        |  password_confirmation)       |
        +---------------+----------------+
                         |  POST /password/change
                         v
        ProfileController@updatePassword (ManagesPassword)
        validate current_password -> UserProcedureService::updateUserPassword()
        (clears require_password_reset)
                         |
             success     |     failure (wrong current password / DB error)
                         v                      v
              redirect "/" with success   back to change-password page + error
```

`/password/change` (GET) and `/password/change` (POST, named `password.update`) are the only routes
a flagged user can reach besides `logout` — enforced on every single request, not just on navigation
attempts from the login page.

**Note on route naming**: `routes/auth.php`'s stock Breeze `PUT /password` (also named
`password.update`, for the *profile* "change my password" form) and this `POST /password/change`
route share the same route name. Only one view in the app calls `route('password.update')` — this
change-password form — and it resolves correctly to `/password/change` (verified directly: route
registration order in this app happens to favor it). The stock `PUT /password` endpoint is not
reachable by name from anywhere in this app's UI; the profile page's own password form uses the
separately-named `profile.password.update` instead. Not a live bug, but a fragile setup — any change
to route load order in `routes/web.php` (which `require`s `auth.php` last) could silently redirect
this form somewhere else.

## 5. Password confirmation (re-auth gate)

```
Any route with the 'password.confirm' middleware, if not recently confirmed
                          |
                          v
              GET /confirm-password
              +------------------------+
              | confirm-password page  |
              | (password)             |
              +-----------+-------------+
                          | POST /confirm-password
                          v
              ConfirmablePasswordController@store
              re-validate the current session's
              password against the 'web' guard
                          |
              valid       |       invalid
                          v                v
      session: auth.password_confirmed_at   back to form + error
      = now() -> redirect()->intended
      (falls back to /dashboard)
```

## 6. Profile: update info / update password / delete account

All three live on one page, `GET /profile` (`profile.edit`), rendered as three separate part-includes
(`update-profile-information-form`, `update-password-form`, `delete-user-form`), each its own `<form>`
posting to its own route:

```
GET /profile
+------------------------------------------------------------+
| profile.edit                                                |
|  [Profile Information form] --PATCH /profile--------------->| ProfileController@update
|  [Update Password form]     --PUT /profile/password-------->| @updateProfilePassword
|  [Delete Account form]      --DELETE /profile-------------->| @destroy
+------------------------------------------------------------+
```

- **Update info** (`profile.update`) — validated by `ProfileUpdateRequest`, written via
  `UserProcedureService::updateUser()`, redirects back to `profile.edit` with `status=profile-updated`.
- **Update password** (`profile.password.update`) — requires `current_password`, writes via the same
  `updateUserPassword()` call the forced-reset flow uses, redirects back with `status=password-updated`.
- **Delete account** (`profile.destroy`) — requires the current password, logs the user out
  **before** the delete call runs, then invalidates the session. `Auth::logout()` happening first
  means the delete itself runs as an anonymous DB operation, not tied to the (now-gone) auth session.

## 7. Logout

```
POST /logout  ->  AuthenticatedSessionController@destroy
                  audit-log 'logout' -> Auth::logout() -> invalidate session
                  -> regenerate CSRF token -> redirect "/"
```
