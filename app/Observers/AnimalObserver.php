<?php

namespace App\Observers;

use App\Jobs\CreateAuditLog;
use App\Models\Animal;
use Illuminate\Support\Facades\Auth;

class AnimalObserver
{
    /**
     * Handle the Animal "created" event.
     */
    public function created(Animal $animal): void
    {
        $this->logAudit('created', $animal, null);
    }

    /**
     * Handle the Animal "updated" event.
     */
    public function updated(Animal $animal): void
    {
        // Only log if actual changes occurred
        if ($animal->isDirty()) {
            $this->logAudit('updated', $animal, $animal->getChanges());
        }
    }

    /**
     * Handle the Animal "deleted" event.
     */
    public function deleted(Animal $animal): void
    {
        $this->logAudit('deleted', $animal, $animal->getOriginal());
    }

    /**
     * Log audit trail for Animal model changes.
     */
    protected function logAudit($actionType, Animal $animal, $changes)
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
                    'old' => $animal->getOriginal($field),
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
            'auditable_type' => get_class($animal),
            'auditable_id' => $animal->id,
            'auditable_connection' => $animal->getConnectionName(), // 'shafiqah'
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'changed_fields' => $changedFields,
            'metadata' => [
                'animal_name' => $animal->name,
                'species' => $animal->species,
            ],
        ]);
    }
}
