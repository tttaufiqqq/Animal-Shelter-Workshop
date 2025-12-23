# Audit Trail System Documentation

## Overview

The Animal Shelter Workshop implements a comprehensive audit trail system to track critical operations across all 5 distributed databases. This system provides admin oversight for compliance, troubleshooting, and accountability.

**Storage Location:** All audit logs are centralized in the **taufiq** database (PostgreSQL), which is always online and required for the application to function.

**Audit Categories:**
1. **User Authentication Audit** - Login/logout tracking with success/failure details
2. **Payment & Adoption Audit** - Financial transactions and adoption lifecycle
3. **Animal Welfare Audit** - Medical records, vaccinations, and adoption status changes
4. **Rescue Operations Audit** - Rescue status tracking and caretaker accountability

---

## Database Schema

### audit_logs Table (Taufiq Database - PostgreSQL)

```sql
CREATE TABLE audit_logs (
    id BIGSERIAL PRIMARY KEY,

    -- WHO performed the action
    user_id BIGINT NULL,
    user_name VARCHAR(255) NULL,
    user_email VARCHAR(255) NULL,
    user_role VARCHAR(50) NULL,

    -- WHAT action was performed
    category VARCHAR(50) NOT NULL,  -- 'authentication', 'payment', 'animal', 'rescue'
    action VARCHAR(100) NOT NULL,   -- 'login_success', 'payment_completed', etc.
    entity_type VARCHAR(50) NULL,   -- 'Booking', 'Animal', 'Rescue', etc.
    entity_id BIGINT NULL,

    -- WHERE in the distributed system
    source_database VARCHAR(20) NULL,  -- 'taufiq', 'eilya', 'shafiqah', 'atiqah', 'danish'

    -- WHEN it happened
    performed_at TIMESTAMP NOT NULL,

    -- HOW/WHY (context)
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    request_url TEXT NULL,
    http_method VARCHAR(10) NULL,

    -- DETAILS (JSONB for PostgreSQL)
    old_values JSONB NULL,
    new_values JSONB NULL,
    metadata JSONB NULL,  -- Includes correlation_id for linking related operations

    -- OUTCOME
    status VARCHAR(20) NOT NULL,  -- 'success', 'failure', 'error'
    error_message TEXT NULL,

    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

**Indexes for Performance:**
- `idx_audit_user`: (user_id, performed_at DESC)
- `idx_audit_category_action`: (category, action, performed_at DESC)
- `idx_audit_entity`: (entity_type, entity_id)
- `idx_audit_date`: (performed_at DESC)
- `idx_audit_status`: (status, performed_at DESC)
- GIN indexes on JSONB fields (metadata, old_values, new_values)

---

## Audit Category 1: User Authentication

### Purpose
Track all login and logout activities with detailed success/failure information for security auditing and intrusion detection.

### Audited Actions
- `login_success` - Successful user authentication
- `login_failed` - Failed login attempt (invalid credentials or rate limiting)
- `logout` - User logout

### Table Display Columns

| Column | Database Field | Display Format | Purpose |
|--------|----------------|----------------|---------|
| **Timestamp** | `performed_at` | `25 Dec 2025, 14:30:15` | When the action occurred |
| **User** | `user_name` + `user_email` | `John Doe (john@example.com)` | Who attempted the action |
| **Action** | `action` | Badge: `LOGIN_SUCCESS` (green), `LOGIN_FAILED` (red), `LOGOUT` (gray) | Type of authentication event |
| **IP Address** | `ip_address` | `192.168.1.100` | Source location |
| **Device** | `user_agent` | `Chrome 120 on Windows` | Browser/device information |
| **Status** | `status` | Icon: ✓ Success, ✗ Failure | Outcome indicator |
| **Failure Reason** | `error_message` | `Invalid credentials` or `Too many login attempts` | Why login failed (if applicable) |

### Available Filters
- Date range picker (from/to dates)
- User search (name or email)
- Action type dropdown (Login Success, Login Failed, Logout)
- IP address search
- Status filter (Success, Failure)

### Key Features
- **Brute Force Detection**: Highlights consecutive failed login attempts from the same IP
- **Security Alerts**: Red background for failed login attempts
- **Session Tracking**: Links login/logout events by session ID
- **Export to CSV**: For compliance audits and security reports

### Example Use Cases
1. **Security Investigation**: Track unauthorized access attempts
2. **User Activity**: Monitor when specific users logged in
3. **Compliance**: Generate login/logout reports for audits
4. **Troubleshooting**: Identify authentication issues

---

## Audit Category 2: Payment & Adoption

### Purpose
Track the complete adoption lifecycle from booking creation through payment processing to adoption finalization. Critical for financial compliance and legal documentation.

### Audited Actions
- `booking_created` - Appointment booking created
- `booking_confirmed` - Booking status changed to Confirmed
- `payment_completed` - Successful payment via ToyyibPay
- `payment_failed` - Failed payment attempt
- `adoption_status_changed` - Animal adoption status updated
- `adoption_completed` - Adoption record finalized

### Table Display Columns

| Column | Database Field | Display Format | Purpose |
|--------|----------------|----------------|---------|
| **Timestamp** | `performed_at` | `25 Dec 2025, 14:30` | Transaction time |
| **Booking ID** | `entity_id` | `#12345` (clickable link) | Reference to booking |
| **User** | `user_name` | `Jane Smith` | Adopter name |
| **Action** | `action` | `PAYMENT_SUCCESS`, `BOOKING_CONFIRMED`, `ADOPTION_COMPLETED` | Lifecycle stage |
| **Amount** | `metadata->amount` | `RM 250.00` | Payment value |
| **Animals** | `metadata->animal_names` | `Fluffy, Max` | Animals involved |
| **Payment Method** | `metadata->payment_gateway` | `ToyyibPay - FPX Online Banking` | Payment gateway |
| **Bill Code** | `metadata->bill_code` | `abc123xyz` | ToyyibPay reference |
| **Status** | `status` | Badge: Success (green), Failed (red) | Transaction outcome |

