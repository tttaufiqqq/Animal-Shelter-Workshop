<?php

use App\Models\Animal;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;

it('accepts a gateway callback without a session', function () {
    Http::fake([
        'dev.toyyibpay.com/index.php/api/getBillTransactions' => Http::response([['billpaymentStatus' => '1']], 200),
    ]);

    $animal = Animal::factory()->create(['species' => 'Dog', 'adoption_status' => 'Available']);
    $booking = $this->makeBookingFor($this->makeAdopter(), [$animal], ['status' => 'Confirmed']);

    // No actingAs(), no withSession() — a real ToyyibPay server has neither.
    $response = $this->post('/payment/callback', [
        'billcode' => 'cbtest1',
        'status_id' => 1,
        'refno' => 'BOOKING-' . $booking->id . '-999',
        'amount' => 2000,
    ]);

    $response->assertOk();
    expect($booking->fresh()->status)->toBe('Completed');
});

it('rejects a forged callback that the gateway does not confirm', function () {
    Http::fake([
        'dev.toyyibpay.com/index.php/api/getBillTransactions' => Http::response([['billpaymentStatus' => '3']], 200),
    ]);

    $animal = Animal::factory()->create(['species' => 'Dog', 'adoption_status' => 'Available']);
    $booking = $this->makeBookingFor($this->makeAdopter(), [$animal], ['status' => 'Confirmed']);

    $response = $this->post('/payment/callback', [
        'billcode' => 'cbtest2',
        'status_id' => 1, // forged — claims success
        'refno' => 'BOOKING-' . $booking->id . '-999',
        'amount' => 2000,
    ]);

    $response->assertOk();
    expect($booking->fresh()->status)->not->toBe('Completed');
    expect($animal->fresh()->adoption_status)->not->toBe('Adopted');
});

it('is idempotent across duplicate callbacks', function () {
    Http::fake([
        'dev.toyyibpay.com/index.php/api/getBillTransactions' => Http::response([['billpaymentStatus' => '1']], 200),
    ]);

    $animal = Animal::factory()->create(['species' => 'Cat', 'adoption_status' => 'Available']);
    $booking = $this->makeBookingFor($this->makeAdopter(), [$animal], ['status' => 'Confirmed']);

    $payload = [
        'billcode' => 'cbtest3',
        'status_id' => 1,
        'refno' => 'BOOKING-' . $booking->id . '-999',
        'amount' => 1000,
    ];

    $this->post('/payment/callback', $payload)->assertOk();
    $this->post('/payment/callback', $payload)->assertOk();

    expect(Transaction::where('bill_code', 'cbtest3')->count())->toBe(1);
});

it('ignores a malformed refno instead of touching the wrong booking', function () {
    Http::fake([
        'dev.toyyibpay.com/index.php/api/getBillTransactions' => Http::response([['billpaymentStatus' => '1']], 200),
    ]);

    $animal = Animal::factory()->create(['species' => 'Dog', 'adoption_status' => 'Available']);
    $booking = $this->makeBookingFor($this->makeAdopter(), [$animal], ['status' => 'Confirmed']);

    // "XX-BOOKING-{$id}" — a naive explode('-')[1] would read "BOOKING", not the id.
    $response = $this->post('/payment/callback', [
        'billcode' => 'cbtest4',
        'status_id' => 1,
        'refno' => 'XX-BOOKING-' . $booking->id,
        'amount' => 2000,
    ]);

    $response->assertOk();
    expect($booking->fresh()->status)->toBe('Completed');
});

it('does nothing for a refno with no booking reference at all', function () {
    Http::fake([
        'dev.toyyibpay.com/index.php/api/getBillTransactions' => Http::response([['billpaymentStatus' => '1']], 200),
    ]);

    $response = $this->post('/payment/callback', [
        'billcode' => 'cbtest5',
        'status_id' => 1,
        'refno' => 'not-a-booking-ref',
        'amount' => 2000,
    ]);

    $response->assertOk();
    expect(Transaction::where('bill_code', 'cbtest5')->exists())->toBeFalse();
});
