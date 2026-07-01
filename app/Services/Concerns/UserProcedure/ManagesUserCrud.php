<?php

namespace App\Services\Concerns\UserProcedure;

use Illuminate\Support\Facades\DB;

trait ManagesUserCrud
{
    public function createUser(array $data): array
    {
        $audit = $this->getAuditContext();

        $result = DB::connection('users')->select('SELECT * FROM fn_user_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $data['name'],
            $data['email'],
            $data['password'],
            $data['phoneNum'] ?? null,
            $data['address'] ?? null,
            $data['city'] ?? null,
            $data['state'] ?? null,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'user_id' => $row->o_user_id ?? null,
            'message' => $row->o_message ?? 'Failed to create user',
        ];
    }

    public function readUser(int $userId): ?object
    {
        $result = DB::connection('users')->select('SELECT * FROM sp_user_read(?)', [$userId]);

        return $result[0] ?? null;
    }

    public function updateUser(int $userId, array $data): array
    {
        $audit = $this->getAuditContext();

        $result = DB::connection('users')->select('SELECT * FROM fn_user_update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $userId,
            $data['name'],
            $data['email'],
            $data['phoneNum'] ?? null,
            $data['address'] ?? null,
            $data['city'] ?? null,
            $data['state'] ?? null,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'message' => $row->o_message ?? 'Failed to update user',
        ];
    }

    public function updateUserPassword(int $userId, string $newPassword): array
    {
        $audit = $this->getAuditContext();

        $result = DB::connection('users')->select('SELECT * FROM fn_user_update_password(?, ?, ?, ?, ?, ?)', [
            $userId,
            $newPassword,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'message' => $row->o_message ?? 'Failed to update password',
        ];
    }

    public function deleteUser(int $userId): array
    {
        $audit = $this->getAuditContext();

        $result = DB::connection('users')->select('SELECT * FROM fn_user_delete(?, ?, ?, ?, ?)', [
            $userId,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'user_name' => $row->o_user_name ?? null,
            'message' => $row->o_message ?? 'Failed to delete user',
        ];
    }
}
