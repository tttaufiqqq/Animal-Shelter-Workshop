<?php

namespace App\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShafiqahProcedureService
{
    /**
     * Extract validation message from a trigger exception
     *
     * When MySQL triggers use SIGNAL SQLSTATE '45000', they throw an exception
     * with the custom MESSAGE_TEXT. This method extracts that message.
     *
     * @param QueryException $e
     * @return string|null The validation message if it's a trigger error, null otherwise
     */
    protected function extractTriggerValidationMessage(QueryException $e): ?string
    {
        // SQLSTATE 45000 is used for custom validation errors in triggers
        if (str_contains($e->getMessage(), '45000')) {
            // Extract the message text from the exception
            // Format: SQLSTATE[45000]: <<Unknown error>>: 1644 Custom message here
            if (preg_match('/1644\s+(.+?)(?:\s*\(|$)/i', $e->getMessage(), $matches)) {
                return trim($matches[1]);
            }
            // Alternative format without error code
            if (preg_match('/45000.*?:\s*(.+?)(?:\s*\(|$)/i', $e->getMessage(), $matches)) {
                return trim($matches[1]);
            }
        }
        return null;
    }

    /**
     * Set audit context variables for MySQL triggers
     *
     * NOTE: Triggers have been disabled. Auditing is now handled at application level
     * via writeAuditLog() method which writes to taufiq's centralized audit_logs table.
     * This method is kept for backward compatibility but does nothing.
     */
    protected function setAuditContext(): void
    {
        // DISABLED: MySQL triggers removed - using centralized auditing in taufiq database
        // No longer need to set session variables for triggers
        return;
    }

