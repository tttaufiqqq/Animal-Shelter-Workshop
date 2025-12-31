<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Scheduled Maintenance - Session cleanup, cache cleanup
     */
    public function up(): void
    {
        // 1. Session Cleanup
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION cleanup_expired_sessions()
            RETURNS TABLE(deleted_count INTEGER) AS $$
            DECLARE
                affected_rows INTEGER;
            BEGIN
                DELETE FROM sessions
                WHERE last_activity < EXTRACT(EPOCH FROM NOW() - INTERVAL '2 hours')::INTEGER;

                GET DIAGNOSTICS affected_rows = ROW_COUNT;

                RETURN QUERY SELECT affected_rows;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // 2. Cache Cleanup
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION cleanup_expired_cache()
            RETURNS TABLE(deleted_count INTEGER) AS $$
            DECLARE
                affected_rows INTEGER;
            BEGIN
                DELETE FROM cache
                WHERE expiration < EXTRACT(EPOCH FROM NOW())::INTEGER;

                GET DIAGNOSTICS affected_rows = ROW_COUNT;

                RETURN QUERY SELECT affected_rows;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // 3. Combined Maintenance Procedure
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION run_scheduled_maintenance()
            RETURNS TABLE(
                task_name VARCHAR,
                records_affected INTEGER,
                execution_time INTERVAL
            ) AS $$
            DECLARE
                v_start_time TIMESTAMP;
                v_end_time TIMESTAMP;
                v_sessions_deleted INTEGER;
                v_cache_deleted INTEGER;
                v_audit_deleted INTEGER;
            BEGIN
                -- Cleanup sessions
                v_start_time := NOW();
                SELECT * INTO v_sessions_deleted FROM cleanup_expired_sessions();
                v_end_time := NOW();
                RETURN QUERY SELECT 'cleanup_sessions'::VARCHAR, v_sessions_deleted, v_end_time - v_start_time;

                -- Cleanup cache
                v_start_time := NOW();
                SELECT * INTO v_cache_deleted FROM cleanup_expired_cache();
                v_end_time := NOW();
                RETURN QUERY SELECT 'cleanup_cache'::VARCHAR, v_cache_deleted, v_end_time - v_start_time;

                -- Cleanup audit logs (keep 90 days)
                v_start_time := NOW();
                SELECT * INTO v_audit_deleted FROM cleanup_old_audit_logs(90);
                v_end_time := NOW();
                RETURN QUERY SELECT 'cleanup_audit_logs'::VARCHAR, v_audit_deleted, v_end_time - v_start_time;

                -- Unlock expired accounts
                v_start_time := NOW();
                SELECT * INTO v_sessions_deleted FROM unlock_expired_accounts();
                v_end_time := NOW();
                RETURN QUERY SELECT 'unlock_expired_accounts'::VARCHAR, v_sessions_deleted, v_end_time - v_start_time;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // 4. Database Vacuum and Analyze (for PostgreSQL performance)
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION optimize_database_tables()
            RETURNS TABLE(
                table_name VARCHAR,
                operation VARCHAR,
                status VARCHAR
            ) AS $$
            BEGIN
                -- Vacuum and analyze critical tables
                VACUUM ANALYZE users;
                RETURN QUERY SELECT 'users'::VARCHAR, 'VACUUM ANALYZE'::VARCHAR, 'completed'::VARCHAR;

                VACUUM ANALYZE audit_logs;
                RETURN QUERY SELECT 'audit_logs'::VARCHAR, 'VACUUM ANALYZE'::VARCHAR, 'completed'::VARCHAR;

                VACUUM ANALYZE adopter_profile;
                RETURN QUERY SELECT 'adopter_profile'::VARCHAR, 'VACUUM ANALYZE'::VARCHAR, 'completed'::VARCHAR;

                VACUUM ANALYZE sessions;
                RETURN QUERY SELECT 'sessions'::VARCHAR, 'VACUUM ANALYZE'::VARCHAR, 'completed'::VARCHAR;

                VACUUM ANALYZE cache;
                RETURN QUERY SELECT 'cache'::VARCHAR, 'VACUUM ANALYZE'::VARCHAR, 'completed'::VARCHAR;
            END;
            $$ LANGUAGE plpgsql;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS cleanup_expired_sessions()');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS cleanup_expired_cache()');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS run_scheduled_maintenance()');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS optimize_database_tables()');
    }
};
