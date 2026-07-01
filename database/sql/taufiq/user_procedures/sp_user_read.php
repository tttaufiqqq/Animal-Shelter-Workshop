<?php

return <<<'SQL'
CREATE OR REPLACE FUNCTION sp_user_read(p_user_id BIGINT)
RETURNS TABLE(
    id BIGINT,
    name VARCHAR,
    email VARCHAR,
    "phoneNum" VARCHAR,
    address TEXT,
    city VARCHAR,
    state VARCHAR,
    account_status VARCHAR,
    suspended_at TIMESTAMP,
    suspended_by BIGINT,
    suspension_reason TEXT,
    locked_until TIMESTAMP,
    lock_reason TEXT,
    failed_login_attempts INTEGER,
    last_failed_login_at TIMESTAMP,
    require_password_reset BOOLEAN,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)
AS $$
BEGIN
    RETURN QUERY
    SELECT
        u.id, u.name, u.email, u."phoneNum", u.address, u.city, u.state,
        u.account_status, u.suspended_at, u.suspended_by, u.suspension_reason,
        u.locked_until, u.lock_reason, u.failed_login_attempts,
        u.last_failed_login_at, u.require_password_reset,
        u.created_at, u.updated_at
    FROM users u
    WHERE u.id = p_user_id;
END;
$$ LANGUAGE plpgsql;
SQL;