### Available Filters
- Date range picker
- Booking ID search
- User search (adopter name)
- Amount range (min/max RM values)
- Status filter (Success, Failed)
- Payment method filter

### Key Features
- **Timeline View**: Shows complete booking → payment → adoption flow
- **Correlation Tracking**: Links all related operations via correlation_id
- **Financial Reconciliation**: Total revenue summary at page top
- **Red Flags**: Highlights refunded/cancelled transactions
- **Cross-Database Tracking**: Correlates data from Danish, Shafiqah, and Taufiq databases

### Example Use Cases
1. **Payment Reconciliation**: Match ToyyibPay transactions to adoptions
2. **Failed Payment Investigation**: Identify why payments failed
3. **Revenue Reporting**: Generate adoption fee reports
4. **Audit Trail**: Legal documentation of ownership transfer

### Correlation ID Example
A single adoption flow creates multiple linked audit logs:

```
Correlation ID: 550e8400-e29b-41d4-a716-446655440000

Timeline:
14:30:15 - DANISH: Booking #123 created
14:30:16 - DANISH: Booking #123 confirmed (RM 250)
14:31:45 - DANISH: Payment completed (bill_code: abc123xyz)
14:31:46 - SHAFIQAH: Animal #42 status → Adopted
14:31:46 - SHAFIQAH: Animal #43 status → Adopted
14:31:47 - DANISH: Transaction #789 created
14:31:48 - DANISH: Adoption #101 finalized

Databases Involved: 2 (Danish, Shafiqah)
Total Operations: 7
Duration: 33 seconds
Status: All Success
```

---

## Audit Category 3: Animal Welfare

### Purpose
Track all animal-related operations including creation, medical treatments, vaccinations, and adoption status changes. Essential for animal welfare compliance and veterinary accountability.

