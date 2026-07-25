<?php

namespace App\Console\Commands;

use App\Services\DatabaseConnectionChecker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshDatabaseStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:refresh-status {--silent : Run without output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and log the live status of every distributed database connection (runs via scheduler)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $checker = app(DatabaseConnectionChecker::class);
        $silent = $this->option('silent');

        $currentStatus = $checker->checkAll();
        $disconnected = array_filter($currentStatus, fn($db) => !$db['connected']);

        if (!empty($disconnected)) {
            Log::warning('Database connection check: some databases offline', [
                'offline' => array_map(fn($db) => "{$db['connection']} ({$db['module']})", $disconnected),
                'timestamp' => now()->toDateTimeString(),
            ]);
        }

        if (!$silent) {
            $connected = array_filter($currentStatus, fn($db) => $db['connected']);
            $this->info(sprintf(
                'Database status: %d/%d online',
                count($connected),
                count($currentStatus)
            ));
        }

        return Command::SUCCESS;
    }
}
