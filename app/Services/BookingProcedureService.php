<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Service for calling stored procedures on the danish (MariaDB) connection.
 *
 * Calling convention:
 *   OUT params use MariaDB session variables — call the procedure with @o_var
 *   placeholders, then SELECT those session variables in a follow-up query.
 *
 *   Procedures that only return a result set use DB::select('CALL sp(?)').
 */
class BookingProcedureService
{
    // ==========================================
    // BOOKING PROCEDURES
    // ==========================================

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

    // ==========================================
    // VISIT LIST PROCEDURES
    // ==========================================

    public function getOrCreateVisitList(int $userId): array
    {
        DB::connection('booking')->statement(
            'CALL sp_visit_list_get_or_create(?, @o_list_id, @o_is_new, @o_status, @o_message)',
            [$userId]
        );

        $result = DB::connection('booking')->selectOne(
            'SELECT @o_list_id AS list_id, @o_is_new AS is_new, @o_status AS `status`, @o_message AS `message`'
        );

        return [
            'success' => $result->status === 'success',
            'list_id' => $result->list_id,
            'is_new'  => (bool) $result->is_new,
            'message' => $result->message,
        ];
    }

    public function addAnimalToVisitList(int $listId, int $animalId): array
    {
        DB::connection('booking')->statement(
            'CALL sp_visit_list_add_animal(?, ?, @o_status, @o_message)',
            [$listId, $animalId]
        );

        $result = DB::connection('booking')->selectOne(
            'SELECT @o_status AS `status`, @o_message AS `message`'
        );

        return [
            'success' => $result->status === 'success',
            'message' => $result->message,
        ];
    }

    public function removeAnimalFromVisitList(int $listId, int $animalId): array
    {
        DB::connection('booking')->statement(
            'CALL sp_visit_list_remove_animal(?, ?, @o_status, @o_message)',
            [$listId, $animalId]
        );

        $result = DB::connection('booking')->selectOne(
            'SELECT @o_status AS `status`, @o_message AS `message`'
        );

        return [
            'success' => $result->status === 'success',
            'message' => $result->message,
        ];
    }

    public function getVisitListAnimals(int $listId): array
    {
        return DB::connection('booking')->select('CALL sp_visit_list_get_animals(?)', [$listId]);
    }

    public function deleteVisitList(int $listId): array
    {
        DB::connection('booking')->statement(
            'CALL sp_visit_list_delete(?, @o_status, @o_message)',
            [$listId]
        );

        $result = DB::connection('booking')->selectOne(
            'SELECT @o_status AS `status`, @o_message AS `message`'
        );

        return [
            'success' => $result->status === 'success',
            'message' => $result->message,
        ];
    }

    public function clearVisitListAnimals(int $listId, array $animalIds): array
    {
        $animalIdsString = implode(',', $animalIds);

        DB::connection('booking')->statement(
            'CALL sp_visit_list_clear_animals(?, ?, @o_removed_count, @o_status, @o_message)',
            [$listId, $animalIdsString]
        );

        $result = DB::connection('booking')->selectOne(
            'SELECT @o_removed_count AS removed_count, @o_status AS `status`, @o_message AS `message`'
        );

        return [
            'success'       => $result->status === 'success',
            'removed_count' => $result->removed_count,
            'message'       => $result->message,
        ];
    }

    // ==========================================
    // ADOPTION PROCEDURES
    // ==========================================

    public function createAdoption(array $data): array
    {
        DB::connection('booking')->statement(
            'CALL sp_adoption_create(?, ?, ?, ?, ?, @o_adoption_id, @o_status, @o_message)',
            [
                $data['booking_id'],
                $data['transaction_id'],
                $data['animal_id'],
                $data['fee'],
                $data['remarks'] ?? null,
            ]
        );

        $result = DB::connection('booking')->selectOne(
            'SELECT @o_adoption_id AS adoption_id, @o_status AS `status`, @o_message AS `message`'
        );

        return [
            'success'    => $result->status === 'success',
            'adoption_id' => $result->adoption_id,
            'message'    => $result->message,
        ];
    }

    public function readAdoption(int $adoptionId): ?object
    {
        $result = DB::connection('booking')->select('CALL sp_adoption_read(?)', [$adoptionId]);

        return $result[0] ?? null;
    }

    public function getAdoptionsByBooking(int $bookingId): array
    {
        return DB::connection('booking')->select('CALL sp_adoption_get_by_booking(?)', [$bookingId]);
    }

    // ==========================================
    // TRANSACTION PROCEDURES
    // ==========================================

    public function createTransaction(array $data): array
    {
        DB::connection('booking')->statement(
            'CALL sp_transaction_create(?, ?, ?, ?, ?, ?, ?, @o_transaction_id, @o_status, @o_message)',
            [
                $data['user_id'],
                $data['amount'],
                $data['status'],
                $data['type'],
                $data['bill_code'] ?? null,
                $data['reference_no'] ?? null,
                $data['remarks'] ?? null,
            ]
        );

        $result = DB::connection('booking')->selectOne(
            'SELECT @o_transaction_id AS transaction_id, @o_status AS `status`, @o_message AS `message`'
        );

        return [
            'success'        => $result->status === 'success',
            'transaction_id' => $result->transaction_id,
            'message'        => $result->message,
        ];
    }

    public function readTransaction(int $transactionId): ?object
    {
        $result = DB::connection('booking')->select('CALL sp_transaction_read(?)', [$transactionId]);

        return $result[0] ?? null;
    }

    public function updateTransactionStatus(int $transactionId, string $newStatus): array
    {
        DB::connection('booking')->statement(
            'CALL sp_transaction_update_status(?, ?, @o_old_status, @o_status, @o_message)',
            [$transactionId, $newStatus]
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

    public function getTransactionByBillCode(string $billCode): ?object
    {
        $result = DB::connection('booking')->select('CALL sp_transaction_get_by_bill_code(?)', [$billCode]);

        return $result[0] ?? null;
    }

    public function getTransactionsByUser(int $userId, ?string $status = null): array
    {
        return DB::connection('booking')->select('CALL sp_transaction_get_by_user(?, ?)', [$userId, $status]);
    }

    // ==========================================
    // ANIMAL-BOOKING PIVOT PROCEDURES
    // ==========================================

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
