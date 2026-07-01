<?php

namespace App\Services\Concerns\UserProcedure;

use Illuminate\Support\Facades\DB;

trait UserAnalyticsAndMaintenance
{
    public function getUserAccountStats(): ?object
    {
        $result = DB::connection('users')->select('SELECT * FROM get_user_account_stats()');

        return $result[0] ?? null;
    }

    public function getAdopterProfileStats(): ?object
    {
        $result = DB::connection('users')->select('SELECT * FROM get_adopter_profile_stats()');

        return $result[0] ?? null;
    }

    public function getRecentRegistrations(int $daysBack = 7): array
    {
        return DB::connection('users')->select('SELECT * FROM get_recent_registrations(?)', [$daysBack]);
    }

    public function getHighRiskUsers(int $threshold = 3): array
    {
        return DB::connection('users')->select('SELECT * FROM get_high_risk_users(?)', [$threshold]);
    }

    public function getUserActivitySummary(int $userId, int $daysBack = 30): ?object
    {
        $result = DB::connection('users')->select('SELECT * FROM get_user_activity_summary(?, ?)', [$userId, $daysBack]);

        return $result[0] ?? null;
    }

    public function resetFailedLoginAttempts(int $userId): void
    {
        DB::connection('users')->select('SELECT reset_failed_login_attempts(?)', [$userId]);
    }

    public function incrementFailedLoginAttempts(int $userId): ?object
    {
        $result = DB::connection('users')->select('SELECT * FROM increment_failed_login_attempts(?)', [$userId]);

        return $result[0] ?? null;
    }

    public function unlockExpiredAccounts(): int
    {
        $result = DB::connection('users')->select('SELECT * FROM unlock_expired_accounts()');

        return $result[0]->unlocked_count ?? 0;
    }

    public function runScheduledMaintenance(): array
    {
        return DB::connection('users')->select('SELECT * FROM run_scheduled_maintenance()');
    }

    public function cleanupOldAuditLogs(int $retentionDays = 90): int
    {
        $result = DB::connection('users')->select('SELECT * FROM cleanup_old_audit_logs(?)', [$retentionDays]);

        return $result[0]->deleted_count ?? 0;
    }
}
