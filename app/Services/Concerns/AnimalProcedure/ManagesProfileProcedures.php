<?php

namespace App\Services\Concerns\AnimalProcedure;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ManagesProfileProcedures
{
    public function upsertAnimalProfile(int $animalId, array $data): array
    {
        $this->setAuditContext();
        $user = Auth::user();

        try {
            DB::connection('animals')->statement('CALL sp_animal_profile_upsert(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_profile_id, @o_status, @o_message)', [
                $animalId, $data['age'] ?? null, $data['size'] ?? null, $data['energy_level'] ?? null,
                $data['good_with_kids'] ?? false, $data['good_with_pets'] ?? false,
                $data['temperament'] ?? null, $data['medical_needs'] ?? null,
                $user->id ?? null, $user->name ?? null, $user->email ?? null,
            ]);
            $result = DB::connection('animals')->select('SELECT @o_profile_id as profile_id, @o_status as status, @o_message as message')[0];
            return ['success' => $result->status === 'success', 'profile_id' => $result->profile_id, 'message' => $result->message];
        } catch (QueryException $e) {
            $msg = $this->extractTriggerValidationMessage($e);
            if ($msg) return ['success' => false, 'profile_id' => null, 'message' => $msg];
            throw $e;
        }
    }

    public function readAnimalProfile(int $animalId): ?object
    {
        DB::connection('animals')->statement('CALL sp_animal_profile_read(?)', [$animalId]);
        $result = DB::connection('animals')->select('SELECT * FROM animal_profile WHERE animalID = ?', [$animalId]);
        return $result[0] ?? null;
    }
}
