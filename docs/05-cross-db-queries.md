# Cross-Database Query Patterns

## The Fundamental Limit

SQL `JOIN` requires both tables to be reachable in the same DB session. Because each
Laravel connection maps to a different physical server (or at minimum a different DB
engine), **no SQL JOIN can span two connections**. All cross-DB data assembly happens
in PHP after issuing separate queries to each connection.

---

## Eloquent: `setConnection()` for Cross-DB Relationships

When defining a relationship that targets a different database, call `setConnection()`
on the relationship builder to switch the query to the correct connection. Without this,
Eloquent would query the current model's connection for the related table.

```php
// Animal model lives on 'animals' connection (MySQL, msi)
class Animal extends Model
{
    protected $connection = 'animals';

    // CROSS-DB: rescue lives on 'reporting' (MariaDB, workshop-2)
    public function rescue()
    {
        return $this->setConnection('reporting')
            ->belongsTo(Rescue::class, 'rescueID', 'id');
    }

    // CROSS-DB: slot lives on 'shelter' (MySQL, msi — different connection)
    public function slot()
    {
        return $this->setConnection('shelter')
            ->belongsTo(Slot::class, 'slotID', 'id');
    }
}
```

The `setConnection()` call affects the *relationship query*, not the current model's
connection. The Animal model itself still reads/writes via `animals`.

Same pattern applies to `hasMany`, `hasOne`, and `belongsTo` in all directions:

```php
// User model ('users' connection) → Reports ('reporting' connection)
public function reports()
{
    return $this->setConnection('reporting')
        ->hasMany(Report::class, 'userID', 'id');
}

// Rescue model ('reporting') → Animals ('animals')
public function animals()
{
    return $this->setConnection('animals')
        ->hasMany(Animal::class, 'rescueID', 'id');
}
```

---

## Cross-DB Many-to-Many with Custom Pivot

The `animal_booking` and `visit_list_animal` pivot tables (also called junction or bridge tables) live on the `booking` connection,
but one side of the M2M (`Animal`) lives on `animals`. This requires a custom pivot model
that explicitly declares its connection.

```php
// AnimalBooking — the pivot model
class AnimalBooking extends Pivot
{
    protected $connection = 'booking';  // pivot is on Danish's DB
    protected $table = 'animal_booking';
    public $incrementing = true;

    // CROSS-DB: animal lives on 'animals'
    public function animal()
    {
        return $this->setConnection('animals')
            ->belongsTo(Animal::class, 'animalID', 'id');
    }
}
```

The `belongsToMany` declaration must pin the pivot query to the connection that owns
the pivot table — `setConnection('booking')` — regardless of which model calls it:

```php
// From Booking model (already on 'booking') — pivot is on the same connection
public function animals()
{
    return $this->setConnection('booking')
        ->belongsToMany(Animal::class, 'animal_booking', 'bookingID', 'animalID')
        ->using(AnimalBooking::class)
        ->withPivot('remarks')
        ->withTimestamps();
}

// From Animal model (on 'animals') — MUST still set 'booking' for the pivot
public function bookings()
{
    return $this->setConnection('booking')
        ->belongsToMany(Booking::class, 'animal_booking', 'animalID', 'bookingID')
        ->using(AnimalBooking::class)
        ->withPivot('remarks')
        ->withTimestamps();
}
```

Eloquent executes this as **two separate queries**: one to the pivot table on `booking`,
then a second `WHERE id IN (...)` to the related model's connection (`animals`). There is
no JOIN between the two databases.

---

## Manual Cross-DB Resolution (when Eloquent is insufficient)

For complex cross-DB data assembly (e.g. dashboard lists showing users + their bookings
+ their animals), the pattern is to query each connection independently and merge in PHP:

```php
// 1. Get bookings from 'booking' DB
$bookings = DB::connection('booking')
    ->table('booking')
    ->where('userID', $userId)
    ->get();

// 2. Collect animal IDs from pivot
$animalIds = DB::connection('booking')
    ->table('animal_booking')
    ->whereIn('bookingID', $bookings->pluck('id'))
    ->pluck('animalID');

// 3. Get animals from 'animals' DB
$animals = DB::connection('animals')
    ->table('animal')
    ->whereIn('id', $animalIds)
    ->get()
    ->keyBy('id');

// 4. Assemble in PHP
foreach ($bookings as $booking) { ... }
```

