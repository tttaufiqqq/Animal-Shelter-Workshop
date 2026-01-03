<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration creates TRUE PostgreSQL PROCEDURES (not functions)
     * that use CALL syntax with OUT parameters, similar to MySQL stored procedures
     */
    public function up(): void
    {
        // ==========================================
        // USER CREATE PROCEDURE (TRUE PROCEDURE)
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE PROCEDURE sp_user_create_proc(
                p_name VARCHAR,
                p_email VARCHAR,
                p_password VARCHAR,
                p_phone_num VARCHAR,
                p_address TEXT,
                p_city VARCHAR,
                p_state VARCHAR,
                p_audit_user_id BIGINT,
                p_audit_user_name VARCHAR,
                p_audit_user_email VARCHAR,
                p_audit_user_role VARCHAR,
                OUT o_user_id BIGINT,
                OUT o_status VARCHAR,
                OUT o_message VARCHAR
            )
            LANGUAGE plpgsql
            AS \$\$
            DECLARE
                v_exists BOOLEAN;
            BEGIN
                -- Check if email already exists
                SELECT EXISTS(SELECT 1 FROM users WHERE email = p_email) INTO v_exists;

                IF v_exists THEN
                    o_user_id := NULL;
                    o_status := 'error';
                    o_message := 'Email already exists';
                    RETURN;
                END IF;

                -- Insert new user
                INSERT INTO users (name, email, password, \"phoneNum\", address, city, state, created_at, updated_at)
                VALUES (p_name, p_email, p_password, p_phone_num, p_address, p_city, p_state, NOW(), NOW())
                RETURNING id INTO o_user_id;

                -- Log to audit (manual log since this is pre-authentication)
                INSERT INTO audit_logs (
                    user_id, user_name, user_email, user_role,
                    category, action, entity_type, entity_id,
                    source_database, performed_at, status,
                    new_values
                ) VALUES (
                    p_audit_user_id, p_audit_user_name, p_audit_user_email, p_audit_user_role,
                    'user_management', 'user_created', 'User', o_user_id,
                    'taufiq', NOW(), 'success',
                    jsonb_build_object(
                        'name', p_name,
                        'email', p_email,
                        'phoneNum', p_phone_num,
                        'city', p_city,
                        'state', p_state
                    )
                );

                o_status := 'success';
                o_message := 'User created successfully';
            END;
            \$\$;
        ");

        // ==========================================
        // USER UPDATE PROCEDURE (TRUE PROCEDURE)
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE PROCEDURE sp_user_update_proc(
                p_user_id BIGINT,
                p_name VARCHAR,
                p_email VARCHAR,
                p_phone_num VARCHAR,
                p_address TEXT,
                p_city VARCHAR,
                p_state VARCHAR,
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
                v_exists BOOLEAN;
                v_email_conflict BOOLEAN;
            BEGIN
                -- Check if user exists
                SELECT EXISTS(SELECT 1 FROM users WHERE id = p_user_id) INTO v_exists;

                IF NOT v_exists THEN
                    o_status := 'error';
                    o_message := 'User not found';
                    RETURN;
                END IF;

                -- Check if email is taken by another user
                SELECT EXISTS(
                    SELECT 1 FROM users
                    WHERE email = p_email AND id != p_user_id
                ) INTO v_email_conflict;

                IF v_email_conflict THEN
                    o_status := 'error';
                    o_message := 'Email already exists';
                    RETURN;
                END IF;

                -- Update user
                UPDATE users
                SET
                    name = p_name,
                    email = p_email,
                    \"phoneNum\" = p_phone_num,
                    address = p_address,
                    city = p_city,
                    state = p_state,
                    updated_at = NOW()
                WHERE id = p_user_id;

                -- Note: Trigger trg_log_user_changes will automatically log this

                o_status := 'success';
                o_message := 'User updated successfully';
            END;
            \$\$;
        ");

        // ==========================================
        // USER UPDATE PASSWORD PROCEDURE (TRUE PROCEDURE)
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE PROCEDURE sp_user_update_password_proc(
                p_user_id BIGINT,
                p_new_password VARCHAR,
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
                v_exists BOOLEAN;
            BEGIN
                -- Check if user exists
                SELECT EXISTS(SELECT 1 FROM users WHERE id = p_user_id) INTO v_exists;

                IF NOT v_exists THEN
                    o_status := 'error';
                    o_message := 'User not found';
                    RETURN;
                END IF;

                -- Update password and clear require_password_reset flag
                UPDATE users
                SET
                    password = p_new_password,
                    require_password_reset = FALSE,
                    updated_at = NOW()
                WHERE id = p_user_id;

                -- Note: Trigger trg_log_user_changes will automatically log this

                o_status := 'success';
                o_message := 'Password updated successfully';
            END;
            \$\$;
        ");

        // ==========================================
        // USER DELETE PROCEDURE (TRUE PROCEDURE)
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE PROCEDURE sp_user_delete_proc(
                p_user_id BIGINT,
                p_audit_user_id BIGINT,
                p_audit_user_name VARCHAR,
                p_audit_user_email VARCHAR,
                p_audit_user_role VARCHAR,
                OUT o_user_name VARCHAR,
                OUT o_status VARCHAR,
                OUT o_message VARCHAR
            )
            LANGUAGE plpgsql
            AS \$\$
            BEGIN
                -- Check if user exists and get name
                SELECT name INTO o_user_name FROM users WHERE id = p_user_id;

                IF o_user_name IS NULL THEN
                    o_status := 'error';
                    o_message := 'User not found';
                    RETURN;
                END IF;

                -- Delete user (cascade will delete adopter_profile)
                DELETE FROM users WHERE id = p_user_id;

                -- Note: Trigger trg_log_user_changes will automatically log this

                o_status := 'success';
                o_message := 'User deleted successfully';
            END;
            \$\$;
        ");

        // ==========================================
        // USER SUSPEND PROCEDURE (TRUE PROCEDURE)
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE PROCEDURE sp_user_suspend_proc(
                p_user_id BIGINT,
                p_reason TEXT,
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
                v_exists BOOLEAN;
            BEGIN
                -- Check if user exists
                SELECT EXISTS(SELECT 1 FROM users WHERE id = p_user_id) INTO v_exists;

                IF NOT v_exists THEN
                    o_status := 'error';
                    o_message := 'User not found';
                    RETURN;
                END IF;

                -- Suspend user
                UPDATE users
                SET
                    account_status = 'suspended',
                    suspended_at = NOW(),
                    suspended_by = p_audit_user_id,
                    suspension_reason = p_reason,
                    updated_at = NOW()
                WHERE id = p_user_id;

                -- Note: Trigger trg_log_user_changes will automatically log this

                o_status := 'success';
                o_message := 'User suspended successfully';
            END;
            \$\$;
        ");

        // ==========================================
        // USER LOCK PROCEDURE (TRUE PROCEDURE)
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE PROCEDURE sp_user_lock_proc(
                p_user_id BIGINT,
                p_duration_minutes INTEGER,
                p_reason TEXT,
                p_audit_user_id BIGINT,
                p_audit_user_name VARCHAR,
                p_audit_user_email VARCHAR,
                p_audit_user_role VARCHAR,
                OUT o_locked_until TIMESTAMP,
                OUT o_status VARCHAR,
                OUT o_message VARCHAR
            )
            LANGUAGE plpgsql
            AS \$\$
            DECLARE
                v_exists BOOLEAN;
            BEGIN
                -- Check if user exists
                SELECT EXISTS(SELECT 1 FROM users WHERE id = p_user_id) INTO v_exists;

                IF NOT v_exists THEN
                    o_locked_until := NULL;
                    o_status := 'error';
                    o_message := 'User not found';
                    RETURN;
                END IF;

                -- Calculate lock expiry
                o_locked_until := NOW() + (p_duration_minutes || ' minutes')::INTERVAL;

                -- Lock user
                UPDATE users
                SET
                    account_status = 'locked',
                    locked_until = o_locked_until,
                    lock_reason = p_reason,
                    updated_at = NOW()
                WHERE id = p_user_id;

                -- Note: Trigger trg_log_user_changes will automatically log this

                o_status := 'success';
                o_message := 'User locked successfully';
            END;
            \$\$;
        ");

        // ==========================================
        // USER UNLOCK PROCEDURE (TRUE PROCEDURE)
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE PROCEDURE sp_user_unlock_proc(
                p_user_id BIGINT,
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
                v_exists BOOLEAN;
            BEGIN
                -- Check if user exists
                SELECT EXISTS(SELECT 1 FROM users WHERE id = p_user_id) INTO v_exists;

                IF NOT v_exists THEN
                    o_status := 'error';
                    o_message := 'User not found';
                    RETURN;
                END IF;

                -- Unlock user
                UPDATE users
                SET
                    account_status = 'active',
                    locked_until = NULL,
                    lock_reason = NULL,
                    failed_login_attempts = 0,
                    last_failed_login_at = NULL,
                    updated_at = NOW()
                WHERE id = p_user_id;

                -- Note: Trigger trg_log_user_changes will automatically log this

                o_status := 'success';
                o_message := 'User unlocked successfully';
            END;
            \$\$;
        ");

        // ==========================================
        // USER FORCE PASSWORD RESET PROCEDURE (TRUE PROCEDURE)
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE PROCEDURE sp_user_force_password_reset_proc(
                p_user_id BIGINT,
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
                v_exists BOOLEAN;
            BEGIN
                -- Check if user exists
                SELECT EXISTS(SELECT 1 FROM users WHERE id = p_user_id) INTO v_exists;

                IF NOT v_exists THEN
                    o_status := 'error';
                    o_message := 'User not found';
                    RETURN;
                END IF;

                -- Force password reset
                UPDATE users
                SET
                    require_password_reset = TRUE,
                    updated_at = NOW()
                WHERE id = p_user_id;

                -- Note: Trigger trg_log_user_changes will automatically log this

                o_status := 'success';
                o_message := 'Password reset required';
            END;
            \$\$;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('taufiq')->unprepared('DROP PROCEDURE IF EXISTS sp_user_create_proc');
        DB::connection('taufiq')->unprepared('DROP PROCEDURE IF EXISTS sp_user_update_proc');
        DB::connection('taufiq')->unprepared('DROP PROCEDURE IF EXISTS sp_user_update_password_proc');
        DB::connection('taufiq')->unprepared('DROP PROCEDURE IF EXISTS sp_user_delete_proc');
        DB::connection('taufiq')->unprepared('DROP PROCEDURE IF EXISTS sp_user_suspend_proc');
        DB::connection('taufiq')->unprepared('DROP PROCEDURE IF EXISTS sp_user_lock_proc');
        DB::connection('taufiq')->unprepared('DROP PROCEDURE IF EXISTS sp_user_unlock_proc');
        DB::connection('taufiq')->unprepared('DROP PROCEDURE IF EXISTS sp_user_force_password_reset_proc');
    }
};
