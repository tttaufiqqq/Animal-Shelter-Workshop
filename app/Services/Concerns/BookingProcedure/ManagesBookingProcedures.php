<?php

namespace App\Services\Concerns\BookingProcedure;

use Illuminate\Support\Facades\DB;

trait ManagesBookingProcedures
{
    public function createBooking(array $data): array
    {
        DB::connection('booking')->statement(
            'CALL sp_booking_create(?, ?, ?, ?, @o_booking_id, @o_status, @o_message)',
            [
                $data['user_id'],
                $data['appointment_date'],
                $data['appointment_time'],
                $data['status'] ?? 'Pending',
            ]
        );

        $result = DB::connection('booking')->selectOne(
            'SELECT @o_booking_id AS booking_id, @o_status AS `status`, @o_message AS `message`'
        );

        return [
            'success'    => $result->status === 'success',
            'booking_id' => $result->booking_id,
            'message'    => $result->message,
        ];
    }

    public function readBooking(int $bookingId): ?object
    {
        $result = DB::connection('booking')->select('CALL sp_booking_read(?)', [$bookingId]);

        return $result[0] ?? null;
    }

    public function updateBookingStatus(int $bookingId, string $newStatus, ?int $userId = null): array
    {
        DB::connection('booking')->statement(
            'CALL sp_booking_update_status(?, ?, ?, @o_old_status, @o_status, @o_message)',
            [$bookingId, $newStatus, $userId]
        );

        $result = DB::connection('booking')->selectOne(
            'SELECT @o_old_status AS old_status, @o_status AS `status`, @o_message AS `message`'
        );

        return [
            'success'    => $result->status === 'success',
            'old_status' => $result->old_status,
            'message'    => $result->message,
        ];
    }

    public function cancelBooking(int $bookingId, int $userId): array
    {
        DB::connection('booking')->statement(
            'CALL sp_booking_cancel(?, ?, @o_old_status, @o_status, @o_message)',
            [$bookingId, $userId]
        );

        $result = DB::connection('booking')->selectOne(
            'SELECT @o_old_status AS old_status, @o_status AS `status`, @o_message AS `message`'
        );

        return [
            'success'    => $result->status === 'success',
            'old_status' => $result->old_status,
            'message'    => $result->message,
        ];
    }

    public function checkTimeConflicts(string $appointmentDate, string $appointmentTime, array $animalIds, ?int $excludeBookingId = null): array
    {
        $animalIdsString = implode(',', $animalIds);

        $result = DB::connection('booking')->select(
            'CALL sp_booking_check_time_conflicts(?, ?, ?, ?)',
            [$appointmentDate, $appointmentTime, $animalIdsString, $excludeBookingId]
        );

        return array_column($result, 'animalID');
    }
}
