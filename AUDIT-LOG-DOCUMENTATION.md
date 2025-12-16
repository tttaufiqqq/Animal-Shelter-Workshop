# Audit Log System Documentation

## Table of Contents
1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Requirements](#requirements)
4. [Components](#components)
5. [Implementation Details](#implementation-details)
6. [Usage Guide](#usage-guide)
7. [Database Schema](#database-schema)
8. [API Reference](#api-reference)
9. [Troubleshooting](#troubleshooting)
10. [Future Enhancements](#future-enhancements)

---

## Overview

### Purpose
The Audit Log System provides comprehensive tracking of user activities and data changes across the Animal Rescue & Adoption Management System's distributed database architecture. It enables administrators to monitor:
- User authentication events (login, logout, failed attempts)
- CRUD operations on critical models (Animal, Medical, Vaccination)
- Changed fields with before/after values
- Cross-database operations across 5 separate databases

### Key Features
- ✅ **Asynchronous Logging** - No performance impact on user requests
- ✅ **Changed Fields Only** - Efficient storage of only modified attributes
- ✅ **Cross-Database Tracking** - Monitors operations across all 5 databases
- ✅ **Admin-Only Access** - Secure access control using Spatie Laravel Permission
- ✅ **Rich Filtering** - Filter by user, action type, model, date range
- ✅ **Detailed Context** - IP address, user agent, request URL, HTTP method

### Technology Stack
- **Laravel 11** - PHP framework
- **PostgreSQL** - Audit log storage (taufiq database)
- **Laravel Queue** - Asynchronous job processing
- **Spatie Laravel Permission** - Role-based access control
- **Blade + Tailwind CSS** - Admin interface

---

## Architecture

### Distributed Database Context

The application uses **5 separate databases** across different engines:

| Connection | Engine | Port | Purpose |
|------------|--------|------|---------|
| `taufiq` | PostgreSQL | 5432 | Users, Roles, **Audit Logs** |
| `eilya` | MySQL | 3307 | Stray Reports, Rescues |
| `shafiqah` | MySQL | 3309 | **Animals, Medical, Vaccinations** |
| `atiqah` | MySQL | 3308 | Shelter, Slots, Inventory |
| `danish` | SQL Server | 1434 | Bookings, Adoptions |

### Audit Log Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     User Actions                             │
│  (Login, Logout, Create Animal, Update Medical, etc.)       │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│              Event Listeners & Observers                     │
│  • LogAuthenticationEvent (login/logout/failed)             │
│  • AnimalObserver (create/update/delete)                    │
│  • MedicalObserver (create/update/delete)                   │
│  • VaccinationObserver (create/update/delete)               │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│                  CreateAuditLog Job                          │
│  • Dispatched to queue (asynchronous)                       │
│  • Retries: 3 attempts with exponential backoff             │
│  • Timeout: 30 seconds                                      │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│              Queue Worker Processing                         │
│  php artisan queue:work --tries=3 --timeout=30              │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│         Audit Log Storage (Taufiq Database)                  │
│  • audit_logs table (PostgreSQL)                            │
│  • JSONB fields with GIN indexes                            │
│  • Immutable records (no updates)                           │
└─────────────────────────────────────────────────────────────┘
```

### Design Decisions

#### 1. Storage Location: Taufiq Database
**Rationale:** Store audit logs alongside user data in the same PostgreSQL database for:
- Logical relationship with users table
- PostgreSQL's superior JSON querying capabilities (JSONB + GIN indexes)
- Consistent database engine for user-related data

#### 2. Asynchronous Processing
**Rationale:** Queue jobs to avoid performance impact:
- User requests complete immediately
- Audit log creation happens in background
- Failed jobs can be retried automatically
- Queue worker can be scaled independently

#### 3. Changed Fields Only
**Rationale:** Store only modified attributes to:
- Reduce storage requirements
- Improve query performance
- Maintain meaningful audit trail
- Avoid noise from timestamp-only updates

#### 4. Cross-Database Context
**Rationale:** Track which database connection was used:
- Essential for distributed architecture
- Helps identify data location
- Supports cross-database forensics

---

## Requirements

### System Requirements Clarified During Planning

**User Specifications:**
1. ✅ **Database**: Store in taufiq (PostgreSQL) with users
2. ✅ **Tracking Scope**: Authentication + CRUD on Animal, Medical, Vaccination
3. ✅ **Detail Level**: Changed fields only (before/after values)
4. ✅ **Performance**: Asynchronous (queued) logging
5. ✅ **Access Control**: Admin-only access to audit logs

### PHP Extensions
- `pdo_pgsql` - PostgreSQL PDO driver
- `mbstring` - Multi-byte string functions
- Standard Laravel 11 requirements

### Environment Configuration

**Queue System:**
```env
QUEUE_CONNECTION=database
```

**Database Connection (Taufiq):**
```env
DB5_HOST=127.0.0.1
DB5_PORT=5432
DB5_DATABASE=workshop
DB5_USERNAME=postgres
DB5_PASSWORD=password
```

---

## Components

### 1. Database Migration

**File:** `database/migrations/2025_12_16_131741_create_audit_logs_table.php`

**Features:**
- Creates `audit_logs` table in taufiq database
- PostgreSQL JSONB columns for efficient JSON storage
- GIN indexes for fast JSONB queries
- Immutable design (no `updated_at` column)

**Schema:**
```php
Schema::connection('taufiq')->create('audit_logs', function (Blueprint $table) {
    $table->id();

    // User tracking (denormalized)
    $table->unsignedBigInteger('user_id')->nullable();
    $table->string('user_email')->nullable();
    $table->string('user_name')->nullable();

    // Action metadata
    $table->string('action_type', 50);
    $table->string('auditable_type', 100)->nullable();
    $table->unsignedBigInteger('auditable_id')->nullable();
    $table->string('auditable_connection', 50)->nullable();

    // Request context
    $table->ipAddress('ip_address')->nullable();
    $table->text('user_agent')->nullable();
    $table->string('url', 500)->nullable();
    $table->string('method', 10)->nullable();

    // Changed data (JSONB)
    $table->jsonb('changed_fields')->nullable();
    $table->jsonb('metadata')->nullable();

    // Timestamp
    $table->timestamp('created_at')->useCurrent();

    // Indexes
    $table->index('user_id');
    $table->index('action_type');
    $table->index(['auditable_type', 'auditable_id']);
    $table->index('created_at');
    $table->index(['user_id', 'created_at']);
});

// GIN indexes for JSONB
DB::connection('taufiq')->statement('CREATE INDEX audit_logs_changed_fields_gin ON audit_logs USING GIN (changed_fields)');
DB::connection('taufiq')->statement('CREATE INDEX audit_logs_metadata_gin ON audit_logs USING GIN (metadata)');
```

### 2. AuditLog Model

**File:** `app/Models/AuditLog.php`

**Features:**
- Connection: `taufiq` (PostgreSQL)
- Immutable (no `updated_at`)
- Query scopes for filtering
- Accessors for formatted output
- Cross-database User relationship

**Key Methods:**

```php
// Query Scopes
scopeForUser($query, $userId)          // Filter by user
scopeForAction($query, $actionType)    // Filter by action
scopeForModel($query, $modelClass)     // Filter by model
scopeDateRange($query, $start, $end)   // Date range filter
scopeRecent($query, $days = 30)        // Recent logs

// Accessors
getActionLabelAttribute()              // "Created", "Updated", etc.
getModelNameAttribute()                // "Animal", "Medical", etc.

// Relationship
user()                                 // BelongsTo User (cross-database)
```

### 3. CreateAuditLog Job

**File:** `app/Jobs/CreateAuditLog.php`

**Configuration:**
- Queue: `audit` (dedicated queue)
- Tries: 3 attempts
- Timeout: 30 seconds
- Backoff: [5, 10, 20] seconds (exponential)

**Functionality:**
```php
public function __construct(array $data)
{
    $this->data = $data;
    $this->onQueue('audit');
}

public function handle(): void
{
    try {
        AuditLog::create($this->data);
    } catch (\Exception $e) {
        Log::error('Failed to create audit log', [
            'data' => $this->data,
            'error' => $e->getMessage(),
        ]);
        throw $e; // Trigger retry
    }
}

public function failed(\Throwable $exception)
{
    Log::critical('Audit log creation failed permanently', [
        'data' => $this->data,
        'error' => $exception->getMessage(),
    ]);
}
```

### 4. Authentication Event Listener

**File:** `app/Listeners/LogAuthenticationEvent.php`

**Events Tracked:**
- `Illuminate\Auth\Events\Login` → `handleLogin()`
- `Illuminate\Auth\Events\Logout` → `handleLogout()`
- `Illuminate\Auth\Events\Failed` → `handleFailed()`
- `Illuminate\Auth\Events\Lockout` → `handleLockout()`

**Example Handler:**
```php
public function handleLogin(Login $event)
{
    CreateAuditLog::dispatch([
        'user_id' => $event->user->id,
        'user_email' => $event->user->email,
        'user_name' => $event->user->name,
        'action_type' => 'login',
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'url' => request()->fullUrl(),
        'method' => request()->method(),
        'metadata' => ['guard' => $event->guard],
    ]);
}
```

### 5. Model Observers

**Files:**
- `app/Observers/AnimalObserver.php`
- `app/Observers/MedicalObserver.php`
- `app/Observers/VaccinationObserver.php`

**Events Tracked:**
- `created()` - New record created
- `updated()` - Record updated (only if dirty)
- `deleted()` - Record deleted

**Changed Fields Logic:**
```php
protected function logAudit($actionType, Animal $animal, $changes)
{
    $user = Auth::user();

    // Build changed_fields (only for updates)
    $changedFields = null;
    if ($actionType === 'updated' && $changes) {
        $changedFields = [];
        foreach ($changes as $field => $newValue) {
            // Skip timestamps
            if (in_array($field, ['updated_at', 'created_at'])) {
                continue;
            }

            $changedFields[$field] = [
                'old' => $animal->getOriginal($field),
                'new' => $newValue,
            ];
        }

        // Don't log if only timestamps changed
        if (empty($changedFields)) {
            return;
        }
    }

    CreateAuditLog::dispatch([
        'user_id' => $user?->id,
        'user_email' => $user?->email,
        'user_name' => $user?->name,
        'action_type' => $actionType,
        'auditable_type' => get_class($animal),
        'auditable_id' => $animal->id,
        'auditable_connection' => $animal->getConnectionName(),
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'url' => request()->fullUrl(),
        'method' => request()->method(),
        'changed_fields' => $changedFields,
        'metadata' => [
            'animal_name' => $animal->name,
            'species' => $animal->species,
        ],
    ]);
}
```

### 6. Service Provider Registration

**File:** `app/Providers/AppServiceProvider.php`

**Registration:**
```php
public function boot(): void
{
    // Register Model Observers
    Animal::observe(AnimalObserver::class);
    Medical::observe(MedicalObserver::class);
    Vaccination::observe(VaccinationObserver::class);

    // Register Authentication Event Listeners
    Event::listen(Login::class, [LogAuthenticationEvent::class, 'handleLogin']);
    Event::listen(Logout::class, [LogAuthenticationEvent::class, 'handleLogout']);
    Event::listen(Failed::class, [LogAuthenticationEvent::class, 'handleFailed']);
    Event::listen(Lockout::class, [LogAuthenticationEvent::class, 'handleLockout']);
}
```

### 7. AuditLogController

**File:** `app/Http/Controllers/AuditLogController.php`

**Features:**
- Admin-only middleware (using Spatie `hasRole('admin')`)
- Filterable index page
- Detailed view page
- PostgreSQL ILIKE search (case-insensitive)

**Methods:**
```php
public function index(Request $request)
{
    $query = AuditLog::query()->with('user')->latest();

    // Apply filters
    if ($request->filled('user_id')) {
        $query->forUser($request->user_id);
    }

    if ($request->filled('action_type')) {
        $query->forAction($request->action_type);
    }

    if ($request->filled('model_type')) {
        $query->forModel($request->model_type);
    }

    // Date range (default: last 30 days)
    if ($request->filled('days')) {
        $query->recent($request->days);
    } else {
        $query->recent(30);
    }

    // Search
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('user_email', 'ILIKE', "%{$search}%")
              ->orWhere('user_name', 'ILIKE', "%{$search}%");
        });
    }

    $logs = $query->paginate(50);

    // Get filter options
    $users = User::orderBy('name')->get(['id', 'name', 'email']);
    $actionTypes = AuditLog::distinct()->pluck('action_type');
    $modelTypes = AuditLog::distinct()->whereNotNull('auditable_type')->pluck('auditable_type');

    return view('audit-logs.index', compact('logs', 'users', 'actionTypes', 'modelTypes'));
}

public function show($id)
{
    $log = AuditLog::with('user')->findOrFail($id);
    return view('audit-logs.show', compact('log'));
}
```

### 8. Routes

**File:** `routes/web.php`

```php
// Audit Logs (Admin only)
Route::middleware(['auth'])->prefix('audit-logs')->group(function () {
    Route::get('/', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/{id}', [AuditLogController::class, 'show'])->name('audit-logs.show');
});
```

### 9. Blade Views

#### Index View
**File:** `resources/views/audit-logs/index.blade.php`

**Features:**
- Filter form (user, action, model, date range)
- Data table with color-coded action badges
- Pagination (50 per page)
- Responsive design (Tailwind CSS)

**Action Badge Colors:**
- 🟢 **Created** - Green
- 🟡 **Updated** - Yellow
- 🔴 **Deleted** - Red
- 🔵 **Login** - Blue
- ⚪ **Logout** - Gray
- 🔴 **Failed Login** - Red

#### Detail View
**File:** `resources/views/audit-logs/show.blade.php`

**Features:**
- Log metadata grid (timestamp, user, action, model, IP, request)
- Changed fields table (field, old value, new value)
- Additional metadata (JSON pretty-printed)
- User agent display
- Back to list button

### 10. Navigation

**File:** `resources/views/layouts/navigation.blade.php`

**Admin-Only Links:**
```blade
@role('admin')
<x-nav-link :href="route('audit-logs.index')" :active="request()->routeIs('audit-logs.*')">
    {{ __('Audit Logs') }}
</x-nav-link>
@endrole
```

Added to:
- Desktop navigation (header)
- Mobile navigation (responsive menu)

---

## Implementation Details

### Queue Configuration

**File:** `config/queue.php`

**Database Connection:**
```php
'connections' => [
    'database' => [
        'driver' => 'database',
        'connection' => 'taufiq',  // Hardcoded to taufiq
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
    ],
],

'batching' => [
    'database' => 'taufiq',
    'table' => 'job_batches',
],

'failed' => [
    'driver' => 'database-uuids',
    'database' => 'taufiq',
    'table' => 'failed_jobs',
],
```

**Environment Variable:**
```env
QUEUE_CONNECTION=database
```

### Composer Dev Script

**File:** `composer.json`

**Updated Script:**
```json
"dev": [
    "Composer\\Config::disableProcessTimeout",
    "npx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\" \"php artisan serve\" \"php artisan queue:work --tries=3 --timeout=30\" \"php artisan pail --timeout=0\" \"npm run dev\" --names=server,queue,logs,vite --kill-others"
]
```

**Changes:**
- ❌ Old: `queue:listen --tries=1`
- ✅ New: `queue:work --tries=3 --timeout=30`

**Improvements:**
- Better performance (`queue:work` vs `queue:listen`)
- 3 retry attempts instead of 1
- 30-second timeout for long-running jobs

---

## Usage Guide

### For Administrators

#### Accessing Audit Logs

1. **Log in as admin:**
   - Email: `admin1@gmail.com` or `admin2@gmail.com`
   - Password: `password`

2. **Navigate to Audit Logs:**
   - Click **Audit Logs** in the navigation menu
   - Only visible to users with `admin` role

#### Filtering Audit Logs

**Available Filters:**
- **User**: Select specific user to view their actions
- **Action**: Filter by action type (created, updated, deleted, login, logout, failed_login)
- **Model**: Filter by model type (Animal, Medical, Vaccination)
- **Date Range**: Last 7 days, 30 days, or 90 days

**Applying Filters:**
1. Select desired filters from dropdowns
2. Click **Apply Filters**
3. Click **Clear** to reset all filters

#### Viewing Audit Log Details

1. Click **View Details** on any log entry
2. See detailed information:
   - **Metadata**: Timestamp, user, action, model, IP, request
   - **Changed Fields**: Table showing old vs new values
   - **Additional Metadata**: JSON context (e.g., animal_name, species)
   - **User Agent**: Full browser/device information

#### Understanding Action Types

| Action Type | Description | Color |
|-------------|-------------|-------|
| `created` | New record created | 🟢 Green |
| `updated` | Record modified | 🟡 Yellow |
| `deleted` | Record deleted | 🔴 Red |
| `login` | User logged in successfully | 🔵 Blue |
| `logout` | User logged out | ⚪ Gray |
| `failed_login` | Failed login attempt or lockout | 🔴 Red |

### For Developers

#### Starting the Application

**Option 1: Full Development Stack (Recommended)**
```bash
composer dev
```

This starts:
- Web server (http://localhost:8000)
- Queue worker (processes audit log jobs)
- Laravel Pail (logs viewer)
- Vite (asset compilation)

**Option 2: Separate Processes**

Terminal 1 - Web Server:
```bash
php artisan serve
```

Terminal 2 - Queue Worker:
```bash
php artisan queue:work --tries=3 --timeout=30
```

Terminal 3 - Logs (Optional):
```bash
php artisan pail --timeout=0
```

Terminal 4 - Vite (Optional):
```bash
npm run dev
```

#### Running Migrations

**Initial Setup:**
```bash
php artisan migrate --database=taufiq
```

**Fresh Migration (All Databases):**
```bash
php artisan db:fresh-all --seed
# OR
composer fresh
```

#### Monitoring the Queue

**Check Queue Status:**
```bash
php artisan queue:monitor
```

**Check Pending Jobs:**
```sql
-- Connect to taufiq database
SELECT * FROM jobs;
```

**Check Failed Jobs:**
```sql
-- Connect to taufiq database
SELECT * FROM failed_jobs;
```

**Retry Failed Jobs:**
```bash
php artisan queue:retry all
```

#### Testing Audit Logs

**1. Test Authentication Logging:**
```bash
# Attempt login in browser
# Check audit_logs table for 'login' action
```

**2. Test Model CRUD Logging:**
```php
// In Tinker or controller
use App\Models\Animal;

// Create
$animal = Animal::create([
    'name' => 'Test Dog',
    'species' => 'Dog',
    'age' => 2,
    'gender' => 'Male',
    'adoption_status' => 'Not Adopted',
]);
// Check audit_logs for 'created' action

// Update
$animal->update(['name' => 'Updated Name']);
// Check audit_logs for 'updated' action with changed_fields

// Delete
$animal->delete();
// Check audit_logs for 'deleted' action
```

**3. Verify Queue Processing:**
```sql
-- Check jobs table (should be empty or processing)
SELECT COUNT(*) FROM jobs;

-- Check audit_logs table (should have entries)
SELECT COUNT(*) FROM audit_logs;
SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 10;
```

---

## Database Schema

### audit_logs Table

**Connection:** `taufiq` (PostgreSQL)

**Columns:**

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | BIGSERIAL | No | Primary key |
| `user_id` | BIGINT | Yes | User who performed action |
| `user_email` | VARCHAR(255) | Yes | Denormalized user email |
| `user_name` | VARCHAR(255) | Yes | Denormalized user name |
| `action_type` | VARCHAR(50) | No | Action performed |
| `auditable_type` | VARCHAR(100) | Yes | Model class name |
| `auditable_id` | BIGINT | Yes | Model ID |
| `auditable_connection` | VARCHAR(50) | Yes | Database connection |
| `ip_address` | INET | Yes | User IP address |
| `user_agent` | TEXT | Yes | Browser/device info |
| `url` | VARCHAR(500) | Yes | Request URL |
| `method` | VARCHAR(10) | Yes | HTTP method |
| `changed_fields` | JSONB | Yes | Changed fields with old/new values |
| `metadata` | JSONB | Yes | Additional context |
| `created_at` | TIMESTAMP | No | When action occurred |

**Indexes:**
- Primary key on `id`
- Index on `user_id`
- Index on `action_type`
- Composite index on `(auditable_type, auditable_id)`
- Index on `created_at`
- Composite index on `(user_id, created_at)`
- GIN index on `changed_fields` (JSONB)
- GIN index on `metadata` (JSONB)

**Constraints:**
- No foreign keys (cross-database architecture)
- User data denormalized (preserved even if user deleted)

### Example Records

**Login Event:**
```json
{
  "id": 1,
  "user_id": 1,
  "user_email": "admin1@gmail.com",
  "user_name": "Admin User",
  "action_type": "login",
  "auditable_type": null,
  "auditable_id": null,
  "auditable_connection": null,
  "ip_address": "127.0.0.1",
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)...",
  "url": "http://localhost:8000/login",
  "method": "POST",
  "changed_fields": null,
  "metadata": {
    "guard": "web"
  },
  "created_at": "2025-12-16 13:45:23"
}
```

**Animal Update:**
```json
{
  "id": 2,
  "user_id": 1,
  "user_email": "admin1@gmail.com",
  "user_name": "Admin User",
  "action_type": "updated",
  "auditable_type": "App\\Models\\Animal",
  "auditable_id": 5,
  "auditable_connection": "shafiqah",
  "ip_address": "127.0.0.1",
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)...",
  "url": "http://localhost:8000/animal/5",
  "method": "PUT",
  "changed_fields": {
    "name": {
      "old": "Fluffy",
      "new": "Fluffy Jr."
    },
    "weight": {
      "old": 5.5,
      "new": 6.2
    }
  },
  "metadata": {
    "animal_name": "Fluffy Jr.",
    "species": "Cat"
  },
  "created_at": "2025-12-16 13:47:15"
}
```

---

## API Reference

### AuditLog Model Methods

#### Query Scopes

**forUser($userId)**
```php
// Filter logs by user ID
$logs = AuditLog::forUser(1)->get();
```

**forAction($actionType)**
```php
// Filter logs by action type
$logs = AuditLog::forAction('login')->get();
```

**forModel($modelClass)**
```php
// Filter logs by model class
$logs = AuditLog::forModel('App\Models\Animal')->get();
```

**dateRange($startDate, $endDate)**
```php
// Filter logs by date range
$logs = AuditLog::dateRange('2025-12-01', '2025-12-31')->get();
```

**recent($days = 30)**
```php
// Get logs from last N days
$logs = AuditLog::recent(7)->get();  // Last 7 days
```

#### Accessors

**$log->action_label**
```php
// Get formatted action label
echo $log->action_label;  // "Created", "Updated", "Logged In", etc.
```

**$log->model_name**
```php
// Get short model name
echo $log->model_name;  // "Animal", "Medical", etc.
```

#### Relationships

**$log->user**
```php
// Get user who performed action (cross-database)
$user = $log->user;
echo $user->name;
```

### CreateAuditLog Job

**Dispatching:**
```php
use App\Jobs\CreateAuditLog;

CreateAuditLog::dispatch([
    'user_id' => 1,
    'user_email' => 'user@example.com',
    'user_name' => 'John Doe',
    'action_type' => 'custom_action',
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'url' => request()->fullUrl(),
    'method' => request()->method(),
    'metadata' => ['custom_key' => 'custom_value'],
]);
```

**Synchronous Dispatch (Testing Only):**
```php
CreateAuditLog::dispatchSync([...]);
```

### Custom Audit Logging

**Manual Audit Log Creation:**
```php
use App\Jobs\CreateAuditLog;
use Illuminate\Support\Facades\Auth;

// Log custom action
CreateAuditLog::dispatch([
    'user_id' => Auth::id(),
    'user_email' => Auth::user()->email,
    'user_name' => Auth::user()->name,
    'action_type' => 'custom_export',
    'auditable_type' => 'App\Models\Report',
    'auditable_id' => null,
    'auditable_connection' => 'eilya',
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'url' => request()->fullUrl(),
    'method' => request()->method(),
    'metadata' => [
        'export_format' => 'CSV',
        'record_count' => 150,
    ],
]);
```

---

## Troubleshooting

### Common Issues

#### 1. Audit Logs Not Being Created

**Symptoms:**
- User actions occur but no audit logs in database
- Jobs table accumulating entries

**Diagnosis:**
```bash
# Check if jobs are queued
php artisan tinker
>>> DB::connection('taufiq')->table('jobs')->count();
```

**Solutions:**

**A. Queue Worker Not Running**
```bash
# Start queue worker
php artisan queue:work --tries=3 --timeout=30

# OR use composer dev
composer dev
```

**B. Queue Connection Misconfigured**
```bash
# Check .env
cat .env | grep QUEUE_CONNECTION
# Should be: QUEUE_CONNECTION=database

# If wrong, update .env and restart
```

**C. Database Connection Failed**
```bash
# Test taufiq connection
php artisan tinker
>>> DB::connection('taufiq')->getPdo();
```

#### 2. Jobs Failing Repeatedly

**Symptoms:**
- Jobs in `failed_jobs` table
- Critical logs in Laravel logs

**Diagnosis:**
```bash
# Check failed jobs
php artisan tinker
>>> DB::connection('taufiq')->table('failed_jobs')->get();
```

**Solutions:**

**A. Review Error Logs**
```bash
# Check Laravel logs
cat storage/logs/laravel.log | grep "Audit log creation failed"
```

**B. Retry Failed Jobs**
```bash
# Retry all failed jobs
php artisan queue:retry all

# Retry specific job
php artisan queue:retry <job-id>
```

**C. Clear Failed Jobs**
```bash
# After fixing issue, clear failed jobs
php artisan queue:flush
```

#### 3. Permission Denied (403) When Accessing Audit Logs

**Symptoms:**
- 403 error when accessing `/audit-logs`
- Error: "Unauthorized access to audit logs"

**Diagnosis:**
```bash
# Check user role
php artisan tinker
>>> $user = User::where('email', 'your-email@example.com')->first();
>>> $user->roles->pluck('name');
```

**Solutions:**

**A. Assign Admin Role**
```bash
php artisan tinker
>>> $user = User::where('email', 'your-email@example.com')->first();
>>> $user->assignRole('admin');
```

**B. Verify Role Exists**
```bash
php artisan tinker
>>> Spatie\Permission\Models\Role::all()->pluck('name');
```

**C. Seed Roles (if missing)**
```bash
php artisan db:seed --class=RoleSeeder
```

#### 4. JSONB Query Errors

**Symptoms:**
- Errors when filtering or querying audit logs
- "operator does not exist" errors

**Diagnosis:**
```bash
# Verify PostgreSQL version
php artisan tinker
>>> DB::connection('taufiq')->select('SHOW server_version')[0]->server_version;
```

**Solutions:**

**A. Ensure PostgreSQL 9.4+**
- JSONB requires PostgreSQL 9.4 or higher
- Check version: `psql --version`

**B. Verify GIN Indexes**
```sql
-- Connect to taufiq database
SELECT indexname FROM pg_indexes WHERE tablename = 'audit_logs';
-- Should show: audit_logs_changed_fields_gin, audit_logs_metadata_gin
```

**C. Recreate Indexes**
```bash
# Drop and recreate if needed
php artisan migrate:rollback --database=taufiq --step=1
php artisan migrate --database=taufiq
```

#### 5. Memory Limit Issues with Queue Worker

**Symptoms:**
- Queue worker stops processing
- "Allowed memory size exhausted" errors

**Diagnosis:**
```bash
# Check memory limit
php -i | grep memory_limit
```

**Solutions:**

**A. Increase PHP Memory Limit**
```ini
# php.ini
memory_limit = 512M
```

**B. Use Memory-Aware Queue Worker**
```bash
# Stop worker after 128MB used
php artisan queue:work --memory=128 --tries=3 --timeout=30
```

**C. Restart Queue Worker Periodically**
```bash
# Restart after 1000 jobs
php artisan queue:work --max-jobs=1000 --tries=3 --timeout=30
```

### Performance Issues

#### 1. Slow Audit Log Queries

**Symptoms:**
- Admin page takes long to load
- Slow filter responses

**Solutions:**

**A. Verify Indexes**
```sql
-- Check if indexes exist
SELECT indexname FROM pg_indexes WHERE tablename = 'audit_logs';
```

**B. Optimize Queries**
```php
// Use eager loading
$logs = AuditLog::with('user')->latest()->paginate(50);

// Use specific columns
$logs = AuditLog::select(['id', 'user_id', 'action_type', 'created_at'])
    ->latest()
    ->paginate(50);
```

**C. Archive Old Logs**
```php
// Delete logs older than 1 year
AuditLog::where('created_at', '<', now()->subYear())->delete();
```

#### 2. Queue Backlog

**Symptoms:**
- Jobs table growing
- Audit logs delayed

**Solutions:**

**A. Scale Queue Workers**
```bash
# Run multiple workers
php artisan queue:work --queue=audit --tries=3 --timeout=30 &
php artisan queue:work --queue=audit --tries=3 --timeout=30 &
```

**B. Optimize Job Processing**
```php
// In CreateAuditLog job
public $timeout = 10;  // Reduce timeout
public $tries = 1;     // Reduce retries
```

**C. Use Redis Queue (Advanced)**
```env
QUEUE_CONNECTION=redis
```

### Debugging Tips

**Enable Query Logging:**
```php
// In controller or Tinker
DB::connection('taufiq')->enableQueryLog();

// Perform action

// View queries
dd(DB::connection('taufiq')->getQueryLog());
```

**Monitor Queue in Real-Time:**
```bash
# Watch jobs table
watch -n 1 'php artisan tinker --execute="echo DB::connection(\"taufiq\")->table(\"jobs\")->count();"'
```

**Check Audit Log Creation:**
```bash
# Tail audit logs
php artisan tinker --execute="AuditLog::latest()->limit(5)->get()->each(fn($log) => dump($log->toArray()));"
```

---

## Future Enhancements

### Planned Features

#### 1. Export Functionality
**Priority:** Medium

**Description:** Allow admins to export audit logs to CSV/Excel

**Implementation:**
```php
// Controller method
public function export(Request $request)
{
    $query = AuditLog::query()
        ->when($request->filled('user_id'), fn($q) => $q->forUser($request->user_id))
        ->when($request->filled('action_type'), fn($q) => $q->forAction($request->action_type))
        ->recent($request->days ?? 30);

    return Excel::download(new AuditLogsExport($query), 'audit-logs.xlsx');
}
```

**Package:** `maatwebsite/excel`

#### 2. Real-Time Notifications
**Priority:** Low

**Description:** Notify admins of suspicious activity in real-time

**Use Cases:**
- Multiple failed login attempts from same IP
- Bulk deletions
- Unusual activity patterns

**Implementation:**
```php
// In observer
if ($this->isSuspicious($animal, $actionType)) {
    event(new SuspiciousActivityDetected($log));
}
```

**Technologies:** Laravel Echo, Pusher, or WebSockets

#### 3. Audit Log Archival
**Priority:** High (for long-term)

**Description:** Archive logs older than 1 year to separate table or storage

**Implementation:**
```php
// Artisan command
php artisan audit:archive --older-than=1year

// Archive to separate table
$archived = AuditLog::where('created_at', '<', now()->subYear())
    ->get()
    ->each(function ($log) {
        ArchivedAuditLog::create($log->toArray());
        $log->delete();
    });
```

#### 4. Advanced Search
**Priority:** Medium

**Description:** Full-text search on changed fields and metadata

**Implementation:**
```sql
-- Create full-text index
CREATE INDEX audit_logs_fulltext ON audit_logs
USING GIN (to_tsvector('english', changed_fields::text));

-- Search query
SELECT * FROM audit_logs
WHERE to_tsvector('english', changed_fields::text) @@ to_tsquery('english', 'search_term');
```

#### 5. Analytics Dashboard
**Priority:** Medium

**Description:** Visualize audit log data with charts and insights

**Metrics:**
- Most active users
- Peak activity hours
- Model modification frequency
- Failed login patterns

**Implementation:**
```php
// Controller
public function analytics()
{
    $data = [
        'active_users' => AuditLog::selectRaw('user_id, user_name, COUNT(*) as count')
            ->groupBy('user_id', 'user_name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get(),

        'actions_by_type' => AuditLog::selectRaw('action_type, COUNT(*) as count')
            ->groupBy('action_type')
            ->get(),

        'hourly_activity' => AuditLog::selectRaw('EXTRACT(HOUR FROM created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->get(),
    ];

    return view('audit-logs.analytics', $data);
}
```

**Charting Library:** Chart.js or ApexCharts

#### 6. Compliance Reports
**Priority:** Low

**Description:** Generate GDPR-compliant audit trail reports

**Features:**
- PDF export with digital signature
- Custom date ranges
- Filterable by user or action
- Official header/footer

**Implementation:**
```php
use Barryvdh\DomPDF\Facade\Pdf;

public function complianceReport(Request $request)
{
    $logs = AuditLog::dateRange($request->start_date, $request->end_date)
        ->get();

    $pdf = Pdf::loadView('audit-logs.compliance-report', compact('logs'));

    return $pdf->download('compliance-report-' . now()->format('Y-m-d') . '.pdf');
}
```

**Package:** `barryvdh/laravel-dompdf`

#### 7. API Audit Logging
**Priority:** Low (currently no API)

**Description:** Extend audit logging to API requests via middleware

**Implementation:**
```php
// Middleware
class AuditApiRequest
{
    public function handle($request, $next)
    {
        $response = $next($request);

        if (Auth::guard('api')->check()) {
            CreateAuditLog::dispatch([
                'user_id' => Auth::guard('api')->id(),
                'action_type' => 'api_request',
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'metadata' => [
                    'endpoint' => $request->path(),
                    'status_code' => $response->status(),
                ],
            ]);
        }

        return $response;
    }
}
```

#### 8. Audit Log Restoration
**Priority:** Low

**Description:** Restore deleted records from audit log data

**Implementation:**
```php
public function restore($logId)
{
    $log = AuditLog::findOrFail($logId);

    if ($log->action_type !== 'deleted') {
        abort(400, 'Can only restore deleted records');
    }

    $modelClass = $log->auditable_type;
    $model = new $modelClass;

    // Restore from changed_fields (stored original values)
    $restored = $model->create($log->changed_fields);

    return redirect()->back()->with('success', 'Record restored successfully');
}
```

### Enhancement Roadmap

**Phase 1 (High Priority):**
1. Audit log archival system
2. Export to CSV/Excel

**Phase 2 (Medium Priority):**
3. Advanced search with full-text
4. Analytics dashboard
5. Real-time notifications

**Phase 3 (Low Priority):**
6. Compliance reports
7. API audit logging
8. Audit log restoration

---

## Security Considerations

### Access Control
- ✅ **Admin-only access** enforced via middleware
- ✅ **Role-based permissions** using Spatie Laravel Permission
- ✅ **No public endpoints** - all routes require authentication

### Data Protection
- ✅ **Denormalized user data** - preserved even if user deleted
- ✅ **Immutable records** - no updates to audit logs
- ✅ **IP address logging** - for forensic investigation
- ⚠️ **User agent logging** - may contain sensitive browser data

### GDPR Compliance
- ✅ **Right to access** - admins can view user's audit trail
- ⚠️ **Right to erasure** - consider anonymizing user data on deletion
- ✅ **Data retention** - implement archival/deletion policy

**Recommended Implementation:**
```php
// User deletion event
public function deleted(User $user)
{
    // Anonymize audit logs
    AuditLog::where('user_id', $user->id)
        ->update([
            'user_email' => 'deleted-user@localhost',
            'user_name' => 'Deleted User',
            'ip_address' => '0.0.0.0',
        ]);
}
```

### Performance Security
- ✅ **Asynchronous logging** - prevents DoS via audit log spam
- ✅ **Queue worker limits** - memory and job count limits
- ✅ **Database indexes** - prevent slow query attacks

---

## Maintenance

### Regular Tasks

**Daily:**
- ✅ Monitor queue worker status
- ✅ Check `failed_jobs` table

**Weekly:**
- ✅ Review audit log growth
- ✅ Check disk space (taufiq database)
- ✅ Verify backup system includes audit logs

**Monthly:**
- ✅ Analyze audit log patterns
- ✅ Review failed login attempts
- ✅ Test audit log exports

**Yearly:**
- ✅ Archive logs older than 1 year
- ✅ Review access control policies
- ✅ Update retention policies

### Monitoring Commands

```bash
# Check database size
php artisan tinker
>>> DB::connection('taufiq')->select("SELECT pg_size_pretty(pg_total_relation_size('audit_logs'))");

# Count logs per day
php artisan tinker
>>> DB::connection('taufiq')->table('audit_logs')
    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
    ->groupBy('date')
    ->orderBy('date', 'desc')
    ->limit(30)
    ->get();

# Check queue health
php artisan queue:monitor

# List failed jobs
php artisan queue:failed
```

---

## Changelog

### Version 1.0.0 (2025-12-16)
**Initial Release**

**Features:**
- ✅ Audit log system with PostgreSQL JSONB storage
- ✅ Authentication event tracking (login/logout/failed)
- ✅ Model CRUD tracking (Animal, Medical, Vaccination)
- ✅ Asynchronous queue processing
- ✅ Admin interface with filtering
- ✅ Changed fields only storage
- ✅ Cross-database tracking

**Files Added:**
- Migration: `create_audit_logs_table.php`
- Model: `AuditLog.php`
- Job: `CreateAuditLog.php`
- Listener: `LogAuthenticationEvent.php`
- Observers: `AnimalObserver.php`, `MedicalObserver.php`, `VaccinationObserver.php`
- Controller: `AuditLogController.php`
- Views: `audit-logs/index.blade.php`, `audit-logs/show.blade.php`

**Configuration:**
- Updated `config/queue.php` to use taufiq database
- Updated `composer.json` dev script for queue worker
- Registered observers and listeners in `AppServiceProvider.php`
- Added routes to `routes/web.php`
- Added navigation links to `layouts/navigation.blade.php`

---

## Support

### Getting Help

**Documentation:**
- This file: `AUDIT-LOG-DOCUMENTATION.md`
- Project README: `README.md`
- Laravel Docs: https://laravel.com/docs/11.x

**Common Questions:**
- See [Troubleshooting](#troubleshooting) section
- Check Laravel logs: `storage/logs/laravel.log`
- Review queue worker output

**Reporting Issues:**
1. Check [Troubleshooting](#troubleshooting) first
2. Gather error logs and stack traces
3. Document steps to reproduce
4. Include environment details (PHP version, database versions)

### Credits

**Developed for:** BITU3923 Workshop II - UTeM
**Project:** Animal Rescue & Adoption Management System
**Implementation Date:** December 16, 2025
**Implemented by:** Claude (Anthropic) via Claude Code CLI

**Technologies Used:**
- Laravel 11.x
- PostgreSQL 18.0
- Spatie Laravel Permission 6.21
- Tailwind CSS
- Alpine.js
- Livewire 3.6

---

## Conclusion

The Audit Log System provides comprehensive activity tracking across the distributed database architecture, enabling administrators to monitor user actions, investigate security incidents, and maintain compliance with data protection regulations.

**Key Achievements:**
- ✅ Complete audit trail of authentication and data changes
- ✅ Efficient storage using PostgreSQL JSONB
- ✅ Asynchronous processing for zero performance impact
- ✅ Admin-friendly interface with powerful filtering
- ✅ Cross-database tracking capability
- ✅ Extensible architecture for future enhancements

**Next Steps:**
1. Start the application with `composer dev`
2. Test audit logging by performing various actions
3. Access admin interface at `/audit-logs`
4. Review logs and verify tracking works correctly
5. Consider implementing [Future Enhancements](#future-enhancements)

For questions or issues, refer to the [Troubleshooting](#troubleshooting) and [Support](#support) sections.

---

**End of Documentation**
