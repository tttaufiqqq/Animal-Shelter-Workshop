<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $conn   = DB::connection('users');
        $sqlDir = database_path('sql/taufiq-users/high_performance_views');

        $conn->unprepared(require "$sqlDir/v_user_full_profile.php");
        $conn->unprepared(require "$sqlDir/v_user_account_stats.php");
        $conn->unprepared(require "$sqlDir/v_adopter_profile_stats.php");
        $conn->unprepared(require "$sqlDir/v_high_risk_users.php");
        $conn->unprepared(require "$sqlDir/v_active_users_with_profiles.php");
        $conn->unprepared(require "$sqlDir/v_user_activity_last_30_days.php");
        $conn->unprepared(require "$sqlDir/refresh_all_taufiq_stats.php");
    }

    public function down(): void
    {
        $conn = DB::connection('users');

        $conn->unprepared('DROP FUNCTION IF EXISTS refresh_all_taufiq_stats');
        $conn->unprepared('DROP VIEW IF EXISTS v_user_full_profile CASCADE');
        $conn->unprepared('DROP VIEW IF EXISTS v_high_risk_users CASCADE');
        $conn->unprepared('DROP VIEW IF EXISTS v_active_users_with_profiles CASCADE');
        $conn->unprepared('DROP VIEW IF EXISTS v_user_activity_last_30_days CASCADE');
        $conn->unprepared('DROP MATERIALIZED VIEW IF EXISTS v_user_account_stats CASCADE');
        $conn->unprepared('DROP MATERIALIZED VIEW IF EXISTS v_adopter_profile_stats CASCADE');
    }
};