This is the only way to simulate a JOIN across DB boundaries.

---

## Stored Procedures (MariaDB — `booking` connection)

The `booking` connection uses MariaDB stored procedures for all write operations.
All procedure calls go through `app/Services/BookingProcedureService.php`.

### Procedures with OUT parameters

MariaDB does not return OUT parameters the same way as SQL Server's `OUTPUT`. The
convention is to use session variables as OUT params, then read them with a separate
`SELECT`:

```php
// Step 1: CALL the procedure, passing @o_var placeholders for OUT params
DB::connection('booking')->statement(
    'CALL sp_booking_create(?, ?, ?, ?, @o_booking_id, @o_status, @o_message)',
    [$userId, $date, $time, $status]
);

// Step 2: SELECT the session variables to retrieve results
$result = DB::connection('booking')->selectOne(
    'SELECT @o_booking_id AS booking_id, @o_status AS `status`, @o_message AS `message`'
);
```

The session variables (`@o_booking_id`, `@o_status`, `@o_message`) are scoped to the
MariaDB session. They persist until the connection is closed or overwritten.

### Procedures that return result sets

When a procedure uses `SELECT` to return rows (no OUT params), use `DB::select()`:

```php
$rows = DB::connection('booking')->select('CALL sp_booking_read(?)', [$bookingId]);
$booking = $rows[0] ?? null;
```

### Passing arrays to procedures

MariaDB stored procedures do not accept array parameters. Arrays (e.g. a list of
animal IDs) are serialized to a comma-separated string and parsed with `FIND_IN_SET`
or a `SUBSTRING_INDEX` cursor loop inside the procedure:

```php
$animalIdsString = implode(',', $animalIds);  // e.g. "3,7,12"
DB::connection('booking')->statement(
    'CALL sp_booking_attach_animals(?, ?, @o_attached_count, @o_status, @o_message)',
    [$bookingId, $animalIdsString]
);
```

---

## PostgreSQL Views and Materialized Views (`users` connection)

The `users` connection uses PostgreSQL-specific features for read optimization.

### Regular views

```php
// Query the view exactly like a table
$profile = DB::connection('users')
    ->table('v_user_full_profile')
    ->where('id', $userId)
    ->first();
```

Key views: `v_user_full_profile` (user + adopter_profile + roles in one row),
`v_high_risk_users` (security monitoring), `v_active_users_with_profiles` (matching).

### Materialized views

`v_user_account_stats` and `v_adopter_profile_stats` are materialized — they cache
the result of expensive aggregations. They must be refreshed explicitly:

```php
// Refresh all materialized views via the PL/pgSQL helper function
DB::connection('users')->selectOne('SELECT refresh_all_taufiq_stats()');
```

Materialized views are stale between refreshes. They are not automatically updated
on INSERT/UPDATE/DELETE. Schedule `refresh_all_taufiq_stats()` via a Laravel scheduled
command for dashboards that need near-real-time stats.

---

## Connection Resilience

`app/Services/DatabaseConnectionChecker.php` handles availability checks before
cross-DB queries with two resilience mechanisms:

### Circuit breaker

If a connection fails, a file-cache key `db_circuit_breaker_{connection}` is set for
30 seconds. Subsequent checks within that window return `false` immediately without
attempting a new TCP connection — preventing page hangs from repeated timeout waits.

### Two-tier cache

Connection status is cached in both the primary Laravel cache (database, backed by the
`users` PostgreSQL) and a file cache fallback. If `users` is unreachable, the file cache
serves the last-known status. Cache TTL: 5 minutes when all DBs are online, 15 seconds
when any DB is offline (to detect recovery quickly).

### Usage in models

```php
// Check before a cross-DB query that is non-critical (images can be omitted)
if (!app(DatabaseConnectionChecker::class)->isConnected('reporting')) {
    return collect([]);
}
return $this->images()->get();
```

For critical paths (e.g. booking creation), the controller should check all required
connections before beginning work and return a user-facing error if any are offline.
