<?php

namespace App\Services\Concerns\ShelterProcedure;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ManagesCategoryProcedures
{
    public function createCategory(array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('shelter')->statement('CALL sp_category_create(?, ?, ?, ?, ?, @o_category_id, @o_status, @o_message)', [
            $data['main'],
            $data['sub'],
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('shelter')->select('SELECT @o_category_id as category_id, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'category_id' => $result->category_id,
            'message' => $result->message,
        ];
    }

    public function readCategory(int $categoryId): ?object
    {
        DB::connection('shelter')->statement('CALL sp_category_read(?)', [$categoryId]);

        $result = DB::connection('shelter')->select('SELECT * FROM category WHERE id = ?', [$categoryId]);

        return $result[0] ?? null;
    }

    public function updateCategory(int $categoryId, array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('shelter')->statement('CALL sp_category_update(?, ?, ?, ?, ?, ?, @o_status, @o_message)', [
            $categoryId,
            $data['main'],
            $data['sub'],
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('shelter')->select('SELECT @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'message' => $result->message,
        ];
    }

    public function deleteCategory(int $categoryId): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('shelter')->statement('CALL sp_category_delete(?, ?, ?, ?, @o_has_inventories, @o_status, @o_message)', [
            $categoryId,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('shelter')->select('SELECT @o_has_inventories as has_inventories, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'has_inventories' => (bool) $result->has_inventories,
            'message' => $result->message,
        ];
    }
}
