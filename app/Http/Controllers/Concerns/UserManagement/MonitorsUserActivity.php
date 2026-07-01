<?php

namespace App\Http\Controllers\Concerns\UserManagement;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait MonitorsUserActivity
{
    public function getUserActivity($userId)
    {
        $user = User::findOrFail($userId);
        $currentUser = Auth::user();
        $canManage = $user->id !== $currentUser->id && !$user->hasRole('admin');

        $stats = AuditLog::selectRaw("
            COUNT(CASE WHEN action = 'login_success' THEN 1 END) as total_logins,
            COUNT(CASE WHEN action = 'login_failed' THEN 1 END) as failed_logins,
            COUNT(CASE WHEN action = 'login_failed' AND performed_at >= ? THEN 1 END) as recent_failed_logins,
            COUNT(DISTINCT ip_address) as unique_ips
        ", [now()->subDay()])
            ->where('category', 'authentication')
            ->where('user_email', $user->email)
            ->first();

        $authStats = [
            'total_logins' => (int) $stats->total_logins,
            'failed_logins' => (int) $stats->failed_logins,
            'recent_failed_logins' => (int) $stats->recent_failed_logins,
            'unique_ips' => (int) $stats->unique_ips,
        ];

        $recentActivity = AuditLog::select(['action', 'performed_at', 'ip_address', 'status'])
            ->where('category', 'authentication')
            ->where('user_email', $user->email)
            ->orderBy('performed_at', 'desc')
            ->limit(20)
            ->get();

        $suspiciousPatterns = $this->detectSuspiciousActivityOptimized($user);

        return response()->json([
            'user' => $user,
            'stats' => $authStats,
            'recent_activity' => $recentActivity,
            'suspicious_patterns' => $suspiciousPatterns,
            'can_manage' => $canManage,
        ]);
    }

    private function detectSuspiciousActivityOptimized(User $user)
    {
        $detection = AuditLog::selectRaw("
            COUNT(CASE WHEN action = 'login_failed' AND performed_at >= ? THEN 1 END) as failed_30min,
            COUNT(DISTINCT CASE WHEN performed_at >= ? THEN ip_address END) as unique_ips_1hr,
            COUNT(CASE WHEN action IN ('login_success', 'logout') AND performed_at >= ? THEN 1 END) as auth_events_10min
        ", [
            now()->subMinutes(30),
            now()->subHour(),
            now()->subMinutes(10),
        ])
            ->where('category', 'authentication')
            ->where('user_email', $user->email)
            ->first();

        $patterns = [];

        if ($detection->failed_30min >= 3) {
            $patterns[] = [
                'type' => 'multiple_failed_logins',
                'severity' => 'high',
                'description' => "{$detection->failed_30min} failed login attempts in last 30 minutes",
                'count' => $detection->failed_30min,
            ];
        }

        if ($detection->unique_ips_1hr > 2) {
            $patterns[] = [
                'type' => 'multiple_ip_addresses',
                'severity' => 'medium',
                'description' => "Login attempts from {$detection->unique_ips_1hr} different IP addresses in last hour",
            ];
        }

        if ($detection->auth_events_10min >= 5) {
            $patterns[] = [
                'type' => 'rapid_login_logout',
                'severity' => 'medium',
                'description' => "{$detection->auth_events_10min} login/logout events in last 10 minutes",
                'count' => $detection->auth_events_10min,
            ];
        }

        return $patterns;
    }

    private function detectSuspiciousActivity(User $user)
    {
        return $this->detectSuspiciousActivityOptimized($user);
    }
}
