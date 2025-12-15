<?php

namespace App\Console\Commands;

use App\Services\DatabaseConnectionChecker;
use Illuminate\Console\Command;

class ClearDatabaseStatusCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clear-status-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the cached database connection status (forces fresh connectivity check)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $checker = app(DatabaseConnectionChecker::class);

        $this->info('Clearing database connection status cache and session data...');

        // Clear Laravel cache
        $checker->clearCache();

        // Clear session storage (middleware caches status here too)
        if (session()->has('db_connection_status')) {
            session()->forget([
                'db_connection_status',
                'db_connected',
                'db_disconnected',
                'db_connection_status_checked',
                'db_connection_status_expiry',
            ]);
            $this->comment('✓ Session cache cleared');
        }

        $this->info('Cache cleared! Checking fresh connection status...');
        $this->newLine();

        // Force fresh check and display results
        $status = $checker->checkAll(false);

        foreach ($status as $connection => $info) {
            $icon = $info['connected'] ? '✓' : '✗';
            $statusText = $info['connected'] ? '<fg=green>ONLINE</>' : '<fg=red>OFFLINE</>';

            $this->line(sprintf(
                '%s <fg=yellow>%s</> (%s) - %s - %s',
                $icon,
                strtoupper($connection),
                $info['name'],
                $info['module'],
                $statusText
            ));
        }

        $this->newLine();

        $connected = count($checker->getConnected());
        $total = count($status);

        if ($connected === $total) {
            $this->info("✓ All {$total} databases are online!");
            return Command::SUCCESS;
        } else {
            $this->warn("⚠ {$connected}/{$total} databases online. Some databases are still offline.");
            return Command::SUCCESS;
        }
    }
}
