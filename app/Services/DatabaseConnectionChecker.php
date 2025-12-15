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
        // Use cached results if available and cache is enabled
        if ($useCache && Cache::has('db_connection_status')) {
            return Cache::get('db_connection_status');
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

        Cache::put('db_connection_status', $results, $cacheDuration);

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
            // Set a very short timeout for connection check (500ms)
            $startTime = microtime(true);
            $maxTime = 0.5; // 500ms maximum

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
     *
     * @param string $connection
     * @return bool
     */
    public function isConnected(string $connection): bool
    {
        $status = $this->checkAll();
        return $status[$connection]['connected'] ?? false;
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
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget('db_connection_status');
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
