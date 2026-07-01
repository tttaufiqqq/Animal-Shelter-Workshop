<?php

namespace App\Console\Commands\Concerns;

trait MaintenanceFormatters
{
    private function hasAnyOption(): bool
    {
        return $this->option('unlock-accounts')
            || $this->option('cleanup-sessions')
            || $this->option('cleanup-cache')
            || $this->option('cleanup-audit')
            || $this->option('optimize');
    }

    private function formatTaskName(string $taskName): string
    {
        return ucwords(str_replace('_', ' ', $taskName));
    }

    private function formatInterval(string $interval): string
    {
        // PostgreSQL intervals are in format like "00:00:00.123456"
        return $interval;
    }
}
