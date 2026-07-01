<?php

namespace App\Services\Concerns\UserProcedure;

use Illuminate\Support\Facades\DB;

trait ManagesUserSecurity
{
    public function suspendUser(int $userId, string $reason): array
    {
        $audit = $this->getAuditContext();

        $result = DB::connection('users')->select('SELECT * FROM fn_user_suspend(?, ?, ?, ?, ?, ?)', [
            $userId,
            $reason,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'message' => $row->o_message ?? 'Failed to suspend user',
        ];
    }

    public function lockUser(int $userId, int $durationMinutes, string $reason): array
    {
        $audit = $this->getAuditContext();

        $result = DB::connection('users')->select('SELECT * FROM fn_user_lock(?, ?, ?, ?, ?, ?, ?)', [
            $userId,
            $durationMinutes,
            $reason,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'locked_until' => $row->o_locked_until ?? null,
            'message' => $row->o_message ?? 'Failed to lock user',
        ];
    }

    public function unlockUser(int $userId): array
    {
        $audit = $this->getAuditContext();

        $result = DB::connection('users')->select('SELECT * FROM fn_user_unlock(?, ?, ?, ?, ?)', [
            $userId,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'message' => $row->o_message ?? 'Failed to unlock user',
        ];
    }

    public function forcePasswordReset(int $userId): array
    {
        $audit = $this->getAuditContext();

        $result = DB::connection('users')->select('SELECT * FROM fn_user_force_password_reset(?, ?, ?, ?, ?)', [
            $userId,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'message' => $row->o_message ?? 'Failed to force password reset',
        ];
    }
}
