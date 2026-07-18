<?php

it('forbids confirming a booking owned by another user', function () {
    $owner = $this->makeAdopter();
    $attacker = $this->makeAdopter();
    $animal = $this->makeAnimalWithProfile();
    $booking = $this->makeBookingFor($owner, [$animal]);

    $response = $this->actingAs($attacker)->patchJson("/bookings/{$booking->id}/confirm", [
        'animal_ids' => [$animal->id],
        'total_fee' => 20,
        'agree_terms' => true,
    ]);

    $response->assertForbidden();
    expect($booking->fresh()->status)->toBe('Pending');
});

it('forbids cancelling another users booking', function () {
    $owner = $this->makeAdopter();
    $attacker = $this->makeAdopter();
    $booking = $this->makeBookingFor($owner);

    $response = $this->actingAs($attacker)->patch("/bookings/{$booking->id}/cancel");

    $response->assertForbidden();
    expect($booking->fresh()->status)->toBe('Pending');
});
