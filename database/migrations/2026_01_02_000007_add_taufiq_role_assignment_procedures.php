<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==========================================
        // USER ASSIGN ROLE PROCEDURE
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION sp_user_assign_role(
                p_user_id BIGINT,
                p_role_id BIGINT,
                p_audit_user_id BIGINT,
                p_audit_user_name VARCHAR,
                p_audit_user_email VARCHAR,
                p_audit_user_role VARCHAR
            )
            RETURNS TABLE(
                o_status VARCHAR,
                o_message VARCHAR
            )
            AS \$\$
            DECLARE
                v_user_exists BOOLEAN;
                v_role_exists BOOLEAN;
                v_already_assigned BOOLEAN;
                v_role_name VARCHAR;
            BEGIN
                -- Check if user exists
                SELECT EXISTS(SELECT 1 FROM users WHERE id = p_user_id) INTO v_user_exists;

                IF NOT v_user_exists THEN
                    RETURN QUERY SELECT
                        'error'::VARCHAR,
                        'User not found'::VARCHAR;
                    RETURN;
                END IF;

                -- Check if role exists
                SELECT name INTO v_role_name FROM roles WHERE id = p_role_id;

                IF v_role_name IS NULL THEN
                    RETURN QUERY SELECT
                        'error'::VARCHAR,
                        'Role not found'::VARCHAR;
                    RETURN;
                END IF;

                -- Check if already assigned
                SELECT EXISTS(
                    SELECT 1 FROM model_has_roles
                    WHERE role_id = p_role_id
                      AND model_id = p_user_id
                      AND model_type = 'App\\Models\\User'
                ) INTO v_already_assigned;

                IF v_already_assigned THEN
                    RETURN QUERY SELECT
                        'error'::VARCHAR,
                        'Role already assigned to user'::VARCHAR;
                    RETURN;
                END IF;

                -- Assign role
                INSERT INTO model_has_roles (role_id, model_type, model_id)
                VALUES (p_role_id, 'App\\Models\\User', p_user_id);

                -- Note: Trigger trg_log_role_changes will automatically log this

                RETURN QUERY SELECT
                    'success'::VARCHAR,
                    ('Role ' || v_role_name || ' assigned successfully')::VARCHAR;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // USER REVOKE ROLE PROCEDURE
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION sp_user_revoke_role(
                p_user_id BIGINT,
                p_role_id BIGINT,
                p_audit_user_id BIGINT,
                p_audit_user_name VARCHAR,
                p_audit_user_email VARCHAR,
                p_audit_user_role VARCHAR
            )
            RETURNS TABLE(
                o_status VARCHAR,
                o_message VARCHAR
            )
            AS \$\$
            DECLARE
                v_user_exists BOOLEAN;
                v_role_assigned BOOLEAN;
                v_role_name VARCHAR;
            BEGIN
                -- Check if user exists
                SELECT EXISTS(SELECT 1 FROM users WHERE id = p_user_id) INTO v_user_exists;

                IF NOT v_user_exists THEN
                    RETURN QUERY SELECT
                        'error'::VARCHAR,
                        'User not found'::VARCHAR;
                    RETURN;
                END IF;

                -- Get role name
                SELECT name INTO v_role_name FROM roles WHERE id = p_role_id;

                -- Check if role is assigned
                SELECT EXISTS(
                    SELECT 1 FROM model_has_roles
                    WHERE role_id = p_role_id
                      AND model_id = p_user_id
                      AND model_type = 'App\\Models\\User'
                ) INTO v_role_assigned;

                IF NOT v_role_assigned THEN
                    RETURN QUERY SELECT
                        'error'::VARCHAR,
                        'Role not assigned to user'::VARCHAR;
                    RETURN;
                END IF;

                -- Revoke role
                DELETE FROM model_has_roles
                WHERE role_id = p_role_id
                  AND model_id = p_user_id
                  AND model_type = 'App\\Models\\User';

                -- Note: Trigger trg_log_role_changes will automatically log this

                RETURN QUERY SELECT
                    'success'::VARCHAR,
                    ('Role ' || COALESCE(v_role_name, 'Unknown') || ' revoked successfully')::VARCHAR;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // GET USER ROLES PROCEDURE
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION sp_user_get_roles(p_user_id BIGINT)
            RETURNS TABLE(
                role_id BIGINT,
                role_name VARCHAR,
                guard_name VARCHAR
            )
            AS \$\$
            BEGIN
                RETURN QUERY
                SELECT
                    r.id,
                    r.name,
                    r.guard_name
                FROM roles r
                INNER JOIN model_has_roles mhr ON r.id = mhr.role_id
                WHERE mhr.model_id = p_user_id
                  AND mhr.model_type = 'App\\Models\\User';
            END;
            \$\$ LANGUAGE plpgsql;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS sp_user_assign_role');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS sp_user_revoke_role');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS sp_user_get_roles');
    }
};
