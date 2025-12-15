<?php

namespace App;

use App\Services\DatabaseConnectionChecker;
use Illuminate\Support\Facades\Log;
use PDOException;
use Illuminate\Database\QueryException;

trait DatabaseErrorHandler
{
    /**
     * Execute a database query with error handling
     * Pre-checks if database is online before attempting query
     *
     * @param callable $callback The database operation to execute
     * @param mixed $fallback The fallback value if database fails
     * @param string|null $connection Optional database connection name to check first
     * @return mixed
     */
    protected function safeQuery(callable $callback, $fallback = null, ?string $connection = null)
    {
        // Pre-check if database is online (uses cached results, very fast)
        if ($connection) {
            $checker = app(DatabaseConnectionChecker::class);
            if (!$checker->isConnected($connection)) {
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
     * Check if a specific database connection is available
     * Uses cached DatabaseConnectionChecker for instant results
     *
     * @param string $connection Connection name (eilya, atiqah, shafiqah, danish, taufiq)
     * @return bool
     */
    protected function isDatabaseAvailable(string $connection): bool
    {
        $checker = app(DatabaseConnectionChecker::class);
        return $checker->isConnected($connection);
    }

    /**
     * Get available database connections
     * Uses cached DatabaseConnectionChecker for instant results
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
