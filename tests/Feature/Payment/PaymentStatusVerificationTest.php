<?php

use App\Models\Animal;
use Illuminate\Support\Facades\Http;

function fakeGatewayVerification(bool $confirmed): void
{
    Http::fake([
        'dev.toyyibpay.com/index.php/api/getBillTransactions' => Http::response(
            $confirmed ? [['billpaymentStatus' => '1']] : [['billpaymentStatus' => '3']],
            200
        ),
        'dev.toyyibpay.com/index.php/api/createBill' => Http::response([['BillCode' => 'testbill123']], 200),
    ]);
}

function hitPaymentStatus($test, $user, $booking, $animal, int $statusId = 1)
{
    return $test->actingAs($user)->withSession([
        'booking_id' => $booking->id,
        'adoption_fee' => 20,
        'animal_ids' => [$animal->id],
        'animal_names' => $animal->name,
        'reference_no' => 'BOOKING-' . $booking->id . '-123',
        'fee_breakdowns' => [$animal->id => 20],
    ])->get("/payment/status?status_id={$statusId}&billcode=abc123");
}

it('does not complete a booking when the gateway reports the bill unpaid', function () {
    fakeGatewayVerification(confirmed: false);

    $user = $this->makeAdopter();
    $animal = Animal::factory()->create(['species' => 'Dog', 'adoption_status' => 'Available']);
    $booking = $this->makeBookingFor($user, [$animal], ['status' => 'Confirmed']);

    hitPaymentStatus($this, $user, $booking, $animal, statusId: 1);

    expect($booking->fresh()->status)->not->toBe('Completed');
    expect($animal->fresh()->adoption_status)->not->toBe('Adopted');
});

it('completes a booking only when the gateway confirms payment', function () {
    fakeGatewayVerification(confirmed: true);

    $user = $this->makeAdopter();
    $animal = Animal::factory()->create(['species' => 'Dog', 'adoption_status' => 'Available']);
    $booking = $this->makeBookingFor($user, [$animal], ['status' => 'Confirmed']);

    hitPaymentStatus($this, $user, $booking, $animal, statusId: 1);

    expect($booking->fresh()->status)->toBe('Completed');
    expect($animal->fresh()->adoption_status)->toBe('Adopted');
});

it('does not grant the adopter role on an unverified payment', function () {
    fakeGatewayVerification(confirmed: false);

    $user = $this->makePublicUser();
    $animal = Animal::factory()->create(['species' => 'Dog', 'adoption_status' => 'Available']);
    $booking = $this->makeBookingFor($user, [$animal], ['status' => 'Confirmed']);

    hitPaymentStatus($this, $user, $booking, $animal, statusId: 1);

    expect($user->fresh()->hasRole('adopter'))->toBeFalse();
    expect($user->fresh()->hasRole('public user'))->toBeTrue();
});

it('does not mark animals Adopted when the client claims success but the gateway disagrees', function () {
    fakeGatewayVerification(confirmed: false);

    $user = $this->makeAdopter();
    $animal = Animal::factory()->create(['species' => 'Cat', 'adoption_status' => 'Available']);
    $booking = $this->makeBookingFor($user, [$animal], ['status' => 'Confirmed']);

    // Client-controlled query string still claims success — gateway is the source of truth.
    hitPaymentStatus($this, $user, $booking, $animal, statusId: 1);

    expect($animal->fresh()->adoption_status)->toBe('Available');
});
