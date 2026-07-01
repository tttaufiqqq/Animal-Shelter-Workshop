<?php

namespace App\Services\Concerns\AnimalProcedure;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ManagesAnimalProcedures
{
    public function createAnimal(array $data): array
    {
        $this->setAuditContext();
        $user = Auth::user();

        try {
            DB::connection('animals')->statement('CALL sp_animal_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_animal_id, @o_status, @o_message)', [
                $data['name'], $data['species'] ?? null, $data['health_details'] ?? null,
                $data['age'] ?? null, $data['gender'] ?? 'Unknown', $data['weight'] ?? null,
                $data['adoption_status'] ?? 'Not Adopted', $data['rescueID'] ?? null, $data['slotID'] ?? null,
                $user->id ?? null, $user->name ?? null, $user->email ?? null,
            ]);
            $result = DB::connection('animals')->select('SELECT @o_animal_id as animal_id, @o_status as status, @o_message as message')[0];
            return ['success' => $result->status === 'success', 'animal_id' => $result->animal_id, 'message' => $result->message];
        } catch (QueryException $e) {
            $msg = $this->extractTriggerValidationMessage($e);
            if ($msg) return ['success' => false, 'animal_id' => null, 'message' => $msg];
            throw $e;
        }
    }

    public function readAnimal(int $animalId): ?object
    {
        DB::connection('animals')->statement('CALL sp_animal_read(?)', [$animalId]);
        $result = DB::connection('animals')->select('SELECT * FROM animal WHERE id = ?', [$animalId]);
        return $result[0] ?? null;
    }

    public function readPaginatedAnimals(array $filters, int $offset = 0, int $limit = 12): array
    {
        DB::connection('animals')->statement('CALL sp_animal_read_paginated(?, ?, ?, ?, ?, ?, ?, ?)', [
            $filters['search'] ?? null, $filters['species'] ?? null, $filters['health_details'] ?? null,
            $filters['adoption_status'] ?? null, $filters['gender'] ?? null, $filters['rescue_ids'] ?? null,
            $offset, $limit,
        ]);

        $data = DB::connection('animals')->select('SELECT * FROM animal LIMIT ? OFFSET ?', [$limit, $offset]);
        $total = DB::connection('animals')->select('SELECT COUNT(*) as total FROM animal')[0]->total;

        return ['data' => $data, 'total' => $total];
    }

    public function updateAnimal(int $animalId, array $data): array
    {
        $this->setAuditContext();
        $user = Auth::user();

        try {
            DB::connection('animals')->statement('CALL sp_animal_update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_status, @o_message)', [
                $animalId, $data['name'], $data['species'] ?? null, $data['health_details'] ?? null,
                $data['age'] ?? null, $data['gender'] ?? 'Unknown', $data['weight'] ?? null,
                $data['slotID'] ?? null, $user->id ?? null, $user->name ?? null, $user->email ?? null,
            ]);
            $result = DB::connection('animals')->select('SELECT @o_status as status, @o_message as message')[0];
            return ['success' => $result->status === 'success', 'message' => $result->message];
        } catch (QueryException $e) {
            $msg = $this->extractTriggerValidationMessage($e);
            if ($msg) return ['success' => false, 'message' => $msg];
            throw $e;
        }
    }

    public function deleteAnimal(int $animalId): array
    {
        $this->setAuditContext();
        $user = Auth::user();

        try {
            DB::connection('animals')->statement('CALL sp_animal_delete(?, ?, ?, ?, @o_animal_name, @o_slot_id, @o_status, @o_message)', [
                $animalId, $user->id ?? null, $user->name ?? null, $user->email ?? null,
            ]);
            $result = DB::connection('animals')->select('SELECT @o_animal_name as animal_name, @o_slot_id as slot_id, @o_status as status, @o_message as message')[0];
            return ['success' => $result->status === 'success', 'animal_name' => $result->animal_name, 'slot_id' => $result->slot_id, 'message' => $result->message];
        } catch (QueryException $e) {
            $msg = $this->extractTriggerValidationMessage($e);
            if ($msg) return ['success' => false, 'animal_name' => null, 'slot_id' => null, 'message' => $msg];
            throw $e;
        }
    }

    public function assignSlot(int $animalId, ?int $slotId): array
    {
        $this->setAuditContext();
        $user = Auth::user();

        try {
            DB::connection('animals')->statement('CALL sp_animal_assign_slot(?, ?, ?, ?, ?, @o_previous_slot_id, @o_status, @o_message)', [
                $animalId, $slotId, $user->id ?? null, $user->name ?? null, $user->email ?? null,
            ]);
            $result = DB::connection('animals')->select('SELECT @o_previous_slot_id as previous_slot_id, @o_status as status, @o_message as message')[0];
            return ['success' => $result->status === 'success', 'previous_slot_id' => $result->previous_slot_id, 'message' => $result->message];
        } catch (QueryException $e) {
            $msg = $this->extractTriggerValidationMessage($e);
            if ($msg) return ['success' => false, 'previous_slot_id' => null, 'message' => $msg];
            throw $e;
        }
    }
}
