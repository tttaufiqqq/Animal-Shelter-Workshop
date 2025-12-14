# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Animal Rescue & Adoption Management System - A Laravel 11 web application for managing animal rescue operations, medical records, shelter inventory, and adoption processes. Built for BITU3923 Workshop II at UTeM.

**Tech Stack:**
- Laravel 11 (PHP 8.2+)
- Blade Templates + Tailwind CSS + Alpine.js
- Livewire 3.6
- Spatie Laravel Permission (role-based access control)
- Pest for testing
- Laravel Breeze for authentication

## Distributed Database Architecture

This project uses a **distributed database architecture with 5 separate databases**, each on different database engines. This is the most critical architectural aspect.

### Database Connections

| Connection Name | Database Engine | Port | Module/Domain |
|----------------|-----------------|------|---------------|
| `taufiq` | PostgreSQL | 5434 | User Management (Users, Roles, AdopterProfiles) |
| `eilya` | MySQL | 3307 | Stray Reporting Management (Reports, Rescues, Images) |
| `shafiqah` | MySQL | 3309 | Animal Management (Animals, Medical, Vaccinations, Clinics, Vets, AnimalProfiles) |
| `atiqah` | MySQL | 3308 | Shelter Management (Slots, Sections, Inventory, Categories) |
| `danish` | SQL Server | 1434 | Booking & Adoption Management (Bookings, Adoptions, Transactions, VisitLists) |

### Working with Cross-Database Relationships

**CRITICAL:** Foreign key constraints do NOT exist across databases. Relationships between models on different connections are **logical only**, enforced at the application layer.

#### Model Connection Patterns

Models specify their database connection using the `$connection` property:

```php
// Example: Animal model uses 'shafiqah' connection
protected $connection = 'shafiqah';
```

#### Cross-Database Relationship Pattern

When defining relationships to models on different databases, use `setConnection()`:

```php
// In Animal model (on 'shafiqah' database)
public function rescue()
{
    return $this->setConnection('eilya')
        ->belongsTo(Rescue::class, 'rescueID', 'id');
}
```

#### Foreign Key Validation

**ALWAYS validate foreign keys in the application layer** before creating/updating records that reference data from other databases. Check that the referenced record exists before saving.

### Migration Strategy

Each migration explicitly sets its database connection:

```php
public function up(): void
{
    Schema::connection('danish')->create('bookings', function (Blueprint $table) {
        // ...
    });
}
```

Migrations are designed to work with MySQL, PostgreSQL, and SQL Server. Be careful with:
- Auto-incrementing primary keys (different syntax across engines)
- Index constraints and unique constraints (naming varies)
- Data types (e.g., `text` vs `nvarchar(max)`)

## Development Commands

### Initial Setup
```bash
composer setup  # Install dependencies, copy .env, generate key, migrate, build assets
```

### Running the Development Environment
```bash
composer dev    # Runs server, queue, logs (pail), and Vite concurrently
# OR individually:
php artisan serve
php artisan queue:listen --tries=1
php artisan pail --timeout=0
npm run dev
```

### Database Operations

**Fresh Migration (ALL databases):**
```bash
php artisan db:fresh-all
# OR using composer:
composer fresh
```

**Fresh Migration + Seed (ALL databases):**
```bash
php artisan db:fresh-all --seed
# OR using composer (automatically includes --seed):
composer fresh
```

**Standard Migration:**
```bash
php artisan migrate
```

**Seeding only:**
```bash
php artisan db:seed
```

**IMPORTANT:** Use `db:fresh-all` instead of `migrate:fresh` for this project. Laravel's default `migrate:fresh` only drops tables from the default connection, not all 5 distributed databases. The custom `db:fresh-all` command properly drops all tables from all connections (taufiq, eilya, shafiqah, atiqah, danish) before running migrations.

