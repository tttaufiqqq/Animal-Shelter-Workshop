<?php

namespace App\Services\Concerns\DatabaseConnection;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait ChecksConnections
{
    public function checkAll(bool $useCache = true): array
    {
        $cacheKey = 'db_connection_status';
        $lockKey = 'db_connection_check_in_progress';

        if ($useCache) {
            try {
                if (Cache::has($cacheKey)) {
                    return Cache::get($cacheKey);
                }
            } catch (\Exception $e) {
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

        try {
            $fileCache = Cache::store('file');
            if ($fileCache->has($lockKey)) {
                usleep(100000);
                try {
                    if (Cache::has($cacheKey)) {
                        return Cache::get($cacheKey);
                    }
                } catch (\Exception $e) {
                    if ($fileCache->has($cacheKey)) {
                        return $fileCache->get($cacheKey);
                    }
                }
            }
            $fileCache->put($lockKey, true, 10);
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

        $allOnline = collect($results)->every(fn($db) => $db['connected']);
        $cacheDuration = $allOnline ? 300 : 15;

        try {
            Cache::put($cacheKey, $results, $cacheDuration);
        } catch (\Exception $e) {
            // Not critical
        }

        try {
            Cache::store('file')->put($cacheKey, $results, $cacheDuration);
        } catch (\Exception $e) {
            // Not critical
        }

        try {
            Cache::store('file')->forget($lockKey);
        } catch (\Exception $e) {
            // Not critical
        }

        return $results;
    }

    public function checkConnection(string $connection): bool
    {
        $circuitKey = "db_circuit_breaker_{$connection}";

        try {
            if (Cache::store('file')->has($circuitKey)) {
                return false;
            }
        } catch (\Exception $e) {
            // File cache failed, proceed with check
        }

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
            DB::connection($connection)->getPdo();

            try {
                Cache::store('file')->forget($circuitKey);
            } catch (\Exception $e) {
                // Not critical
            }

            return true;
        } catch (\Exception $e) {
            try {
                Cache::store('file')->put($circuitKey, true, 30);
            } catch (\Exception $e2) {
                // Not critical
            }

            \Log::debug("Connection check failed for {$connection}: " . $e->getMessage());
            return false;
        }
    }

    public function isConnected(string $connection): bool
    {
        $allDbKey = 'db_connection_status';
        $singleDbKey = "db_connection_status_{$connection}";

        try {
            if (Cache::has($allDbKey)) {
                $status = Cache::get($allDbKey);
                return $status[$connection]['connected'] ?? false;
            }
        } catch (\Exception $e) {
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

        try {
            if (Cache::has($singleDbKey)) {
                return Cache::get($singleDbKey);
            }
        } catch (\Exception $e) {
            try {
                $fileCache = Cache::store('file');
                if ($fileCache->has($singleDbKey)) {
                    return $fileCache->get($singleDbKey);
                }
            } catch (\Exception $e2) {
                // Continue to fresh check
            }
        }

        $isConnected = $this->checkConnection($connection);

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
}
