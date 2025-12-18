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
        'taufiq' => [
            'name' => 'Taufiq (PostgreSQL)',
            'module' => 'User Management',
            'port' => 5434,
        ],
        'eilya' => [
            'name' => 'Eilya (MySQL)',
            'module' => 'Stray Reporting',
            'port' => 3307,
        ],
        'shafiqah' => [
            'name' => 'Shafiqah (MySQL)',
            'module' => 'Animal Management',
            'port' => 3309,
        ],
        'atiqah' => [
            'name' => 'Atiqah (MySQL)',
            'module' => 'Shelter Management',
            'port' => 3308,
        ],
        'danish' => [
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
        // Smart cache strategy: try database cache first, fallback to file cache
        // This avoids circular dependency during initial connection checks
        $cacheKey = 'db_connection_status';

        if ($useCache) {
            // Try database cache first (faster when online)
            try {
                if (Cache::has($cacheKey)) {
                    return Cache::get($cacheKey);
                }
            } catch (\Exception $e) {
                // Database cache failed, try file cache
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

        $results = [];

        foreach (self::CONNECTIONS as $connection => $info) {
            $results[$connection] = array_merge($info, [
                'connected' => $this->checkConnection($connection),
                'connection' => $connection,
            ]);
        }

        // Smart cache duration:
        // - All databases online: 30 minutes (stable state)
        // - Any database offline: 60 seconds (check frequently for recovery)
        $allOnline = collect($results)->every(fn($db) => $db['connected']);
        $cacheDuration = $allOnline ? 1800 : 60;

        // Store in both caches for redundancy
        // Database cache (when online) - primary
        try {
            Cache::put($cacheKey, $results, $cacheDuration);
        } catch (\Exception $e) {
            // Database cache failed, not critical
        }

        // File cache - fallback/backup
        try {
            Cache::store('file')->put($cacheKey, $results, $cacheDuration);
        } catch (\Exception $e) {
            // File cache failed, not critical
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
        try {
            // Set timeout for connection check
            // Reduced to 1 second for faster failure detection
            $startTime = microtime(true);
            $maxTime = 1.0; // 1 second maximum (fast fail for offline databases)

            // Set PDO timeout attributes before connecting
            config(["database.connections.{$connection}.options" => [
                \PDO::ATTR_TIMEOUT => 1,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]]);

            // Attempt to get PDO connection
            $pdo = DB::connection($connection)->getPdo();

            // Check if we exceeded timeout
            if ((microtime(true) - $startTime) > $maxTime) {
                \Log::warning("Connection check for {$connection} took too long");
                return false;
            }

            return true;
        } catch (\Exception $e) {
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
        // Smart cache strategy: try database cache first, fallback to file cache
        $allDbKey = 'db_connection_status';
        $singleDbKey = "db_connection_status_{$connection}";

        // Try to get from ALL databases cache first (most complete)
        try {
            if (Cache::has($allDbKey)) {
                $status = Cache::get($allDbKey);
                return $status[$connection]['connected'] ?? false;
            }
        } catch (\Exception $e) {
            // Try file cache
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

        // Try individual connection cache
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

        // Cache individual connection status for 60 seconds in both stores
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
