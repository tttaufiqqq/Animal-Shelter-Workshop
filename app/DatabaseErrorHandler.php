<?php

namespace App;

use App\Services\DatabaseConnectionChecker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PDOException;
use Illuminate\Database\QueryException;

trait DatabaseErrorHandler
{
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
     * Check if a specific database connection is available.
     *
     * InjectDatabaseStatus (app/Http/Middleware) already probes all 5
     * connections once per request and caches the result for 15s
     * (web_db_connection_status) -- every controller on a normal web
     * request was previously re-probing the same connection here via a
     * second live TCP+PDO check (confirmed ~200ms each), compounding with
     * however many safeQuery()/isDatabaseAvailable() calls a page made.
     * Reusing that cache here avoids the duplicate probe. Falls back to a
     * live check when nothing's cached yet (console commands, tests,
     * anything running outside that middleware).
     *
     * @param string $connection Connection name (eilya, atiqah, shafiqah, danish, taufiq)
     * @return bool
     */
    protected function isDatabaseAvailable(string $connection): bool
    {
        $cached = Cache::get('web_db_connection_status');
        if (isset($cached[$connection]['connected'])) {
            return $cached[$connection]['connected'];
        }

        $checker = app(DatabaseConnectionChecker::class);
        return $checker->isConnected($connection);
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
