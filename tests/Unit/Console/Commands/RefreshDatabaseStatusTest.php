<?php

use Illuminate\Support\Facades\Config;

// Unit suite gets no DB trait (tests/Pest.php) — same forced-unreachable
// pattern as DatabaseConnectionCheckerTest, so this doesn't touch real data.
function forceShelterUnreachableForRefresh(): void
{
    Config::set('database.connections.shelter.host', '127.0.0.1');
    Config::set('database.connections.shelter.port', 1);
}

function restoreShelterConnectionForRefresh(): void
{
    Config::set('database.connections.shelter.host', env('DB2_HOST'));
    Config::set('database.connections.shelter.port', env('DB2_PORT'));
}

afterEach(function () {
    restoreShelterConnectionForRefresh();
});

it('exits successfully without --fail-on-down even when a connection is offline (scheduler behavior)', function () {
    forceShelterUnreachableForRefresh();

    $this->artisan('db:refresh-status')->assertExitCode(0);
});

it('exits non-zero with --fail-on-down when a connection is offline', function () {
    forceShelterUnreachableForRefresh();

    $this->artisan('db:refresh-status', ['--fail-on-down' => true])
        ->expectsOutputToContain('DOWN: shelter')
        ->assertExitCode(1);
});

it('exits successfully with --fail-on-down when every connection is online', function () {
    restoreShelterConnectionForRefresh();

    $this->artisan('db:refresh-status', ['--fail-on-down' => true])->assertExitCode(0);
});
