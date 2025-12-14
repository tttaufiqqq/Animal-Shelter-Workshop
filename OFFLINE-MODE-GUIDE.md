# Offline Mode & Database Failure Handling Guide

This guide explains how the application handles situations when remote databases are unavailable due to internet connectivity issues.

## ✅ Implementation Status: COMPLETED

All controllers and components have been updated with offline mode handling. The application now gracefully handles database connectivity issues.

## Problem

When using a hotspot with limited or no internet connection, the application would hang or crash when trying to connect to remote distributed databases (eilya, atiqah, shafiqah, danish).

## Solutions Implemented

### 1. Reduced Connection Timeouts

**File:** `config/database.php`

All remote database connections now have **3-second timeouts**:

- **MySQL** (eilya, atiqah, shafiqah): `PDO::ATTR_TIMEOUT => 3`
- **SQL Server** (danish): `ConnectTimeout => 3` and `LoginTimeout => 3`
- **PostgreSQL** (taufiq): `PDO::ATTR_TIMEOUT => 3` and `connect_timeout => 3`

**Benefit:** Instead of waiting 30+ seconds for connection failures, the app fails fast (3 seconds) and can handle the error gracefully.

### 2. Database Failure Middleware

**File:** `app/Http/Middleware/HandleDatabaseFailures.php`

This middleware catches database connection exceptions and:
- Logs the error
- Sets a session flag (`db_offline`)
- For web requests: Redirects back with an error message
- For AJAX requests: Returns JSON error response

**Registered in:** `bootstrap/app.php` as part of the web middleware group.

### 3. Visual Warning Banner

**Files:**
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/guest.blade.php`

A yellow warning banner appears at the top of pages when databases are offline:

```
⚠️ Limited Connectivity: Some databases are currently unavailable.
   You may experience limited functionality or missing data.
```

### 4. Database Error Handler Trait

**File:** `app/DatabaseErrorHandler.php`

Controllers can use this trait for safe database operations:

```php
use App\DatabaseErrorHandler;

class MyController extends Controller
{
    use DatabaseErrorHandler;

    public function index()
    {
        // Safe query with fallback
        $animals = $this->safeQuery(
            fn() => Animal::all(),
            collect([]) // Fallback: empty collection
        );

        // Check if specific database is available
        if ($this->isDatabaseAvailable('shafiqah')) {
            $animals = Animal::all();
        } else {
            $animals = collect([]); // Show empty list
        }

        // Get all available databases
        $availableDbs = $this->getAvailableDatabases();
        // Returns: ['taufiq', 'shafiqah'] (if only these 2 are reachable)
    }
}
```

## Controller Implementation Examples

### Example 1: Using safeQuery()

```php
use App\DatabaseErrorHandler;

class AnimalManagementController extends Controller
{
    use DatabaseErrorHandler;

    public function index()
    {
        // Safe query - returns empty collection if database fails
        $animals = $this->safeQuery(
            fn() => Animal::with(['images', 'slot'])->paginate(10),
            collect([])
        );

        return view('animal-management.index', compact('animals'));
    }
}
```

### Example 2: Check Database Availability First

```php
use App\DatabaseErrorHandler;

class DashboardController extends Controller
{
    use DatabaseErrorHandler;

    public function index()
    {
        $stats = [
            'animals' => 0,
            'bookings' => 0,
            'rescues' => 0,
        ];

        // Only query if databases are available
        if ($this->isDatabaseAvailable('shafiqah')) {
            $stats['animals'] = Animal::count();
        }

        if ($this->isDatabaseAvailable('danish')) {
            $stats['bookings'] = Booking::whereIn('status', ['Pending', 'Confirmed'])->count();
        }

        if ($this->isDatabaseAvailable('eilya')) {
            $stats['rescues'] = Rescue::where('status', 'In Progress')->count();
        }

        return view('dashboard', compact('stats'));
    }
}
```

### Example 3: Traditional Try-Catch (Alternative)

```php
public function show($id)
{
    try {
        $animal = Animal::with(['images', 'bookings', 'slot'])->findOrFail($id);
        return view('animal-management.show', compact('animal'));
    } catch (\PDOException | \Illuminate\Database\QueryException $e) {
        \Log::warning('Database query failed: ' . $e->getMessage());
        session()->flash('db_offline', true);

        return redirect()->back()->with('error', 'Unable to load animal details. Database connection failed.');
    }
}
```

## View Implementation

In Blade views, you can check the offline status:

```blade
@if(session('db_offline'))
    <div class="alert alert-warning">
        <p>Some features may be unavailable due to database connectivity issues.</p>
    </div>
@endif

@if($animals->isEmpty() && session('db_offline'))
    <p class="text-gray-500">No data available. Please check your internet connection.</p>
@elseif($animals->isEmpty())
    <p class="text-gray-500">No animals found.</p>
@else
    @foreach($animals as $animal)
        <!-- Display animal -->
    @endforeach
@endif
```

## Best Practices

### ✅ DO:

1. **Use the DatabaseErrorHandler trait** for consistent error handling
2. **Provide fallback values** (empty collections, default values)
3. **Show meaningful messages** to users when databases are offline
4. **Log errors** for debugging
5. **Check database availability** before critical operations

### ❌ DON'T:

1. **Don't assume databases are always available** - always handle failures
2. **Don't let exceptions crash the page** - catch and handle them
3. **Don't hide errors from users** - show the warning banner
4. **Don't retry indefinitely** - use the 3-second timeout

## Testing Offline Mode

To test the offline mode behavior:

1. **Disconnect from hotspot** or disable internet
2. **Access any page** that queries remote databases
3. **Expected behavior:**
   - Page loads within 3 seconds (not hanging)
   - Yellow warning banner appears
   - Empty/default data is shown
   - No PHP errors or crashes

## Configuration

### Adjusting Timeout Duration

Edit `config/database.php` to change timeout values:

```php
// MySQL connections
'options' => [
    PDO::ATTR_TIMEOUT => 5, // Change from 3 to 5 seconds
],

// SQL Server connection
'options' => [
    'ConnectTimeout' => 5,
    'LoginTimeout' => 5,
],

// PostgreSQL connection
'options' => [
    PDO::ATTR_TIMEOUT => 5,
],
'connect_timeout' => 5,
```

**Recommendation:** Keep timeouts between 3-5 seconds for best user experience.

## Summary

The application now gracefully handles database connectivity issues by:

1. **Failing fast** (3-second timeouts)
2. **Catching exceptions** (middleware + trait)
3. **Showing warnings** (visual banner)
4. **Providing fallbacks** (empty data instead of crashes)
5. **Logging errors** (for debugging)

This ensures blade files display properly even when remote databases are unreachable.
