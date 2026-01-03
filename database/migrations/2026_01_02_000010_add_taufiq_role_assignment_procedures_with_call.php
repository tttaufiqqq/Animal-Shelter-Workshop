<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration creates TRUE PostgreSQL PROCEDURES (not functions)
     * for Role Assignment operations using CALL syntax with OUT parameters
     */
    public function up(): void
    {
        // ==========================================
        // USER ASSIGN ROLE PROCEDURE (TRUE PROCEDURE)
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE PROCEDURE sp_user_assign_role_proc(
                p_user_id BIGINT,
                p_role_id BIGINT,
                p_audit_user_id BIGINT,
                p_audit_user_name VARCHAR,
                p_audit_user_email VARCHAR,
                p_audit_user_role VARCHAR,
                OUT o_status VARCHAR,
                OUT o_message VARCHAR
            )
            LANGUAGE plpgsql
            AS \$\$
            DECLARE
                v_user_exists BOOLEAN;
                v_role_name VARCHAR;
                v_already_assigned BOOLEAN;
            BEGIN
                -- Check if user exists
                SELECT EXISTS(SELECT 1 FROM users WHERE id = p_user_id) INTO v_user_exists;

                IF NOT v_user_exists THEN
                    o_status := 'error';
                    o_message := 'User not found';
                    RETURN;
                END IF;

                -- Check if role exists
                SELECT name INTO v_role_name FROM roles WHERE id = p_role_id;

                IF v_role_name IS NULL THEN
                    o_status := 'error';
                    o_message := 'Role not found';
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
                    o_status := 'error';
                    o_message := 'Role already assigned to user';
                    RETURN;
                END IF;

                -- Assign role
                INSERT INTO model_has_roles (role_id, model_type, model_id)
                VALUES (p_role_id, 'App\\Models\\User', p_user_id);

                -- Note: Trigger trg_log_role_changes will automatically log this

                o_status := 'success';
                o_message := 'Role ' || v_role_name || ' assigned successfully';
            END;
            \$\$;
        ");

        // ==========================================
        // USER REVOKE ROLE PROCEDURE (TRUE PROCEDURE)
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE PROCEDURE sp_user_revoke_role_proc(
                p_user_id BIGINT,
                p_role_id BIGINT,
                p_audit_user_id BIGINT,
                p_audit_user_name VARCHAR,
                p_audit_user_email VARCHAR,
                p_audit_user_role VARCHAR,
                OUT o_status VARCHAR,
                OUT o_message VARCHAR
            )
            LANGUAGE plpgsql
            AS \$\$
            DECLARE
                v_user_exists BOOLEAN;
                v_role_name VARCHAR;
                v_role_assigned BOOLEAN;
            BEGIN
                -- Check if user exists
                SELECT EXISTS(SELECT 1 FROM users WHERE id = p_user_id) INTO v_user_exists;

                IF NOT v_user_exists THEN
                    o_status := 'error';
                    o_message := 'User not found';
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
                    o_status := 'error';
                    o_message := 'Role not assigned to user';
                    RETURN;
                END IF;

                -- Revoke role
                DELETE FROM model_has_roles
                WHERE role_id = p_role_id
                  AND model_id = p_user_id
                  AND model_type = 'App\\Models\\User';

                -- Note: Trigger trg_log_role_changes will automatically log this

                o_status := 'success';
                o_message := 'Role ' || COALESCE(v_role_name, 'Unknown') || ' revoked successfully';
            END;
            \$\$;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('taufiq')->unprepared('DROP PROCEDURE IF EXISTS sp_user_assign_role_proc');
        DB::connection('taufiq')->unprepared('DROP PROCEDURE IF EXISTS sp_user_revoke_role_proc');
    }
};
