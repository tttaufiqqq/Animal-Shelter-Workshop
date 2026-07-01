<?php

namespace App\Services\Concerns\ReportingProcedure;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ManagesImageProcedures
{
    public function createImage(array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('reporting')->statement('CALL sp_image_create(?, ?, ?, ?, ?, ?, ?, @o_image_id, @o_status, @o_message)', [
            $data['image_path'],
            $data['reportID'] ?? null,
            $data['animalID'] ?? null,
            $data['clinicID'] ?? null,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('reporting')->select('SELECT @o_image_id as image_id, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'image_id' => $result->image_id,
            'message' => $result->message,
        ];
    }

    public function deleteImage(int $imageId): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('reporting')->statement('CALL sp_image_delete(?, ?, ?, ?, @o_image_path, @o_status, @o_message)', [
            $imageId,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('reporting')->select('SELECT @o_image_path as image_path, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'image_path' => $result->image_path,
            'message' => $result->message,
        ];
    }

    public function readImagesByReport(int $reportId): array
    {
        DB::connection('reporting')->statement('CALL sp_image_read_by_report(?)', [$reportId]);

        return DB::connection('reporting')->select('
            SELECT * FROM image
            WHERE reportID = ?
            ORDER BY created_at
        ', [$reportId]);
    }

    public function readImagesByAnimal(int $animalId): array
    {
        DB::connection('reporting')->statement('CALL sp_image_read_by_animal(?)', [$animalId]);

        return DB::connection('reporting')->select('
            SELECT * FROM image
            WHERE animalID = ?
            ORDER BY created_at
        ', [$animalId]);
    }
}
