<?php

return <<<'SQL'
CREATE OR REPLACE VIEW v_high_risk_users AS
SELECT
    u.id,
    u.name,
    u.email,
    u.account_status,
    u.failed_login_attempts,
    u.last_failed_login_at,
    u.locked_until,
    u.lock_reason,

    -- Risk Score (0-100)
    LEAST(
        (u.failed_login_attempts * 20) +
        CASE
            WHEN u.account_status = 'locked' THEN 20
            WHEN u.account_status = 'suspended' THEN 30
            ELSE 0
        END +
        CASE
            WHEN u.last_failed_login_at > NOW() - INTERVAL '1 hour' THEN 15
            WHEN u.last_failed_login_at > NOW() - INTERVAL '24 hours' THEN 10
            ELSE 0
        END,
        100
    ) AS risk_score,

    -- Risk Level
    CASE
        WHEN u.failed_login_attempts >= 5 OR u.account_status = 'suspended' THEN 'critical'
        WHEN u.failed_login_attempts >= 3 OR u.account_status = 'locked' THEN 'high'
        WHEN u.failed_login_attempts >= 1 THEN 'medium'
        ELSE 'low'
    END AS risk_level,

    -- Time Since Last Failure
    CASE
        WHEN u.last_failed_login_at IS NOT NULL THEN
            EXTRACT(EPOCH FROM (NOW() - u.last_failed_login_at))::INTEGER
        ELSE NULL
    END AS seconds_since_last_failure,

    u.created_at,
    u.updated_at

FROM users u
WHERE u.failed_login_attempts >= 1
   OR u.account_status IN ('locked', 'suspended')
ORDER BY
    CASE
        WHEN u.account_status = 'suspended' THEN 1
        WHEN u.account_status = 'locked' THEN 2
        ELSE 3
    END,
    u.failed_login_attempts DESC,
    u.last_failed_login_at DESC NULLS LAST;
SQL;
