# Audit Trail System - Setup Guide

## Quick Start

Follow these steps to activate the audit trail system in your Animal Shelter Workshop application.

### Step 1: Run the Migration

The audit trail system requires a new table in the **taufiq** database (PostgreSQL).

```bash
# Run the migration
php artisan migrate

# Or if you want to refresh all databases:
php artisan db:fresh-all --seed
```

**Important:** Make sure the taufiq database is online before running the migration.

### Step 2: Verify Installation

Check that the audit_logs table was created successfully:

```bash
# Check taufiq database connection
php artisan db:check-connections

# Query the audit_logs table
php artisan tinker
>>> \App\Models\AuditLog::count()
```

### Step 3: Test Audit Logging

Perform some actions to generate audit logs:

1. **Test Authentication Audit:**
   - Log out
   - Log in with correct credentials (creates `login_success` log)
   - Try logging in with wrong password (creates `login_failed` log)
   - Log in again
   - Log out (creates `logout` log)

2. **Test Payment Audit:**
   - Create a booking for an animal
   - Confirm the booking
   - Complete the payment process
   - Check audit logs for the entire flow

3. **Test Animal Welfare Audit:**
   - Add a new animal
   - Add a medical record
   - Add a vaccination record
   - Check audit logs for these operations

4. **Test Rescue Operations Audit:**
   - Assign a caretaker to a rescue
   - Update the rescue status
   - Check audit logs for caretaker activities

### Step 4: View Audit Logs

#### Option A: Database Query

```bash
# View recent audit logs via tinker
php artisan tinker
>>> \App\Models\AuditLog::latest('performed_at')->limit(10)->get(['performed_at', 'category', 'action', 'user_name', 'status'])
```

#### Option B: Admin Dashboard (Web Interface)

1. Log in as an **Admin** user
2. Navigate to: `http://localhost:8000/admin/audit`
3. Explore the audit categories:
   - **Dashboard**: Overview and statistics
   - **Authentication**: Login/logout logs
   - **Payments**: Financial transactions
   - **Animals**: Medical and welfare records
   - **Rescues**: Rescue operations

#### Option C: Direct Database Query

```sql
-- Connect to taufiq database
SELECT
    performed_at,
    category,
    action,
    user_name,
    status,
    metadata->>'correlation_id' as correlation_id
FROM audit_logs
ORDER BY performed_at DESC
LIMIT 20;
```

## Configuration

### Environment Variables

No additional environment variables needed! The system uses the existing database connections.

### Middleware

The `CorrelateAuditTrail` middleware is already registered in `bootstrap/app.php`:

```php
$middleware->web(append: [
    \App\Http\Middleware\CorrelateAuditTrail::class,
]);
```

## Features

### 1. Automatic Audit Logging

Audit logs are automatically created for:
- ✅ User login/logout
- ✅ Booking creation and confirmation
- ✅ Payment processing (success/failure)
- ✅ Animal adoption status changes
- ✅ Medical record creation
- ✅ Vaccination records
- ✅ Caretaker assignments
- ✅ Rescue status updates

### 2. Correlation IDs

Related operations are linked via `correlation_id` in the metadata field. This allows you to view the complete timeline of a multi-step operation (e.g., booking → payment → adoption).

### 3. Cross-Database Tracking

The system tracks operations across all 5 databases:
- **Taufiq** (PostgreSQL): User authentication
- **Danish** (SQL Server): Payments and adoptions
- **Shafiqah** (MySQL): Animal welfare
- **Eilya** (MySQL): Rescue operations

### 4. Export to CSV

Admins can export audit logs to CSV for compliance reporting:

```
http://localhost:8000/admin/audit/export/authentication
http://localhost:8000/admin/audit/export/payment
http://localhost:8000/admin/audit/export/animal
http://localhost:8000/admin/audit/export/rescue
```

## Admin Routes

All admin audit routes require **Admin** role:

```
GET /admin/audit                      - Dashboard
GET /admin/audit/authentication       - Login/logout logs
GET /admin/audit/payments             - Payment logs
GET /admin/audit/animals              - Animal welfare logs
GET /admin/audit/rescues              - Rescue operations logs
GET /admin/audit/timeline/{id}        - Correlated timeline
GET /admin/audit/export/{category}    - Export to CSV
```

## Troubleshooting

### Issue 1: "Table 'audit_logs' doesn't exist"

**Solution:**
```bash
# Run the migration
php artisan migrate

# If migration already ran, check if table exists:
php artisan db:fresh-one taufiq --seed
```

### Issue 2: "403 Forbidden" when accessing admin/audit

**Solution:**
- Ensure you're logged in as a user with **Admin** role
- Check role assignment:
  ```bash
  php artisan tinker
  >>> $user = \App\Models\User::find(1);
  >>> $user->assignRole('Admin');
  ```

### Issue 3: Audit logs not being created

**Solution:**
- Check if taufiq database is online: `php artisan db:check-connections`
- Check Laravel logs: `tail -f storage/logs/laravel.log`
- Verify AuditService is being called (add debug logs)

### Issue 4: Middleware not running

**Solution:**
- Clear route cache: `php artisan route:clear`
- Clear config cache: `php artisan config:clear`
- Restart dev server: `composer dev`

## Performance

- **Write Impact:** ~20-50ms per audit log
- **Storage:** ~2 KB per log
- **Indexes:** Optimized for common queries
- **Graceful Degradation:** Audit failures never break the app

## Data Retention

Recommended retention periods:

| Category | Retention | Reason |
|----------|-----------|--------|
| Authentication | Indefinite | Security compliance |
| Payment/Adoption | Indefinite | Financial/legal records |
| Animal Welfare | 5 years | Welfare regulations |
| Rescue Operations | 2 years | Operational records |

## Next Steps

1. ✅ Run migration
2. ✅ Test audit logging
3. ✅ Access admin dashboard
4. 📋 Set up data retention policy
5. 📋 Configure automated backup of audit logs
6. 📋 Train admins on audit dashboard usage

## Support

For detailed documentation, see `AUDIT_TRAIL_SYSTEM.md`.

For distributed database setup, see `DISTRIBUTED_SETUP_GUIDE.md`.
