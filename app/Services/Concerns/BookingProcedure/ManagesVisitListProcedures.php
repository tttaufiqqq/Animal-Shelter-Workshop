<?php

namespace App\Services\Concerns\BookingProcedure;

use Illuminate\Support\Facades\DB;

trait ManagesVisitListProcedures
{
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
}
