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
        // USER CREATE PROCEDURE
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION sp_user_create(
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
                p_audit_user_role VARCHAR
            )
            RETURNS TABLE(
                o_user_id BIGINT,
                o_status VARCHAR,
                o_message VARCHAR
            )
            AS \$\$
            DECLARE
                v_user_id BIGINT;
                v_exists BOOLEAN;
            BEGIN
                -- Check if email already exists
                SELECT EXISTS(SELECT 1 FROM users WHERE email = p_email) INTO v_exists;

                IF v_exists THEN
                    RETURN QUERY SELECT
                        NULL::BIGINT,
                        'error'::VARCHAR,
                        'Email already exists'::VARCHAR;
                    RETURN;
                END IF;

                -- Insert new user
                INSERT INTO users (name, email, password, \"phoneNum\", address, city, state, created_at, updated_at)
                VALUES (p_name, p_email, p_password, p_phone_num, p_address, p_city, p_state, NOW(), NOW())
                RETURNING id INTO v_user_id;

                -- Log to audit (manual log since this is pre-authentication)
                INSERT INTO audit_logs (
                    user_id, user_name, user_email, user_role,
                    category, action, entity_type, entity_id,
                    source_database, performed_at, status,
                    new_values
                ) VALUES (
                    p_audit_user_id, p_audit_user_name, p_audit_user_email, p_audit_user_role,
                    'user_management', 'user_created', 'User', v_user_id,
                    'taufiq', NOW(), 'success',
                    jsonb_build_object(
                        'name', p_name,
                        'email', p_email,
                        'phoneNum', p_phone_num,
                        'city', p_city,
                        'state', p_state
                    )
                );

                RETURN QUERY SELECT
                    v_user_id,
                    'success'::VARCHAR,
                    'User created successfully'::VARCHAR;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // USER READ PROCEDURE
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION sp_user_read(p_user_id BIGINT)
            RETURNS TABLE(
                id BIGINT,
                name VARCHAR,
                email VARCHAR,
                \"phoneNum\" VARCHAR,
                address TEXT,
                city VARCHAR,
                state VARCHAR,
                account_status VARCHAR,
                suspended_at TIMESTAMP,
                suspended_by BIGINT,
                suspension_reason TEXT,
                locked_until TIMESTAMP,
                lock_reason TEXT,
                failed_login_attempts INTEGER,
                last_failed_login_at TIMESTAMP,
                require_password_reset BOOLEAN,
                created_at TIMESTAMP,
                updated_at TIMESTAMP
            )
            AS \$\$
            BEGIN
                RETURN QUERY
                SELECT
                    u.id, u.name, u.email, u.\"phoneNum\", u.address, u.city, u.state,
                    u.account_status, u.suspended_at, u.suspended_by, u.suspension_reason,
                    u.locked_until, u.lock_reason, u.failed_login_attempts,
                    u.last_failed_login_at, u.require_password_reset,
                    u.created_at, u.updated_at
                FROM users u
                WHERE u.id = p_user_id;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // USER UPDATE PROCEDURE
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION sp_user_update(
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
                p_audit_user_role VARCHAR
            )
            RETURNS TABLE(
                o_status VARCHAR,
                o_message VARCHAR
            )
            AS \$\$
            DECLARE
                v_exists BOOLEAN;
                v_email_conflict BOOLEAN;
                v_old_values JSONB;
            BEGIN
                -- Check if user exists
                SELECT EXISTS(SELECT 1 FROM users WHERE id = p_user_id) INTO v_exists;

                IF NOT v_exists THEN
                    RETURN QUERY SELECT
                        'error'::VARCHAR,
                        'User not found'::VARCHAR;
                    RETURN;
                END IF;

                -- Check if email is taken by another user
                SELECT EXISTS(
                    SELECT 1 FROM users
                    WHERE email = p_email AND id != p_user_id
                ) INTO v_email_conflict;

                IF v_email_conflict THEN
                    RETURN QUERY SELECT
                        'error'::VARCHAR,
                        'Email already exists'::VARCHAR;
                    RETURN;
                END IF;

                -- Store old values for audit
                SELECT jsonb_build_object(
                    'name', name,
                    'email', email,
                    'phoneNum', \"phoneNum\",
                    'address', address,
                    'city', city,
                    'state', state
                ) INTO v_old_values
                FROM users WHERE id = p_user_id;

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

                RETURN QUERY SELECT
                    'success'::VARCHAR,
                    'User updated successfully'::VARCHAR;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // USER UPDATE PASSWORD PROCEDURE
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION sp_user_update_password(
                p_user_id BIGINT,
                p_new_password VARCHAR,
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
                v_exists BOOLEAN;
            BEGIN
                -- Check if user exists
                SELECT EXISTS(SELECT 1 FROM users WHERE id = p_user_id) INTO v_exists;

                IF NOT v_exists THEN
                    RETURN QUERY SELECT
                        'error'::VARCHAR,
                        'User not found'::VARCHAR;
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

                RETURN QUERY SELECT
                    'success'::VARCHAR,
                    'Password updated successfully'::VARCHAR;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // USER DELETE PROCEDURE
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION sp_user_delete(
                p_user_id BIGINT,
                p_audit_user_id BIGINT,
                p_audit_user_name VARCHAR,
                p_audit_user_email VARCHAR,
                p_audit_user_role VARCHAR
            )
            RETURNS TABLE(
                o_user_name VARCHAR,
                o_status VARCHAR,
                o_message VARCHAR
            )
            AS \$\$
            DECLARE
                v_exists BOOLEAN;
                v_user_name VARCHAR;
            BEGIN
                -- Check if user exists
                SELECT name INTO v_user_name FROM users WHERE id = p_user_id;

                IF v_user_name IS NULL THEN
                    RETURN QUERY SELECT
                        NULL::VARCHAR,
                        'error'::VARCHAR,
                        'User not found'::VARCHAR;
                    RETURN;
                END IF;

                -- Delete user (cascade will delete adopter_profile)
                DELETE FROM users WHERE id = p_user_id;

                -- Note: Trigger trg_log_user_changes will automatically log this

                RETURN QUERY SELECT
                    v_user_name,
                    'success'::VARCHAR,
                    'User deleted successfully'::VARCHAR;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // USER SUSPEND PROCEDURE
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION sp_user_suspend(
                p_user_id BIGINT,
                p_reason TEXT,
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
                v_exists BOOLEAN;
            BEGIN
                -- Check if user exists
                SELECT EXISTS(SELECT 1 FROM users WHERE id = p_user_id) INTO v_exists;

                IF NOT v_exists THEN
                    RETURN QUERY SELECT
                        'error'::VARCHAR,
                        'User not found'::VARCHAR;
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

                RETURN QUERY SELECT
                    'success'::VARCHAR,
                    'User suspended successfully'::VARCHAR;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // USER LOCK PROCEDURE
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION sp_user_lock(
                p_user_id BIGINT,
                p_duration_minutes INTEGER,
                p_reason TEXT,
                p_audit_user_id BIGINT,
                p_audit_user_name VARCHAR,
                p_audit_user_email VARCHAR,
                p_audit_user_role VARCHAR
            )
            RETURNS TABLE(
                o_locked_until TIMESTAMP,
                o_status VARCHAR,
                o_message VARCHAR
            )
            AS \$\$
            DECLARE
                v_exists BOOLEAN;
                v_locked_until TIMESTAMP;
            BEGIN
                -- Check if user exists
                SELECT EXISTS(SELECT 1 FROM users WHERE id = p_user_id) INTO v_exists;

                IF NOT v_exists THEN
                    RETURN QUERY SELECT
                        NULL::TIMESTAMP,
                        'error'::VARCHAR,
                        'User not found'::VARCHAR;
                    RETURN;
                END IF;

                -- Calculate lock expiry
                v_locked_until := NOW() + (p_duration_minutes || ' minutes')::INTERVAL;

                -- Lock user
                UPDATE users
                SET
                    account_status = 'locked',
                    locked_until = v_locked_until,
                    lock_reason = p_reason,
                    updated_at = NOW()
                WHERE id = p_user_id;

                -- Note: Trigger trg_log_user_changes will automatically log this

                RETURN QUERY SELECT
                    v_locked_until,
                    'success'::VARCHAR,
                    'User locked successfully'::VARCHAR;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // USER UNLOCK PROCEDURE
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION sp_user_unlock(
                p_user_id BIGINT,
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
                v_exists BOOLEAN;
            BEGIN
                -- Check if user exists
                SELECT EXISTS(SELECT 1 FROM users WHERE id = p_user_id) INTO v_exists;

                IF NOT v_exists THEN
                    RETURN QUERY SELECT
                        'error'::VARCHAR,
                        'User not found'::VARCHAR;
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

                RETURN QUERY SELECT
                    'success'::VARCHAR,
                    'User unlocked successfully'::VARCHAR;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // USER FORCE PASSWORD RESET PROCEDURE
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION sp_user_force_password_reset(
                p_user_id BIGINT,
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
                v_exists BOOLEAN;
            BEGIN
                -- Check if user exists
                SELECT EXISTS(SELECT 1 FROM users WHERE id = p_user_id) INTO v_exists;

                IF NOT v_exists THEN
                    RETURN QUERY SELECT
                        'error'::VARCHAR,
                        'User not found'::VARCHAR;
                    RETURN;
                END IF;

                -- Force password reset
                UPDATE users
                SET
                    require_password_reset = TRUE,
                    updated_at = NOW()
                WHERE id = p_user_id;

                -- Note: Trigger trg_log_user_changes will automatically log this

                RETURN QUERY SELECT
                    'success'::VARCHAR,
                    'Password reset required'::VARCHAR;
            END;
            \$\$ LANGUAGE plpgsql;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS sp_user_create');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS sp_user_read');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS sp_user_update');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS sp_user_update_password');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS sp_user_delete');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS sp_user_suspend');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS sp_user_lock');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS sp_user_unlock');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS sp_user_force_password_reset');
    }
};