    /**
     * Write audit log to centralized taufiq database
     *
     * @param string $category Entity category (clinic, vet, animal, medical, vaccination)
     * @param string $action Action performed (created, updated, deleted)
     * @param string $entityType Entity class name (Clinic, Vet, Animal, etc.)
     * @param int|null $entityId Entity ID
     * @param array|null $oldValues Previous values (for update/delete)
     * @param array|null $newValues New values (for create/update)
     * @param string $status Outcome status (success, failure, error)
     */
    protected function writeAuditLog(
        string $category,
        string $action,
        string $entityType,
        ?int $entityId,
        ?array $oldValues = null,
        ?array $newValues = null,
        string $status = 'success'
    ): void {
        try {
            $user = Auth::user();

            DB::connection('taufiq')->table('audit_logs')->insert([
                'user_id' => $user->id ?? null,
                'user_name' => $user->name ?? null,
                'user_email' => $user->email ?? null,
                'user_role' => $user ? $user->getRoleNames()->first() : null,
                'category' => $category,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => $newValues ? json_encode($newValues) : null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now(),
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            // Log audit failure but don't break the main operation
            \Log::warning('Failed to write audit log to taufiq: ' . $e->getMessage());
        }
    }

    // ==========================================
    // CLINIC PROCEDURES
    // ==========================================

    /**
     * Create a new clinic
     *
     * @param array $data ['name', 'address', 'contactNum', 'latitude', 'longitude']
     * @return array ['success' => bool, 'clinic_id' => int|null, 'message' => string]
     */
    public function createClinic(array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        try {
            DB::connection('shafiqah')->statement('CALL sp_clinic_create(?, ?, ?, ?, ?, ?, ?, ?, @o_clinic_id, @o_status, @o_message)', [
                $data['name'],
                $data['address'] ?? null,
                $data['contactNum'] ?? null,
                $data['latitude'] ?? null,
                $data['longitude'] ?? null,
                $user->id ?? null,
                $user->name ?? null,
                $user->email ?? null,
            ]);

            $result = DB::connection('shafiqah')->select('SELECT @o_clinic_id as clinic_id, @o_status as status, @o_message as message')[0];

            return [
                'success' => $result->status === 'success',
                'clinic_id' => $result->clinic_id,
                'message' => $result->message,
            ];
        } catch (QueryException $e) {
            // Check if this is a validation trigger error
            $validationMessage = $this->extractTriggerValidationMessage($e);
            if ($validationMessage) {
                return [
                    'success' => false,
                    'clinic_id' => null,
                    'message' => $validationMessage,
                ];
            }
            throw $e; // Re-throw if not a validation error
        }
    }

    /**
     * Read a single clinic by ID
     *
     * @param int $clinicId
     * @return object|null
     */
    public function readClinic(int $clinicId): ?object
    {
        DB::connection('shafiqah')->statement('CALL sp_clinic_read(?)', [$clinicId]);

        $result = DB::connection('shafiqah')->select('SELECT * FROM clinic WHERE id = ?', [$clinicId]);

        return $result[0] ?? null;
    }

    /**
     * Read all clinics with vet count
     *
     * @return array
     */
    public function readAllClinics(): array
    {
        DB::connection('shafiqah')->statement('CALL sp_clinic_read_all()');

        return DB::connection('shafiqah')->select('
            SELECT
                c.id, c.name, c.address, c.contactNum, c.latitude, c.longitude,
                c.created_at, c.updated_at, COUNT(v.id) as vet_count
            FROM clinic c
            LEFT JOIN vet v ON c.id = v.clinicID
            GROUP BY c.id, c.name, c.address, c.contactNum, c.latitude, c.longitude, c.created_at, c.updated_at
            ORDER BY c.name
        ');
    }

    /**
     * Update a clinic
     *
     * @param int $clinicId
     * @param array $data ['name', 'address', 'contactNum', 'latitude', 'longitude']
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateClinic(int $clinicId, array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        try {
            DB::connection('shafiqah')->statement('CALL sp_clinic_update(?, ?, ?, ?, ?, ?, ?, ?, ?, @o_status, @o_message)', [
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

            $result = DB::connection('shafiqah')->select('SELECT @o_status as status, @o_message as message')[0];

            return [
                'success' => $result->status === 'success',
                'message' => $result->message,
            ];
        } catch (QueryException $e) {
            $validationMessage = $this->extractTriggerValidationMessage($e);
            if ($validationMessage) {
                return [
                    'success' => false,
                    'message' => $validationMessage,
                ];
            }
            throw $e;
        }
    }

    /**
     * Delete a clinic
     *
     * @param int $clinicId
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteClinic(int $clinicId): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        try {
            DB::connection('shafiqah')->statement('CALL sp_clinic_delete(?, ?, ?, ?, @o_status, @o_message)', [
                $clinicId,
                $user->id ?? null,
                $user->name ?? null,
                $user->email ?? null,
            ]);

            $result = DB::connection('shafiqah')->select('SELECT @o_status as status, @o_message as message')[0];

            return [
                'success' => $result->status === 'success',
                'message' => $result->message,
            ];
        } catch (QueryException $e) {
            $validationMessage = $this->extractTriggerValidationMessage($e);
            if ($validationMessage) {
                return [
                    'success' => false,
                    'message' => $validationMessage,
                ];
            }
            throw $e;
        }
    }

    // ==========================================
    // VET PROCEDURES
    // ==========================================

    /**
     * Create a new vet
     *
     * @param array $data ['name', 'email', 'contactNum', 'specialization', 'license_no', 'clinicID']
     * @return array ['success' => bool, 'vet_id' => int|null, 'message' => string]
     */
    public function createVet(array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        try {
            DB::connection('shafiqah')->statement('CALL sp_vet_create(?, ?, ?, ?, ?, ?, ?, ?, ?, @o_vet_id, @o_status, @o_message)', [
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

            $result = DB::connection('shafiqah')->select('SELECT @o_vet_id as vet_id, @o_status as status, @o_message as message')[0];

            // Handle null/empty status - treat as failure with default message
            $status = $result->status ?? 'error';
            $message = $result->message ?? ($status === 'success' ? 'Veterinarian added successfully!' : 'Failed to add veterinarian. Please try again.');

            // Write audit log to taufiq database
            $this->writeAuditLog(
                'vet',
                'created',
                'Vet',
                $result->vet_id,
                null,
                $status === 'success' ? $data : null,
                $status
            );

            return [
                'success' => $status === 'success',
                'vet_id' => $result->vet_id,
                'message' => $message,
            ];
        } catch (QueryException $e) {
            $validationMessage = $this->extractTriggerValidationMessage($e);
            if ($validationMessage) {
                // Write audit log for validation failure
                $this->writeAuditLog('vet', 'created', 'Vet', null, null, $data, 'validation_error');
                return [
                    'success' => false,
                    'vet_id' => null,
                    'message' => $validationMessage,
                ];
            }
            throw $e;
        }
    }

    /**
     * Read all vets with clinic info
     *
     * @return array
     */
    public function readAllVets(): array
    {
        DB::connection('shafiqah')->statement('CALL sp_vet_read_all()');

        return DB::connection('shafiqah')->select('
            SELECT
                v.id, v.name, v.email, v.contactNum, v.specialization, v.license_no,
                v.clinicID, c.name as clinic_name, v.created_at, v.updated_at
            FROM vet v
            LEFT JOIN clinic c ON v.clinicID = c.id
            ORDER BY v.name
        ');
    }

    /**
     * Update a vet
     *
     * @param int $vetId
     * @param array $data ['name', 'email', 'contactNum', 'specialization', 'license_no', 'clinicID']
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateVet(int $vetId, array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        try {
            DB::connection('shafiqah')->statement('CALL sp_vet_update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_status, @o_message)', [
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

            $result = DB::connection('shafiqah')->select('SELECT @o_status as status, @o_message as message')[0];

            return [
                'success' => $result->status === 'success',
                'message' => $result->message,
            ];
        } catch (QueryException $e) {
            $validationMessage = $this->extractTriggerValidationMessage($e);
            if ($validationMessage) {
                return [
                    'success' => false,
                    'message' => $validationMessage,
                ];
            }
            throw $e;
        }
    }

    /**
     * Delete a vet
     *
     * @param int $vetId
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteVet(int $vetId): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        try {
            DB::connection('shafiqah')->statement('CALL sp_vet_delete(?, ?, ?, ?, @o_status, @o_message)', [
                $vetId,
                $user->id ?? null,
                $user->name ?? null,
                $user->email ?? null,
            ]);

            $result = DB::connection('shafiqah')->select('SELECT @o_status as status, @o_message as message')[0];

            return [
                'success' => $result->status === 'success',
                'message' => $result->message,
            ];
        } catch (QueryException $e) {
            $validationMessage = $this->extractTriggerValidationMessage($e);
            if ($validationMessage) {
                return [
                    'success' => false,
                    'message' => $validationMessage,
                ];
            }
            throw $e;
        }
    }

    // ==========================================
    // ANIMAL PROCEDURES
    // ==========================================

    /**
     * Create a new animal
     *
     * @param array $data ['name', 'species', 'health_details', 'age', 'gender', 'weight', 'adoption_status', 'rescueID', 'slotID']
     * @return array ['success' => bool, 'animal_id' => int|null, 'message' => string]
     */
    public function createAnimal(array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        try {
            DB::connection('shafiqah')->statement('CALL sp_animal_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_animal_id, @o_status, @o_message)', [
                $data['name'],
                $data['species'] ?? null,
                $data['health_details'] ?? null,
                $data['age'] ?? null,
                $data['gender'] ?? 'Unknown',
                $data['weight'] ?? null,
                $data['adoption_status'] ?? 'Not Adopted',
                $data['rescueID'] ?? null,
                $data['slotID'] ?? null,
                $user->id ?? null,
                $user->name ?? null,
                $user->email ?? null,
            ]);

            $result = DB::connection('shafiqah')->select('SELECT @o_animal_id as animal_id, @o_status as status, @o_message as message')[0];

            return [
                'success' => $result->status === 'success',
                'animal_id' => $result->animal_id,
                'message' => $result->message,
            ];
        } catch (QueryException $e) {
            $validationMessage = $this->extractTriggerValidationMessage($e);
            if ($validationMessage) {
                return [
                    'success' => false,
                    'animal_id' => null,
                    'message' => $validationMessage,
                ];
            }
            throw $e;
        }
    }

    /**
     * Read a single animal by ID
     *
     * @param int $animalId
     * @return object|null
     */
    public function readAnimal(int $animalId): ?object
    {
        DB::connection('shafiqah')->statement('CALL sp_animal_read(?)', [$animalId]);

        $result = DB::connection('shafiqah')->select('SELECT * FROM animal WHERE id = ?', [$animalId]);

        return $result[0] ?? null;
    }

    /**
     * Read paginated animals with filters
     *
     * @param array $filters ['search', 'species', 'health_details', 'adoption_status', 'gender', 'rescue_ids']
     * @param int $offset
     * @param int $limit
     * @return array ['data' => array, 'total' => int]
     */
    public function readPaginatedAnimals(array $filters, int $offset = 0, int $limit = 12): array
    {
        DB::connection('shafiqah')->statement('CALL sp_animal_read_paginated(?, ?, ?, ?, ?, ?, ?, ?)', [
            $filters['search'] ?? null,
            $filters['species'] ?? null,
            $filters['health_details'] ?? null,
            $filters['adoption_status'] ?? null,
            $filters['gender'] ?? null,
            $filters['rescue_ids'] ?? null,
            $offset,
            $limit,
        ]);

        // First result set: paginated data
        $data = DB::connection('shafiqah')->select('SELECT * FROM animal LIMIT ? OFFSET ?', [$limit, $offset]);

        // Second result set: total count
        $total = DB::connection('shafiqah')->select('SELECT COUNT(*) as total FROM animal')[0]->total;

        return [
            'data' => $data,
            'total' => $total,
        ];
    }

    /**
     * Update an animal
     *
     * @param int $animalId
     * @param array $data ['name', 'species', 'health_details', 'age', 'gender', 'weight', 'slotID']
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateAnimal(int $animalId, array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        try {
            DB::connection('shafiqah')->statement('CALL sp_animal_update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_status, @o_message)', [
                $animalId,
                $data['name'],
                $data['species'] ?? null,
                $data['health_details'] ?? null,
                $data['age'] ?? null,
                $data['gender'] ?? 'Unknown',
                $data['weight'] ?? null,
                $data['slotID'] ?? null,
                $user->id ?? null,
                $user->name ?? null,
                $user->email ?? null,
            ]);

            $result = DB::connection('shafiqah')->select('SELECT @o_status as status, @o_message as message')[0];

            return [
                'success' => $result->status === 'success',
                'message' => $result->message,
            ];
        } catch (QueryException $e) {
            $validationMessage = $this->extractTriggerValidationMessage($e);
            if ($validationMessage) {
                return [
                    'success' => false,
                    'message' => $validationMessage,
                ];
            }
            throw $e;
        }
    }

    /**
     * Delete an animal
     *
     * @param int $animalId
     * @return array ['success' => bool, 'animal_name' => string|null, 'slot_id' => int|null, 'message' => string]
     */
    public function deleteAnimal(int $animalId): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        try {
            DB::connection('shafiqah')->statement('CALL sp_animal_delete(?, ?, ?, ?, @o_animal_name, @o_slot_id, @o_status, @o_message)', [
                $animalId,
                $user->id ?? null,
                $user->name ?? null,
                $user->email ?? null,
            ]);

            $result = DB::connection('shafiqah')->select('SELECT @o_animal_name as animal_name, @o_slot_id as slot_id, @o_status as status, @o_message as message')[0];

            return [
                'success' => $result->status === 'success',
                'animal_name' => $result->animal_name,
                'slot_id' => $result->slot_id,
                'message' => $result->message,
            ];
        } catch (QueryException $e) {
            $validationMessage = $this->extractTriggerValidationMessage($e);
            if ($validationMessage) {
                return [
                    'success' => false,
                    'animal_name' => null,
                    'slot_id' => null,
                    'message' => $validationMessage,
                ];
            }
            throw $e;
        }
    }

    /**
     * Assign a slot to an animal
     *
     * @param int $animalId
     * @param int|null $slotId
     * @return array ['success' => bool, 'previous_slot_id' => int|null, 'message' => string]
     */
    public function assignSlot(int $animalId, ?int $slotId): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        try {
            DB::connection('shafiqah')->statement('CALL sp_animal_assign_slot(?, ?, ?, ?, ?, @o_previous_slot_id, @o_status, @o_message)', [
                $animalId,
                $slotId,
                $user->id ?? null,
                $user->name ?? null,
                $user->email ?? null,
            ]);

            $result = DB::connection('shafiqah')->select('SELECT @o_previous_slot_id as previous_slot_id, @o_status as status, @o_message as message')[0];

            return [
                'success' => $result->status === 'success',
                'previous_slot_id' => $result->previous_slot_id,
                'message' => $result->message,
            ];
        } catch (QueryException $e) {
            $validationMessage = $this->extractTriggerValidationMessage($e);
            if ($validationMessage) {
                return [
                    'success' => false,
                    'previous_slot_id' => null,
                    'message' => $validationMessage,
                ];
            }
            throw $e;
        }
    }

    // ==========================================
    // MEDICAL PROCEDURES
    // ==========================================

    /**
     * Create a medical record
     *
     * @param array $data ['animalID', 'treatment_type', 'diagnosis', 'action', 'remarks', 'vetID', 'costs']
     * @return array ['success' => bool, 'medical_id' => int|null, 'message' => string]
     */
    public function createMedical(array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        try {
            DB::connection('shafiqah')->statement('CALL sp_medical_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_medical_id, @o_status, @o_message)', [
                $data['animalID'],
                $data['treatment_type'] ?? null,
                $data['diagnosis'] ?? null,
                $data['action'] ?? null,
                $data['remarks'] ?? null,
                $data['vetID'] ?? null,
                $data['costs'] ?? null,
                $user->id ?? null,
                $user->name ?? null,
                $user->email ?? null,
            ]);

            $result = DB::connection('shafiqah')->select('SELECT @o_medical_id as medical_id, @o_status as status, @o_message as message')[0];

            return [
                'success' => $result->status === 'success',
                'medical_id' => $result->medical_id,
                'message' => $result->message,
            ];
        } catch (QueryException $e) {
            $validationMessage = $this->extractTriggerValidationMessage($e);
            if ($validationMessage) {
                return [
                    'success' => false,
                    'medical_id' => null,
                    'message' => $validationMessage,
                ];
            }
            throw $e;
        }
    }

    /**
     * Read all medical records for an animal
     *
     * @param int $animalId
     * @return array
     */
    public function readMedicalByAnimal(int $animalId): array
    {
        DB::connection('shafiqah')->statement('CALL sp_medical_read_by_animal(?)', [$animalId]);

        return DB::connection('shafiqah')->select('
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

    // ==========================================
    // VACCINATION PROCEDURES
    // ==========================================

    /**
     * Create a vaccination record
     *
     * @param array $data ['animalID', 'name', 'type', 'next_due_date', 'remarks', 'vetID', 'costs']
     * @return array ['success' => bool, 'vaccination_id' => int|null, 'message' => string]
     */
    public function createVaccination(array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        try {
            DB::connection('shafiqah')->statement('CALL sp_vaccination_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_vaccination_id, @o_status, @o_message)', [
                $data['animalID'],
                $data['name'],
                $data['type'] ?? null,
                $data['next_due_date'] ?? null,
                $data['remarks'] ?? null,
                $data['vetID'] ?? null,
                $data['costs'] ?? null,
                $user->id ?? null,
                $user->name ?? null,
                $user->email ?? null,
            ]);

            $result = DB::connection('shafiqah')->select('SELECT @o_vaccination_id as vaccination_id, @o_status as status, @o_message as message')[0];

            return [
                'success' => $result->status === 'success',
                'vaccination_id' => $result->vaccination_id,
                'message' => $result->message,
            ];
        } catch (QueryException $e) {
            $validationMessage = $this->extractTriggerValidationMessage($e);
            if ($validationMessage) {
                return [
                    'success' => false,
                    'vaccination_id' => null,
                    'message' => $validationMessage,
                ];
            }
            throw $e;
        }
    }

    /**
     * Read all vaccination records for an animal
     *
     * @param int $animalId
     * @return array
     */
    public function readVaccinationByAnimal(int $animalId): array
    {
        DB::connection('shafiqah')->statement('CALL sp_vaccination_read_by_animal(?)', [$animalId]);

        return DB::connection('shafiqah')->select('
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

    // ==========================================
    // ANIMAL PROFILE PROCEDURES
    // ==========================================

    /**
     * Create or update an animal profile
     *
     * @param int $animalId
     * @param array $data ['age', 'size', 'energy_level', 'good_with_kids', 'good_with_pets', 'temperament', 'medical_needs']
     * @return array ['success' => bool, 'profile_id' => int|null, 'message' => string]
     */
    public function upsertAnimalProfile(int $animalId, array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        try {
            DB::connection('shafiqah')->statement('CALL sp_animal_profile_upsert(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_profile_id, @o_status, @o_message)', [
                $animalId,
                $data['age'] ?? null,
                $data['size'] ?? null,
                $data['energy_level'] ?? null,
                $data['good_with_kids'] ?? false,
                $data['good_with_pets'] ?? false,
                $data['temperament'] ?? null,
                $data['medical_needs'] ?? null,
                $user->id ?? null,
                $user->name ?? null,
                $user->email ?? null,
            ]);

            $result = DB::connection('shafiqah')->select('SELECT @o_profile_id as profile_id, @o_status as status, @o_message as message')[0];

            return [
                'success' => $result->status === 'success',
                'profile_id' => $result->profile_id,
                'message' => $result->message,
            ];
        } catch (QueryException $e) {
            $validationMessage = $this->extractTriggerValidationMessage($e);
            if ($validationMessage) {
                return [
                    'success' => false,
                    'profile_id' => null,
                    'message' => $validationMessage,
                ];
            }
            throw $e;
        }
    }

    /**
     * Read animal profile by animal ID
     *
     * @param int $animalId
     * @return object|null
     */
    public function readAnimalProfile(int $animalId): ?object
    {
        DB::connection('shafiqah')->statement('CALL sp_animal_profile_read(?)', [$animalId]);

        $result = DB::connection('shafiqah')->select('SELECT * FROM animal_profile WHERE animalID = ?', [$animalId]);

        return $result[0] ?? null;
    }
}
