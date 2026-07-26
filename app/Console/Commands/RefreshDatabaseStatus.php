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
    protected $signature = 'db:refresh-status {--silent : Run without output} {--fail-on-down : Exit non-zero and list each offline connection (CD health checks only — the scheduler must never fail on this)}';

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

        if ($this->option('fail-on-down') && !empty($disconnected)) {
            foreach ($disconnected as $db) {
                $this->error("  DOWN: {$db['connection']} ({$db['module']})");
            }

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
