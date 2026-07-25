<?php

namespace App\Console\Commands;

use App\Services\DatabaseConnectionChecker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonitorDatabaseConnections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Continuously monitor database connections and report status changes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $checker = app(DatabaseConnectionChecker::class);

        $this->info('Starting database connection monitoring...');
        $this->info('This will check connections every 30 seconds.');
        $this->newLine();

        // Get initial status
        $previousStatus = $checker->checkAll();
        $this->displayStatus($previousStatus);

        while (true) {
            // Wait 30 seconds
            sleep(30);

            $currentStatus = $checker->checkAll();

            // Detect changes
            $changes = $this->detectChanges($previousStatus, $currentStatus);

            if (!empty($changes)) {
                $this->newLine();
                $this->warn('⚠ Connection status changes detected:');

                foreach ($changes as $change) {
                    $this->line($change);
                }

                $this->newLine();
                $this->displayStatus($currentStatus);

                // Log changes
                Log::info('Database connection status changed', [
                    'changes' => $changes,
                    'timestamp' => now()->toDateTimeString(),
                ]);
            } else {
                $this->comment('[' . now()->format('H:i:s') . '] No changes detected. All connections stable.');
            }

            $previousStatus = $currentStatus;
        }

        return Command::SUCCESS;
    }

    /**
     * Detect changes between two status arrays
     *
     * @param array $previous
     * @param array $current
     * @return array
     */
    private function detectChanges(array $previous, array $current): array
    {
        $changes = [];

        foreach ($current as $connection => $info) {
            $wasConnected = $previous[$connection]['connected'] ?? false;
            $isConnected = $info['connected'];

            if ($wasConnected !== $isConnected) {
                $status = $isConnected ? '<fg=green>CAME ONLINE</>' : '<fg=red>WENT OFFLINE</>';
                $changes[] = "  • <fg=yellow>{$connection}</> ({$info['module']}) {$status}";
            }
        }

        return $changes;
    }

    /**
     * Display current connection status
     *
     * @param array $status
     * @return void
     */
    private function displayStatus(array $status): void
    {
        foreach ($status as $connection => $info) {
            $icon = $info['connected'] ? '✓' : '✗';
            $statusText = $info['connected'] ? '<fg=green>ONLINE</>' : '<fg=red>OFFLINE</>';

            $this->line(sprintf(
                '%s <fg=yellow>%s</> - %s',
                $icon,
                strtoupper($connection),
                $statusText
            ));
        }
    }
}
