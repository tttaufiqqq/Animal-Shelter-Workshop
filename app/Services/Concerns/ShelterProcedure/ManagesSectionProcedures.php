<?php

namespace App\Services\Concerns\ShelterProcedure;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ManagesSectionProcedures
{
    public function createSection(array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('shelter')->statement('CALL sp_section_create(?, ?, ?, ?, ?, @o_section_id, @o_status, @o_message)', [
            $data['name'],
            $data['description'],
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('shelter')->select('SELECT @o_section_id as section_id, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'section_id' => $result->section_id,
            'message' => $result->message,
        ];
    }

    public function readSection(int $sectionId): ?object
    {
        DB::connection('shelter')->statement('CALL sp_section_read(?)', [$sectionId]);

        $result = DB::connection('shelter')->select('SELECT * FROM section WHERE id = ?', [$sectionId]);

        return $result[0] ?? null;
    }

    public function updateSection(int $sectionId, array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('shelter')->statement('CALL sp_section_update(?, ?, ?, ?, ?, ?, @o_status, @o_message)', [
            $sectionId,
            $data['name'],
            $data['description'],
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

    public function deleteSection(int $sectionId): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('shelter')->statement('CALL sp_section_delete(?, ?, ?, ?, @o_has_slots, @o_status, @o_message)', [
            $sectionId,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('shelter')->select('SELECT @o_has_slots as has_slots, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'has_slots' => (bool) $result->has_slots,
            'message' => $result->message,
        ];
    }
}
