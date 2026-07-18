<?php

use App\Models\Animal;
use App\Models\Slot;
use App\Models\Transaction;
use App\Services\DatabaseConnectionChecker;
use Illuminate\Support\Facades\Http;

it('does not leave animals adopted without a transaction record', function () {
    Http::fake([
        'dev.toyyibpay.com/index.php/api/getBillTransactions' => Http::response([['billpaymentStatus' => '1']], 200),
    ]);

    $user = $this->makeAdopter();
    $animal = Animal::factory()->create(['species' => 'Dog', 'adoption_status' => 'Available']);
    $booking = $this->makeBookingFor($user, [$animal], ['status' => 'Confirmed']);

    // decimal(10,2) on transaction.amount overflows here, forcing a real SQL
    // failure mid-transaction — proving the rollback actually covers the animals
    // connection too, not just booking.
    $response = $this->actingAs($user)->withSession([
        'booking_id' => $booking->id,
        'adoption_fee' => 999999999999,
        'animal_ids' => [$animal->id],
        'animal_names' => $animal->name,
        'reference_no' => 'BOOKING-' . $booking->id . '-overflow',
        'fee_breakdowns' => [],
    ])->get('/payment/status?status_id=1&billcode=overflow-bill');

    $response->assertServerError();
    expect($booking->fresh()->status)->not->toBe('Completed');
    expect($animal->fresh()->adoption_status)->not->toBe('Adopted');
    expect(Transaction::where('bill_code', 'overflow-bill')->exists())->toBeFalse();
});

it('does not divide by zero when the session has no animal ids', function () {
    Http::fake([
        'dev.toyyibpay.com/index.php/api/getBillTransactions' => Http::response([['billpaymentStatus' => '1']], 200),
    ]);

    $user = $this->makeAdopter();
    $booking = $this->makeBookingFor($user, [], ['status' => 'Confirmed']);

    $response = $this->actingAs($user)->withSession([
        'booking_id' => $booking->id,
        'adoption_fee' => 20,
        'animal_ids' => [],
        'reference_no' => 'BOOKING-' . $booking->id . '-empty',
    ])->get('/payment/status?status_id=1&billcode=empty-bill');

    $response->assertRedirect(route('booking:main'));
    expect($booking->fresh()->status)->not->toBe('Completed');
});

it('skips shelter updates when the shelter db is offline', function () {
    Http::fake([
        'dev.toyyibpay.com/index.php/api/getBillTransactions' => Http::response([['billpaymentStatus' => '1']], 200),
    ]);

    $this->app->instance(DatabaseConnectionChecker::class, new class extends DatabaseConnectionChecker {
        public function isConnected(string $connection): bool
        {
            return $connection !== 'shelter';
        }
    });

    $user = $this->makeAdopter();
    $section = \App\Models\Section::factory()->create();
    $slot = Slot::factory()->create(['sectionID' => $section->id, 'capacity' => 1, 'status' => 'occupied']);
    $animal = Animal::factory()->create(['species' => 'Dog', 'adoption_status' => 'Available', 'slotID' => $slot->id]);
    $booking = $this->makeBookingFor($user, [$animal], ['status' => 'Confirmed']);

    $response = $this->actingAs($user)->withSession([
        'booking_id' => $booking->id,
        'adoption_fee' => 20,
        'animal_ids' => [$animal->id],
        'animal_names' => $animal->name,
        'reference_no' => 'BOOKING-' . $booking->id . '-shelteroffline',
        'fee_breakdowns' => [$animal->id => 20],
    ])->get('/payment/status?status_id=1&billcode=shelter-offline-bill');

    $response->assertRedirect(route('booking:main'));
    expect($booking->fresh()->status)->toBe('Completed');
    expect($animal->fresh()->adoption_status)->toBe('Adopted');
    // Shelter DB was "offline" — the slot must be untouched, not just left correct by luck.
    expect($slot->fresh()->status)->toBe('occupied');
});

it('marks a slot available when its last animal is adopted', function () {
    Http::fake([
        'dev.toyyibpay.com/index.php/api/getBillTransactions' => Http::response([['billpaymentStatus' => '1']], 200),
    ]);

    // Note: a DB trigger enforces capacity >= 1 (slot.capacity cannot be 0), so
    // the true zero-capacity edge case the plan flagged can't occur in practice —
    // this pins the ordinary "last animal leaves" case instead.
    $user = $this->makeAdopter();
    $section = \App\Models\Section::factory()->create();
    $slot = Slot::factory()->create(['sectionID' => $section->id, 'capacity' => 1, 'status' => 'occupied']);
    $animal = Animal::factory()->create(['species' => 'Dog', 'adoption_status' => 'Available', 'slotID' => $slot->id]);
    $booking = $this->makeBookingFor($user, [$animal], ['status' => 'Confirmed']);

    $this->actingAs($user)->withSession([
        'booking_id' => $booking->id,
        'adoption_fee' => 20,
        'animal_ids' => [$animal->id],
        'animal_names' => $animal->name,
        'reference_no' => 'BOOKING-' . $booking->id . '-lastanimal',
        'fee_breakdowns' => [$animal->id => 20],
    ])->get('/payment/status?status_id=1&billcode=last-animal-bill');

    expect($slot->fresh()->status)->toBe('available');
});
