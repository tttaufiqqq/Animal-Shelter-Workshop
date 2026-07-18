<?php

use App\Models\AnimalBooking;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

uses(Tests\Concerns\TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->assertTestDatabase('booking');
});

afterEach(function () {
    $this->truncate('booking', ['adoption', 'animal_booking', 'booking']);
});

function callBookingCreate(?int $userId, ?string $date, ?string $time, ?string $status = null): object
{
    DB::connection('booking')->statement(
        'CALL sp_booking_create(?, ?, ?, ?, @o_booking_id, @o_status, @o_message)',
        [$userId, $date, $time, $status]
    );

    return DB::connection('booking')->selectOne(
        'SELECT @o_booking_id AS booking_id, @o_status AS `status`, @o_message AS `message`'
    );
}

function callBookingUpdateStatus(int $bookingId, string $newStatus, ?int $userId): object
{
    DB::connection('booking')->statement(
        'CALL sp_booking_update_status(?, ?, ?, @o_old_status, @o_status, @o_message)',
        [$bookingId, $newStatus, $userId]
    );

    return DB::connection('booking')->selectOne(
        'SELECT @o_old_status AS old_status, @o_status AS `status`, @o_message AS `message`'
    );
}

function callBookingCancel(int $bookingId, int $userId): object
{
    DB::connection('booking')->statement(
        'CALL sp_booking_cancel(?, ?, @o_old_status, @o_status, @o_message)',
        [$bookingId, $userId]
    );

    return DB::connection('booking')->selectOne(
        'SELECT @o_old_status AS old_status, @o_status AS `status`, @o_message AS `message`'
    );
}

// --- sp_booking_create ---

it('creates a booking on the happy path', function () {
    $result = callBookingCreate(101, '2026-08-01', '10:00:00', 'Pending');

    expect($result->status)->toBe('success');
    expect($result->booking_id)->not->toBeNull();
    $this->assertDatabaseHas('booking', [
        'id' => $result->booking_id,
        'userID' => 101,
        'status' => 'Pending',
    ], 'booking');
});

it('defaults status to Pending when none is given', function () {
    $result = callBookingCreate(101, '2026-08-01', '10:00:00', null);

    expect($result->status)->toBe('success');
    $this->assertDatabaseHas('booking', ['id' => $result->booking_id, 'status' => 'Pending'], 'booking');
});

it('returns an error status instead of throwing when the user id is null', function () {
    $result = callBookingCreate(null, '2026-08-01', '10:00:00', 'Pending');

    expect($result->status)->toBe('error');
    expect($result->booking_id)->toBeNull();
    expect($result->message)->toBe('User ID is required');
});

it('returns an error status instead of throwing when the date is null', function () {
    $result = callBookingCreate(101, null, '10:00:00', 'Pending');

    expect($result->status)->toBe('error');
    expect($result->booking_id)->toBeNull();
});

it('does not leak a previous successful booking id into a later failed call', function () {
    // @o_* are MariaDB session variables that persist across CALLs on a pooled
    // connection — a procedure that failed to reset them would let PHP read
    // the previous call's booking_id and report success for the wrong booking.
    $first = callBookingCreate(101, '2026-08-01', '10:00:00', 'Pending');
    expect($first->status)->toBe('success');

    $second = callBookingCreate(null, '2026-08-01', '10:00:00', 'Pending');

    expect($second->status)->toBe('error');
    expect($second->booking_id)->toBeNull();
});

// --- sp_booking_read ---

it('reads back a booking by id', function () {
    $booking = Booking::factory()->create(['userID' => 202, 'status' => 'Pending']);

    $rows = DB::connection('booking')->select('CALL sp_booking_read(?)', [$booking->id]);

    expect($rows)->toHaveCount(1);
    expect((int) $rows[0]->userID)->toBe(202);
});

// --- sp_booking_update_status ---

