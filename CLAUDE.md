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
