<?php

return <<<'SQL'
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
$$ LANGUAGE plpgsql;
SQL;
