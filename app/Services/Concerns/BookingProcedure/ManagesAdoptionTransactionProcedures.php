<?php

namespace App\Services\Concerns\BookingProcedure;

use Illuminate\Support\Facades\DB;

trait ManagesAdoptionTransactionProcedures
{
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
            'success'     => $result->status === 'success',
            'adoption_id' => $result->adoption_id,
            'message'     => $result->message,
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
}
