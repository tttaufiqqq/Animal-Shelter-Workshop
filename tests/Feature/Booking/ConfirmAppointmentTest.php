<?php

use App\Models\Animal;
use App\Models\Booking;
use App\Models\VisitList;
use Illuminate\Support\Facades\DB;

function confirmAppointmentPayload(array $overrides = []): array
{
    return array_merge([
        'appointment_date' => now()->addDay()->format('Y-m-d'),
        'appointment_time' => '10:00',
        'terms' => '1',
    ], $overrides);
}

it('errors when the visit list is empty', function () {
    $user = $this->makeAdopter();
    $animal = Animal::factory()->create();

    $response = $this->actingAs($user)->post('/visit-list/confirm', confirmAppointmentPayload([
        'animal_ids' => [$animal->id],
    ]));

    $response->assertSessionHas('error', 'Your visit list is empty.');
    expect(Booking::where('userID', $user->id)->count())->toBe(0);
});

it('rejects animals that are not on the visit list', function () {
    $user = $this->makeAdopter();
    $onList = Animal::factory()->create();
    $notOnList = Animal::factory()->create();
    $this->makeVisitListFor($user, [$onList]);

    $response = $this->actingAs($user)->post('/visit-list/confirm', confirmAppointmentPayload([
        'animal_ids' => [$notOnList->id],
    ]));

    $response->assertSessionHas('error', 'Some selected animals are not in your visit list.');
    expect(Booking::where('userID', $user->id)->count())->toBe(0);
});

it('rejects a past appointment date', function () {
    $user = $this->makeAdopter();
    $animal = Animal::factory()->create();
    $this->makeVisitListFor($user, [$animal]);

    $response = $this->actingAs($user)->post('/visit-list/confirm', confirmAppointmentPayload([
        'animal_ids' => [$animal->id],
        'appointment_date' => now()->subDay()->format('Y-m-d'),
    ]));

    $response->assertSessionHasErrors('appointment_date');
    expect(Booking::where('userID', $user->id)->count())->toBe(0);
});

it('requires the terms to be accepted', function () {
    $user = $this->makeAdopter();
    $animal = Animal::factory()->create();
    $this->makeVisitListFor($user, [$animal]);

    $response = $this->actingAs($user)->post('/visit-list/confirm', confirmAppointmentPayload([
        'animal_ids' => [$animal->id],
        'terms' => false,
    ]));

    $response->assertSessionHasErrors('terms');
    expect(Booking::where('userID', $user->id)->count())->toBe(0);
});

it('creates a Pending booking, attaches the animal, and clears it from the visit list on success', function () {
    $user = $this->makeAdopter();
    $animal = Animal::factory()->create();
    $visitList = $this->makeVisitListFor($user, [$animal]);

    $response = $this->actingAs($user)->post('/visit-list/confirm', confirmAppointmentPayload([
        'animal_ids' => [$animal->id],
    ]));

    $response->assertSessionHas('success');

    $booking = Booking::where('userID', $user->id)->first();
    expect($booking)->not->toBeNull()
        ->and($booking->status)->toBe('Pending');

    $attachedAnimalIds = DB::connection('booking')->table('animal_booking')->where('bookingID', $booking->id)->pluck('animalID')->toArray();
    expect($attachedAnimalIds)->toBe([$animal->id]);

    expect(DB::connection('booking')->table('visit_list_animal')->where('listID', $visitList->id)->exists())->toBeFalse();
    expect(VisitList::find($visitList->id))->toBeNull();
});

it('keeps the visit list when animals remain on it after confirming a subset', function () {
    $user = $this->makeAdopter();
    $booked = Animal::factory()->create();
    $keptOnList = Animal::factory()->create();
    $visitList = $this->makeVisitListFor($user, [$booked, $keptOnList]);

    $this->actingAs($user)->post('/visit-list/confirm', confirmAppointmentPayload([
        'animal_ids' => [$booked->id],
    ]));

    expect(VisitList::find($visitList->id))->not->toBeNull();
    $remaining = DB::connection('booking')->table('visit_list_animal')->where('listID', $visitList->id)->pluck('animalID')->toArray();
    expect($remaining)->toBe([$keptOnList->id]);
});

it('blocks confirming an animal already Pending or Confirmed for another user at the same slot', function (string $conflictingStatus) {
    $existingUser = $this->makeAdopter();
    $animal = Animal::factory()->create();
    $date = now()->addDays(3)->format('Y-m-d');
    $this->makeBookingFor($existingUser, [$animal], [
        'status' => $conflictingStatus,
        'appointment_date' => $date,
        'appointment_time' => '14:00:00',
    ]);

    $newUser = $this->makeAdopter();
    $this->makeVisitListFor($newUser, [$animal]);

    $response = $this->actingAs($newUser)->post('/visit-list/confirm', confirmAppointmentPayload([
        'animal_ids' => [$animal->id],
        'appointment_date' => $date,
        'appointment_time' => '14:00',
    ]));

    $response->assertSessionHas('error');
    expect(Booking::where('userID', $newUser->id)->count())->toBe(0);
})->with(['Pending', 'Confirmed']);

it('does not block a new booking against an animal whose earlier booking was cancelled', function () {
    $existingUser = $this->makeAdopter();
    $animal = Animal::factory()->create();
    $date = now()->addDays(3)->format('Y-m-d');
    $this->makeBookingFor($existingUser, [$animal], [
        'status' => 'Cancelled',
        'appointment_date' => $date,
        'appointment_time' => '14:00:00',
    ]);

    $newUser = $this->makeAdopter();
    $this->makeVisitListFor($newUser, [$animal]);

    $response = $this->actingAs($newUser)->post('/visit-list/confirm', confirmAppointmentPayload([
        'animal_ids' => [$animal->id],
        'appointment_date' => $date,
        'appointment_time' => '14:00',
    ]));

    $response->assertSessionHas('success');
    expect(Booking::where('userID', $newUser->id)->count())->toBe(1);
});

it('blocks a duplicate booking for the same animal, date, and time by the same user', function () {
    $user = $this->makeAdopter();
    $animal = Animal::factory()->create();
    $date = now()->addDays(3)->format('Y-m-d');
    $this->makeBookingFor($user, [$animal], [
        'status' => 'Pending',
        'appointment_date' => $date,
        'appointment_time' => '14:00:00',
    ]);

    $this->makeVisitListFor($user, [$animal]);

    $response = $this->actingAs($user)->post('/visit-list/confirm', confirmAppointmentPayload([
        'animal_ids' => [$animal->id],
        'appointment_date' => $date,
        'appointment_time' => '14:00',
    ]));

    $response->assertSessionHas('error');
    expect(Booking::where('userID', $user->id)->count())->toBe(1);
});
