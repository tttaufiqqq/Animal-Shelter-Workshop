<?php

namespace Tests\Concerns;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

/**
 * Wraps every distributed connection in a transaction per test.
 *
 * RefreshDatabase only ever refreshes the *default* connection, which this
 * app never queries directly — every model pins its own $connection and
 * every migration targets one explicitly (see docs/db-architecture.md).
 * DatabaseTransactions scoped to all five named connections is the trait
 * that actually isolates test writes here.
 *
 * Not for stored-procedure/trigger tests: sp_booking_create and friends run
 * their own START TRANSACTION / COMMIT, which commits this trait's outer
 * transaction early on MariaDB/MySQL (no nested transactions) and makes
 * rollback a no-op. Those tests truncate explicitly instead (Phase 2).
 */
trait UsesDistributedDatabases
{
    use DatabaseTransactions;

    protected $connectionsToTransact = [
        'users',
        'reporting',
        'booking',
        'shelter',
        'animals',
    ];

    protected function setUpUsesDistributedDatabases(): void
    {
        $this->guardAgainstNonTestDatabases();

        // The circuit breaker (App\Services\Concerns\DatabaseConnection\
        // ChecksConnections) always writes to Cache::store('file'), so
        // CACHE_STORE=array does not isolate it — db_circuit_breaker_*
        // keys survive across tests unless flushed here.
        Cache::store('file')->flush();
    }

    /**
     * Refuses to run unless every distributed connection targets a *_test
     * database. A single misconfigured env here would otherwise run
     * destructive writes against the live workshop_2 databases over
     * Tailscale.
     */
    protected function guardAgainstNonTestDatabases(): void
    {
        if (! app()->environment('testing')) {
            throw new RuntimeException(
                'UsesDistributedDatabases must only run under APP_ENV=testing.'
            );
        }

        foreach ($this->connectionsToTransact as $connection) {
            $database = config("database.connections.{$connection}.database");

            if (! str_ends_with((string) $database, '_test')) {
                throw new RuntimeException(
                    "Refusing to run: connection '{$connection}' targets database ".
                    "'{$database}', which does not end in '_test'. Check .env.testing."
                );
            }
        }
    }
}