### Audited Actions
- `animal_created` - New animal registered in system
- `adoption_status_changed` - Adoption status updated
- `medical_added` - Medical treatment record created
- `vaccination_added` - Vaccination record created

### Table Display Columns

| Column | Database Field | Display Format | Purpose |
|--------|----------------|----------------|---------|
| **Timestamp** | `performed_at` | `25 Dec 2025, 14:30` | Action time |
| **Animal** | `metadata->animal_name` + `entity_id` | `Fluffy (#42)` (clickable link) | Subject animal |
| **Action** | `action` | `ANIMAL_CREATED`, `MEDICAL_ADDED`, `VACCINATION_ADDED` | Operation type |
| **Performed By** | `user_name` + `user_role` | `Dr. Sarah (Caretaker)` | Who performed the action |
| **Old Value** | `old_values` | `Not Adopted` | Previous state |
| **New Value** | `new_values` | `Adopted` | Updated state |
| **Associated Data** | `metadata` | `Vet: Dr. Ahmad, Cost: RM 120` | Contextual details |
| **Reason/Details** | `metadata` | `Adopted by John Doe (Booking #123)` | Why it changed |

### Available Filters
- Date range picker
- Animal name/ID search
- Action type dropdown
- User filter (who made change)
- Adoption status filter

### Key Features
- **Medical History Timeline**: Complete health record for each animal
- **Vaccination Tracking**: Upcoming due dates and compliance
- **Deletion Alerts**: Warns if animals deleted without adoption record
- **Cost Tracking**: Medical and vaccination expenses per animal
- **Veterinarian Accountability**: Tracks which vet performed procedures

### Example Use Cases
1. **Health Records**: View complete medical history for an animal
2. **Adoption Verification**: Prove animal was healthy before adoption
3. **Veterinary Billing**: Track medical costs per animal
4. **Vaccination Compliance**: Identify overdue vaccinations
5. **Welfare Investigation**: Audit animal care and treatment

### Sample Metadata Structure

