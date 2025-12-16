<?php

namespace App\Observers;

use App\Jobs\CreateAuditLog;
use App\Models\Vaccination;
use Illuminate\Support\Facades\Auth;

class VaccinationObserver
{
    /**
     * Handle the Vaccination "created" event.
     */
    public function created(Vaccination $vaccination): void
    {
        $this->logAudit('created', $vaccination, null);
    }

    /**
     * Handle the Vaccination "updated" event.
     */
    public function updated(Vaccination $vaccination): void
    {
        // Only log if actual changes occurred
        if ($vaccination->isDirty()) {
            $this->logAudit('updated', $vaccination, $vaccination->getChanges());
        }
    }

    /**
     * Handle the Vaccination "deleted" event.
     */
    public function deleted(Vaccination $vaccination): void
    {
        $this->logAudit('deleted', $vaccination, $vaccination->getOriginal());
    }

    /**
     * Log audit trail for Vaccination model changes.
     */
    protected function logAudit($actionType, Vaccination $vaccination, $changes)
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
                    'old' => $vaccination->getOriginal($field),
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
            'auditable_type' => get_class($vaccination),
            'auditable_id' => $vaccination->id,
            'auditable_connection' => $vaccination->getConnectionName(), // 'shafiqah'
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'changed_fields' => $changedFields,
            'metadata' => [
                'vaccination_id' => $vaccination->id,
                'animal_id' => $vaccination->animalID,
                'vaccine_name' => $vaccination->vaccine_name ?? null,
            ],
        ]);
    }
}
