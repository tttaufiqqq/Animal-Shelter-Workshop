<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DatabaseConnectionChecker
{
    /**
     * All database connections in the distributed architecture
     */
    private const CONNECTIONS = [
        'users' => [
            'name' => 'Taufiq (PostgreSQL)',
            'module' => 'User Management',
            'port' => 5434,
        ],
        'reporting' => [
            'name' => 'Eilya (MySQL)',
            'module' => 'Stray Reporting',
            'port' => 3307,
        ],
        'animals' => [
            'name' => 'Shafiqah (MySQL)',
            'module' => 'Animal Management',
            'port' => 3309,
        ],
        'shelter' => [
            'name' => 'Atiqah (MySQL)',
            'module' => 'Shelter Management',
            'port' => 3308,
        ],
        'booking' => [
            'name' => 'Danish (SQL Server)',
            'module' => 'Booking & Adoption',
            'port' => 1434,
        ],
    ];

    /**
     * Check all database connections and return their status
     *
     * @param bool $useCache Whether to use cached results (default: true)
     * @return array
     */
    public function checkAll(bool $useCache = true): array
    {
        // Cache strategy: Use taufiq database cache as primary (taufiq must be online for auth)
        // Fallback to file cache if taufiq is unreachable
        $cacheKey = 'db_connection_status';
        $lockKey = 'db_connection_check_in_progress';

        if ($useCache) {
            // Try taufiq database cache FIRST (taufiq must be online for authentication)
            try {
                if (Cache::has($cacheKey)) {
                    return Cache::get($cacheKey);
                }
            } catch (\Exception $e) {
                // Database cache failed (taufiq might be offline), try file cache as fallback
                try {
                    $fileCache = Cache::store('file');
                    if ($fileCache->has($cacheKey)) {
                        return $fileCache->get($cacheKey);
                    }
                } catch (\Exception $e2) {
                    // Both failed, continue to fresh check
                }
            }
        }

        // Check if another request is already checking connections (mutex/lock)
        // This prevents multiple simultaneous connection checks that cause timeouts
        // Use file cache for lock (more reliable than database cache)
        try {
            $fileCache = Cache::store('file');
            if ($fileCache->has($lockKey)) {
                // Another request is checking, wait briefly and try to get cached result
                usleep(100000); // Wait 100ms

                // Try database cache first
                try {
                    if (Cache::has($cacheKey)) {
                        return Cache::get($cacheKey);
                    }
                } catch (\Exception $e) {
                    // Try file cache
                    if ($fileCache->has($cacheKey)) {
                        return $fileCache->get($cacheKey);
                    }
                }
                // If still no cache, fall through to check ourselves
            }

            // Set lock to prevent other requests from checking simultaneously
            $fileCache->put($lockKey, true, 10); // Lock for 10 seconds max
        } catch (\Exception $e) {
            // Lock failed, proceed anyway
        }

        $results = [];

        foreach (self::CONNECTIONS as $connection => $info) {
            $results[$connection] = array_merge($info, [
                'connected' => $this->checkConnection($connection),
                'connection' => $connection,
            ]);
        }

        // Smart cache duration:
        // - All databases online: 5 minutes (stable state, check periodically)
        // - Any database offline: 15 seconds (check frequently for real-time recovery detection)
        $allOnline = collect($results)->every(fn($db) => $db['connected']);
        $cacheDuration = $allOnline ? 300 : 15;

        // Store in taufiq database cache FIRST (primary - fast access, taufiq always online for auth)
        try {
            Cache::put($cacheKey, $results, $cacheDuration);
        } catch (\Exception $e) {
            // Database cache failed, not critical
        }

        // Store in file cache as backup (fallback when taufiq temporarily unreachable)
        try {
            Cache::store('file')->put($cacheKey, $results, $cacheDuration);
        } catch (\Exception $e) {
            // File cache failed, not critical
        }

        // Release lock
        try {
            Cache::store('file')->forget($lockKey);
        } catch (\Exception $e) {
            // Not critical
        }

        return $results;
    }

    /**
     * Check a single database connection with timeout
     *
     * @param string $connection
     * @return bool
     */
    public function checkConnection(string $connection): bool
    {
        // Check circuit breaker - if connection recently failed, don't try again immediately
        $circuitKey = "db_circuit_breaker_{$connection}";
        $circuitBreakerActive = false;

        try {
            if (Cache::store('file')->has($circuitKey)) {
                // Circuit breaker is active, don't attempt connection
                return false;
            }
        } catch (\Exception $e) {
            // File cache failed, proceed with check
        }

        // TCP probe with 2-second timeout — fast fail before attempting PDO.
        // PDO::MYSQL_ATTR_CONNECT_TIMEOUT is unavailable on this PHP build, so we
        // use fsockopen() which always honours the timeout regardless of PHP/driver version.
        $dbConfig = config("database.connections.$connection");
        if ($dbConfig && isset($dbConfig['host'], $dbConfig['port'])) {
            $socket = @fsockopen($dbConfig['host'], (int) $dbConfig['port'], $errno, $errstr, 2.0);
            if ($socket === false) {
                try {
                    Cache::store('file')->put($circuitKey, true, 30);
                } catch (\Exception $e) {
                    // Not critical
                }
                \Log::debug("TCP probe failed for {$connection} ({$dbConfig['host']}:{$dbConfig['port']}): {$errstr}");
                return false;
            }
            fclose($socket);
        }

        try {
            $pdo = DB::connection($connection)->getPdo();

            // Connection successful - clear any existing circuit breaker
            try {
                Cache::store('file')->forget($circuitKey);
            } catch (\Exception $e) {
                // Not critical
            }

            return true;

        } catch (\Exception $e) {
            // Connection failed - activate circuit breaker for 30 seconds
            // This prevents repeated connection attempts that cause page hangs
            try {
                Cache::store('file')->put($circuitKey, true, 30);
            } catch (\Exception $e2) {
                // Not critical
            }

            \Log::debug("Connection check failed for {$connection}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get connected databases
     *
     * @return array
     */
    public function getConnected(): array
    {
        return array_filter($this->checkAll(), fn($db) => $db['connected']);
    }

    /**
     * Get disconnected databases
     *
     * @return array
     */
    public function getDisconnected(): array
    {
        return array_filter($this->checkAll(), fn($db) => !$db['connected']);
    }

    /**
     * Check if a specific connection is available
     * Optimized to only check the requested connection, not all connections
     *
     * @param string $connection
     * @return bool
     */
    public function isConnected(string $connection): bool
    {
        // Use taufiq database cache as primary (taufiq must be online for authentication)
        $allDbKey = 'db_connection_status';
        $singleDbKey = "db_connection_status_{$connection}";

        // Try to get from ALL databases cache first (in taufiq database)
        try {
            if (Cache::has($allDbKey)) {
                $status = Cache::get($allDbKey);
                return $status[$connection]['connected'] ?? false;
            }
        } catch (\Exception $e) {
            // Database cache failed, try file cache as fallback
            try {
                $fileCache = Cache::store('file');
                if ($fileCache->has($allDbKey)) {
                    $status = $fileCache->get($allDbKey);
                    return $status[$connection]['connected'] ?? false;
                }
            } catch (\Exception $e2) {
                // Continue to individual check
            }
        }

        // Try individual connection cache from taufiq database
        try {
            if (Cache::has($singleDbKey)) {
                return Cache::get($singleDbKey);
            }
        } catch (\Exception $e) {
            // Try file cache
            try {
                $fileCache = Cache::store('file');
                if ($fileCache->has($singleDbKey)) {
                    return $fileCache->get($singleDbKey);
                }
            } catch (\Exception $e2) {
                // Continue to fresh check
            }
        }

        // No cache found, check this connection
        $isConnected = $this->checkConnection($connection);

        // Cache individual connection status for 60 seconds - taufiq database first
        try {
            Cache::put($singleDbKey, $isConnected, 60);
        } catch (\Exception $e) {
            // Not critical
        }

        try {
            Cache::store('file')->put($singleDbKey, $isConnected, 60);
        } catch (\Exception $e) {
            // Not critical
        }

        return $isConnected;
    }

    /**
     * Display connection status in CLI format
     *
     * @return string
     */
    public function getCliOutput(): string
    {
        $status = $this->checkAll(false); // Force fresh check
        $output = [];

        $output[] = "\n╔════════════════════════════════════════════════════════════════════════════╗";
        $output[] = "║              DISTRIBUTED DATABASE CONNECTION STATUS                        ║";
        $output[] = "╠════════════════════════════════════════════════════════════════════════════╣";

        foreach ($status as $connection => $info) {
            $icon = $info['connected'] ? '✓' : '✗';
            $statusText = $info['connected'] ? 'CONNECTED' : 'OFFLINE';
            $color = $info['connected'] ? "\033[32m" : "\033[31m"; // Green : Red
            $reset = "\033[0m";

            $line = sprintf(
                "║ %s%-10s %s%-20s %-25s Port: %-6s%s║",
                $color,
                $icon . ' ' . strtoupper($connection),
                $reset,
                $info['name'],
                $info['module'],
                $info['port'],
                ''
            );

            $output[] = $line;
        }

        $output[] = "╚════════════════════════════════════════════════════════════════════════════╝";

        $connected = count($this->getConnected());
        $total = count(self::CONNECTIONS);
        $output[] = sprintf("\n📊 Connection Summary: %d/%d databases online\n", $connected, $total);

        if ($connected < $total) {
            $output[] = "\033[33m⚠️  WARNING: Some databases are offline. The application will run in limited mode.\033[0m";
            $output[] = "\033[33m   Pages may display without data for offline modules.\033[0m\n";
        } else {
            $output[] = "\033[32m✓ All databases connected successfully!\033[0m\n";
        }

        return implode("\n", $output);
    }

    /**
     * Clear cached connection status
     *
     * @param string|null $connection Optional specific connection to clear, or null for all
     * @return void
     */
    public function clearCache(?string $connection = null): void
    {
        if ($connection) {
            // Clear specific connection cache from both stores
            $key = "db_connection_status_{$connection}";

            try {
                Cache::forget($key);
            } catch (\Exception $e) {
                // Not critical
            }

            try {
                Cache::store('file')->forget($key);
            } catch (\Exception $e) {
                // Not critical
            }

            \Log::info("Cleared cache for database connection: {$connection}");
        } else {
            // Clear all connection caches from both stores
            try {
                Cache::forget('db_connection_status');
            } catch (\Exception $e) {
                // Not critical
            }

            try {
                Cache::store('file')->forget('db_connection_status');
            } catch (\Exception $e) {
                // Not critical
            }

            // Also clear individual connection caches
            foreach (self::CONNECTIONS as $conn => $info) {
                $key = "db_connection_status_{$conn}";

                try {
                    Cache::forget($key);
                } catch (\Exception $e) {
                    // Not critical
                }

                try {
                    Cache::store('file')->forget($key);
                } catch (\Exception $e) {
                    // Not critical
                }
            }

            \Log::info("Cleared all database connection caches");
        }
    }

    /**
     * Get all connection names
     *
     * @return array
     */
    public static function getAllConnections(): array
    {
        return array_keys(self::CONNECTIONS);
    }

    /**
     * Get connection info
     *
     * @param string $connection
     * @return array|null
     */
    public static function getConnectionInfo(string $connection): ?array
    {
        return self::CONNECTIONS[$connection] ?? null;
    }
}
