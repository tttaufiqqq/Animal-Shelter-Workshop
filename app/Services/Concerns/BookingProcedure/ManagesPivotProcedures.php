<?php

namespace App\Services\Concerns\BookingProcedure;

use Illuminate\Support\Facades\DB;

trait ManagesPivotProcedures
{
    public function attachAnimalsToBooking(int $bookingId, array $animalData): array
    {
        $pairs = [];
        foreach ($animalData as $animalId => $remarks) {
            $pairs[] = $animalId . ':' . ($remarks ?? '');
        }
        $animalIdsString = implode(',', $pairs);

        DB::connection('booking')->statement(
            'CALL sp_booking_attach_animals(?, ?, @o_attached_count, @o_status, @o_message)',
            [$bookingId, $animalIdsString]
        );

        $result = DB::connection('booking')->selectOne(
            'SELECT @o_attached_count AS attached_count, @o_status AS `status`, @o_message AS `message`'
        );

        return [
            'success'        => $result->status === 'success',
            'attached_count' => $result->attached_count,
            'message'        => $result->message,
        ];
    }

    public function detachAnimalsFromBooking(int $bookingId, ?array $animalIds = null): array
    {
        $animalIdsString = $animalIds ? implode(',', $animalIds) : null;

        DB::connection('booking')->statement(
            'CALL sp_booking_detach_animals(?, ?, @o_detached_count, @o_status, @o_message)',
            [$bookingId, $animalIdsString]
        );

        $result = DB::connection('booking')->selectOne(
            'SELECT @o_detached_count AS detached_count, @o_status AS `status`, @o_message AS `message`'
        );

        return [
            'success'        => $result->status === 'success',
            'detached_count' => $result->detached_count,
            'message'        => $result->message,
        ];
    }

    public function getBookingAnimals(int $bookingId): array
    {
        return DB::connection('booking')->select('CALL sp_booking_get_animals(?)', [$bookingId]);
    }

    public function updateBookingAnimalRemarks(int $bookingId, int $animalId, ?string $remarks): array
    {
        DB::connection('booking')->statement(
            'CALL sp_booking_update_animal_remarks(?, ?, ?, @o_status, @o_message)',
            [$bookingId, $animalId, $remarks]
        );

        $result = DB::connection('booking')->selectOne(
            'SELECT @o_status AS `status`, @o_message AS `message`'
        );

        return [
            'success' => $result->status === 'success',
            'message' => $result->message,
        ];
    }

    public function getBookingAnimalCount(int $bookingId): int
    {
        DB::connection('booking')->statement(
            'CALL sp_booking_get_animal_count(?, @o_count)',
            [$bookingId]
        );

        $result = DB::connection('booking')->selectOne('SELECT @o_count AS `count`');

        return (int) ($result->count ?? 0);
    }
}
