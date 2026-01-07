<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * STRATEGIC VIEW DESIGN - Only views with HIGH performance impact
     *
     * ✅ WHEN TO USE VIEWS:
     * 1. Complex JOINs called frequently (avoid repeated computation)
     * 2. Aggregations for dashboards (cache expensive calculations)
     * 3. Denormalization for read-heavy operations
     * 4. Security (expose only certain columns)
     *
     * ❌ WHEN NOT TO USE VIEWS:
     * 1. Simple single-table selects (no benefit)
     * 2. Write operations (views don't help INSERTs/UPDATEs)
     * 3. Highly dynamic queries with many parameters
     */
    public function up(): void
    {
        // ==========================================
        // VIEW 1: v_user_full_profile
        // IMPACT: ⚡⚡⚡ ULTRA HIGH
        // WHY: Most common read pattern - user + adopter profile + roles
        // SAVES: 2-3 JOINs on EVERY user profile page load
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE VIEW v_user_full_profile AS
            SELECT
                u.id,
                u.name,
                u.email,
                u.\"phoneNum\",
                u.address,
                u.city,
                u.state,
                u.account_status,
                u.suspended_at,
                u.suspended_by,
                u.suspension_reason,
                u.locked_until,
                u.lock_reason,
                u.failed_login_attempts,
                u.last_failed_login_at,
                u.require_password_reset,
                u.created_at AS user_created_at,
                u.updated_at AS user_updated_at,

                -- Adopter Profile (NULL if not exists)
                ap.id AS adopter_profile_id,
                ap.housing_type,
                ap.has_children,
                ap.has_other_pets,
                ap.activity_level,
                ap.experience,
                ap.preferred_species,
                ap.preferred_size,
                ap.created_at AS adopter_profile_created_at,
                ap.updated_at AS adopter_profile_updated_at,

                -- Role Information (aggregated)
                COALESCE(
                    (SELECT STRING_AGG(r.name, ', ' ORDER BY r.name)
                     FROM model_has_roles mhr
                     JOIN roles r ON r.id = mhr.role_id
                     WHERE mhr.model_id = u.id
                       AND mhr.model_type = 'App\\Models\\User'),
                    'User'
                ) AS roles,

                -- Computed Status Flags
                CASE
                    WHEN u.account_status = 'suspended' THEN TRUE
                    ELSE FALSE
                END AS is_suspended,

                CASE
                    WHEN u.account_status = 'locked' AND u.locked_until > NOW() THEN TRUE
                    ELSE FALSE
                END AS is_locked,

                CASE
                    WHEN u.require_password_reset = TRUE THEN TRUE
                    ELSE FALSE
                END AS needs_password_reset,

                CASE
                    WHEN u.failed_login_attempts >= 3 THEN TRUE
                    ELSE FALSE
                END AS is_high_risk

            FROM users u
            LEFT JOIN adopter_profile ap ON ap.\"adopterID\" = u.id;
        ");

        // ==========================================
        // VIEW 2: v_user_account_stats (MATERIALIZED)
        // IMPACT: ⚡⚡⚡ ULTRA HIGH
        // WHY: Dashboard stats - expensive aggregations
        // SAVES: Full table scans on every dashboard load
        // REFRESH: Every 5 minutes via scheduled task
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE MATERIALIZED VIEW v_user_account_stats AS
            SELECT
                -- Total Users
                COUNT(*)::INTEGER AS total_users,

                -- Active Users
                COUNT(*) FILTER (WHERE account_status = 'active')::INTEGER AS active_users,

                -- Suspended Users
                COUNT(*) FILTER (WHERE account_status = 'suspended')::INTEGER AS suspended_users,

                -- Locked Users
                COUNT(*) FILTER (
                    WHERE account_status = 'locked'
                    AND locked_until > NOW()
                )::INTEGER AS locked_users,

                -- Users Requiring Password Reset
                COUNT(*) FILTER (WHERE require_password_reset = TRUE)::INTEGER AS users_requiring_password_reset,

                -- High Risk Users (3+ failed logins)
                COUNT(*) FILTER (WHERE failed_login_attempts >= 3)::INTEGER AS high_risk_users,

                -- New Users Today
                COUNT(*) FILTER (
                    WHERE created_at >= CURRENT_DATE
                )::INTEGER AS new_users_today,

                -- New Users This Week
                COUNT(*) FILTER (
                    WHERE created_at >= CURRENT_DATE - INTERVAL '7 days'
                )::INTEGER AS new_users_this_week,

                -- New Users This Month
                COUNT(*) FILTER (
                    WHERE created_at >= CURRENT_DATE - INTERVAL '30 days'
                )::INTEGER AS new_users_this_month,

                -- Percentage Calculations (as decimals)
                ROUND(
                    COUNT(*) FILTER (WHERE account_status = 'active')::NUMERIC /
                    NULLIF(COUNT(*), 0) * 100,
                    2
                ) AS active_percentage,

                -- Last Updated Timestamp
                NOW() AS stats_generated_at

            FROM users;
        ");

        // Create index on materialized view for instant access
        DB::connection('taufiq')->unprepared("
            CREATE UNIQUE INDEX idx_user_account_stats_singleton
            ON v_user_account_stats ((1));
        ");

        // ==========================================
        // VIEW 3: v_adopter_profile_stats (MATERIALIZED)
        // IMPACT: ⚡⚡⚡ ULTRA HIGH
        // WHY: Dashboard stats - expensive aggregations
        // SAVES: Full table scans on every dashboard load
        // REFRESH: Every 5 minutes via scheduled task
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE MATERIALIZED VIEW v_adopter_profile_stats AS
            SELECT
                -- Total Adopter Profiles
                COUNT(*)::INTEGER AS total_adopter_profiles,

                -- Breakdown by Housing Type
                COUNT(*) FILTER (WHERE housing_type = 'house')::INTEGER AS house_dwellers,
                COUNT(*) FILTER (WHERE housing_type = 'apartment')::INTEGER AS apartment_dwellers,
                COUNT(*) FILTER (WHERE housing_type = 'condo')::INTEGER AS condo_dwellers,

                -- Breakdown by Children
                COUNT(*) FILTER (WHERE has_children = TRUE)::INTEGER AS profiles_with_children,
                COUNT(*) FILTER (WHERE has_children = FALSE)::INTEGER AS profiles_without_children,

                -- Breakdown by Pets
                COUNT(*) FILTER (WHERE has_other_pets = TRUE)::INTEGER AS profiles_with_other_pets,
                COUNT(*) FILTER (WHERE has_other_pets = FALSE)::INTEGER AS profiles_without_other_pets,

                -- Breakdown by Activity Level
                COUNT(*) FILTER (WHERE activity_level = 'low')::INTEGER AS low_activity,
                COUNT(*) FILTER (WHERE activity_level = 'moderate')::INTEGER AS moderate_activity,
                COUNT(*) FILTER (WHERE activity_level = 'high')::INTEGER AS high_activity,

                -- Breakdown by Experience
                COUNT(*) FILTER (WHERE experience = 'none')::INTEGER AS no_experience,
                COUNT(*) FILTER (WHERE experience = 'beginner')::INTEGER AS beginner_experience,
                COUNT(*) FILTER (WHERE experience = 'intermediate')::INTEGER AS intermediate_experience,
                COUNT(*) FILTER (WHERE experience = 'expert')::INTEGER AS expert_experience,

                -- Breakdown by Preferred Species
                COUNT(*) FILTER (WHERE preferred_species = 'cat')::INTEGER AS prefer_cats,
                COUNT(*) FILTER (WHERE preferred_species = 'dog')::INTEGER AS prefer_dogs,
                COUNT(*) FILTER (WHERE preferred_species = 'both')::INTEGER AS prefer_both,

                -- Breakdown by Preferred Size
                COUNT(*) FILTER (WHERE preferred_size = 'small')::INTEGER AS prefer_small,
                COUNT(*) FILTER (WHERE preferred_size = 'medium')::INTEGER AS prefer_medium,
                COUNT(*) FILTER (WHERE preferred_size = 'large')::INTEGER AS prefer_large,

                -- New Profiles This Month
                COUNT(*) FILTER (
                    WHERE created_at >= CURRENT_DATE - INTERVAL '30 days'
                )::INTEGER AS new_profiles_this_month,

                -- Completion Rate (profiles vs total users)
                ROUND(
                    COUNT(*)::NUMERIC / NULLIF(
                        (SELECT COUNT(*) FROM users),
                        0
                    ) * 100,
                    2
                ) AS profile_completion_rate,

                -- Last Updated Timestamp
                NOW() AS stats_generated_at

            FROM adopter_profile;
        ");

        // Create index on materialized view for instant access
        DB::connection('taufiq')->unprepared("
            CREATE UNIQUE INDEX idx_adopter_profile_stats_singleton
            ON v_adopter_profile_stats ((1));
        ");

        // ==========================================
        // VIEW 4: v_high_risk_users
        // IMPACT: ⚡⚡ HIGH
        // WHY: Security monitoring - complex calculations
        // SAVES: Complex filtering + aggregations for security dashboard
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE VIEW v_high_risk_users AS
            SELECT
                u.id,
                u.name,
                u.email,
                u.account_status,
                u.failed_login_attempts,
                u.last_failed_login_at,
                u.locked_until,
                u.lock_reason,

                -- Risk Score (0-100)
                LEAST(
                    (u.failed_login_attempts * 20) +
                    CASE
                        WHEN u.account_status = 'locked' THEN 20
                        WHEN u.account_status = 'suspended' THEN 30
                        ELSE 0
                    END +
                    CASE
                        WHEN u.last_failed_login_at > NOW() - INTERVAL '1 hour' THEN 15
                        WHEN u.last_failed_login_at > NOW() - INTERVAL '24 hours' THEN 10
                        ELSE 0
                    END,
                    100
                ) AS risk_score,

                -- Risk Level
                CASE
                    WHEN u.failed_login_attempts >= 5 OR u.account_status = 'suspended' THEN 'critical'
                    WHEN u.failed_login_attempts >= 3 OR u.account_status = 'locked' THEN 'high'
                    WHEN u.failed_login_attempts >= 1 THEN 'medium'
                    ELSE 'low'
                END AS risk_level,

                -- Time Since Last Failure
                CASE
                    WHEN u.last_failed_login_at IS NOT NULL THEN
                        EXTRACT(EPOCH FROM (NOW() - u.last_failed_login_at))::INTEGER
                    ELSE NULL
                END AS seconds_since_last_failure,

                u.created_at,
                u.updated_at

            FROM users u
            WHERE u.failed_login_attempts >= 1
               OR u.account_status IN ('locked', 'suspended')
            ORDER BY
                CASE
                    WHEN u.account_status = 'suspended' THEN 1
                    WHEN u.account_status = 'locked' THEN 2
                    ELSE 3
                END,
                u.failed_login_attempts DESC,
                u.last_failed_login_at DESC NULLS LAST;
        ");

        // ==========================================
        // VIEW 5: v_active_users_with_profiles
        // IMPACT: ⚡⚡ HIGH
        // WHY: Common query pattern - active users for adoption matching
        // SAVES: Complex JOINs + filtering for matching algorithm
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE VIEW v_active_users_with_profiles AS
            SELECT
                u.id,
                u.name,
                u.email,
                u.\"phoneNum\",
                u.city,
                u.state,
                u.created_at,

                ap.id AS adopter_profile_id,
                ap.housing_type,
                ap.has_children,
                ap.has_other_pets,
                ap.activity_level,
                ap.experience,
                ap.preferred_species,
                ap.preferred_size,
                ap.created_at AS profile_created_at,

                -- Adopter Readiness Score (0-100)
                (
                    CASE WHEN ap.housing_type IS NOT NULL THEN 25 ELSE 0 END +
                    CASE WHEN ap.activity_level IS NOT NULL THEN 25 ELSE 0 END +
                    CASE WHEN ap.experience IS NOT NULL THEN 25 ELSE 0 END +
                    CASE WHEN ap.preferred_species IS NOT NULL THEN 25 ELSE 0 END
                ) AS readiness_score,

                -- Profile Completeness
                CASE
                    WHEN ap.housing_type IS NOT NULL
                     AND ap.activity_level IS NOT NULL
                     AND ap.experience IS NOT NULL
                     AND ap.preferred_species IS NOT NULL
                    THEN TRUE
                    ELSE FALSE
                END AS is_profile_complete

            FROM users u
            INNER JOIN adopter_profile ap ON ap.\"adopterID\" = u.id
            WHERE u.account_status = 'active'
            ORDER BY ap.created_at DESC;
        ");

        // ==========================================
        // VIEW 6: v_user_activity_last_30_days
        // IMPACT: ⚡⚡ HIGH
        // WHY: Analytics/reporting - aggregating audit logs
        // SAVES: Complex audit log aggregations
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE VIEW v_user_activity_last_30_days AS
            SELECT
                u.id AS user_id,
                u.name,
                u.email,
                u.account_status,

                -- Activity Metrics
                COUNT(al.id) FILTER (
                    WHERE al.performed_at >= NOW() - INTERVAL '30 days'
                )::INTEGER AS total_actions_30_days,

                COUNT(al.id) FILTER (
                    WHERE al.performed_at >= NOW() - INTERVAL '7 days'
                )::INTEGER AS total_actions_7_days,

                COUNT(al.id) FILTER (
                    WHERE al.performed_at >= CURRENT_DATE
                )::INTEGER AS total_actions_today,

                MAX(al.performed_at) AS last_activity_at,

                -- Activity Breakdown
                COUNT(al.id) FILTER (
                    WHERE al.action LIKE '%created%'
                )::INTEGER AS create_actions,

                COUNT(al.id) FILTER (
                    WHERE al.action LIKE '%updated%'
                )::INTEGER AS update_actions,

                COUNT(al.id) FILTER (
                    WHERE al.action LIKE '%deleted%'
                )::INTEGER AS delete_actions,

                -- Days Since Last Activity
                CASE
                    WHEN MAX(al.performed_at) IS NOT NULL THEN
                        EXTRACT(DAY FROM (NOW() - MAX(al.performed_at)))::INTEGER
                    ELSE NULL
                END AS days_since_last_activity,

                -- Activity Status
                CASE
                    WHEN MAX(al.performed_at) >= NOW() - INTERVAL '7 days' THEN 'active'
                    WHEN MAX(al.performed_at) >= NOW() - INTERVAL '30 days' THEN 'moderate'
                    WHEN MAX(al.performed_at) IS NULL THEN 'never_active'
                    ELSE 'inactive'
                END AS activity_status

            FROM users u
            LEFT JOIN audit_logs al ON al.user_id = u.id
            WHERE u.created_at >= NOW() - INTERVAL '90 days'
            GROUP BY u.id, u.name, u.email, u.account_status
            ORDER BY last_activity_at DESC NULLS LAST;
        ");

        // ==========================================
        // HELPER FUNCTION: Refresh All Materialized Views
        // USAGE: SELECT refresh_all_taufiq_stats();
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION refresh_all_taufiq_stats()
            RETURNS TEXT
            AS \$\$
            BEGIN
                REFRESH MATERIALIZED VIEW v_user_account_stats;
                REFRESH MATERIALIZED VIEW v_adopter_profile_stats;
                RETURN 'All materialized views refreshed at ' || NOW()::TEXT;
            END;
            \$\$ LANGUAGE plpgsql;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop helper function
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS refresh_all_taufiq_stats');

        // Drop regular views
        DB::connection('taufiq')->unprepared('DROP VIEW IF EXISTS v_user_full_profile CASCADE');
        DB::connection('taufiq')->unprepared('DROP VIEW IF EXISTS v_high_risk_users CASCADE');
        DB::connection('taufiq')->unprepared('DROP VIEW IF EXISTS v_active_users_with_profiles CASCADE');
        DB::connection('taufiq')->unprepared('DROP VIEW IF EXISTS v_user_activity_last_30_days CASCADE');

        // Drop materialized views
        DB::connection('taufiq')->unprepared('DROP MATERIALIZED VIEW IF EXISTS v_user_account_stats CASCADE');
        DB::connection('taufiq')->unprepared('DROP MATERIALIZED VIEW IF EXISTS v_adopter_profile_stats CASCADE');
    }
};
