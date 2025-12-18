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
     * Auto-detects and clears stale cache when connection recovers
     *
     * @param callable $callback The database operation to execute
     * @param mixed $fallback The fallback value if database fails
     * @param string|null $connection Optional database connection name to check first
     * @return mixed
     */
    protected function safeQuery(callable $callback, $fallback = null, ?string $connection = null)
    {
        $checker = app(DatabaseConnectionChecker::class);
        $wasOffline = false;

        // Pre-check if database is online (uses cached results, very fast)
        if ($connection) {
            if (!$checker->isConnected($connection)) {
                Log::debug("Skipping query - database '$connection' is offline (pre-checked)");
                session()->flash('db_offline', true);
                return $fallback;
            }

            // Mark if cache says it was previously offline
            $wasOffline = $this->wasDatabaseOfflineInCache($connection);
        }

        try {
            $result = $callback();

            // AUTO-DETECT STALE CACHE:
            // If query succeeded but cache said it was offline, clear stale cache
            if ($wasOffline && $connection) {
                Log::info("Database '$connection' has recovered! Clearing stale cache...");
                $checker->clearCache();

                // Also clear session cache
                session()->forget([
                    'db_connection_status',
                    'db_connected',
                    'db_disconnected',
                    'db_connection_status_checked',
                    'db_connection_status_expiry',
                ]);

                session()->flash('db_recovered', "Database '{$connection}' is back online!");
            }

            return $result;
        } catch (PDOException | QueryException $e) {
            Log::warning('Database query failed: ' . $e->getMessage());
            session()->flash('db_offline', true);
            return $fallback;
        }
    }

    /**
     * Check if database was marked as offline in cache
     *
     * @param string $connection
     * @return bool
     */
    private function wasDatabaseOfflineInCache(string $connection): bool
    {
        // Check both session and Laravel cache
        $sessionStatus = session('db_connection_status', []);
        if (isset($sessionStatus[$connection]) && !$sessionStatus[$connection]['connected']) {
            return true;
        }

        $cacheStatus = cache()->get('db_connection_status', []);
        if (isset($cacheStatus[$connection]) && !$cacheStatus[$connection]['connected']) {
            return true;
        }

        return false;
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
