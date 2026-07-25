<?php

namespace App\Services\Concerns\DatabaseConnection;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait ChecksConnections
{
    public function checkAll(): array
    {
        $results = [];
        foreach (self::CONNECTIONS as $connection => $info) {
            $results[$connection] = array_merge($info, [
                'connected' => $this->checkConnection($connection),
                'connection' => $connection,
            ]);
        }

        return $results;
    }

    public function checkConnection(string $connection): bool
    {
        $dbConfig = config("database.connections.$connection");
        if ($dbConfig && isset($dbConfig['host'], $dbConfig['port'])) {
            $socket = @fsockopen($dbConfig['host'], (int) $dbConfig['port'], $errno, $errstr, 2.0);
            if ($socket === false) {
                Log::debug("TCP probe failed for {$connection} ({$dbConfig['host']}:{$dbConfig['port']}): {$errstr}");
                return false;
            }
            fclose($socket);
        }

        try {
            DB::connection($connection)->getPdo();
            return true;
        } catch (\Exception $e) {
            Log::debug("Connection check failed for {$connection}: " . $e->getMessage());
            return false;
        }
    }

    public function isConnected(string $connection): bool
    {
        return $this->checkConnection($connection);
    }
}
