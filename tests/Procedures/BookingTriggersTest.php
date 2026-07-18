<?php

use App\Models\Adoption;
use App\Models\AnimalBooking;
use App\Models\Booking;
use Illuminate\Database\QueryException;

uses(Tests\Concerns\TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->assertTestDatabase('booking');
});

afterEach(function () {
    $this->truncate('booking', ['adoption', 'animal_booking', 'booking']);
});

it('blocks deleting a booking that has adoptions', function () {
    $booking = Booking::factory()->create(['status' => 'Completed']);
    Adoption::factory()->create(['bookingID' => $booking->id]);

    expect(fn () => $booking->delete())
        ->toThrow(QueryException::class, 'Cannot delete booking with associated adoptions');

    $this->assertDatabaseHas('booking', ['id' => $booking->id], 'booking');
});

it('cascades animal_booking rows on booking delete', function () {
    $booking = Booking::factory()->create(['status' => 'Pending']);
    AnimalBooking::factory()->create(['bookingID' => $booking->id, 'animalID' => 10]);

    $booking->delete();

    $this->assertDatabaseMissing('animal_booking', ['bookingID' => $booking->id], 'booking');
});

it('blocks modifying a completed booking', function () {
    $booking = Booking::factory()->create(['status' => 'Completed']);

    expect(fn () => $booking->update(['status' => 'Cancelled']))
        ->toThrow(QueryException::class, 'Cannot modify a completed booking');
});

it('allows updating a completed booking when the status column is left unchanged', function () {
    $booking = Booking::factory()->create(['status' => 'Completed', 'remarks' => 'old']);

    $booking->update(['remarks' => 'new']);

    $this->assertDatabaseHas('booking', ['id' => $booking->id, 'remarks' => 'new', 'status' => 'Completed'], 'booking');
});

it('rejects a past appointment date on insert for a Pending booking', function () {
    expect(fn () => Booking::factory()->create([
        'status' => 'Pending',
        'appointment_date' => now()->subDay()->toDateString(),
    ]))->toThrow(QueryException::class, 'Appointment date cannot be in the past');
});

it('rejects moving a Pending booking to a past appointment date on update', function () {
    $booking = Booking::factory()->create(['status' => 'Pending', 'appointment_date' => '2026-08-01']);

    expect(fn () => $booking->update(['appointment_date' => now()->subDay()->toDateString()]))
        ->toThrow(QueryException::class, 'Appointment date cannot be in the past');
});

it('allows backdating a Confirmed booking — the trigger only guards Pending', function () {
    // Pins current behaviour rather than asserting it's correct: both
    // trg_booking_validate_appointment_date_insert/_update only check
    // `NEW.status = 'Pending'`, so a booking inserted or moved directly to
    // Confirmed can be backdated. Whether that's intended is an open question
    // flagged in the test plan (Phase 2) — not fixed here.
    $pastDate = now()->subDay()->toDateString();

    $booking = Booking::factory()->create([
        'status' => 'Confirmed',
        'appointment_date' => $pastDate,
    ]);

    expect($booking->appointment_date->toDateString())->toBe($pastDate);
});
