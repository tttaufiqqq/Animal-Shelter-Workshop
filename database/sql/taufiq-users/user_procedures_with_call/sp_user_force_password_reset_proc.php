<?php

return <<<'SQL'
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
AS $$
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
$$;
SQL;
