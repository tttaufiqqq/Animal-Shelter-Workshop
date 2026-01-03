<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration creates WRAPPER FUNCTIONS that call the TRUE PROCEDURES
     * and return their OUT parameters as TABLE results (for Laravel compatibility)
     */
    public function up(): void
    {
        // ==========================================
        // USER CREATE WRAPPER
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION fn_user_create(
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
                v_status VARCHAR;
                v_message VARCHAR;
            BEGIN
                -- Call the TRUE PROCEDURE
                CALL sp_user_create_proc(
                    p_name, p_email, p_password, p_phone_num, p_address, p_city, p_state,
                    p_audit_user_id, p_audit_user_name, p_audit_user_email, p_audit_user_role,
                    v_user_id, v_status, v_message
                );

                -- Return OUT parameters as table
                RETURN QUERY SELECT v_user_id, v_status, v_message;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // USER UPDATE WRAPPER
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION fn_user_update(
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
                v_status VARCHAR;
                v_message VARCHAR;
            BEGIN
                CALL sp_user_update_proc(
                    p_user_id, p_name, p_email, p_phone_num, p_address, p_city, p_state,
                    p_audit_user_id, p_audit_user_name, p_audit_user_email, p_audit_user_role,
                    v_status, v_message
                );
                RETURN QUERY SELECT v_status, v_message;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // USER UPDATE PASSWORD WRAPPER
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION fn_user_update_password(
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
                v_status VARCHAR;
                v_message VARCHAR;
            BEGIN
                CALL sp_user_update_password_proc(
                    p_user_id, p_new_password,
                    p_audit_user_id, p_audit_user_name, p_audit_user_email, p_audit_user_role,
                    v_status, v_message
                );
                RETURN QUERY SELECT v_status, v_message;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // USER DELETE WRAPPER
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION fn_user_delete(
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
                v_user_name VARCHAR;
                v_status VARCHAR;
                v_message VARCHAR;
            BEGIN
                CALL sp_user_delete_proc(
                    p_user_id,
                    p_audit_user_id, p_audit_user_name, p_audit_user_email, p_audit_user_role,
                    v_user_name, v_status, v_message
                );
                RETURN QUERY SELECT v_user_name, v_status, v_message;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // USER SUSPEND WRAPPER
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION fn_user_suspend(
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
                v_status VARCHAR;
                v_message VARCHAR;
            BEGIN
                CALL sp_user_suspend_proc(
                    p_user_id, p_reason,
                    p_audit_user_id, p_audit_user_name, p_audit_user_email, p_audit_user_role,
                    v_status, v_message
                );
                RETURN QUERY SELECT v_status, v_message;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // USER LOCK WRAPPER
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION fn_user_lock(
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
                v_locked_until TIMESTAMP;
                v_status VARCHAR;
                v_message VARCHAR;
            BEGIN
                CALL sp_user_lock_proc(
                    p_user_id, p_duration_minutes, p_reason,
                    p_audit_user_id, p_audit_user_name, p_audit_user_email, p_audit_user_role,
                    v_locked_until, v_status, v_message
                );
                RETURN QUERY SELECT v_locked_until, v_status, v_message;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // USER UNLOCK WRAPPER
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION fn_user_unlock(
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
                v_status VARCHAR;
                v_message VARCHAR;
            BEGIN
                CALL sp_user_unlock_proc(
                    p_user_id,
                    p_audit_user_id, p_audit_user_name, p_audit_user_email, p_audit_user_role,
                    v_status, v_message
                );
                RETURN QUERY SELECT v_status, v_message;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // USER FORCE PASSWORD RESET WRAPPER
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION fn_user_force_password_reset(
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
                v_status VARCHAR;
                v_message VARCHAR;
            BEGIN
                CALL sp_user_force_password_reset_proc(
                    p_user_id,
                    p_audit_user_id, p_audit_user_name, p_audit_user_email, p_audit_user_role,
                    v_status, v_message
                );
                RETURN QUERY SELECT v_status, v_message;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // ADOPTER PROFILE UPSERT WRAPPER
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION fn_adopter_profile_upsert(
                p_adopter_id BIGINT,
                p_housing_type VARCHAR,
                p_has_children BOOLEAN,
                p_has_other_pets BOOLEAN,
                p_activity_level VARCHAR,
                p_experience VARCHAR,
                p_preferred_species VARCHAR,
                p_preferred_size VARCHAR,
                p_audit_user_id BIGINT,
                p_audit_user_name VARCHAR,
                p_audit_user_email VARCHAR,
                p_audit_user_role VARCHAR
            )
            RETURNS TABLE(
                o_profile_id BIGINT,
                o_status VARCHAR,
                o_message VARCHAR
            )
            AS \$\$
            DECLARE
                v_profile_id BIGINT;
                v_status VARCHAR;
                v_message VARCHAR;
            BEGIN
                CALL sp_adopter_profile_upsert_proc(
                    p_adopter_id, p_housing_type, p_has_children, p_has_other_pets,
                    p_activity_level, p_experience, p_preferred_species, p_preferred_size,
                    p_audit_user_id, p_audit_user_name, p_audit_user_email, p_audit_user_role,
                    v_profile_id, v_status, v_message
                );
                RETURN QUERY SELECT v_profile_id, v_status, v_message;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // ADOPTER PROFILE DELETE WRAPPER
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION fn_adopter_profile_delete(
                p_adopter_id BIGINT,
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
                v_status VARCHAR;
                v_message VARCHAR;
            BEGIN
                CALL sp_adopter_profile_delete_proc(
                    p_adopter_id,
                    p_audit_user_id, p_audit_user_name, p_audit_user_email, p_audit_user_role,
                    v_status, v_message
                );
                RETURN QUERY SELECT v_status, v_message;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // ROLE ASSIGN WRAPPER
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION fn_user_assign_role(
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
                v_status VARCHAR;
                v_message VARCHAR;
            BEGIN
                CALL sp_user_assign_role_proc(
                    p_user_id, p_role_id,
                    p_audit_user_id, p_audit_user_name, p_audit_user_email, p_audit_user_role,
                    v_status, v_message
                );
                RETURN QUERY SELECT v_status, v_message;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ==========================================
        // ROLE REVOKE WRAPPER
        // ==========================================
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION fn_user_revoke_role(
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
                v_status VARCHAR;
                v_message VARCHAR;
            BEGIN
                CALL sp_user_revoke_role_proc(
                    p_user_id, p_role_id,
                    p_audit_user_id, p_audit_user_name, p_audit_user_email, p_audit_user_role,
                    v_status, v_message
                );
                RETURN QUERY SELECT v_status, v_message;
            END;
            \$\$ LANGUAGE plpgsql;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop wrapper functions
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS fn_user_create');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS fn_user_update');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS fn_user_update_password');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS fn_user_delete');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS fn_user_suspend');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS fn_user_lock');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS fn_user_unlock');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS fn_user_force_password_reset');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS fn_adopter_profile_upsert');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS fn_adopter_profile_delete');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS fn_user_assign_role');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS fn_user_revoke_role');
    }
};