**IMPORTANT:** Seeders must run in a specific order (defined in `DatabaseSeeder.php`) because of cross-database dependencies:
1. RoleSeeder
2. UserSeeder
3. ReportSeeder
4. RescueSeeder
5. CategorySeeder
6. SectionSlotSeeder
7. ClinicVetSeeder
8. AnimalSeeder
9. BookingSeeder
10. TransactionSeeder
11. AdoptionSeeder
12. AnimalProfileSeeder

### Testing
```bash
composer test           # Run all tests
php artisan test        # Laravel test runner
php artisan test --filter=TestName  # Run specific test
```

Testing uses SQLite in-memory database (see `phpunit.xml`).

### Code Quality
```bash
./vendor/bin/pint       # Laravel Pint (PHP CS Fixer)
```

### Assets
```bash
npm run build          # Production build
npm run dev            # Development mode with HMR
```

## Application Architecture

### Module Structure (by Controller)

The application is organized into 5 main modules, each corresponding to a database:

1. **User Management** (Taufiq - PostgreSQL)
   - `ProfileController` - User profiles and settings
   - Uses Spatie Permission for roles: Admin, Caretaker, User

2. **Stray Reporting** (Eilya - MySQL)
   - `StrayReportingManagementController` - Report/Rescue CRUD
   - `RescueMapController` - Geolocation mapping with clustering
   - Reports contain lat/long coordinates
   - Caretakers can be assigned to rescues

3. **Animal Management** (Shafiqah - MySQL)
   - `AnimalManagementController` - Animal CRUD, medical records, vaccinations
   - Manages Clinics and Vets
   - Links animals to rescues (cross-database to Eilya)
   - Links animals to slots (cross-database to Atiqah)

4. **Shelter Management** (Atiqah - MySQL)
   - `ShelterManagementController` - Slots, Sections, Categories, Inventory
   - Slot assignment to animals
   - Inventory tracking (food, medicine, etc.)

5. **Booking & Adoption** (Danish - SQL Server)
   - `BookingAdoptionController` - Appointment bookings, adoptions, payments
   - Visit List feature (users can save animals to visit)
   - ToyyibPay payment integration
   - Transaction and adoption records

### Key Models & Relationships

**Cross-database relationships are common.** Examples:

- `Animal` (shafiqah) → `Rescue` (eilya)
- `Animal` (shafiqah) → `Slot` (atiqah)
- `Animal` (shafiqah) → `Booking` (danish) via `animal_booking` pivot
- `Animal` (shafiqah) → `Image` (eilya)

**Animal Matching System:**
- `AnimalProfile` (in Shafiqah's database) and `AdopterProfile` (in Taufiq's database) tables support matching animals to adopters
- `AnimalProfile` has FK to `animal` table (same database - Shafiqah)
- `AdopterProfile` has FK to `users` table (same database - Taufiq)
- Matching logic in `AnimalManagementController::getMatches()` works across databases at application layer

### Livewire Components

- `Dashboard.php` - Main dashboard with metrics and visualizations
- Used at `/dashboard` route

### Route Organization

Routes are grouped by module in `routes/web.php`:
- Stray-Reporting routes (lines 36-49)
- Animal-Management routes (lines 51-86)
- Shelter-Management routes (lines 88-116)
- Booking-Adoption routes (lines 119-151)

Most routes require `auth` middleware. Role-based permissions use Spatie Laravel Permission.

## Environment Configuration

The `.env` file must define 5 database connections (see `.env.example`):

```
DB1_HOST, DB1_PORT, DB1_DATABASE, DB1_USERNAME, DB1_PASSWORD  # Eilya (MySQL)
DB2_HOST, DB2_PORT, DB2_DATABASE, DB2_USERNAME, DB2_PASSWORD  # Atiqah (MySQL)
DB3_HOST, DB3_PORT, DB3_DATABASE, DB3_USERNAME, DB3_PASSWORD  # Shafiqah (MySQL)
DB4_HOST, DB4_PORT, DB4_DATABASE, DB4_USERNAME, DB4_PASSWORD  # Danish (SQL Server)
DB5_HOST, DB5_PORT, DB5_DATABASE, DB5_USERNAME, DB5_PASSWORD  # Taufiq (PostgreSQL)
```

