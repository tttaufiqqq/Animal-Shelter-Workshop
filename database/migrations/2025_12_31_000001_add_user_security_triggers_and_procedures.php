<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * User Account Security - Auto-lock, Auto-unlock, Batch operations
     */
    public function up(): void
    {
        // 1. Auto-Lock Account After Failed Login Attempts
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION auto_lock_account_on_failed_login()
            RETURNS TRIGGER AS $$
            BEGIN
                -- Lock account for 30 minutes after 5 failed login attempts
                IF NEW.failed_login_attempts >= 5 AND OLD.failed_login_attempts < 5 THEN
                    NEW.account_status := 'locked';
                    NEW.locked_until := NOW() + INTERVAL '30 minutes';
                    NEW.lock_reason := 'Too many failed login attempts';
                END IF;

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::connection('taufiq')->unprepared("
            CREATE TRIGGER trg_auto_lock_account
                BEFORE UPDATE ON users
                FOR EACH ROW
                WHEN (NEW.failed_login_attempts IS DISTINCT FROM OLD.failed_login_attempts)
                EXECUTE FUNCTION auto_lock_account_on_failed_login();
        ");

        // 2. Stored Procedure: Batch Unlock Expired Accounts
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION unlock_expired_accounts()
            RETURNS TABLE(unlocked_count INTEGER) AS $$
            DECLARE
                affected_rows INTEGER;
            BEGIN
                UPDATE users
                SET
                    account_status = 'active',
                    locked_until = NULL,
                    lock_reason = NULL,
                    failed_login_attempts = 0
                WHERE
                    account_status = 'locked'
                    AND locked_until < NOW();

                GET DIAGNOSTICS affected_rows = ROW_COUNT;

                RETURN QUERY SELECT affected_rows;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // 3. Stored Procedure: Reset Failed Login Attempts
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION reset_failed_login_attempts(p_user_id BIGINT)
            RETURNS VOID AS $$
            BEGIN
                UPDATE users
                SET
                    failed_login_attempts = 0,
                    last_failed_login_at = NULL
                WHERE id = p_user_id;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // 4. Stored Procedure: Increment Failed Login Attempts
        DB::connection('taufiq')->unprepared("
            CREATE OR REPLACE FUNCTION increment_failed_login_attempts(p_user_id BIGINT)
            RETURNS TABLE(
                new_attempt_count INTEGER,
                is_locked BOOLEAN,
                locked_until_time TIMESTAMP
            ) AS $$
            DECLARE
                v_new_count INTEGER;
                v_is_locked BOOLEAN;
                v_locked_until TIMESTAMP;
            BEGIN
                UPDATE users
                SET
                    failed_login_attempts = failed_login_attempts + 1,
                    last_failed_login_at = NOW()
                WHERE id = p_user_id
                RETURNING failed_login_attempts,
                          (account_status = 'locked'),
                          users.locked_until
                INTO v_new_count, v_is_locked, v_locked_until;

                RETURN QUERY SELECT v_new_count, v_is_locked, v_locked_until;
            END;
            $$ LANGUAGE plpgsql;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('taufiq')->unprepared('DROP TRIGGER IF EXISTS trg_auto_lock_account ON users');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS auto_lock_account_on_failed_login()');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS unlock_expired_accounts()');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS reset_failed_login_attempts(BIGINT)');
        DB::connection('taufiq')->unprepared('DROP FUNCTION IF EXISTS increment_failed_login_attempts(BIGINT)');
    }
};
