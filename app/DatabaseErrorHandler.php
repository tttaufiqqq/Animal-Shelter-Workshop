<?php

namespace App;

use App\Services\DatabaseConnectionChecker;
use Illuminate\Support\Facades\Log;
use PDOException;
use Illuminate\Database\QueryException;

trait DatabaseErrorHandler
{
    /**
     * Per-request memo of isDatabaseAvailable() results, keyed by connection.
     * Controllers using this trait are resolved fresh per request, so this
     * never survives past the request that populated it -- unlike a
     * time-based cache, there's no window where a DB going offline between
     * requests could read stale. Purely collapses repeat live probes of the
     * *same* connection within one request (e.g. RescueMapController::index()
     * checks 'reporting' three times back to back, ~200ms each).
     *
     * @var array<string, bool>
     */
    protected array $databaseAvailabilityMemo = [];

    /**
     * Execute a database query with error handling.
     * Pre-checks if database is online before attempting the query.
     *
     * @param callable $callback The database operation to execute
     * @param mixed $fallback The fallback value if database fails
     * @param string|null $connection Optional database connection name to check first
     * @return mixed
     */
    protected function safeQuery(callable $callback, $fallback = null, ?string $connection = null)
    {
        if ($connection) {
            if (!$this->isDatabaseAvailable($connection)) {
                Log::debug("Skipping query - database '$connection' is offline (pre-checked)");
                session()->flash('db_offline', true);
                return $fallback;
            }
        }

        try {
            return $callback();
        } catch (PDOException | QueryException $e) {
            Log::warning('Database query failed: ' . $e->getMessage());
            session()->flash('db_offline', true);
            return $fallback;
        }
    }

    /**
     * Check if a specific database connection is available. Live probe on
     * first call per connection per request, memoized for the rest of the
     * request (see $databaseAvailabilityMemo).
     *
     * @param string $connection Connection name (eilya, atiqah, shafiqah, danish, taufiq)
     * @return bool
     */
    protected function isDatabaseAvailable(string $connection): bool
    {
        if (array_key_exists($connection, $this->databaseAvailabilityMemo)) {
            return $this->databaseAvailabilityMemo[$connection];
        }

        $checker = app(DatabaseConnectionChecker::class);
        return $this->databaseAvailabilityMemo[$connection] = $checker->isConnected($connection);
    }

    /**
     * Get available database connections (live probe).
     *
     * @return array Array of available connection names
     */
    protected function getAvailableDatabases(): array
    {
        $checker = app(DatabaseConnectionChecker::class);
        $connected = $checker->getConnected();
        return array_keys($connected);
    }
}
