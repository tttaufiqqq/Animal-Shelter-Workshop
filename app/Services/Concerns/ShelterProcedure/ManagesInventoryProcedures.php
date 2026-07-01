<?php

namespace App\Services\Concerns\ShelterProcedure;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ManagesInventoryProcedures
{
    public function createInventory(array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('shelter')->statement('CALL sp_inventory_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_inventory_id, @o_status, @o_message)', [
            $data['slotID'],
            $data['item_name'],
            $data['categoryID'],
            $data['quantity'],
            $data['weight'] ?? null,
            $data['brand'] ?? null,
            $data['status'] ?? 'available',
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('shelter')->select('SELECT @o_inventory_id as inventory_id, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'inventory_id' => $result->inventory_id,
            'message' => $result->message,
        ];
    }

    public function readInventory(int $inventoryId): ?object
    {
        DB::connection('shelter')->statement('CALL sp_inventory_read(?)', [$inventoryId]);

        $result = DB::connection('shelter')->select('SELECT * FROM inventory WHERE id = ?', [$inventoryId]);

        return $result[0] ?? null;
    }

    public function updateInventory(int $inventoryId, array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('shelter')->statement('CALL sp_inventory_update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_status, @o_message)', [
            $inventoryId,
            $data['item_name'],
            $data['categoryID'],
            $data['quantity'],
            $data['weight'] ?? null,
            $data['brand'] ?? null,
            $data['status'],
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

    public function deleteInventory(int $inventoryId): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('shelter')->statement('CALL sp_inventory_delete(?, ?, ?, ?, @o_status, @o_message)', [
            $inventoryId,
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
}
