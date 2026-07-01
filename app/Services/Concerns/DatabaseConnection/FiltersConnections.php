<?php

namespace App\Services\Concerns\DatabaseConnection;

trait FiltersConnections
{
    public function getConnected(): array
    {
        return array_filter($this->checkAll(), fn($db) => $db['connected']);
    }

    public function getDisconnected(): array
    {
        return array_filter($this->checkAll(), fn($db) => !$db['connected']);
    }

    public function getCliOutput(): string
    {
        $status = $this->checkAll(false);
        $output = [];

        $output[] = "\n╔════════════════════════════════════════════════════════════════════════════╗";
        $output[] = "║              DISTRIBUTED DATABASE CONNECTION STATUS                        ║";
        $output[] = "╠════════════════════════════════════════════════════════════════════════════╣";

        foreach ($status as $connection => $info) {
            $icon = $info['connected'] ? '✓' : '✗';
            $color = $info['connected'] ? "\033[32m" : "\033[31m";
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

    public static function getAllConnections(): array
    {
        return array_keys(self::CONNECTIONS);
    }

    public static function getConnectionInfo(string $connection): ?array
    {
        return self::CONNECTIONS[$connection] ?? null;
    }
}
