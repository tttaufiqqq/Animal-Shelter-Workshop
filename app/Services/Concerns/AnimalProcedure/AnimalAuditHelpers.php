<?php

namespace App\Services\Concerns\AnimalProcedure;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait AnimalAuditHelpers
{
    protected function extractTriggerValidationMessage(QueryException $e): ?string
    {
        if (str_contains($e->getMessage(), '45000')) {
            if (preg_match('/1644\s+(.+?)(?:\s*\(|$)/i', $e->getMessage(), $matches)) {
                return trim($matches[1]);
            }
            if (preg_match('/45000.*?:\s*(.+?)(?:\s*\(|$)/i', $e->getMessage(), $matches)) {
                return trim($matches[1]);
            }
        }
        return null;
    }

    protected function setAuditContext(): void
    {
        // DISABLED: MySQL triggers removed - using centralized auditing in taufiq database
        return;
    }

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

            DB::connection('users')->table('audit_logs')->insert([
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
            \Log::warning('Failed to write audit log to taufiq: ' . $e->getMessage());
        }
    }
}
