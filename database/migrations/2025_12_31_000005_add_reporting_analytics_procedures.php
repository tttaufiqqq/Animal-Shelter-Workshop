<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Reporting & Analytics - User statistics, high-risk users, audit summaries
     */
    public function up(): void
    {
        // 1. Get User Account Statistics
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION get_user_account_stats()
            RETURNS TABLE(
                total_users BIGINT,
                active_users BIGINT,
                suspended_users BIGINT,
                locked_users BIGINT,
                users_with_profiles BIGINT,
                avg_failed_login_attempts NUMERIC
            ) AS $$
            BEGIN
                RETURN QUERY
                SELECT
                    COUNT(*)::BIGINT AS total_users,
                    COUNT(*) FILTER (WHERE account_status = 'active')::BIGINT AS active_users,
                    COUNT(*) FILTER (WHERE account_status = 'suspended')::BIGINT AS suspended_users,
                    COUNT(*) FILTER (WHERE account_status = 'locked')::BIGINT AS locked_users,
                    (SELECT COUNT(DISTINCT \"adopterID\")::BIGINT FROM adopter_profile) AS users_with_profiles,
                    ROUND(AVG(failed_login_attempts), 2) AS avg_failed_login_attempts
                FROM users;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // 2. Get High-Risk User Activity
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION get_high_risk_users(threshold INTEGER DEFAULT 3)
            RETURNS TABLE(
                user_id BIGINT,
                user_name VARCHAR,
                user_email VARCHAR,
                failed_attempts INTEGER,
                last_failed_at TIMESTAMP,
                account_status VARCHAR,
                recent_failed_count BIGINT
            ) AS $$
            BEGIN
                RETURN QUERY
                SELECT
                    u.id,
                    u.name,
                    u.email,
                    u.failed_login_attempts,
                    u.last_failed_login_at,
                    u.account_status,
                    COUNT(al.id)::BIGINT AS recent_failed_count
                FROM users u
                LEFT JOIN audit_logs al ON al.user_id = u.id
                    AND al.action LIKE '%login_failed%'
                    AND al.performed_at >= NOW() - INTERVAL '24 hours'
                WHERE u.failed_login_attempts >= threshold
                   OR u.account_status IN ('suspended', 'locked')
                GROUP BY u.id, u.name, u.email, u.failed_login_attempts,
                         u.last_failed_login_at, u.account_status
                ORDER BY u.failed_login_attempts DESC, recent_failed_count DESC;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // 3. Get User Activity Summary
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION get_user_activity_summary(p_user_id BIGINT, days_back INTEGER DEFAULT 30)
            RETURNS TABLE(
                total_actions BIGINT,
                successful_actions BIGINT,
                failed_actions BIGINT,
                categories_affected TEXT[],
                last_login TIMESTAMP,
                most_active_day DATE
            ) AS $$
            BEGIN
                RETURN QUERY
                SELECT
                    COUNT(*)::BIGINT AS total_actions,
                    COUNT(*) FILTER (WHERE status = 'success')::BIGINT AS successful_actions,
                    COUNT(*) FILTER (WHERE status IN ('failure', 'error'))::BIGINT AS failed_actions,
                    ARRAY_AGG(DISTINCT category) AS categories_affected,
                    MAX(performed_at) FILTER (WHERE action LIKE '%login%') AS last_login,
                    MODE() WITHIN GROUP (ORDER BY DATE(performed_at)) AS most_active_day
                FROM audit_logs
                WHERE user_id = p_user_id
                  AND performed_at >= NOW() - (days_back || ' days')::INTERVAL;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // 4. Get Adopter Profile Statistics
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION get_adopter_profile_stats()
            RETURNS TABLE(
                total_profiles BIGINT,
                with_children BIGINT,
                with_other_pets BIGINT,
                prefer_cats BIGINT,
                prefer_dogs BIGINT,
                prefer_both BIGINT,
                beginner_experience BIGINT,
                intermediate_experience BIGINT,
                expert_experience BIGINT
            ) AS $$
            BEGIN
                RETURN QUERY
                SELECT
                    COUNT(*)::BIGINT AS total_profiles,
                    COUNT(*) FILTER (WHERE has_children = true)::BIGINT AS with_children,
                    COUNT(*) FILTER (WHERE has_other_pets = true)::BIGINT AS with_other_pets,
                    COUNT(*) FILTER (WHERE preferred_species = 'cat')::BIGINT AS prefer_cats,
                    COUNT(*) FILTER (WHERE preferred_species = 'dog')::BIGINT AS prefer_dogs,
                    COUNT(*) FILTER (WHERE preferred_species = 'both')::BIGINT AS prefer_both,
                    COUNT(*) FILTER (WHERE experience = 'beginner')::BIGINT AS beginner_experience,
                    COUNT(*) FILTER (WHERE experience = 'intermediate')::BIGINT AS intermediate_experience,
                    COUNT(*) FILTER (WHERE experience = 'expert')::BIGINT AS expert_experience
                FROM adopter_profile;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // 5. Get Recent User Registrations
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION get_recent_registrations(days_back INTEGER DEFAULT 7)
            RETURNS TABLE(
                user_id BIGINT,
                user_name VARCHAR,
                user_email VARCHAR,
                registered_at TIMESTAMP,
                has_profile BOOLEAN,
                account_status VARCHAR
            ) AS $$
            BEGIN
                RETURN QUERY
                SELECT
                    u.id,
                    u.name,
                    u.email,
                    u.created_at,
                    EXISTS(SELECT 1 FROM adopter_profile WHERE \"adopterID\" = u.id) AS has_profile,
                    u.account_status
                FROM users u
                WHERE u.created_at >= NOW() - (days_back || ' days')::INTERVAL
                ORDER BY u.created_at DESC;
            END;
            $$ LANGUAGE plpgsql;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS get_user_account_stats()');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS get_high_risk_users(INTEGER)');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS get_user_activity_summary(BIGINT, INTEGER)');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS get_adopter_profile_stats()');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS get_recent_registrations(INTEGER)');
    }
};
