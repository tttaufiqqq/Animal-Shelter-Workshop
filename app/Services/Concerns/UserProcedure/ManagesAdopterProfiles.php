<?php

namespace App\Services\Concerns\UserProcedure;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait ManagesAdopterProfiles
{
    public function upsertAdopterProfile(int $adopterId, array $data): array
    {
        $audit = $this->getAuditContext();

        $result = DB::connection('users')->select('SELECT * FROM fn_adopter_profile_upsert(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $adopterId,
            $data['housing_type'] ?? null,
            $data['has_children'] ?? false,
            $data['has_other_pets'] ?? false,
            $data['activity_level'] ?? null,
            $data['experience'] ?? null,
            $data['preferred_species'] ?? null,
            $data['preferred_size'] ?? null,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;
        $success = $row && $row->o_status === 'success';

        if ($success) {
            // ManagesMatching::getMatches() caches scored matches for 300s under
            // this key; without invalidating it here, an edited profile appears
            // to have no effect on matches for up to 5 minutes.
            Cache::forget("animal_matches_user_{$adopterId}");
        }

        return [
            'success' => $success,
            'profile_id' => $row->o_profile_id ?? null,
            'message' => $row->o_message ?? 'Failed to save adopter profile',
        ];
    }

    public function readAdopterProfile(int $adopterId): ?object
    {
        $result = DB::connection('users')->select('SELECT * FROM sp_adopter_profile_read(?)', [$adopterId]);

        return $result[0] ?? null;
    }

    public function deleteAdopterProfile(int $adopterId): array
    {
        $audit = $this->getAuditContext();

        $result = DB::connection('users')->select('SELECT * FROM fn_adopter_profile_delete(?, ?, ?, ?, ?)', [
            $adopterId,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'message' => $row->o_message ?? 'Failed to delete adopter profile',
        ];
    }
}
