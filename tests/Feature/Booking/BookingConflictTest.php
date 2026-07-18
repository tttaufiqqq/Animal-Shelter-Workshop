<?php

use App\Models\Animal;
use App\Models\Booking;

it('blocks the whole request when only one of several requested animals conflicts', function () {
    $existingUser = $this->makeAdopter();
    $conflicting = Animal::factory()->create();
    $free = Animal::factory()->create();
    $date = now()->addDays(3)->format('Y-m-d');
    $this->makeBookingFor($existingUser, [$conflicting], [
        'status' => 'Pending',
        'appointment_date' => $date,
        'appointment_time' => '09:00:00',
    ]);

    $newUser = $this->makeAdopter();
    $this->makeVisitListFor($newUser, [$conflicting, $free]);

    $response = $this->actingAs($newUser)->post('/visit-list/confirm', [
        'appointment_date' => $date,
        'appointment_time' => '09:00',
        'animal_ids' => [$conflicting->id, $free->id],
        'terms' => '1',
    ]);

    $response->assertSessionHas('error');
    expect(Booking::where('userID', $newUser->id)->count())->toBe(0);
});

// Pins current behavior, not necessarily desired: the conflict message names
// the other user by their real name (falling back to "another user" only if
// the user relation can't be loaded, e.g. the users db is offline) rather
// than staying anonymous. Any authenticated adopter who tries to book an
// already-taken slot learns who holds it — a minor PII exposure worth a
// product decision, not fixed here since it may be intentional UX ("someone
// beat you to it, here's who to ask").
it('names the conflicting booking\'s owner rather than staying anonymous', function () {
    $existingUser = $this->makeAdopter(['name' => 'Existing Owner']);
    $animal = Animal::factory()->create();
    $date = now()->addDays(3)->format('Y-m-d');
    $this->makeBookingFor($existingUser, [$animal], [
        'status' => 'Pending',
        'appointment_date' => $date,
        'appointment_time' => '11:00:00',
    ]);

    $newUser = $this->makeAdopter();
    $this->makeVisitListFor($newUser, [$animal]);

    $response = $this->actingAs($newUser)->post('/visit-list/confirm', [
        'appointment_date' => $date,
        'appointment_time' => '11:00',
        'animal_ids' => [$animal->id],
        'terms' => '1',
    ]);

    $errorHtml = $response->getSession()->get('error');
    expect($errorHtml)->toContain('by Existing Owner');
});

it('attributes a conflict to "by you" when the conflicting booking is the requesting users own', function () {
    $user = $this->makeAdopter();
    $animal = Animal::factory()->create();
    $otherAnimal = Animal::factory()->create();
    $date = now()->addDays(3)->format('Y-m-d');
    $this->makeBookingFor($user, [$animal], [
        'status' => 'Pending',
        'appointment_date' => $date,
        'appointment_time' => '11:00:00',
    ]);

    $this->makeVisitListFor($user, [$animal, $otherAnimal]);

    $response = $this->actingAs($user)->post('/visit-list/confirm', [
        'appointment_date' => $date,
        'appointment_time' => '11:00',
        'animal_ids' => [$animal->id, $otherAnimal->id],
        'terms' => '1',
    ]);

    $errorHtml = $response->getSession()->get('error');
    expect($errorHtml)->toContain('by you');
});

it('does not conflict for the same animal at a different time on the same day', function () {
    $existingUser = $this->makeAdopter();
    $animal = Animal::factory()->create();
    $date = now()->addDays(3)->format('Y-m-d');
    $this->makeBookingFor($existingUser, [$animal], [
        'status' => 'Pending',
        'appointment_date' => $date,
        'appointment_time' => '09:00:00',
    ]);

    $newUser = $this->makeAdopter();
    $this->makeVisitListFor($newUser, [$animal]);

    $response = $this->actingAs($newUser)->post('/visit-list/confirm', [
        'appointment_date' => $date,
        'appointment_time' => '15:00',
        'animal_ids' => [$animal->id],
        'terms' => '1',
    ]);

    $response->assertSessionHas('success');
    expect(Booking::where('userID', $newUser->id)->count())->toBe(1);
});
