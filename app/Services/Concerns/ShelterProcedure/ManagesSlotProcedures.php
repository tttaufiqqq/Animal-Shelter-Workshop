<?php

namespace App\Services\Concerns\ShelterProcedure;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ManagesSlotProcedures
{
    public function createSlot(array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('shelter')->statement('CALL sp_slot_create(?, ?, ?, ?, ?, ?, ?, @o_slot_id, @o_status, @o_message)', [
            $data['name'],
            $data['sectionID'],
            $data['capacity'],
            $data['status'] ?? 'available',
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('shelter')->select('SELECT @o_slot_id as slot_id, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'slot_id' => $result->slot_id,
            'message' => $result->message,
        ];
    }

    public function readSlot(int $slotId): ?object
    {
        DB::connection('shelter')->statement('CALL sp_slot_read(?)', [$slotId]);

        $result = DB::connection('shelter')->select('SELECT * FROM slot WHERE id = ?', [$slotId]);

        return $result[0] ?? null;
    }

    public function updateSlot(int $slotId, array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('shelter')->statement('CALL sp_slot_update(?, ?, ?, ?, ?, ?, ?, ?, @o_status, @o_message)', [
            $slotId,
            $data['name'],
            $data['sectionID'],
            $data['capacity'],
            $data['status'] ?? null,
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

    public function deleteSlot(int $slotId): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('shelter')->statement('CALL sp_slot_delete(?, ?, ?, ?, @o_has_animals, @o_animal_count, @o_status, @o_message)', [
            $slotId,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('shelter')->select('SELECT @o_has_animals as has_animals, @o_animal_count as animal_count, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'has_animals' => (bool) $result->has_animals,
            'animal_count' => $result->animal_count ?? 0,
            'message' => $result->message,
        ];
    }
}
