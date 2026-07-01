<?php

namespace App\Services\Concerns\UserView;

use Illuminate\Support\Facades\DB;

trait UserProfileViews
{
    public function getFullUserProfile(int $userId): ?object
    {
        $result = DB::connection('users')->select(
            'SELECT * FROM v_user_full_profile WHERE id = ?',
            [$userId]
        );

        return $result[0] ?? null;
    }

    public function getActiveUsersWithProfiles(bool $completeProfilesOnly = false): array
    {
        $sql = 'SELECT * FROM v_active_users_with_profiles';

        if ($completeProfilesOnly) {
            $sql .= ' WHERE is_profile_complete = TRUE';
        }

        $sql .= ' ORDER BY readiness_score DESC, profile_created_at DESC';

        return DB::connection('users')->select($sql);
    }

    public function getUsersByReadinessScore(int $minScore = 75): array
    {
        return DB::connection('users')->select(
            'SELECT * FROM v_active_users_with_profiles
             WHERE readiness_score >= ?
             ORDER BY readiness_score DESC',
            [$minScore]
        );
    }
}
