<?php

return <<<'SQL'
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
AS $$
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
        "phoneNum" = p_phone_num,
        address = p_address,
        city = p_city,
        state = p_state,
        updated_at = NOW()
    WHERE id = p_user_id;

    -- Note: Trigger trg_log_user_changes will automatically log this

    o_status := 'success';
    o_message := 'User updated successfully';
END;
$$;
SQL;
