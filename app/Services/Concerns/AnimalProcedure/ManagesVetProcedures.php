<?php

namespace App\Services\Concerns\AnimalProcedure;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ManagesVetProcedures
{
    public function createVet(array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        try {
            DB::connection('animals')->statement('CALL sp_vet_create(?, ?, ?, ?, ?, ?, ?, ?, ?, @o_vet_id, @o_status, @o_message)', [
                $data['name'],
                $data['email'] ?? null,
                $data['contactNum'] ?? null,
                $data['specialization'] ?? null,
                $data['license_no'] ?? null,
                $data['clinicID'] ?? null,
                $user->id ?? null,
                $user->name ?? null,
                $user->email ?? null,
            ]);

            $result = DB::connection('animals')->select('SELECT @o_vet_id as vet_id, @o_status as status, @o_message as message')[0];

            $status = $result->status ?? 'error';
            $message = $result->message ?? ($status === 'success' ? 'Veterinarian added successfully!' : 'Failed to add veterinarian. Please try again.');

            $this->writeAuditLog('vet', 'created', 'Vet', $result->vet_id, null, $status === 'success' ? $data : null, $status);

            return ['success' => $status === 'success', 'vet_id' => $result->vet_id, 'message' => $message];
        } catch (QueryException $e) {
            $validationMessage = $this->extractTriggerValidationMessage($e);
            if ($validationMessage) {
                $this->writeAuditLog('vet', 'created', 'Vet', null, null, $data, 'validation_error');
                return ['success' => false, 'vet_id' => null, 'message' => $validationMessage];
            }
            throw $e;
        }
    }

    public function readAllVets(): array
    {
        DB::connection('animals')->statement('CALL sp_vet_read_all()');

        return DB::connection('animals')->select('
            SELECT
                v.id, v.name, v.email, v.contactNum, v.specialization, v.license_no,
                v.clinicID, c.name as clinic_name, v.created_at, v.updated_at
            FROM vet v
            LEFT JOIN clinic c ON v.clinicID = c.id
            ORDER BY v.name
        ');
    }

    public function updateVet(int $vetId, array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        try {
            DB::connection('animals')->statement('CALL sp_vet_update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_status, @o_message)', [
                $vetId,
                $data['name'],
                $data['email'] ?? null,
                $data['contactNum'] ?? null,
                $data['specialization'] ?? null,
                $data['license_no'] ?? null,
                $data['clinicID'] ?? null,
                $user->id ?? null,
                $user->name ?? null,
                $user->email ?? null,
            ]);

            $result = DB::connection('animals')->select('SELECT @o_status as status, @o_message as message')[0];

            return ['success' => $result->status === 'success', 'message' => $result->message];
        } catch (QueryException $e) {
            $validationMessage = $this->extractTriggerValidationMessage($e);
            if ($validationMessage) {
                return ['success' => false, 'message' => $validationMessage];
            }
            throw $e;
        }
    }

    public function deleteVet(int $vetId): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        try {
            DB::connection('animals')->statement('CALL sp_vet_delete(?, ?, ?, ?, @o_status, @o_message)', [
                $vetId,
                $user->id ?? null,
                $user->name ?? null,
                $user->email ?? null,
            ]);

            $result = DB::connection('animals')->select('SELECT @o_status as status, @o_message as message')[0];

            return ['success' => $result->status === 'success', 'message' => $result->message];
        } catch (QueryException $e) {
            $validationMessage = $this->extractTriggerValidationMessage($e);
            if ($validationMessage) {
                return ['success' => false, 'message' => $validationMessage];
            }
            throw $e;
        }
    }
}