ToyyibPay credentials:
```
TOYYIBPAY_KEY=
TOYYIBPAY_CATEGORY=
```

## Critical Development Patterns

### When Creating New Models

1. **Explicitly set `$connection`** property to the correct database
2. **Use `setConnection()` for cross-database relationships**
3. **Never rely on database foreign keys** across databases
4. **Validate foreign keys in controller logic** before saving

### When Writing Migrations

1. **Specify connection explicitly:** `Schema::connection('name')`
2. **Test migration on all 3 database engines** (MySQL, PostgreSQL, SQL Server)
3. **Avoid database-specific syntax** where possible
4. **Use `$table->id()` for auto-increment** (works across engines)

### When Writing Queries

1. **Set connection on the model** or query builder
2. **Be aware of transaction boundaries** - transactions don't span databases
3. **Handle cross-database joins at application level** (not in SQL)

### When Writing Seeders

1. **Respect the seeding order** in `DatabaseSeeder.php`
2. **Validate cross-database references exist** before inserting
3. **Use the correct connection** for each model

## Common Pitfalls

1. **Forgetting to set model connection** - defaults to 'sqlite' if not specified
2. **Assuming foreign key constraints exist** - they don't across databases
3. **Using joins across databases** - not possible, must eager load
4. **Migration compatibility** - syntax that works on MySQL may fail on SQL Server/PostgreSQL
5. **Transaction scope** - can't rollback across multiple databases atomically

## Custom Artisan Commands

### `db:fresh-all` - Fresh Migration for Distributed Databases

**Location:** `app/Console/Commands/FreshAllDatabases.php`

#### The Problem

Laravel's default `migrate:fresh` command only drops tables from the **default** database connection (configured as `sqlite` in this project). In a distributed database architecture with 5 separate databases, this causes critical issues:

- Tables remain in the 4 remote databases (eilya, shafiqah, atiqah, danish)
- Migrations fail with "table already exists" errors
- Requires manual intervention to drop tables on each group member's database

#### The Solution

The custom `db:fresh-all` command drops all tables from **all 5 database connections** before running migrations:

```bash
php artisan db:fresh-all [--seed]
```

#### How It Works

1. **Confirmation Prompt** - Requires explicit user confirmation before proceeding
2. **Drop All Tables** - Iterates through all 5 connections and drops tables:
   - **taufiq** (PostgreSQL) - Uses `DROP TABLE ... CASCADE` for dependencies
   - **eilya** (MySQL) - Disables foreign key checks, drops all tables
   - **shafiqah** (MySQL) - Disables foreign key checks, drops all tables
   - **atiqah** (MySQL) - Disables foreign key checks, drops all tables
   - **danish** (SQL Server) - Drops foreign key constraints first, then tables
3. **Run Migrations** - Executes `php artisan migrate` across all connections
4. **Optional Seeding** - Seeds all databases if `--seed` flag is provided

#### Database-Specific Strategies

**MySQL (eilya, atiqah, shafiqah):**
```php
DB::connection($connection)->statement('SET FOREIGN_KEY_CHECKS=0');
// Drop all tables
DB::connection($connection)->statement('SET FOREIGN_KEY_CHECKS=1');
```

**PostgreSQL (taufiq):**
```php
DB::connection($connection)->statement('DROP TABLE IF EXISTS "table_name" CASCADE');
```

**SQL Server (danish):**
```php
// Drop foreign key constraints first
ALTER TABLE [table] DROP CONSTRAINT [constraint_name]
// Then drop tables
DROP TABLE IF EXISTS [table_name]
```

#### Usage Examples

**Fresh migration without seeding:**
```bash
php artisan db:fresh-all
```

**Fresh migration with seeding (recommended):**
```bash
php artisan db:fresh-all --seed
# OR use the composer shortcut:
composer fresh
```

#### Connection Requirements

- Command works **offline** for the local database (taufiq)
- Remote databases (eilya, shafiqah, atiqah, danish) require active SSH/VPN connections
- If a connection fails, the command logs the error and continues with other databases
- Migrations will only succeed if all database connections are accessible

