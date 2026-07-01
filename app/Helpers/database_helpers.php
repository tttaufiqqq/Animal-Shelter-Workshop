<?php

if (!function_exists('isDatabaseOnline')) {
    /**
     * Check if a database connection is online
     *
     * @param string $connection Connection name (e.g., 'reporting', 'booking', etc.)
     * @return bool
     */
    function isDatabaseOnline($connection)
    {
        try {
            return app(\App\Services\DatabaseConnectionChecker::class)->isConnected($connection);
        } catch (\Exception $e) {
            \Log::error("Failed to check database connection status: " . $e->getMessage());
            return false;
        }
    }
}
