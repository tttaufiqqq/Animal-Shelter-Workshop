<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DanishProcedureService
{
    // ==========================================
    // BOOKING PROCEDURES
    // ==========================================

    /**
     * Create a new booking
     *
     * @param array $data ['user_id', 'appointment_date', 'appointment_time', 'status']
     * @return array ['success' => bool, 'booking_id' => int|null, 'message' => string]
     */
    public function createBooking(array $data): array
    {
        $stmt = DB::connection('danish')->getPdo()->prepare(
            'EXEC sp_booking_create ?, ?, ?, ?, ?, ?, ?'
        );

        $bookingId = null;
        $status = null;
        $message = null;

        $stmt->bindParam(1, $data['user_id'], \PDO::PARAM_INT);
        $stmt->bindParam(2, $data['appointment_date'], \PDO::PARAM_STR);
        $stmt->bindParam(3, $data['appointment_time'], \PDO::PARAM_STR);
        $stmt->bindParam(4, $data['status'] ?? 'Pending', \PDO::PARAM_STR);
        $stmt->bindParam(5, $bookingId, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 8);
        $stmt->bindParam(6, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 20);
        $stmt->bindParam(7, $message, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 4000);

        $stmt->execute();

        return [
            'success' => $status === 'success',
            'booking_id' => $bookingId,
            'message' => $message,
        ];
    }

    /**
     * Read a booking by ID
     *
     * @param int $bookingId
     * @return object|null
     */
    public function readBooking(int $bookingId): ?object
    {
        $result = DB::connection('danish')->select('EXEC sp_booking_read ?', [$bookingId]);

        return $result[0] ?? null;
    }

    /**
     * Update booking status
     *
     * @param int $bookingId
     * @param string $newStatus
     * @param int|null $userId (for authorization check)
     * @return array ['success' => bool, 'old_status' => string|null, 'message' => string]
     */
    public function updateBookingStatus(int $bookingId, string $newStatus, ?int $userId = null): array
    {
        $stmt = DB::connection('danish')->getPdo()->prepare(
            'EXEC sp_booking_update_status ?, ?, ?, ?, ?, ?'
        );

        $oldStatus = null;
        $status = null;
        $message = null;

        $stmt->bindParam(1, $bookingId, \PDO::PARAM_INT);
        $stmt->bindParam(2, $newStatus, \PDO::PARAM_STR);
        $stmt->bindParam(3, $userId, \PDO::PARAM_INT);
        $stmt->bindParam(4, $oldStatus, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 50);
        $stmt->bindParam(5, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 20);
        $stmt->bindParam(6, $message, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 4000);

        $stmt->execute();

        return [
            'success' => $status === 'success',
            'old_status' => $oldStatus,
            'message' => $message,
        ];
    }

    /**
     * Cancel a booking
     *
     * @param int $bookingId
     * @param int $userId
     * @return array ['success' => bool, 'old_status' => string|null, 'message' => string]
     */
    public function cancelBooking(int $bookingId, int $userId): array
    {
        $stmt = DB::connection('danish')->getPdo()->prepare(
            'EXEC sp_booking_cancel ?, ?, ?, ?, ?'
        );

        $oldStatus = null;
        $status = null;
        $message = null;

        $stmt->bindParam(1, $bookingId, \PDO::PARAM_INT);
        $stmt->bindParam(2, $userId, \PDO::PARAM_INT);
        $stmt->bindParam(3, $oldStatus, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 50);
        $stmt->bindParam(4, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 20);
        $stmt->bindParam(5, $message, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 4000);

        $stmt->execute();

        return [
            'success' => $status === 'success',
            'old_status' => $oldStatus,
            'message' => $message,
        ];
    }

    /**
     * Check for time slot conflicts
     *
     * @param string $appointmentDate
     * @param string $appointmentTime
     * @param array $animalIds
     * @param int|null $excludeBookingId
     * @return array List of conflicting animal IDs
     */
    public function checkTimeConflicts(string $appointmentDate, string $appointmentTime, array $animalIds, ?int $excludeBookingId = null): array
    {
        $animalIdsString = implode(',', $animalIds);

        $result = DB::connection('danish')->select(
            'EXEC sp_booking_check_time_conflicts ?, ?, ?, ?',
            [$appointmentDate, $appointmentTime, $animalIdsString, $excludeBookingId]
        );

        return array_column($result, 'animalID');
    }

    // ==========================================
    // VISIT LIST PROCEDURES
    // ==========================================

    /**
     * Get or create visit list for user
     *
     * @param int $userId
     * @return array ['success' => bool, 'list_id' => int|null, 'is_new' => bool, 'message' => string]
     */
    public function getOrCreateVisitList(int $userId): array
    {
        $stmt = DB::connection('danish')->getPdo()->prepare(
            'EXEC sp_visit_list_get_or_create ?, ?, ?, ?, ?'
        );

        $listId = null;
        $isNew = null;
        $status = null;
        $message = null;

        $stmt->bindParam(1, $userId, \PDO::PARAM_INT);
        $stmt->bindParam(2, $listId, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 8);
        $stmt->bindParam(3, $isNew, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 1);
        $stmt->bindParam(4, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 20);
        $stmt->bindParam(5, $message, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 4000);

        $stmt->execute();

        return [
            'success' => $status === 'success',
            'list_id' => $listId,
            'is_new' => (bool) $isNew,
            'message' => $message,
        ];
    }

    /**
     * Add animal to visit list
     *
     * @param int $listId
     * @param int $animalId
     * @return array ['success' => bool, 'message' => string]
     */
    public function addAnimalToVisitList(int $listId, int $animalId): array
    {
        $stmt = DB::connection('danish')->getPdo()->prepare(
            'EXEC sp_visit_list_add_animal ?, ?, ?, ?'
        );

        $status = null;
        $message = null;

        $stmt->bindParam(1, $listId, \PDO::PARAM_INT);
        $stmt->bindParam(2, $animalId, \PDO::PARAM_INT);
        $stmt->bindParam(3, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 20);
        $stmt->bindParam(4, $message, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 4000);

        $stmt->execute();

        return [
            'success' => $status === 'success',
            'message' => $message,
        ];
    }

    /**
     * Remove animal from visit list
     *
     * @param int $listId
     * @param int $animalId
     * @return array ['success' => bool, 'message' => string]
     */
    public function removeAnimalFromVisitList(int $listId, int $animalId): array
    {
        $stmt = DB::connection('danish')->getPdo()->prepare(
            'EXEC sp_visit_list_remove_animal ?, ?, ?, ?'
        );

        $status = null;
        $message = null;

        $stmt->bindParam(1, $listId, \PDO::PARAM_INT);
        $stmt->bindParam(2, $animalId, \PDO::PARAM_INT);
        $stmt->bindParam(3, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 20);
        $stmt->bindParam(4, $message, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 4000);

        $stmt->execute();

        return [
            'success' => $status === 'success',
            'message' => $message,
        ];
    }

    /**
     * Get animals in visit list
     *
     * @param int $listId
     * @return array
     */
    public function getVisitListAnimals(int $listId): array
    {
        return DB::connection('danish')->select('EXEC sp_visit_list_get_animals ?', [$listId]);
    }

    /**
     * Delete visit list
     *
     * @param int $listId
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteVisitList(int $listId): array
    {
        $stmt = DB::connection('danish')->getPdo()->prepare(
            'EXEC sp_visit_list_delete ?, ?, ?'
        );

        $status = null;
        $message = null;

        $stmt->bindParam(1, $listId, \PDO::PARAM_INT);
        $stmt->bindParam(2, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 20);
        $stmt->bindParam(3, $message, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 4000);

        $stmt->execute();

        return [
            'success' => $status === 'success',
            'message' => $message,
        ];
    }

    /**
     * Clear animals from visit list
     *
     * @param int $listId
     * @param array $animalIds
     * @return array ['success' => bool, 'removed_count' => int, 'message' => string]
     */
    public function clearVisitListAnimals(int $listId, array $animalIds): array
    {
        $stmt = DB::connection('danish')->getPdo()->prepare(
            'EXEC sp_visit_list_clear_animals ?, ?, ?, ?, ?'
        );

        $animalIdsString = implode(',', $animalIds);
        $removedCount = null;
        $status = null;
        $message = null;

        $stmt->bindParam(1, $listId, \PDO::PARAM_INT);
        $stmt->bindParam(2, $animalIdsString, \PDO::PARAM_STR);
        $stmt->bindParam(3, $removedCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 4);
        $stmt->bindParam(4, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 20);
        $stmt->bindParam(5, $message, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 4000);

        $stmt->execute();

        return [
            'success' => $status === 'success',
            'removed_count' => $removedCount,
            'message' => $message,
        ];
    }

    // ==========================================
    // ADOPTION PROCEDURES
    // ==========================================

    /**
     * Create adoption record
     *
     * @param array $data ['booking_id', 'transaction_id', 'animal_id', 'fee', 'remarks']
     * @return array ['success' => bool, 'adoption_id' => int|null, 'message' => string]
     */
    public function createAdoption(array $data): array
    {
        $stmt = DB::connection('danish')->getPdo()->prepare(
            'EXEC sp_adoption_create ?, ?, ?, ?, ?, ?, ?, ?'
        );

        $adoptionId = null;
        $status = null;
        $message = null;

        $stmt->bindParam(1, $data['booking_id'], \PDO::PARAM_INT);
        $stmt->bindParam(2, $data['transaction_id'], \PDO::PARAM_INT);
        $stmt->bindParam(3, $data['animal_id'], \PDO::PARAM_INT);
        $stmt->bindParam(4, $data['fee'], \PDO::PARAM_STR);
        $stmt->bindParam(5, $data['remarks'] ?? null, \PDO::PARAM_STR);
        $stmt->bindParam(6, $adoptionId, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 8);
        $stmt->bindParam(7, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 20);
        $stmt->bindParam(8, $message, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 4000);

        $stmt->execute();

        return [
            'success' => $status === 'success',
            'adoption_id' => $adoptionId,
            'message' => $message,
        ];
    }

    /**
     * Read adoption by ID
     *
     * @param int $adoptionId
     * @return object|null
     */
    public function readAdoption(int $adoptionId): ?object
    {
        $result = DB::connection('danish')->select('EXEC sp_adoption_read ?', [$adoptionId]);

        return $result[0] ?? null;
    }

    /**
     * Get adoptions by booking
     *
     * @param int $bookingId
     * @return array
     */
    public function getAdoptionsByBooking(int $bookingId): array
    {
        return DB::connection('danish')->select('EXEC sp_adoption_get_by_booking ?', [$bookingId]);
    }

    // ==========================================
    // TRANSACTION PROCEDURES
    // ==========================================

    /**
     * Create transaction
     *
     * @param array $data ['user_id', 'amount', 'status', 'type', 'bill_code', 'reference_no', 'remarks']
     * @return array ['success' => bool, 'transaction_id' => int|null, 'message' => string]
     */
    public function createTransaction(array $data): array
    {
        $stmt = DB::connection('danish')->getPdo()->prepare(
            'EXEC sp_transaction_create ?, ?, ?, ?, ?, ?, ?, ?, ?, ?'
        );

        $transactionId = null;
        $status = null;
        $message = null;

        $stmt->bindParam(1, $data['user_id'], \PDO::PARAM_INT);
        $stmt->bindParam(2, $data['amount'], \PDO::PARAM_STR);
        $stmt->bindParam(3, $data['status'], \PDO::PARAM_STR);
        $stmt->bindParam(4, $data['type'], \PDO::PARAM_STR);
        $stmt->bindParam(5, $data['bill_code'] ?? null, \PDO::PARAM_STR);
        $stmt->bindParam(6, $data['reference_no'] ?? null, \PDO::PARAM_STR);
        $stmt->bindParam(7, $data['remarks'] ?? null, \PDO::PARAM_STR);
        $stmt->bindParam(8, $transactionId, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 8);
        $stmt->bindParam(9, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 20);
        $stmt->bindParam(10, $message, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 4000);

        $stmt->execute();

        return [
            'success' => $status === 'success',
            'transaction_id' => $transactionId,
            'message' => $message,
        ];
    }

    /**
     * Read transaction by ID
     *
     * @param int $transactionId
     * @return object|null
     */
    public function readTransaction(int $transactionId): ?object
    {
        $result = DB::connection('danish')->select('EXEC sp_transaction_read ?', [$transactionId]);

        return $result[0] ?? null;
    }

    /**
     * Update transaction status
     *
     * @param int $transactionId
     * @param string $newStatus
     * @return array ['success' => bool, 'old_status' => string|null, 'message' => string]
     */
    public function updateTransactionStatus(int $transactionId, string $newStatus): array
    {
        $stmt = DB::connection('danish')->getPdo()->prepare(
            'EXEC sp_transaction_update_status ?, ?, ?, ?, ?'
        );

        $oldStatus = null;
        $status = null;
        $message = null;

        $stmt->bindParam(1, $transactionId, \PDO::PARAM_INT);
        $stmt->bindParam(2, $newStatus, \PDO::PARAM_STR);
        $stmt->bindParam(3, $oldStatus, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 50);
        $stmt->bindParam(4, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 20);
        $stmt->bindParam(5, $message, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 4000);

        $stmt->execute();

        return [
            'success' => $status === 'success',
            'old_status' => $oldStatus,
            'message' => $message,
        ];
    }

    /**
     * Get transaction by bill code
     *
     * @param string $billCode
     * @return object|null
     */
    public function getTransactionByBillCode(string $billCode): ?object
    {
        $result = DB::connection('danish')->select('EXEC sp_transaction_get_by_bill_code ?', [$billCode]);

        return $result[0] ?? null;
    }

    /**
     * Get transactions by user
     *
     * @param int $userId
     * @param string|null $status
     * @return array
     */
    public function getTransactionsByUser(int $userId, ?string $status = null): array
    {
        return DB::connection('danish')->select('EXEC sp_transaction_get_by_user ?, ?', [$userId, $status]);
    }

    // ==========================================
    // ANIMAL-BOOKING PIVOT PROCEDURES
    // ==========================================

    /**
     * Attach animals to booking
     *
     * @param int $bookingId
     * @param array $animalData Format: ['animal_id' => 'remarks', ...]
     * @return array ['success' => bool, 'attached_count' => int, 'message' => string]
     */
    public function attachAnimalsToBooking(int $bookingId, array $animalData): array
    {
        // Format: 'id1:remarks1,id2:remarks2'
        $pairs = [];
        foreach ($animalData as $animalId => $remarks) {
            $pairs[] = $animalId.':'.($remarks ?? '');
        }
        $animalIdsString = implode(',', $pairs);

        $stmt = DB::connection('danish')->getPdo()->prepare(
            'EXEC sp_booking_attach_animals ?, ?, ?, ?, ?'
        );

        $attachedCount = null;
        $status = null;
        $message = null;

        $stmt->bindParam(1, $bookingId, \PDO::PARAM_INT);
        $stmt->bindParam(2, $animalIdsString, \PDO::PARAM_STR);
        $stmt->bindParam(3, $attachedCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 4);
        $stmt->bindParam(4, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 20);
        $stmt->bindParam(5, $message, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 4000);

        $stmt->execute();

        return [
            'success' => $status === 'success',
            'attached_count' => $attachedCount,
            'message' => $message,
        ];
    }

    /**
     * Detach animals from booking
     *
     * @param int $bookingId
     * @param array|null $animalIds (null = detach all)
     * @return array ['success' => bool, 'detached_count' => int, 'message' => string]
     */
    public function detachAnimalsFromBooking(int $bookingId, ?array $animalIds = null): array
    {
        $animalIdsString = $animalIds ? implode(',', $animalIds) : null;

        $stmt = DB::connection('danish')->getPdo()->prepare(
            'EXEC sp_booking_detach_animals ?, ?, ?, ?, ?'
        );

        $detachedCount = null;
        $status = null;
        $message = null;

        $stmt->bindParam(1, $bookingId, \PDO::PARAM_INT);
        $stmt->bindParam(2, $animalIdsString, \PDO::PARAM_STR);
        $stmt->bindParam(3, $detachedCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 4);
        $stmt->bindParam(4, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 20);
        $stmt->bindParam(5, $message, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 4000);

        $stmt->execute();

        return [
            'success' => $status === 'success',
            'detached_count' => $detachedCount,
            'message' => $message,
        ];
    }

    /**
     * Get animals for a booking
     *
     * @param int $bookingId
     * @return array
     */
    public function getBookingAnimals(int $bookingId): array
    {
        return DB::connection('danish')->select('EXEC sp_booking_get_animals ?', [$bookingId]);
    }

    /**
     * Update animal remarks for a booking
     *
     * @param int $bookingId
     * @param int $animalId
     * @param string|null $remarks
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateBookingAnimalRemarks(int $bookingId, int $animalId, ?string $remarks): array
    {
        $stmt = DB::connection('danish')->getPdo()->prepare(
            'EXEC sp_booking_update_animal_remarks ?, ?, ?, ?, ?'
        );

        $status = null;
        $message = null;

        $stmt->bindParam(1, $bookingId, \PDO::PARAM_INT);
        $stmt->bindParam(2, $animalId, \PDO::PARAM_INT);
        $stmt->bindParam(3, $remarks, \PDO::PARAM_STR);
        $stmt->bindParam(4, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 20);
        $stmt->bindParam(5, $message, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 4000);

        $stmt->execute();

        return [
            'success' => $status === 'success',
            'message' => $message,
        ];
    }

    /**
     * Get animal count for a booking
     *
     * @param int $bookingId
     * @return int
     */
    public function getBookingAnimalCount(int $bookingId): int
    {
        $stmt = DB::connection('danish')->getPdo()->prepare(
            'EXEC sp_booking_get_animal_count ?, ?'
        );

        $count = null;

        $stmt->bindParam(1, $bookingId, \PDO::PARAM_INT);
        $stmt->bindParam(2, $count, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 4);

        $stmt->execute();

        return $count ?? 0;
    }
}
