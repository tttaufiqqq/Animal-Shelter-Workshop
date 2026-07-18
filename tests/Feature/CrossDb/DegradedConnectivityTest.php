<?php

use App\Models\Animal;
use Illuminate\Support\Facades\Config;

/**
 * Graceful degradation is a deliberate, documented feature (safeQuery(), the
 * "Limited Connectivity" banner, Animal::getImagesOrEmpty()) — these tests drive
 * it end-to-end instead of it only ever being discovered in a demo.
 *
 * Each connection is pointed at an unreachable host:port for the duration of one
 * test, never a real server — DatabaseConnectionChecker::checkConnection() then
 * fails a real TCP probe deterministically and fast (connection refused on
 * loopback). UsesDistributedDatabases flushes the file cache (where the circuit
 * breaker and connection-status cache live) before every test, so one test's
 * simulated outage can't leak into the next.
 */

function forceConnectionOffline(string $connection): void
{
    Config::set("database.connections.{$connection}.host", '127.0.0.1');
    Config::set("database.connections.{$connection}.port", 1);
}

it('renders the bookings page with a fallback when the booking db is offline', function () {
    $user = $this->makeAdopter();
    forceConnectionOffline('booking');

    $response = $this->actingAs($user)->get('/bookings/index');

    $response->assertOk();
    $bookings = $response->original->getData()['bookings'];
    expect($bookings)->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class);
    expect($bookings->isEmpty())->toBeTrue();
});

it('returns 503 from animal-matches when the users db is offline', function () {
    $user = $this->makeAdopter();
    forceConnectionOffline('users');

    $response = $this->actingAs($user)->getJson('/animal-matches');

    $response->assertStatus(503);
    $response->assertJson(['success' => false]);
});

it('omits images rather than failing when the reporting db is offline', function () {
    $animal = $this->makeAnimalWithProfile();
    forceConnectionOffline('reporting');

    $images = $animal->getImagesOrEmpty();

    expect($images)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    expect($images->isEmpty())->toBeTrue();
});

it('hides the user column when the users db is offline', function () {
    $admin = $this->makeAdmin();
    $bookingOwner = $this->makeAdopter();
    $this->makeBookingFor($bookingOwner);
    forceConnectionOffline('users');

    $response = $this->actingAs($admin)->get('/bookings/all');

    $response->assertOk();
    $bookings = $response->original->getData()['bookings'];
    foreach ($bookings as $booking) {
        expect($booking->relationLoaded('user') ? $booking->user : null)->toBeNull();
    }
});
