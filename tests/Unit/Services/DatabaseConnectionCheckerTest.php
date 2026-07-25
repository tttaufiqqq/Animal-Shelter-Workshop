<?php

use App\Services\DatabaseConnectionChecker;
use Illuminate\Support\Facades\Config;

// Unit suite gets no DB trait (tests/Pest.php), so this file doesn't touch real data.
// checkConnection()/checkAll() do real TCP probes + PDO connects against the real
// distributed databases (per .env.testing) — 'shelter' is deliberately pointed at an
// unreachable host:port to force a deterministic failure without touching a real server.
function forceShelterUnreachable(): void
{
    Config::set('database.connections.shelter.host', '127.0.0.1');
    Config::set('database.connections.shelter.port', 1);
}

function restoreShelterConnection(): void
{
    Config::set('database.connections.shelter.host', env('DB2_HOST'));
    Config::set('database.connections.shelter.port', env('DB2_PORT'));
}

beforeEach(function () {
    $this->checker = app(DatabaseConnectionChecker::class);
    restoreShelterConnection();
});

afterEach(function () {
    restoreShelterConnection();
});

it('reports a connection as offline immediately, with nothing cached to mask it', function () {
    forceShelterUnreachable();

    expect($this->checker->checkConnection('shelter'))->toBeFalse();
});

it('reports a recovered connection as online on the very next check', function () {
    forceShelterUnreachable();
    expect($this->checker->checkConnection('shelter'))->toBeFalse();

    restoreShelterConnection();

    // No circuit breaker, no cache — recovery is visible on the immediate next probe.
    expect($this->checker->checkConnection('shelter'))->toBeTrue();
});

it('checkAll() always reflects live status, never a cached snapshot', function () {
    forceShelterUnreachable();
    $offline = $this->checker->checkAll();
    expect($offline['shelter']['connected'])->toBeFalse();

    restoreShelterConnection();
    $online = $this->checker->checkAll();
    expect($online['shelter']['connected'])->toBeTrue();
});

it('isConnected() always reflects live status, never a cached snapshot', function () {
    forceShelterUnreachable();
    expect($this->checker->isConnected('shelter'))->toBeFalse();

    restoreShelterConnection();
    expect($this->checker->isConnected('shelter'))->toBeTrue();
});