**Medical Record:**
```json
{
    "animal_id": 42,
    "animal_name": "Fluffy",
    "treatment_type": "Wound Treatment",
    "diagnosis": "Minor laceration on left paw",
    "action": "Cleaned and bandaged",
    "vet_id": 5,
    "vet_name": "Dr. Ahmad bin Hassan",
    "costs": 120.00,
    "correlation_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

**Vaccination Record:**
```json
{
    "animal_id": 42,
    "animal_name": "Fluffy",
    "vaccination_name": "Rabies Vaccine",
    "type": "Core",
    "next_due_date": "2026-01-15",
    "vet_id": 5,
    "vet_name": "Dr. Ahmad bin Hassan",
    "costs": 80.00,
    "correlation_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

---

## Audit Category 4: Rescue Operations

### Purpose
Track rescue operations from caretaker assignment through status updates to completion. Essential for operational accountability and performance tracking.

### Audited Actions
- `caretaker_assigned` - Caretaker assigned to new rescue
- `caretaker_reassigned` - Caretaker changed for existing rescue
- `status_updated` - Rescue status changed (Scheduled → In Progress → Success/Failed)

### Table Display Columns

| Column | Database Field | Display Format | Purpose |
|--------|----------------|----------------|---------|
| **Timestamp** | `performed_at` | `25 Dec 2025, 14:30` | Action time |
| **Rescue ID** | `entity_id` | `#78` (clickable link) | Rescue reference |
| **Action** | `action` | `CARETAKER_ASSIGNED`, `STATUS_UPDATED` | Event type |
| **Priority** | `metadata->priority` | Badge: `CRITICAL` (red), `HIGH` (orange), `NORMAL` (blue) | Urgency level |
| **Old Status** | `old_values->status` | `Scheduled` | Previous state |
| **New Status** | `new_values->status` | `In Progress` | Current state |
| **Caretaker** | `metadata->caretaker_name` | `Ahmad bin Ali` | Assigned caretaker |
| **Location** | `metadata->address` | `Jalan Melaka Raya, Melaka` | Report location |
| **Outcome** | `new_values->remarks` | `Animal rescued successfully` | Final notes |

### Available Filters
- Date range picker
- Rescue ID search
- Priority filter (Critical, High, Normal)
- Status filter (Scheduled, In Progress, Success, Failed)
- Caretaker filter (dropdown of all caretakers)
- Location search (city/state)

### Key Features
- **SLA Tracking**: Calculates response time (report → assignment → completion)
- **Critical Priority Alerts**: Auto-highlights if unassigned > 1 hour
- **Performance Dashboard**: Caretaker success rate and average response time
- **Failed Rescue Investigation**: Requires remarks for failed rescues
- **Geographic Tracking**: Shows rescue distribution by location

### Performance Metrics

**Caretaker Performance View:**
- Total rescues assigned
- Success rate (%)
- Average response time (report → assignment)
- Average completion time (assignment → success)
- Failed rescues (with reasons)

### Example Use Cases
1. **Response Time Analysis**: Measure how quickly caretakers respond
2. **Critical Priority Tracking**: Ensure urgent rescues are handled promptly
3. **Performance Review**: Evaluate caretaker effectiveness
4. **Failed Rescue Investigation**: Understand why rescues fail
5. **Resource Allocation**: Identify high-activity areas

### Sample Metadata Structure

**Caretaker Assigned:**
```json
{
    "report_id": 156,
    "caretaker_name": "Ahmad bin Ali",
    "address": "Jalan Melaka Raya, Melaka",
    "priority": "critical",
    "correlation_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

**Status Updated:**
```json
{
    "priority": "critical",
    "report_id": 156,
    "caretaker_name": "Ahmad bin Ali",
    "address": "Jalan Melaka Raya, Melaka",
    "correlation_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

---

## Technical Implementation

### Architecture Overview

```
User Action → Controller → AuditService → AuditLog Model → Taufiq Database (PostgreSQL)
                  ↓
          CorrelateAuditTrail Middleware
          (Generates correlation_id)
```

### Core Components

#### 1. AuditLog Model
**Location:** `app/Models/AuditLog.php`

**Key Features:**
- Connects to taufiq (PostgreSQL) database
- Auto-casts JSONB fields to PHP arrays
- Provides query scopes for filtering
- Relationships to User model

**Query Scopes:**
```php
AuditLog::category('payment')->get();  // Filter by category
AuditLog::byUser($userId)->get();     // Filter by user
AuditLog::dateRange($from, $to)->get();  // Date range
AuditLog::search('search term')->get();  // Full-text search
```

#### 2. AuditService
**Location:** `app/Services/AuditService.php`

**Main Method:**
```php
AuditService::log(
    string $category,     // 'authentication', 'payment', 'animal', 'rescue'
    string $action,       // 'login_success', 'payment_completed', etc.
    array $data = [],     // Context data (old_values, new_values, metadata)
    string $status = 'success'  // 'success', 'failure', 'error'
): ?AuditLog
```

**Helper Methods:**
```php
// Authentication audit
AuditService::logAuthentication(
    string $action,  // 'login_success', 'login_failed', 'logout'
    ?string $email = null,
    ?string $error = null
);

// Payment audit
AuditService::logPayment(
    string $action,      // 'payment_completed', 'payment_failed'
    int $bookingId,
    float $amount,
    array $animalIds,
    ?string $billCode = null,
    string $status = 'success'
);

// Animal audit
AuditService::logAnimal(
    string $action,          // 'animal_created', 'adoption_status_changed'
    int $animalId,
    string $animalName,
    ?array $oldValues = null,
    ?array $newValues = null,
    array $metadata = []
);

// Rescue audit
AuditService::logRescue(
    string $action,      // 'caretaker_assigned', 'status_updated'
    int $rescueId,
    ?array $oldValues = null,
    ?array $newValues = null,
    array $metadata = []
);

// Medical audit
AuditService::logMedical(
    string $action,      // 'medical_added'
    int $animalId,
    string $animalName,
    int $medicalId,
    array $metadata = []
);

// Vaccination audit
AuditService::logVaccination(
    string $action,       // 'vaccination_added'
    int $animalId,
    string $animalName,
    int $vaccinationId,
    array $metadata = []
);
```

#### 3. CorrelateAuditTrail Middleware
**Location:** `app/Http/Middleware/CorrelateAuditTrail.php`

**Purpose:** Generates a unique UUID correlation ID for each HTTP request to link related audit logs.

**How it works:**
1. Generates UUID on each request
2. Stores in request attributes
3. Stores in session (persists across redirects)
4. AuditService automatically includes in metadata

#### 4. Migration
**Location:** `database/migrations/2025_12_23_000001_create_audit_logs_table.php`

**To Run:**
```bash
# Run migration
php artisan migrate

# Or refresh all databases (distributed architecture)
php artisan db:fresh-all --seed
```

### Integration Points

#### Authentication (AuthenticatedSessionController)
```php
// Login success
AuditService::logAuthentication('login_success', Auth::user()->email);

// Login failed
AuditService::logAuthentication('login_failed', $request->email, 'Invalid credentials');

// Logout
AuditService::logAuthentication('logout', $user->email);
```

#### Payments (BookingAdoptionController)
```php
// Booking created
AuditService::log('payment', 'booking_created', [/* ... */]);

// Payment completed
AuditService::logPayment('payment_completed', $bookingId, $amount, $animalIds, $billCode);

// Adoption status changed
AuditService::logAnimal('adoption_status_changed', $animalId, $animalName, ['adoption_status' => 'Not Adopted'], ['adoption_status' => 'Adopted']);
```

#### Animal Welfare (AnimalManagementController)
```php
// Animal created
AuditService::logAnimal('animal_created', $animal->id, $animal->name, null, $animal->toArray());

// Medical record added
AuditService::logMedical('medical_added', $animalId, $animalName, $medical->id, [/* metadata */]);

// Vaccination added
AuditService::logVaccination('vaccination_added', $animalId, $animalName, $vaccination->id, [/* metadata */]);
```

#### Rescue Operations (StrayReportingManagementController)
```php
// Caretaker assigned
AuditService::logRescue('caretaker_assigned', $rescue->id, null, ['caretaker_id' => $caretakerId], [/* metadata */]);

// Status updated
AuditService::logRescue('status_updated', $rescue->id, ['status' => $oldStatus], ['status' => $newStatus], [/* metadata */]);
```

---

## Performance Considerations

### Write Performance
- **Impact:** ~20-50ms per audit log (minimal)
- **Optimization:** Logs are written synchronously for data integrity
- **Future Enhancement:** Consider queue jobs for high-volume operations

### Storage Management
- **Growth Rate:** ~2 KB per log × 1,000 daily operations = ~730 MB/year
- **Retention Strategy:**
  - Critical logs (payment, authentication): Retain indefinitely
  - Non-critical logs: Archive after 1 year, delete after 2 years
- **Archival:** Export old logs to CSV for long-term storage

### Query Performance
- **Indexes:** Multiple indexes on frequently queried columns
- **JSONB Indexes:** GIN indexes for metadata searching in PostgreSQL
- **Pagination:** Always use pagination (100 records/page recommended)
- **Date Filters:** Always include date range filters for large datasets

---

## Data Retention Policy

### Recommended Retention Periods

| Category | Retention Period | Reason |
|----------|-----------------|--------|
| **Authentication** | Indefinite | Security compliance |
| **Payment/Adoption** | Indefinite | Legal/financial records |
| **Animal Welfare** | 5 years | Animal welfare regulations |
| **Rescue Operations** | 2 years | Operational records |

### Archival Process
1. Monthly: Export logs older than retention period to CSV
2. Verify CSV integrity
3. Store securely (encrypted backup)
4. Delete from active database
5. Document archival in backup log

---

## Compliance Guidelines

### Data Protection
- **Personal Data:** User names, emails, and IP addresses are logged
- **GDPR Compliance:** Audit logs exempt from "right to be forgotten" for legal/compliance purposes
- **Access Control:** Only Admin role can view audit logs
- **Encryption:** Database-level encryption recommended for production

### Financial Compliance
- **ToyyibPay Integration:** All payment gateway responses logged
- **Refund Tracking:** Failed/cancelled transactions flagged
- **Revenue Reconciliation:** Daily payment totals should match ToyyibPay reports

### Animal Welfare Compliance
- **Medical Records:** Complete treatment history preserved
- **Vaccination Tracking:** Compliance with immunization schedules
- **Adoption Documentation:** Legal proof of animal ownership transfer

---

## Troubleshooting

### Common Issues

**Issue 1: Audit logs not appearing**
- **Check:** Is taufiq database online?
- **Solution:** Run `php artisan db:check-connections` to verify
- **Error Handling:** Audit failures never break application (graceful degradation)

**Issue 2: Correlation ID not linking logs**
- **Check:** Is CorrelateAuditTrail middleware registered?
- **Solution:** Verify in `bootstrap/app.php` → web middleware array
- **Alternative:** Correlation IDs also stored in session (persists across redirects)

**Issue 3: JSONB queries not working**
- **Check:** Using PostgreSQL syntax for JSONB queries
- **Example:** `WHERE metadata->>'correlation_id' = 'uuid-here'`
- **Note:** Standard JSON operators (`->`, `->>`) work in PostgreSQL

**Issue 4: Migration fails**
- **Check:** Taufiq database connection in `.env`
- **Solution:** Run `php artisan db:fresh-one taufiq` to isolate migration
- **Foreign Key:** Ensure `users` table exists before migrating audit_logs

---

## Future Enhancements

### Planned Features
1. **Admin Dashboard:** Web interface for viewing/filtering audit logs (Admin/AuditController + Blade views)
2. **Real-Time Alerts:** Notify admins of critical events (failed payments, critical rescues)
3. **Async Logging:** Queue jobs for high-volume operations
4. **Advanced Analytics:** Charts and visualizations for audit data
5. **Export Functionality:** CSV/PDF export with custom filters
6. **Automated Reports:** Daily/weekly email summaries to admins

### Admin Controller Routes (Planned)
```php
Route::middleware(['auth', 'role:Admin'])->prefix('admin/audit')->group(function () {
    Route::get('/', [AuditController::class, 'index'])->name('admin.audit.index');
    Route::get('/authentication', [AuditController::class, 'authentication']);
    Route::get('/payments', [AuditController::class, 'payments']);
    Route::get('/animals', [AuditController::class, 'animals']);
    Route::get('/rescues', [AuditController::class, 'rescues']);
    Route::get('/timeline/{correlationId}', [AuditController::class, 'timeline']);
    Route::get('/export/{category}', [AuditController::class, 'export']);
});
```

---

## Summary

The Audit Trail System provides comprehensive tracking of all critical operations across the Animal Shelter Workshop's distributed database architecture. With 4 audit categories covering authentication, payments, animal welfare, and rescue operations, administrators have complete visibility into system activity for compliance, troubleshooting, and accountability.

**Key Benefits:**
- ✅ **Centralized Storage:** All logs in taufiq database (always online)
- ✅ **Cross-Database Tracking:** Correlation IDs link operations across 5 databases
- ✅ **Compliance Ready:** Financial, legal, and animal welfare documentation
- ✅ **Performance Optimized:** Minimal overhead (~20-50ms per log)
- ✅ **Graceful Degradation:** Audit failures never break application
- ✅ **Future-Proof:** JSONB metadata allows flexible querying

**Next Steps:**
1. Run migration: `php artisan migrate`
2. Test audit logging in each module
3. Verify logs in `audit_logs` table
4. Plan admin dashboard development (Admin/AuditController)
