<?php

namespace App\Livewire\Concerns\Dashboard;

use App\Models\Booking;
use App\Models\Adoption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait DashboardAdminStats
{
    public function getAdditionalStats()
    {
        try {
            return [
                'pending_bookings' => Booking::where('status', 'Pending')->count(),
                'confirmed_bookings' => Booking::where('status', 'Confirmed')->count(),
                'total_revenue' => Adoption::sum('fee'),
                'average_booking_value' => Adoption::avg('fee'),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting additional stats: ' . $e->getMessage());
            return [
                'pending_bookings' => 0,
                'confirmed_bookings' => 0,
                'total_revenue' => 0,
                'average_booking_value' => 0,
            ];
        }
    }

    private function getUserManagementStats()
    {
        return $this->safeQuery(function() {
            $userStats = DB::connection('users')->select('SELECT * FROM get_user_account_stats()');
            $stats = $userStats[0] ?? null;

            $adopterStats = DB::connection('users')->select('SELECT * FROM get_adopter_profile_stats()');
            $adopter = $adopterStats[0] ?? null;

            $highRiskUsers = DB::connection('users')->select('SELECT COUNT(*) as count FROM get_high_risk_users(3)');
            $highRiskCount = $highRiskUsers[0]->count ?? 0;

            return (object) [
                'total_users' => $stats->total_users ?? 0,
                'active_users' => $stats->active_users ?? 0,
                'suspended_users' => $stats->suspended_users ?? 0,
                'locked_users' => $stats->locked_users ?? 0,
                'users_with_profiles' => $stats->users_with_profiles ?? 0,
                'avg_failed_attempts' => $stats->avg_failed_login_attempts ?? 0,
                'total_adopter_profiles' => $adopter->total_profiles ?? 0,
                'high_risk_users' => $highRiskCount,
            ];
        }, (object) [
            'total_users' => 0, 'active_users' => 0, 'suspended_users' => 0,
            'locked_users' => 0, 'users_with_profiles' => 0, 'avg_failed_attempts' => 0,
            'total_adopter_profiles' => 0, 'high_risk_users' => 0,
        ], 'users');
    }

    private function getAuditSummary()
    {
        return $this->safeQuery(function() {
            $results = DB::connection('users')->select('SELECT * FROM get_audit_summary_by_category(30)');

            return collect($results)->map(function($item) {
                return (object) [
                    'category' => $item->category,
                    'total_actions' => $item->total_actions,
                    'success_count' => $item->success_count,
                    'failure_count' => $item->failure_count,
                    'unique_users' => $item->unique_users,
                    'most_common_action' => $item->most_common_action,
                    'success_rate' => $item->total_actions > 0
                        ? round(($item->success_count / $item->total_actions) * 100, 2)
                        : 0,
                ];
            });
        }, collect([]), 'users');
    }
}
