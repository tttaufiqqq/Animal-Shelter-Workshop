<?php

return <<<'SQL'
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
AS $$
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
$$ LANGUAGE plpgsql;
SQL;