#### Safety Features

- **Confirmation prompt** before dropping tables
- **Error handling** for each connection (doesn't crash if one fails)
- **Progress feedback** showing tables dropped from each database
- **Transaction safety** - each database operation is isolated

#### When to Use

✅ **Use `db:fresh-all`** when:
- Refreshing all distributed databases
- Setting up development environment
- Switching branches with schema changes
- Fixing migration state issues

❌ **Don't use regular `migrate:fresh`** because:
- Only drops tables from default connection (sqlite)
- Leaves tables in remote databases intact
- Causes "table already exists" errors on next migration

---

## Booking Prevention System

### Overview

The application implements a **multi-layered defense system** to prevent animals from having multiple active bookings simultaneously. This ensures data integrity and prevents booking conflicts.

### Core Rule

**An animal can only have ONE active booking at a time.**

- **Active Booking** = Status is `Pending` or `Confirmed`
- **Inactive Booking** = Status is `Completed`, `Cancelled`, or `Adopted`
- Once a booking is completed/cancelled, the animal becomes available for new bookings

### Multi-Layer Defense Architecture

#### Layer 1: Add to Visit List Validation

**Location:** `BookingAdoptionController::addList()` (Lines 118-143)

**Trigger:** User clicks "Add to Visit List" on animal detail page

**Validation:**
- Checks if animal has ANY active booking from ANY user (not just current user)
- Prevents adding booked animals to visit list
- Provides detailed error messages with booking information

**Error Messages:**
- If booked by current user: Shows booking details and prevents adding
- If booked by another user: Shows that animal is unavailable

**Code:**
```php
$bookedAnimals = $this->getAnimalsWithActiveBookings([$animalId]);

if ($bookedAnimals->isNotEmpty()) {
    $booking = $bookedAnimals->first()->bookings->first();
    $isOwnBooking = $booking->userID == $user->id;
    // Return detailed error message
}
```

#### Layer 2: Booking Confirmation Validation

**Location:** `BookingAdoptionController::confirmAppointment()` (Lines 245-265)

**Trigger:** User submits booking from visit list modal

**Validation:**
- Checks if any selected animals have active bookings (ANY time, not just same slot)
- Prevents creating booking for animals that are already booked
- Shows detailed list of all problematic animals

**Error Message Format:**
> The following animals already have active bookings and cannot be booked again:
>
> **Fluffy**: Already booked by you on **Dec 25, 2025 at 2:00 PM** (Booking #123 - Pending)
> **Max**: Already booked by John Doe on **Dec 26, 2025 at 10:00 AM** (Booking #456 - Confirmed)

**Code:**
```php
$animalsWithActiveBookings = $this->getAnimalsWithActiveBookings($requestedAnimalIds);

if ($animalsWithActiveBookings->isNotEmpty()) {
    $errorMessages = $this->getBookedAnimalsErrorMessage($animalsWithActiveBookings, $user->id);
    // Return detailed HTML error
}
```

#### Layer 3: Time Slot Conflict Check

**Location:** `BookingAdoptionController::confirmAppointment()` (Lines 267-289)

**Trigger:** Secondary safety check during booking confirmation

**Validation:**
- Additional check for specific time slot conflicts
- Redundant safety layer in case Layer 2 is bypassed
- Prevents double-booking at same date/time

### Helper Methods

#### `getAnimalsWithActiveBookings(array $animalIds)`

**Location:** `BookingAdoptionController.php:24-37`

**Purpose:** Central method to check if animals have active bookings

**Returns:** Collection of animals with their active bookings including:
- Booking ID, date, time, status
- User who made the booking
- Ordered by appointment date (earliest first)

**Usage:**
```php
$bookedAnimals = $this->getAnimalsWithActiveBookings([1, 2, 3]);
```

#### `getBookedAnimalsErrorMessage($bookedAnimals, $currentUserId)`

**Location:** `BookingAdoptionController.php:42-66`

**Purpose:** Generate detailed, user-friendly error messages

**Features:**
- Shows animal name
- Distinguishes between own bookings and others ("by you" vs "by Another User")
- Formats dates/times in readable format (e.g., "Dec 25, 2025 at 2:00 PM")
- Shows booking ID and status
- Returns HTML-formatted messages for better readability

### Visual Indicators

#### Animal Detail Page Enhancement

**Location:** `AnimalManagementController::show()` (Lines 548-554)

**Added Variable:** `$activeBooking`

**Purpose:** Provides booking status information to the view

**Code:**
```php
$activeBooking = $animal->bookings()
    ->whereIn('status', ['Pending', 'Confirmed'])
    ->with('user')
    ->orderBy('appointment_date', 'asc')
    ->first();
```

**Suggested Blade Usage:**
```blade
@if($activeBooking)
    <div class="alert alert-warning">
        <strong>Currently Booked!</strong><br>
        This animal has an active booking for
        {{ \Carbon\Carbon::parse($activeBooking->appointment_date)->format('M d, Y') }}
        at {{ \Carbon\Carbon::parse($activeBooking->appointment_time)->format('g:i A') }}
    </div>

    <button disabled class="btn btn-secondary">
        Add to Visit List (Currently Unavailable)
    </button>
@else
    <form action="{{ route('booking-adoption.add-list', $animal->id) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary">
            Add to Visit List
        </button>
    </form>
@endif
```

### Booking Flow Diagram

```
1. Browse Animals → 2. View Details → 3. Add to Visit List
                                            ↓
                                    [Layer 1 Validation]
                                            ↓
                                    Visit List Created
                                            ↓
4. Open Visit List Modal → 5. Select Animals → 6. Confirm Appointment
                                                        ↓
                                                [Layer 2 Validation]
                                                        ↓
                                                [Layer 3 Validation]
                                                        ↓
                                                7. Create Booking ✅
```

### Testing Checklist

- [ ] Try to add an animal with active booking to visit list → Should show error
- [ ] Add animal to visit list, create booking, try to add same animal again → Should show error
- [ ] Try to book multiple animals where some have active bookings → Should show detailed error
- [ ] Complete/cancel a booking, then add animal to visit list → Should work
- [ ] Two users try to book the same animal → Second user should get error
- [ ] View animal detail page with active booking → Should show warning (if implemented in view)

---

## Database Connection Usage Guidelines

### When DB::connection() is REQUIRED

#### 1. Transactions Across Databases

Always specify the connection when starting transactions:

```php
// ❌ WRONG - uses default connection (sqlite)
DB::beginTransaction();

// ✅ CORRECT - specifies connection
DB::connection('shafiqah')->beginTransaction();
DB::connection('eilya')->beginTransaction();
```

**Example from AnimalManagementController:**
```php
// Creating Animal (shafiqah) + Images (eilya)
DB::connection('shafiqah')->beginTransaction();
DB::connection('eilya')->beginTransaction();

try {
    Animal::create([...]);  // → shafiqah
    Image::create([...]);   // → eilya

    DB::connection('shafiqah')->commit();
    DB::connection('eilya')->commit();
} catch (\Exception $e) {
    DB::connection('shafiqah')->rollBack();
    DB::connection('eilya')->rollBack();
}
```

#### 2. Raw Database Queries

Always specify the connection for raw queries:

```php
// ❌ WRONG
DB::select('SELECT * FROM animal WHERE id = ?', [$id]);

// ✅ CORRECT
DB::connection('shafiqah')->select('SELECT * FROM animal WHERE id = ?', [$id]);
```

#### 3. Schema Operations

Always specify the connection for schema operations:

```php
// ✅ CORRECT - from migrations
Schema::connection('danish')->create('bookings', function (Blueprint $table) {
    // ...
});
```

### When DB::connection() is NOT NEEDED

#### 1. Eloquent Model Operations (90% of cases)

Models automatically use their configured connection:

```php
// In Animal.php
class Animal extends Model
{
    protected $connection = 'shafiqah';  // ← Connection defined here
}

// In controllers - NO DB::connection() needed:
Animal::create([...]);           // ✓ Auto uses 'shafiqah'
Animal::find($id);               // ✓ Auto uses 'shafiqah'
Animal::where(...)->get();       // ✓ Auto uses 'shafiqah'
$animal->update([...]);          // ✓ Auto uses 'shafiqah'
$animal->delete();               // ✓ Auto uses 'shafiqah'
```

**All models have connections defined:**
- Animal, Medical, Vaccination, Clinic, Vet, AnimalProfile → `shafiqah`
- Report, Rescue, Image → `eilya`
- Slot, Section, Category, Inventory → `atiqah`
- Booking, VisitList, Adoption, Transaction → `danish`
- User, Role, AdopterProfile → `taufiq`

#### 2. Relationship Operations

Relationships automatically handle cross-database queries:

```php
// Slot model (atiqah) querying Animals (shafiqah)
$slot->animals()->count();  // ✓ Relationship handles connection

// Animal model (shafiqah) querying Bookings (danish)
$animal->bookings;          // ✓ Relationship handles connection
```

#### 3. DB::raw() Within Query Builder

When used inside a query builder, DB::raw() inherits the connection:

```php
$query = Animal::with(['images', 'slot']);  // Already on 'shafiqah'
$query->where(DB::raw('LOWER(name)'), 'LIKE', '%' . $search . '%');  // ✓ Inherits 'shafiqah'
```

### Controller Verification Summary

All controllers are correctly implemented:

| Controller | Uses Transactions | Uses Raw Queries | Status |
|------------|------------------|------------------|--------|
| AnimalManagementController | ✅ Yes - with DB::connection() | ❌ No | ✅ CORRECT |
| BookingAdoptionController | ✅ Yes - with DB::connection() | ❌ No | ✅ CORRECT |
| StrayReportingManagementController | ❌ No | ❌ No | ✅ CORRECT |
| ShelterManagementController | ❌ No | ❌ No | ✅ CORRECT |
| ProfileController | ❌ No | ❌ No | ✅ CORRECT |
| RescueMapController | ❌ No | ❌ No | ✅ CORRECT |

**No fixes needed!** All controllers follow Laravel best practices:
- Transactions explicitly specify connections
- Eloquent operations rely on model-defined connections
- No unnecessary DB::connection() calls

### The Golden Rule

```
┌─────────────────────────────────────────────────────────────┐
│  USE DB::connection() when:                                  │
│    • Starting transactions                                   │
│    • Running raw queries                                     │
│    • Performing schema operations                            │
│                                                              │
│  DON'T USE DB::connection() when:                            │
│    • Using Eloquent CRUD (create, find, update, delete)      │
│    • Using Query Builder on models                           │
│    • Accessing relationships                                 │
│    • Using DB::raw() inside query builder                    │
│                                                              │
│  WHY: Models have $connection property that handles it!      │
└─────────────────────────────────────────────────────────────┘
```

### Cross-Database Operation Examples

#### Example 1: Creating Animal with Images
```php
// Spans 2 databases: shafiqah + eilya
DB::connection('shafiqah')->beginTransaction();
DB::connection('eilya')->beginTransaction();

Animal::create([...]);  // shafiqah - Model handles connection
Image::create([...]);   // eilya - Model handles connection

DB::connection('shafiqah')->commit();
DB::connection('eilya')->commit();
```

#### Example 2: Creating Booking with Animals
```php
// All on danish database
DB::connection('danish')->beginTransaction();

Booking::create([...]);              // danish - Model handles connection
$booking->animals()->attach([...]);  // danish - Pivot uses custom model

DB::connection('danish')->commit();
```

#### Example 3: Reading Cross-Database Data
```php
// No DB::connection() needed - relationships handle it
$animal = Animal::with(['bookings', 'images', 'slot'])->find($id);

// $animal is from shafiqah
// $animal->bookings are from danish (via relationship)
// $animal->images are from eilya (via relationship)
// $animal->slot is from atiqah (via relationship)
```
