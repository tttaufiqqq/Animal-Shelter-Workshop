<?php

namespace App\Services\Concerns\UserView;

use Illuminate\Support\Facades\DB;

trait UserSearchAndFilters
{
    public function getMostActiveUsers(int $limit = 10): array
    {
        return DB::connection('users')->select(
            "SELECT * FROM v_user_activity_last_30_days
             WHERE activity_status != 'never_active'
             ORDER BY total_actions_30_days DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function getInactiveUsers(): array
    {
        return DB::connection('users')->select(
            "SELECT * FROM v_user_activity_last_30_days
             WHERE activity_status = 'inactive'
             ORDER BY days_since_last_activity DESC"
        );
    }

    public function getAdoptersForAnimal(string $species, ?string $size = null): array
    {
        $sql = "SELECT * FROM v_active_users_with_profiles
                WHERE is_profile_complete = TRUE
                AND (preferred_species = ? OR preferred_species = 'both')";

        $params = [$species];

        if ($size) {
            $sql .= " AND (preferred_size = ? OR preferred_size IS NULL)";
            $params[] = $size;
        }

        $sql .= " ORDER BY readiness_score DESC, profile_created_at DESC";

        return DB::connection('users')->select($sql, $params);
    }

    public function getAdoptersByHousing(string $housingType, bool $hasChildren = false, bool $hasOtherPets = false): array
    {
        return DB::connection('users')->select(
            "SELECT * FROM v_active_users_with_profiles
             WHERE housing_type = ?
             AND has_children = ?
             AND has_other_pets = ?
             AND is_profile_complete = TRUE
             ORDER BY readiness_score DESC",
            [$housingType, $hasChildren, $hasOtherPets]
        );
    }

    public function searchUsers(string $searchTerm): array
    {
        return DB::connection('users')->select(
            "SELECT * FROM v_user_full_profile
             WHERE LOWER(name) LIKE LOWER(?)
             OR LOWER(email) LIKE LOWER(?)
             ORDER BY name",
            ["%{$searchTerm}%", "%{$searchTerm}%"]
        );
    }

    public function getUsersByStatus(string $status): array
    {
        return DB::connection('users')->select(
            'SELECT * FROM v_user_full_profile
             WHERE account_status = ?
             ORDER BY user_created_at DESC',
            [$status]
        );
    }

    public function getSuspendedUsers(): array
    {
        return DB::connection('users')->select(
            "SELECT * FROM v_user_full_profile
             WHERE is_suspended = TRUE
             ORDER BY suspended_at DESC"
        );
    }

    public function getLockedUsers(): array
    {
        return DB::connection('users')->select(
            "SELECT * FROM v_user_full_profile
             WHERE is_locked = TRUE
             ORDER BY locked_until DESC"
        );
    }
}