it('updates a booking status', function () {
    $booking = Booking::factory()->create(['userID' => 303, 'status' => 'Pending']);

    $result = callBookingUpdateStatus($booking->id, 'Confirmed', null);

    expect($result->status)->toBe('success');
    expect($result->old_status)->toBe('Pending');
    $this->assertDatabaseHas('booking', ['id' => $booking->id, 'status' => 'Confirmed'], 'booking');
});

it('rejects a status update from a user who does not own the booking', function () {
    $booking = Booking::factory()->create(['userID' => 303, 'status' => 'Pending']);

    $result = callBookingUpdateStatus($booking->id, 'Confirmed', 999);

    expect($result->status)->toBe('error');
    $this->assertDatabaseHas('booking', ['id' => $booking->id, 'status' => 'Pending'], 'booking');
});

it('returns an error status for a booking that does not exist', function () {
    $result = callBookingUpdateStatus(999999, 'Confirmed', null);

    expect($result->status)->toBe('error');
    expect($result->old_status)->toBeNull();
});

// --- sp_booking_cancel ---

it('cancels a Pending booking', function () {
    $booking = Booking::factory()->create(['userID' => 404, 'status' => 'Pending']);

    $result = callBookingCancel($booking->id, 404);

    expect($result->status)->toBe('success');
    $this->assertDatabaseHas('booking', ['id' => $booking->id, 'status' => 'Cancelled'], 'booking');
});

it('refuses to cancel a booking owned by another user', function () {
    $booking = Booking::factory()->create(['userID' => 404, 'status' => 'Pending']);

    $result = callBookingCancel($booking->id, 999);

    expect($result->status)->toBe('error');
    $this->assertDatabaseHas('booking', ['id' => $booking->id, 'status' => 'Pending'], 'booking');
});

it('refuses to cancel a booking that is already Completed', function () {
    $booking = Booking::factory()->create(['userID' => 404, 'status' => 'Completed']);

    $result = callBookingCancel($booking->id, 404);

    expect($result->status)->toBe('error');
    expect($result->message)->toContain('Cannot cancel booking with status');
});

// --- sp_booking_check_time_conflicts ---

it('finds a conflicting animal at the same date and time', function () {
    $booking = Booking::factory()->create([
        'appointment_date' => '2026-08-01',
        'appointment_time' => '10:00:00',
        'status' => 'Pending',
    ]);
    AnimalBooking::factory()->create(['bookingID' => $booking->id, 'animalID' => 55]);

    $rows = DB::connection('booking')->select(
        'CALL sp_booking_check_time_conflicts(?, ?, ?, ?)',
        ['2026-08-01', '10:00:00', '55,56', null]
    );

    expect(array_map('intval', array_column($rows, 'animalID')))->toBe([55]);
});

it('returns no conflicts for an empty animal id list', function () {
    // implode(',', []) yields '' — FIND_IN_SET(x, '') must not match everything.
    $booking = Booking::factory()->create([
        'appointment_date' => '2026-08-01',
        'appointment_time' => '10:00:00',
        'status' => 'Pending',
    ]);
    AnimalBooking::factory()->create(['bookingID' => $booking->id, 'animalID' => 55]);

    $rows = DB::connection('booking')->select(
        'CALL sp_booking_check_time_conflicts(?, ?, ?, ?)',
        ['2026-08-01', '10:00:00', '', null]
    );

    expect($rows)->toBeEmpty();
});

it('excludes the given booking id from its own conflict check', function () {
    $booking = Booking::factory()->create([
        'appointment_date' => '2026-08-01',
        'appointment_time' => '10:00:00',
        'status' => 'Pending',
    ]);
    AnimalBooking::factory()->create(['bookingID' => $booking->id, 'animalID' => 55]);

    $rows = DB::connection('booking')->select(
        'CALL sp_booking_check_time_conflicts(?, ?, ?, ?)',
        ['2026-08-01', '10:00:00', '55', $booking->id]
    );

    expect($rows)->toBeEmpty();
});
