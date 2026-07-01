<?php

namespace App\Services\Concerns\UserProcedure;

use Illuminate\Support\Facades\DB;

trait ManagesUserRoles
{
    public function assignRole(int $userId, int $roleId): array
    {
        $audit = $this->getAuditContext();

        $result = DB::connection('users')->select('SELECT * FROM fn_user_assign_role(?, ?, ?, ?, ?, ?)', [
            $userId,
            $roleId,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'message' => $row->o_message ?? 'Failed to assign role',
        ];
    }

    public function revokeRole(int $userId, int $roleId): array
    {
        $audit = $this->getAuditContext();

        $result = DB::connection('users')->select('SELECT * FROM fn_user_revoke_role(?, ?, ?, ?, ?, ?)', [
            $userId,
            $roleId,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'message' => $row->o_message ?? 'Failed to revoke role',
        ];
    }
}
