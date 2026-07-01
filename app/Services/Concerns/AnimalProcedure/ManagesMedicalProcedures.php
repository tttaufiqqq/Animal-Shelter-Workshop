<?php

namespace App\Services\Concerns\AnimalProcedure;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ManagesMedicalProcedures
{
    public function createMedical(array $data): array
    {
        $this->setAuditContext();
        $user = Auth::user();

        try {
            DB::connection('animals')->statement('CALL sp_medical_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_medical_id, @o_status, @o_message)', [
                $data['animalID'], $data['treatment_type'] ?? null, $data['diagnosis'] ?? null,
                $data['action'] ?? null, $data['remarks'] ?? null, $data['vetID'] ?? null,
                $data['costs'] ?? null, $user->id ?? null, $user->name ?? null, $user->email ?? null,
            ]);
            $result = DB::connection('animals')->select('SELECT @o_medical_id as medical_id, @o_status as status, @o_message as message')[0];
            return ['success' => $result->status === 'success', 'medical_id' => $result->medical_id, 'message' => $result->message];
        } catch (QueryException $e) {
            $msg = $this->extractTriggerValidationMessage($e);
            if ($msg) return ['success' => false, 'medical_id' => null, 'message' => $msg];
            throw $e;
        }
    }

    public function readMedicalByAnimal(int $animalId): array
    {
        DB::connection('animals')->statement('CALL sp_medical_read_by_animal(?)', [$animalId]);

        return DB::connection('animals')->select('
            SELECT
                m.id, m.treatment_type, m.diagnosis, m.action, m.remarks, m.costs,
                m.animalID, m.vetID, v.name as vet_name, v.specialization as vet_specialization,
                m.created_at, m.updated_at
            FROM medical m
            LEFT JOIN vet v ON m.vetID = v.id
            WHERE m.animalID = ?
            ORDER BY m.created_at DESC
        ', [$animalId]);
    }

    public function createVaccination(array $data): array
    {
        $this->setAuditContext();
        $user = Auth::user();

        try {
            DB::connection('animals')->statement('CALL sp_vaccination_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_vaccination_id, @o_status, @o_message)', [
                $data['animalID'], $data['name'], $data['type'] ?? null, $data['next_due_date'] ?? null,
                $data['remarks'] ?? null, $data['vetID'] ?? null, $data['costs'] ?? null,
                $user->id ?? null, $user->name ?? null, $user->email ?? null,
            ]);
            $result = DB::connection('animals')->select('SELECT @o_vaccination_id as vaccination_id, @o_status as status, @o_message as message')[0];
            return ['success' => $result->status === 'success', 'vaccination_id' => $result->vaccination_id, 'message' => $result->message];
        } catch (QueryException $e) {
            $msg = $this->extractTriggerValidationMessage($e);
            if ($msg) return ['success' => false, 'vaccination_id' => null, 'message' => $msg];
            throw $e;
        }
    }

    public function readVaccinationByAnimal(int $animalId): array
    {
        DB::connection('animals')->statement('CALL sp_vaccination_read_by_animal(?)', [$animalId]);

        return DB::connection('animals')->select('
            SELECT
                v.id, v.name, v.type, v.next_due_date, v.remarks, v.costs,
                v.animalID, v.vetID, vt.name as vet_name, vt.specialization as vet_specialization,
                v.created_at, v.updated_at
            FROM vaccination v
            LEFT JOIN vet vt ON v.vetID = vt.id
            WHERE v.animalID = ?
            ORDER BY v.created_at DESC
        ', [$animalId]);
    }
}
