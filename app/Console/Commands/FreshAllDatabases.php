<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FreshAllDatabases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:fresh-all {--seed : Seed the database after migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop all tables from all distributed databases and run migrations';

    /**
     * All database connections used in the distributed architecture
     */
    protected array $connections = ['users', 'reporting', 'animals', 'shelter', 'booking'];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->confirm('This will DROP ALL TABLES from all 5 databases. Do you want to continue?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('Dropping all tables from all distributed databases...');

        $successCount = 0;
        $failedConnections = [];

        foreach ($this->connections as $connection) {
            if ($this->dropAllTables($connection)) {
                $successCount++;
            } else {
                $failedConnections[] = $connection;
            }
        }

        $this->newLine();

        if (count($failedConnections) > 0) {
            $this->warn('⚠ Some databases were skipped (offline or unreachable):');
            foreach ($failedConnections as $conn) {
                $this->warn("  • {$conn}");
            }
            $this->newLine();
        }

        $this->info("Running migrations on {$successCount} available database(s)...");
        $this->call('migrate');

        if ($this->option('seed')) {
            $this->newLine();
            $this->info('Seeding databases...');
            $this->warn('Note: Seeding may fail if cross-database dependencies are offline.');
            $this->call('db:seed');
        }

        $this->newLine();

        if (count($failedConnections) === 0) {
            $this->info('✓ All databases have been refreshed successfully!');
        } else {
            $this->warn("✓ {$successCount}/{$this->countConnections()} databases refreshed. " . count($failedConnections) . " database(s) were offline.");
        }

        return 0;
    }

    /**
     * Count total connections
     */
    protected function countConnections(): int
    {
        return count($this->connections);
    }

    /**
     * Drop all tables from a specific database connection
     * Returns true if successful, false if database is offline/unreachable
     */
    protected function dropAllTables(string $connection): bool
    {
        try {
            // Test connection first
            DB::connection($connection)->getPdo();

            $driver = config("database.connections.{$connection}.driver");
            $this->info("  Dropping tables from '{$connection}' ({$driver})...");

            switch ($driver) {
                case 'mysql':
                case 'mariadb':
                    $this->dropMySQLTables($connection);
                    break;
                case 'pgsql':
                    $this->dropPostgreSQLTables($connection);
                    break;
                default:
                    $this->warn("  Unknown driver '{$driver}' for connection '{$connection}'");
                    return false;
            }

            return true;

        } catch (\Exception $e) {
            $this->warn("  ⚠ Skipping '{$connection}': " . $this->getConnectionErrorMessage($e));
            return false;
        }
    }

    /**
     * Get user-friendly connection error message
     */
    protected function getConnectionErrorMessage(\Exception $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, 'Connection refused') || str_contains($message, 'Unable to connect')) {
            return 'Database offline or unreachable';
        }

        if (str_contains($message, 'Access denied')) {
            return 'Authentication failed';
        }

        if (str_contains($message, 'Unknown database')) {
            return 'Database does not exist';
        }

        return 'Connection failed';
    }

    /**
     * Drop all tables from a MySQL database
     */
    protected function dropMySQLTables(string $connection): void
    {
        DB::connection($connection)->statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = DB::connection($connection)
            ->select('SHOW TABLES');

        $tableKey = 'Tables_in_' . config("database.connections.{$connection}.database");

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            DB::connection($connection)->statement("DROP TABLE IF EXISTS `{$tableName}`");
        }

        DB::connection($connection)->statement('SET FOREIGN_KEY_CHECKS=1');

        $count = count($tables);
        $this->info("    ✓ Dropped {$count} tables");
    }

    /**
     * Drop all tables from a PostgreSQL database
     */
    protected function dropPostgreSQLTables(string $connection): void
    {
        $tables = DB::connection($connection)
            ->select("
                SELECT tablename
                FROM pg_catalog.pg_tables
                WHERE schemaname = 'public'
            ");

        foreach ($tables as $table) {
            DB::connection($connection)->statement("DROP TABLE IF EXISTS \"{$table->tablename}\" CASCADE");
        }

        $count = count($tables);
        $this->info("    ✓ Dropped {$count} tables");
    }

}

