<?php

namespace App\Http\Controllers\Concerns\Audit;

use App\Models\AuditLog;

trait DetectsSuspiciousUsers
{
    private function detectSuspiciousUsers($logs)
    {
        $suspicious = [];
        $uniqueEmails = $logs->pluck('user_email')->unique();

        foreach ($uniqueEmails as $email) {
            if (!$email) continue;

            $patterns = [];

            $recentFailedLogins = AuditLog::category('authentication')
                ->action('login_failed')
                ->where('user_email', $email)
                ->where('performed_at', '>=', now()->subMinutes(30))
                ->count();

            if ($recentFailedLogins >= 3) {
                $patterns[] = [
                    'type' => 'multiple_failed_logins',
                    'severity' => 'high',
                    'count' => $recentFailedLogins,
                ];
            }

            $recentIPs = AuditLog::category('authentication')
                ->where('user_email', $email)
                ->where('performed_at', '>=', now()->subHour())
                ->distinct('ip_address')
                ->count('ip_address');

            if ($recentIPs > 2) {
                $patterns[] = [
                    'type' => 'multiple_ip_addresses',
                    'severity' => 'medium',
                    'count' => $recentIPs,
                ];
            }

            $recentAuthEvents = AuditLog::category('authentication')
                ->whereIn('action', ['login_success', 'logout'])
                ->where('user_email', $email)
                ->where('performed_at', '>=', now()->subMinutes(10))
                ->count();

            if ($recentAuthEvents >= 5) {
                $patterns[] = [
                    'type' => 'rapid_login_logout',
                    'severity' => 'medium',
                    'count' => $recentAuthEvents,
                ];
            }

            if (!empty($patterns)) {
                $suspicious[$email] = $patterns;
            }
        }

        return $suspicious;
    }
}
