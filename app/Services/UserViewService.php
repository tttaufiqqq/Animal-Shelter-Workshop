<?php

namespace App\Services;

use App\Services\Concerns\UserView\UserDashboardStats;
use App\Services\Concerns\UserView\UserProfileViews;
use App\Services\Concerns\UserView\UserSearchAndFilters;
use App\Services\Concerns\UserView\UserSecurityViews;

/**
 * Ultra-fast queries using optimized database views
 *
 * Performance Benefits:
 * - v_user_full_profile: 2-3x faster than manual JOINs
 * - v_user_account_stats: 50-100x faster (materialized, no table scan)
 * - v_adopter_profile_stats: 50-100x faster (materialized, no table scan)
 * - v_high_risk_users: 5-10x faster (pre-filtered, pre-calculated)
 */
class UserViewService
{
    use UserProfileViews,
        UserDashboardStats,
        UserSecurityViews,
        UserSearchAndFilters;
}
