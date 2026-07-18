<?php

use App\Models\Animal;
use App\Models\Medical;
use App\Models\Vaccination;
use Illuminate\Support\Facades\Http;

function fakeToyyibPayCreateBill(): void
{
    Http::fake([
        'dev.toyyibpay.com/index.php/api/createBill' => Http::response([['BillCode' => 'testbill123']], 200),
    ]);
}

function assertBillAmountSent(float $expectedRinggit): void
{
    Http::assertSent(function ($request) use ($expectedRinggit) {
        return $request->url() === 'https://dev.toyyibpay.com/index.php/api/createBill'
            && (float) $request['billAmount'] === $expectedRinggit * 100;
    });
}

it('recomputes the fee server-side and ignores a tampered total_fee of 0', function () {
    fakeToyyibPayCreateBill();

    $user = $this->makeAdopter();
    $animal = Animal::factory()->create(['species' => 'Dog']);
    $booking = $this->makeBookingFor($user, [$animal]);

    $this->actingAs($user)->patchJson("/bookings/{$booking->id}/confirm", [
        'animal_ids' => [$animal->id],
        'total_fee' => 0,
        'agree_terms' => true,
    ]);

    assertBillAmountSent(20); // dog base fee, not the tampered RM0
});

it('charges the correct base fee per species', function (string $species, float $expectedFee) {
    fakeToyyibPayCreateBill();

    $user = $this->makeAdopter();
    $animal = Animal::factory()->create(['species' => $species]);
    $booking = $this->makeBookingFor($user, [$animal]);

    $this->actingAs($user)->patchJson("/bookings/{$booking->id}/confirm", [
        'animal_ids' => [$animal->id],
        'total_fee' => 999999,
        'agree_terms' => true,
    ]);

    assertBillAmountSent($expectedFee);
})->with([
    'dog' => ['Dog', 20],
    'cat' => ['Cat', 10],
    'rabbit (unknown species)' => ['Rabbit', 100],
]);

it('adds RM10 per medical record and RM20 per vaccination', function () {
    fakeToyyibPayCreateBill();

    $user = $this->makeAdopter();
    $animal = Animal::factory()->create(['species' => 'Cat']);
    Medical::factory()->count(2)->create(['animalID' => $animal->id]);
    Vaccination::factory()->count(1)->create(['animalID' => $animal->id]);
    $booking = $this->makeBookingFor($user, [$animal]);

    $this->actingAs($user)->patchJson("/bookings/{$booking->id}/confirm", [
        'animal_ids' => [$animal->id],
        'agree_terms' => true,
    ]);

    // RM10 base + 2 medical * RM10 + 1 vaccination * RM20 = 50
    assertBillAmountSent(50);
});

it('confirms a booking and marks it Confirmed regardless of what the client submits as total_fee', function () {
    fakeToyyibPayCreateBill();

    $user = $this->makeAdopter();
    $animal = Animal::factory()->create(['species' => 'Dog']);
    $booking = $this->makeBookingFor($user, [$animal]);

    $this->actingAs($user)->patchJson("/bookings/{$booking->id}/confirm", [
        'animal_ids' => [$animal->id],
        'total_fee' => -50,
        'agree_terms' => true,
    ]);

    expect($booking->fresh()->status)->toBe('Confirmed');
});
