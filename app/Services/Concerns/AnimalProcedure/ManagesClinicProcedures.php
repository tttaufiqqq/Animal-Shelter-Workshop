<?php

namespace App\Services\Concerns\AnimalProcedure;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ManagesClinicProcedures
{
    public function createClinic(array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        try {
            DB::connection('animals')->statement('CALL sp_clinic_create(?, ?, ?, ?, ?, ?, ?, ?, @o_clinic_id, @o_status, @o_message)', [
                $data['name'],
                $data['address'] ?? null,
                $data['contactNum'] ?? null,
                $data['latitude'] ?? null,
                $data['longitude'] ?? null,
                $user->id ?? null,
                $user->name ?? null,
                $user->email ?? null,
            ]);

            $result = DB::connection('animals')->select('SELECT @o_clinic_id as clinic_id, @o_status as status, @o_message as message')[0];

            return [
                'success' => $result->status === 'success',
                'clinic_id' => $result->clinic_id,
                'message' => $result->message,
            ];
        } catch (QueryException $e) {
            $validationMessage = $this->extractTriggerValidationMessage($e);
            if ($validationMessage) {
                return ['success' => false, 'clinic_id' => null, 'message' => $validationMessage];
            }
            throw $e;
        }
    }

    public function readClinic(int $clinicId): ?object
    {
        DB::connection('animals')->statement('CALL sp_clinic_read(?)', [$clinicId]);

        $result = DB::connection('animals')->select('SELECT * FROM clinic WHERE id = ?', [$clinicId]);

        return $result[0] ?? null;
    }

    public function readAllClinics(): array
    {
        DB::connection('animals')->statement('CALL sp_clinic_read_all()');

        return DB::connection('animals')->select('
            SELECT
                c.id, c.name, c.address, c.contactNum, c.latitude, c.longitude,
                c.created_at, c.updated_at, COUNT(v.id) as vet_count
            FROM clinic c
            LEFT JOIN vet v ON c.id = v.clinicID
            GROUP BY c.id, c.name, c.address, c.contactNum, c.latitude, c.longitude, c.created_at, c.updated_at
            ORDER BY c.name
        ');
    }

    public function updateClinic(int $clinicId, array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        try {
            DB::connection('animals')->statement('CALL sp_clinic_update(?, ?, ?, ?, ?, ?, ?, ?, ?, @o_status, @o_message)', [
                $clinicId,
                $data['name'],
                $data['address'] ?? null,
                $data['contactNum'] ?? null,
                $data['latitude'] ?? null,
                $data['longitude'] ?? null,
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

    public function deleteClinic(int $clinicId): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        try {
            DB::connection('animals')->statement('CALL sp_clinic_delete(?, ?, ?, ?, @o_status, @o_message)', [
                $clinicId,
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
