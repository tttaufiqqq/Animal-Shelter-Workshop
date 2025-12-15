<?php

namespace App\Traits;

use App\Services\DatabaseConnectionChecker;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use PDOException;

trait HandlesOfflineDatabase
{
    /**
     * Execute a query safely with offline database handling
     *
     * @param callable $callback The query to execute
     * @param mixed $default Default value to return if database is offline
     * @param string|null $connection Optional connection name to check first
     * @return mixed
     */
    protected function safeQuery(callable $callback, $default = null, ?string $connection = null)
    {
        // If connection is specified, check if it's online first
        if ($connection) {
            $checker = app(DatabaseConnectionChecker::class);
            if (!$checker->isConnected($connection)) {
                return $this->getDefaultValue($default);
            }
        }

        try {
            return $callback();
        } catch (PDOException | QueryException $e) {
            \Log::warning("Database query failed: {$e->getMessage()}", [
                'connection' => $connection,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $this->getDefaultValue($default);
        }
    }

    /**
     * Get default value for offline database
     *
     * @param mixed $default
     * @return mixed
     */
    private function getDefaultValue($default)
    {
        if (is_null($default)) {
            return collect(); // Return empty collection by default
        }

        if (is_callable($default)) {
            return $default();
        }

        return $default;
    }

    /**
     * Safe count query
     *
     * @param callable $callback
     * @param string|null $connection
     * @return int
     */
    protected function safeCount(callable $callback, ?string $connection = null): int
    {
        return $this->safeQuery($callback, 0, $connection);
    }

    /**
     * Safe find query
     *
     * @param callable $callback
     * @param string|null $connection
     * @return mixed
     */
    protected function safeFind(callable $callback, ?string $connection = null)
    {
        return $this->safeQuery($callback, null, $connection);
    }

    /**
     * Safe collection query
     *
     * @param callable $callback
     * @param string|null $connection
     * @return Collection
     */
    protected function safeCollection(callable $callback, ?string $connection = null): Collection
    {
        return $this->safeQuery($callback, collect(), $connection);
    }

    /**
     * Execute multiple queries safely and merge results
     *
     * @param array $queries Array of ['callback' => callable, 'connection' => string]
     * @return Collection
     */
    protected function safeMergeQueries(array $queries): Collection
    {
        $results = collect();

        foreach ($queries as $query) {
            $callback = $query['callback'];
            $connection = $query['connection'] ?? null;

            $result = $this->safeCollection($callback, $connection);
            $results = $results->merge($result);
        }

        return $results;
    }

    /**
     * Check if a specific database connection is available
     *
     * @param string $connection
     * @return bool
     */
    protected function isDatabaseOnline(string $connection): bool
    {
        $checker = app(DatabaseConnectionChecker::class);
        return $checker->isConnected($connection);
    }

    /**
     * Get offline database message for views
     *
     * @param string $module Module name (e.g., 'Animal Management')
     * @return array
     */
    protected function getOfflineMessage(string $module): array
    {
        return [
            'offline' => true,
            'module' => $module,
            'message' => "The {$module} database is currently offline. Please check your connection to the remote server.",
        ];
    }
}
