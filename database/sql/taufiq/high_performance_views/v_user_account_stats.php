<?php

return <<<'SQL'
CREATE MATERIALIZED VIEW v_user_account_stats AS
SELECT
    -- Total Users
    COUNT(*)::INTEGER AS total_users,

    -- Active Users
    COUNT(*) FILTER (WHERE account_status = 'active')::INTEGER AS active_users,

    -- Suspended Users
    COUNT(*) FILTER (WHERE account_status = 'suspended')::INTEGER AS suspended_users,

    -- Locked Users
    COUNT(*) FILTER (
        WHERE account_status = 'locked'
        AND locked_until > NOW()
    )::INTEGER AS locked_users,

    -- Users Requiring Password Reset
    COUNT(*) FILTER (WHERE require_password_reset = TRUE)::INTEGER AS users_requiring_password_reset,

    -- High Risk Users (3+ failed logins)
    COUNT(*) FILTER (WHERE failed_login_attempts >= 3)::INTEGER AS high_risk_users,

    -- New Users Today
    COUNT(*) FILTER (
        WHERE created_at >= CURRENT_DATE
    )::INTEGER AS new_users_today,

    -- New Users This Week
    COUNT(*) FILTER (
        WHERE created_at >= CURRENT_DATE - INTERVAL '7 days'
    )::INTEGER AS new_users_this_week,

    -- New Users This Month
    COUNT(*) FILTER (
        WHERE created_at >= CURRENT_DATE - INTERVAL '30 days'
    )::INTEGER AS new_users_this_month,

    -- Percentage Calculations (as decimals)
    ROUND(
        COUNT(*) FILTER (WHERE account_status = 'active')::NUMERIC /
        NULLIF(COUNT(*), 0) * 100,
        2
    ) AS active_percentage,

    -- Last Updated Timestamp
    NOW() AS stats_generated_at

FROM users;

CREATE UNIQUE INDEX idx_user_account_stats_singleton
ON v_user_account_stats ((1));
SQL;
