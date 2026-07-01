<?php

return <<<'SQL'
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
AS $$
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
$$ LANGUAGE plpgsql;
SQL;
