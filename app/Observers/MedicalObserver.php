<?php

namespace App\Observers;

use App\Jobs\CreateAuditLog;
use App\Models\Medical;
use Illuminate\Support\Facades\Auth;

class MedicalObserver
{
    /**
     * Handle the Medical "created" event.
     */
    public function created(Medical $medical): void
    {
        $this->logAudit('created', $medical, null);
    }

    /**
     * Handle the Medical "updated" event.
     */
    public function updated(Medical $medical): void
    {
        // Only log if actual changes occurred
        if ($medical->isDirty()) {
            $this->logAudit('updated', $medical, $medical->getChanges());
        }
    }

    /**
     * Handle the Medical "deleted" event.
     */
    public function deleted(Medical $medical): void
    {
        $this->logAudit('deleted', $medical, $medical->getOriginal());
    }

    /**
     * Log audit trail for Medical model changes.
     */
    protected function logAudit($actionType, Medical $medical, $changes)
    {
        $user = Auth::user();

        // Build changed_fields (only for updates)
        $changedFields = null;
        if ($actionType === 'updated' && $changes) {
            $changedFields = [];
            foreach ($changes as $field => $newValue) {
                // Skip timestamps
                if (in_array($field, ['updated_at', 'created_at'])) {
                    continue;
                }

                $changedFields[$field] = [
                    'old' => $medical->getOriginal($field),
                    'new' => $newValue,
                ];
            }

            // Don't log if only timestamps changed
            if (empty($changedFields)) {
                return;
            }
        }

        CreateAuditLog::dispatch([
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'user_name' => $user?->name,
            'action_type' => $actionType,
            'auditable_type' => get_class($medical),
            'auditable_id' => $medical->id,
            'auditable_connection' => $medical->getConnectionName(), // 'shafiqah'
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'changed_fields' => $changedFields,
            'metadata' => [
                'medical_id' => $medical->id,
                'animal_id' => $medical->animalID,
                'treatment_type' => $medical->treatment_type ?? null,
            ],
        ]);
    }
}
