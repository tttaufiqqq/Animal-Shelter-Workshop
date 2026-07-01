<?php

namespace App\Services\Concerns\DatabaseConnection;

use Illuminate\Support\Facades\Cache;

trait ManagesConnectionCache
{
    public function clearCache(?string $connection = null): void
    {
        if ($connection) {
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
}
