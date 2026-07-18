<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * For stored-procedure/trigger tests (tests/Procedures), which live outside
 * tests/Feature so they never pick up UsesDistributedDatabases: procedures
 * issue their own START TRANSACTION/COMMIT, which would commit that trait's
 * wrapping transaction early on MariaDB/MySQL (no nested transactions) and
 * turn its rollback into a no-op, leaking rows between tests. These tests
 * truncate explicitly instead, mirroring app/Console/Commands/FreshAllDatabases.php.
 */
trait TruncatesDistributedDatabases
{
    protected function assertTestDatabase(string $connection): void
    {
        if (! app()->environment('testing')) {
            throw new RuntimeException('TruncatesDistributedDatabases must only run under APP_ENV=testing.');
        }

        $database = config("database.connections.{$connection}.database");

        if (! str_ends_with((string) $database, '_test')) {
            throw new RuntimeException(
                "Refusing to run: connection '{$connection}' targets database ".
                "'{$database}', which does not end in '_test'. Check .env.testing."
            );
        }
    }

    /**
     * @param  array<int, string>  $tables  Table names to truncate. Order only
     *                                       matters on the pgsql path (CASCADE
     *                                       handles it anyway); MySQL/MariaDB
     *                                       disables FK checks around the loop.
     */
    protected function truncate(string $connection, array $tables): void
    {
        $this->assertTestDatabase($connection);

        $driver = config("database.connections.{$connection}.driver");

        if ($driver === 'pgsql') {
            foreach ($tables as $table) {
                DB::connection($connection)->statement("TRUNCATE TABLE \"{$table}\" RESTART IDENTITY CASCADE");
            }

            return;
        }

        DB::connection($connection)->statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            DB::connection($connection)->statement("TRUNCATE TABLE `{$table}`");
        }

        DB::connection($connection)->statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
