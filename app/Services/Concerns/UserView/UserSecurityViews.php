<?php

namespace App\Services\Concerns\UserView;

use Illuminate\Support\Facades\DB;

trait UserSecurityViews
{
    public function getHighRiskUsers(int $minFailedAttempts = 1, ?string $riskLevel = null): array
    {
        $sql = 'SELECT * FROM v_high_risk_users WHERE failed_login_attempts >= ?';
        $params = [$minFailedAttempts];

        if ($riskLevel) {
            $sql .= ' AND risk_level = ?';
            $params[] = $riskLevel;
        }

        $sql .= ' ORDER BY risk_score DESC, failed_login_attempts DESC';

        return DB::connection('users')->select($sql, $params);
    }

    public function getCriticalSecurityAlerts(): array
    {
        return DB::connection('users')->select(
            "SELECT * FROM v_high_risk_users
             WHERE risk_score >= 60
             ORDER BY risk_score DESC"
        );
    }

    public function getUserActivity(?string $activityStatus = null): array
    {
        $sql = 'SELECT * FROM v_user_activity_last_30_days';

        if ($activityStatus) {
            $sql .= ' WHERE activity_status = ?';
            $params = [$activityStatus];
        } else {
            $params = [];
        }

        $sql .= ' ORDER BY last_activity_at DESC NULLS LAST';

        return DB::connection('users')->select($sql, $params);
    }
}
