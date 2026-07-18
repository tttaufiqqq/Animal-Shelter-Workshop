# Users / Admin — Activity Diagrams

## Suspend user (`ManagesUserAccounts::suspendUser`)

```
        ( start )
            |
            v
   [ validate: reason required, string, <=1000 chars ]
            |
       invalid? ----yes----> [ 422 JSON: validation errors ] --> ( end )
            | no
            v
   [ User::findOrFail($userId) ]
            |
       not found? --yes--> [ 404 JSON: User not found ] --> ( end )
            | found
            v
      <target === admin (self)?>
            |-- yes --> [ 403 JSON: cannot suspend own account ] --> ( end )
            | no
            v
      <target has role 'admin'?>
            |-- yes --> [ 403 JSON: cannot suspend admin accounts ] --> ( end )
            | no
            v
   [ UserProcedureService::suspendUser(id, reason) ]
   (fn_user_suspend, Postgres)
            |
       result.success == false? --yes--> [ 500 JSON: service message ] --> ( end )
            | true
            v
   [ 200 JSON: "suspended successfully" ]
            |
            v
        ( end )
```

## Lock user (`ManagesUserAccounts::lockUser`)

```
        ( start )
            |
            v
   [ validate: duration in {1_hour,24_hours,7_days,custom},
     custom_duration required if custom, reason required ]
            |
       invalid? ----yes----> [ 422 JSON ] --> ( end )
            | no
            v
   [ findOrFail ] --not found--> [ 404 ] --> ( end )
            |
      <target is self, or has role 'admin'?>
            |-- yes --> [ 403 JSON ] --> ( end )
            | no
            v
   [ map duration -> minutes
     (60 / 1440 / 10080 / custom_duration*60) ]
            |
            v
   [ UserProcedureService::lockUser(id, minutes, reason) ]
   (fn_user_lock computes locked_until in Postgres, NOW()+interval)
            |
       success == false? --yes--> [ 500 JSON ] --> ( end )
            | true
            v
   [ 200 JSON: "locked until {locked_until}" ]
            |
            v
        ( end )
```

## Unlock user (`ManagesUserAccounts::unlockUser`)

```
        ( start )
            |
            v
   [ User::findOrFail($userId) ]
            |
            v
      <account_status in {locked, suspended}?>
            |-- no --> [ 400 JSON: not locked or suspended ] --> ( end )
            | yes
            v
   [ UserProcedureService::unlockUser(id) ]  (fn_user_unlock)
            |
       success == false? --yes--> [ 500 JSON ] --> ( end )
            | true
            v
   [ 200 JSON: "unlocked successfully" ] --> ( end )
```

## Force password reset (`ManagesUserAccounts::forcePasswordReset`)

```
        ( start )
            |
            v
   [ validate: password required, min:8, confirmed ]
            |
       invalid? --yes--> [ 422 JSON ] --> ( end )
            | no
            v
   [ findOrFail ]
            |
      <target is self, or has role 'admin'?>
            |-- yes --> [ 403 JSON ] --> ( end )
            | no
            v
   [ updateUserPassword(id, Hash::make(password)) ]
            |
       success == false? --yes--> [ 500 JSON ] --> ( end )
            | true
            v
   [ forcePasswordReset(id) ]  -- sets require_password_reset = true --
            |
       success == false? --yes--> [ 500 JSON ] --> ( end )
            | true
            v
   [ 200 JSON: "password reset; required on next login" ]
            |
            v
        ( end )

   Note: this is where the Users/Admin module hands off to the Auth
   module's RequirePasswordChange gate — see docs/flows/auth for what
   happens the next time this user logs in.
```

## Export audit logs to CSV (`ExportsAuditLogs::export`)

```
        ( start )
            |
            v
   [ category == 'all'? ]
       yes -> [ query = AuditLog::with('user') ]
       no  -> [ query = AuditLog::category(category)->with('user') ]
            |
            v
   [ apply whichever filters are present:
     date_from, date_to, category (if 'all'), action, status ]
            |
            v
   [ fetch up to 10,000 rows, order by performed_at desc ]
            |
            v
   [ pick CSV header row by category
     (all / authentication / payment / animal / rescue —
      each has a different column set) ]
            |
            v
   [ stream rows -> per-category fputcsv mapping ] --loop per row-->
            |
            v
   [ return as a downloadable .csv, filename stamped with category + timestamp ]
            |
            v
        ( end )
```
