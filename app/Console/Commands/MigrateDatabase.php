<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MigrateDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:migrate-one {connection : Database connection name (taufiq, eilya, shafiqah, atiqah, danish)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for a specific database connection only';

    /**
     * Valid database connections
     */
    protected array $validConnections = ['users', 'reporting', 'animals', 'shelter', 'booking'];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $connection = $this->argument('connection');

        if (!in_array($connection, $this->validConnections)) {
            $this->error("Invalid connection '{$connection}'");
            $this->info('Valid connections: ' . implode(', ', $this->validConnections));
            return 1;
        }

        $this->info("Running migrations for '{$connection}' database...");

        try {
            // Run migrations with specific database connection
            Artisan::call('migrate', [
                '--database' => $connection,
                '--force' => true,
            ]);

            $output = Artisan::output();
            $this->line($output);

            $this->info("✓ Migrations completed for '{$connection}'");

            return 0;

        } catch (\Exception $e) {
            $this->error("✗ Migration failed for '{$connection}': {$e->getMessage()}");
            return 1;
        }
    }
}
