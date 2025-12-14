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
    protected array $connections = ['taufiq', 'eilya', 'shafiqah', 'atiqah', 'danish'];

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

        foreach ($this->connections as $connection) {
            $this->dropAllTables($connection);
        }

        $this->newLine();
        $this->info('Running migrations...');
        $this->call('migrate');

        if ($this->option('seed')) {
            $this->newLine();
            $this->info('Seeding databases...');
            $this->call('db:seed');
        }

        $this->newLine();
        $this->info('✓ All databases have been refreshed successfully!');

        return 0;
    }

    /**
     * Drop all tables from a specific database connection
     */
    protected function dropAllTables(string $connection): void
    {
        try {
            $driver = config("database.connections.{$connection}.driver");
            $this->info("  Dropping tables from '{$connection}' ({$driver})...");

            switch ($driver) {
                case 'mysql':
                    $this->dropMySQLTables($connection);
                    break;
                case 'pgsql':
                    $this->dropPostgreSQLTables($connection);
                    break;
                case 'sqlsrv':
                    $this->dropSQLServerTables($connection);
                    break;
                default:
                    $this->warn("  Unknown driver '{$driver}' for connection '{$connection}'");
            }
        } catch (\Exception $e) {
            $this->error("  Error dropping tables from '{$connection}': {$e->getMessage()}");
        }
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

    /**
     * Drop all tables from a SQL Server database
     */
    protected function dropSQLServerTables(string $connection): void
    {
        // First, drop all foreign key constraints
        $foreignKeys = DB::connection($connection)
            ->select("
                SELECT
                    OBJECT_NAME(parent_object_id) AS TableName,
                    name AS ConstraintName
                FROM sys.foreign_keys
            ");

        foreach ($foreignKeys as $fk) {
            DB::connection($connection)->statement(
                "ALTER TABLE [{$fk->TableName}] DROP CONSTRAINT [{$fk->ConstraintName}]"
            );
        }

        // Then drop all tables
        $tables = DB::connection($connection)
            ->select("
                SELECT TABLE_NAME
                FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_TYPE = 'BASE TABLE'
            ");

        foreach ($tables as $table) {
            DB::connection($connection)->statement("DROP TABLE IF EXISTS [{$table->TABLE_NAME}]");
        }

        $count = count($tables);
        $this->info("    ✓ Dropped {$count} tables");
    }
}
