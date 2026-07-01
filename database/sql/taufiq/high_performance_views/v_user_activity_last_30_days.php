<?php

return <<<'SQL'
CREATE OR REPLACE VIEW v_user_activity_last_30_days AS
SELECT
    u.id AS user_id,
    u.name,
    u.email,
    u.account_status,

    -- Activity Metrics
    COUNT(al.id) FILTER (
        WHERE al.performed_at >= NOW() - INTERVAL '30 days'
    )::INTEGER AS total_actions_30_days,

    COUNT(al.id) FILTER (
        WHERE al.performed_at >= NOW() - INTERVAL '7 days'
    )::INTEGER AS total_actions_7_days,

    COUNT(al.id) FILTER (
        WHERE al.performed_at >= CURRENT_DATE
    )::INTEGER AS total_actions_today,

    MAX(al.performed_at) AS last_activity_at,

    -- Activity Breakdown
    COUNT(al.id) FILTER (
        WHERE al.action LIKE '%created%'
    )::INTEGER AS create_actions,

    COUNT(al.id) FILTER (
        WHERE al.action LIKE '%updated%'
    )::INTEGER AS update_actions,

    COUNT(al.id) FILTER (
        WHERE al.action LIKE '%deleted%'
    )::INTEGER AS delete_actions,

    -- Days Since Last Activity
    CASE
        WHEN MAX(al.performed_at) IS NOT NULL THEN
            EXTRACT(DAY FROM (NOW() - MAX(al.performed_at)))::INTEGER
        ELSE NULL
    END AS days_since_last_activity,

    -- Activity Status
    CASE
        WHEN MAX(al.performed_at) >= NOW() - INTERVAL '7 days' THEN 'active'
        WHEN MAX(al.performed_at) >= NOW() - INTERVAL '30 days' THEN 'moderate'
        WHEN MAX(al.performed_at) IS NULL THEN 'never_active'
        ELSE 'inactive'
    END AS activity_status

FROM users u
LEFT JOIN audit_logs al ON al.user_id = u.id
WHERE u.created_at >= NOW() - INTERVAL '90 days'
GROUP BY u.id, u.name, u.email, u.account_status
ORDER BY last_activity_at DESC NULLS LAST;
SQL;
