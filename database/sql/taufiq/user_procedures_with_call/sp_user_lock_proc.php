<?php

return <<<'SQL'
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
AS $$
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
$$;
SQL;
