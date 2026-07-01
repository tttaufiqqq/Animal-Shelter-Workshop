<?php

return <<<'SQL'
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
AS $$
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
$$ LANGUAGE plpgsql;
SQL;
