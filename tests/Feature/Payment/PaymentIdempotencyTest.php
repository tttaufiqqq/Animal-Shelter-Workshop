<?php

use App\Models\Animal;
use App\Models\Adoption;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;

it('creates exactly one transaction when the return url is hit twice', function () {
    Http::fake([
        'dev.toyyibpay.com/index.php/api/getBillTransactions' => Http::response([['billpaymentStatus' => '1']], 200),
    ]);

    $user = $this->makeAdopter();
    $animal = Animal::factory()->create(['species' => 'Dog', 'adoption_status' => 'Available']);
    $booking = $this->makeBookingFor($user, [$animal], ['status' => 'Confirmed']);

    $sessionData = [
        'booking_id' => $booking->id,
        'adoption_fee' => 20,
        'animal_ids' => [$animal->id],
        'animal_names' => $animal->name,
        'reference_no' => 'BOOKING-' . $booking->id . '-123',
        'fee_breakdowns' => [$animal->id => 20],
    ];

    $this->actingAs($user)->withSession($sessionData)->get('/payment/status?status_id=1&billcode=repeat-hit');
    $this->actingAs($user)->withSession($sessionData)->get('/payment/status?status_id=1&billcode=repeat-hit');

    expect(Transaction::where('bill_code', 'repeat-hit')->count())->toBe(1);
});

it('creates exactly one adoption row per animal on repeat hits', function () {
    Http::fake([
        'dev.toyyibpay.com/index.php/api/getBillTransactions' => Http::response([['billpaymentStatus' => '1']], 200),
    ]);

    $user = $this->makeAdopter();
    $animal = Animal::factory()->create(['species' => 'Cat', 'adoption_status' => 'Available']);
    $booking = $this->makeBookingFor($user, [$animal], ['status' => 'Confirmed']);

    $sessionData = [
        'booking_id' => $booking->id,
        'adoption_fee' => 10,
        'animal_ids' => [$animal->id],
        'animal_names' => $animal->name,
        'reference_no' => 'BOOKING-' . $booking->id . '-456',
        'fee_breakdowns' => [$animal->id => 10],
    ];

    $this->actingAs($user)->withSession($sessionData)->get('/payment/status?status_id=1&billcode=repeat-hit-2');
    $this->actingAs($user)->withSession($sessionData)->get('/payment/status?status_id=1&billcode=repeat-hit-2');

    expect(Adoption::where('bookingID', $booking->id)->where('animalID', $animal->id)->count())->toBe(1);
});
