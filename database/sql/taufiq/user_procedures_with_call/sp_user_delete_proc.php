<?php

return <<<'SQL'
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
AS $$
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
$$;
SQL;
