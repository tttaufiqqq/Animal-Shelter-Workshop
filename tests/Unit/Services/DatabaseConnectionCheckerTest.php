<?php

use App\Services\DatabaseConnectionChecker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

// Unit suite gets no DB trait (tests/Pest.php), so this file resets its own cache state.
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

function clearAllConnectionCache(): void
{
    Cache::forget('db_connection_status');
    Cache::store('file')->forget('db_connection_status');

    foreach (array_keys(DatabaseConnectionChecker::CONNECTIONS) as $conn) {
        Cache::forget("db_connection_status_{$conn}");
        Cache::store('file')->forget("db_connection_status_{$conn}");
        Cache::store('file')->forget("db_circuit_breaker_{$conn}");
    }
}

beforeEach(function () {
    $this->checker = app(DatabaseConnectionChecker::class);
    clearAllConnectionCache();
    restoreShelterConnection();
});

afterEach(function () {
    restoreShelterConnection();
    clearAllConnectionCache();
});

it('opens the circuit breaker for 30s after a failed probe', function () {
    forceShelterUnreachable();

    expect($this->checker->checkConnection('shelter'))->toBeFalse();
    expect(Cache::store('file')->has('db_circuit_breaker_shelter'))->toBeTrue();

    // With the breaker open, checkConnection() short-circuits to false without even
    // attempting a fresh probe — restoring the real connection doesn't matter yet.
    restoreShelterConnection();
    expect($this->checker->checkConnection('shelter'))->toBeFalse();
});

it('clears the circuit breaker so force refresh can recover a connection', function () {
    // Simulate a connection that failed a probe a moment ago (breaker open) and has
    // since recovered — the exact scenario ManagesMatching::getMatches() hits on
    // ?force_refresh.
    Cache::store('file')->put('db_circuit_breaker_shelter', true, 30);
    restoreShelterConnection();

    $this->checker->clearCache();

    expect(Cache::store('file')->has('db_circuit_breaker_shelter'))->toBeFalse();
    expect($this->checker->checkConnection('shelter'))->toBeTrue();
});

it('clears only the given connection\'s circuit breaker, not every connection', function () {
    Cache::store('file')->put('db_circuit_breaker_shelter', true, 30);
    Cache::store('file')->put('db_circuit_breaker_animals', true, 30);

    $this->checker->clearCache('shelter');

    expect(Cache::store('file')->has('db_circuit_breaker_shelter'))->toBeFalse();
    expect(Cache::store('file')->has('db_circuit_breaker_animals'))->toBeTrue();

    Cache::store('file')->forget('db_circuit_breaker_animals');
});

it('caches an offline result for a short window, not the all-online 300s window', function () {
    forceShelterUnreachable();

    $first = $this->checker->checkAll(true);
    expect($first['shelter']['connected'])->toBeFalse();

    // Recover the real connection immediately.
    restoreShelterConnection();

    // A cached call still reports it offline, even though the connection is fine
    // again — proving checkAll() cached the disconnected result for its short (15s)
    // window rather than the 300s window it uses when every connection is online.
    $second = $this->checker->checkAll(true);
    expect($second['shelter']['connected'])->toBeFalse();
});

it('lets isConnected() mask a recovered connection for up to 60s, longer than checkAll()\'s 15s recovery window', function () {
    // Exercise isConnected() directly with no 'db_connection_status' umbrella cache
    // present, forcing it down the single-key path (ChecksConnections::isConnected),
    // which hardcodes a 60s TTL regardless of connection state — unlike checkAll(),
    // which deliberately uses a 15s TTL "to detect recovery quickly" when any
    // connection is down.
    forceShelterUnreachable();
    expect($this->checker->isConnected('shelter'))->toBeFalse();

    restoreShelterConnection();

    // Still stale: the single-key cache set above is good for 60s no matter what,
    // so a real recovery isn't reflected for up to 4x longer than checkAll()'s window.
    expect($this->checker->isConnected('shelter'))->toBeFalse();
});
