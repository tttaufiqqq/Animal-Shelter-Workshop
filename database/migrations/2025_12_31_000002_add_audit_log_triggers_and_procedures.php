<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Audit Log Automation - Auto-log changes, cleanup old logs
     */
    public function up(): void
    {
        // 1. Auto-Log User Changes
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION log_user_changes()
            RETURNS TRIGGER AS $$
            DECLARE
                v_action VARCHAR(100);
                v_old_values JSONB;
                v_new_values JSONB;
            BEGIN
                -- Determine action type
                IF TG_OP = 'INSERT' THEN
                    v_action := 'user_created';
                    v_old_values := NULL;
                    v_new_values := to_jsonb(NEW);
                ELSIF TG_OP = 'UPDATE' THEN
                    v_action := 'user_updated';
                    v_old_values := to_jsonb(OLD);
                    v_new_values := to_jsonb(NEW);
                ELSIF TG_OP = 'DELETE' THEN
                    v_action := 'user_deleted';
                    v_old_values := to_jsonb(OLD);
                    v_new_values := NULL;
                END IF;

                -- Insert audit log
                INSERT INTO audit_logs (
                    user_id, user_name, user_email,
                    category, action, entity_type, entity_id,
                    source_database, performed_at,
                    old_values, new_values, status
                ) VALUES (
                    COALESCE(NEW.id, OLD.id),
                    COALESCE(NEW.name, OLD.name),
                    COALESCE(NEW.email, OLD.email),
                    'user_management', v_action, 'User', COALESCE(NEW.id, OLD.id),
                    'taufiq', NOW(),
                    v_old_values, v_new_values, 'success'
                );

                RETURN COALESCE(NEW, OLD);
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::connection('taufiq')->unprepared("
            CREATE TRIGGER trg_log_user_changes
                AFTER INSERT OR UPDATE OR DELETE ON users
                FOR EACH ROW
                EXECUTE FUNCTION log_user_changes();
        ");

        // 2. Auto-Log Role Assignment Changes
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION log_role_assignment_changes()
            RETURNS TRIGGER AS $$
            DECLARE
                v_action VARCHAR(100);
                v_user_record RECORD;
            BEGIN
                -- Get user details
                SELECT id, name, email INTO v_user_record
                FROM users
                WHERE id = COALESCE(NEW.model_id, OLD.model_id)
                LIMIT 1;

                -- Determine action
                IF TG_OP = 'INSERT' THEN
                    v_action := 'role_assigned';
                ELSIF TG_OP = 'DELETE' THEN
                    v_action := 'role_revoked';
                END IF;

                INSERT INTO audit_logs (
                    user_id, user_name, user_email,
                    category, action, entity_type, entity_id,
                    source_database, performed_at,
                    new_values, old_values, status
                ) VALUES (
                    v_user_record.id, v_user_record.name, v_user_record.email,
                    'authorization', v_action, 'RoleAssignment', COALESCE(NEW.role_id, OLD.role_id),
                    'taufiq', NOW(),
                    CASE WHEN TG_OP = 'INSERT' THEN to_jsonb(NEW) ELSE NULL END,
                    CASE WHEN TG_OP = 'DELETE' THEN to_jsonb(OLD) ELSE NULL END,
                    'success'
                );

                RETURN COALESCE(NEW, OLD);
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::connection('taufiq')->unprepared("
            CREATE TRIGGER trg_log_role_changes
                AFTER INSERT OR DELETE ON model_has_roles
                FOR EACH ROW
                EXECUTE FUNCTION log_role_assignment_changes();
        ");

        // 3. Stored Procedure: Clean Old Audit Logs
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION cleanup_old_audit_logs(retention_days INTEGER DEFAULT 90)
            RETURNS TABLE(deleted_count INTEGER) AS $$
            DECLARE
                affected_rows INTEGER;
            BEGIN
                DELETE FROM audit_logs
                WHERE performed_at < NOW() - (retention_days || ' days')::INTERVAL;

                GET DIAGNOSTICS affected_rows = ROW_COUNT;

                RETURN QUERY SELECT affected_rows;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // 4. Stored Procedure: Get Audit Summary by Category
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION get_audit_summary_by_category(days_back INTEGER DEFAULT 30)
            RETURNS TABLE(
                category VARCHAR,
                total_actions BIGINT,
                success_count BIGINT,
                failure_count BIGINT,
                unique_users BIGINT,
                most_common_action VARCHAR
            ) AS $$
            BEGIN
                RETURN QUERY
                SELECT
                    al.category,
                    COUNT(*)::BIGINT AS total_actions,
                    COUNT(*) FILTER (WHERE al.status = 'success')::BIGINT AS success_count,
                    COUNT(*) FILTER (WHERE al.status IN ('failure', 'error'))::BIGINT AS failure_count,
                    COUNT(DISTINCT al.user_id)::BIGINT AS unique_users,
                    MODE() WITHIN GROUP (ORDER BY al.action) AS most_common_action
                FROM audit_logs al
                WHERE al.performed_at >= NOW() - (days_back || ' days')::INTERVAL
                GROUP BY al.category
                ORDER BY total_actions DESC;
            END;
            $$ LANGUAGE plpgsql;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('taufiq')->unprepared('DROP TRIGGER IF EXISTS trg_log_user_changes ON users');
        DB::connection('taufiq')->unprepared('DROP TRIGGER IF EXISTS trg_log_role_changes ON model_has_roles');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS log_user_changes()');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS log_role_assignment_changes()');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS cleanup_old_audit_logs(INTEGER)');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS get_audit_summary_by_category(INTEGER)');
    }
};
