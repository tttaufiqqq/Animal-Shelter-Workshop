<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FreshDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:fresh-one {connection : Database connection name (taufiq, eilya, shafiqah, atiqah, danish)} {--seed : Seed the database after migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop all tables and re-run migrations for a specific database connection';

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

        if (!$this->confirm("This will DROP ALL TABLES from '{$connection}' database. Continue?")) {
            $this->info('Operation cancelled.');
            return 0;
        }

        try {
            // Test connection first
            DB::connection($connection)->getPdo();

            $this->info("Dropping all tables from '{$connection}'...");
            $this->dropAllTables($connection);

            $this->newLine();
            $this->info("Running migrations for '{$connection}'...");
            $this->call('db:migrate-one', ['connection' => $connection]);

            if ($this->option('seed')) {
                $this->newLine();
                $this->warn("Note: Seeding may fail if '{$connection}' has cross-database dependencies that are offline.");
                $this->info('Seeding database...');
                $this->call('db:seed');
            }

            $this->newLine();
            $this->info("✓ '{$connection}' database refreshed successfully!");

            return 0;

        } catch (\Exception $e) {
            $this->error("✗ Failed to refresh '{$connection}': {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Drop all tables from the specified connection
     */
    protected function dropAllTables(string $connection): void
    {
        $driver = config("database.connections.{$connection}.driver");

        switch ($driver) {
            case 'mysql':
            case 'mariadb':
                $this->dropMySQLTables($connection);
                break;
            case 'pgsql':
                $this->dropPostgreSQLTables($connection);
                break;
            default:
                throw new \Exception("Unknown driver '{$driver}' for connection '{$connection}'");
        }
    }

    /**
     * Drop all tables from a MySQL database
     */
    protected function dropMySQLTables(string $connection): void
    {
        DB::connection($connection)->statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = DB::connection($connection)->select('SHOW TABLES');
        $tableKey = 'Tables_in_' . config("database.connections.{$connection}.database");

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            DB::connection($connection)->statement("DROP TABLE IF EXISTS `{$tableName}`");
        }

        DB::connection($connection)->statement('SET FOREIGN_KEY_CHECKS=1');

        $count = count($tables);
        $this->info("  ✓ Dropped {$count} tables");
    }

    /**
     * Drop all tables from a PostgreSQL database
     */
    protected function dropPostgreSQLTables(string $connection): void
    {
        $tables = DB::connection($connection)->select("
            SELECT tablename
            FROM pg_catalog.pg_tables
            WHERE schemaname = 'public'
        ");

        foreach ($tables as $table) {
            DB::connection($connection)->statement("DROP TABLE IF EXISTS \"{$table->tablename}\" CASCADE");
        }

        $count = count($tables);
        $this->info("  ✓ Dropped {$count} tables");
    }

}

