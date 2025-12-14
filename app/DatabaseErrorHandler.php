<?php

namespace App;

use Illuminate\Support\Facades\Log;
use PDOException;
use Illuminate\Database\QueryException;

trait DatabaseErrorHandler
{
    /**
     * Execute a database query with error handling
     *
     * @param callable $callback The database operation to execute
     * @param mixed $fallback The fallback value if database fails
     * @return mixed
     */
    protected function safeQuery(callable $callback, $fallback = null)
    {
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
     *
     * @param string $connection Connection name (eilya, atiqah, shafiqah, danish, taufiq)
     * @return bool
     */
    protected function isDatabaseAvailable(string $connection): bool
    {
        try {
            \DB::connection($connection)->getPdo();
            return true;
        } catch (\Exception $e) {
            Log::debug("Database connection '$connection' is not available: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get available database connections
     *
     * @return array Array of available connection names
     */
    protected function getAvailableDatabases(): array
    {
        $connections = ['taufiq', 'eilya', 'shafiqah', 'atiqah', 'danish'];
        $available = [];

        foreach ($connections as $connection) {
            if ($this->isDatabaseAvailable($connection)) {
                $available[] = $connection;
            }
        }

        return $available;
    }
}
