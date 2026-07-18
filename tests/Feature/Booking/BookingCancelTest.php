<?php

use App\Models\Booking;

it('cancels a Pending or Confirmed booking', function (string $status) {
    $user = $this->makeAdopter();
    $booking = $this->makeBookingFor($user, [], ['status' => $status]);

    $response = $this->actingAs($user)->patch("/bookings/{$booking->id}/cancel");

    $response->assertRedirect(route('booking:main'));
    $response->assertSessionHas('success');
    expect($booking->fresh()->status)->toBe('Cancelled');
})->with(['Pending', 'Confirmed']);

it('refuses to cancel a Completed or already Cancelled booking', function (string $status) {
    $user = $this->makeAdopter();
    $booking = $this->makeBookingFor($user, [], ['status' => $status]);

    $response = $this->actingAs($user)->patch("/bookings/{$booking->id}/cancel");

    $response->assertSessionHas('error');
    expect($booking->fresh()->status)->toBe($status);
})->with(['Completed', 'Cancelled']);
