<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use App\Services\AuditService;
use App\Services\TaufiqProcedureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    protected TaufiqProcedureService $taufiqService;

    public function __construct(TaufiqProcedureService $taufiqService)
    {
        $this->taufiqService = $taufiqService;
    }
    /**
     * Get detailed user activity and statistics
     * OPTIMIZED: Reduced from 13+ queries to just 3 queries
     */
    public function getUserActivity($userId)
    {
        $user = User::findOrFail($userId);

        $currentUser = Auth::user();

        // Check if current user can manage this user
        $canManage = $user->id !== $currentUser->id && !$user->hasRole('admin');

        // OPTIMIZED: Get ALL statistics in ONE query using aggregations
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

        // OPTIMIZED: Get recent activity (last 20) - single query
        $recentActivity = AuditLog::select(['action', 'performed_at', 'ip_address', 'status'])
            ->where('category', 'authentication')
            ->where('user_email', $user->email)
            ->orderBy('performed_at', 'desc')
            ->limit(20)
            ->get();

        // OPTIMIZED: Detect suspicious patterns with minimal queries
        $suspiciousPatterns = $this->detectSuspiciousActivityOptimized($user);

        return response()->json([
            'user' => $user,
            'stats' => $authStats,
            'recent_activity' => $recentActivity,
            'suspicious_patterns' => $suspiciousPatterns,
            'can_manage' => $canManage,
        ]);
    }

    /**
     * Suspend a user account permanently
     */
    public function suspendUser(Request $request, $userId)
    {
        try {
            $request->validate([
                'reason' => 'required|string|max:1000',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all()),
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $user = User::findOrFail($userId);
            $admin = Auth::user();

            // Prevent suspending yourself
            if ($user->id === $admin->id) {
                return response()->json(['success' => false, 'error' => 'You cannot suspend your own account'], 403);
            }

            // Prevent suspending other admins
            if ($user->hasRole('admin')) {
                return response()->json(['success' => false, 'error' => 'You cannot suspend admin accounts'], 403);
            }

            // Suspend user using stored procedure
            $result = $this->taufiqService->suspendUser($userId, $request->reason);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            // Note: Database trigger will automatically log this action to audit_logs

            return response()->json([
                'success' => true,
                'message' => 'User account suspended successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'error' => 'User not found'], 404);
        } catch (\Exception $e) {
            \Log::error('Error suspending user: ' . $e->getMessage(), [
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'error' => 'An error occurred while suspending the user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Lock a user account temporarily
     */
    public function lockUser(Request $request, $userId)
    {
        try {
            $request->validate([
                'duration' => 'required|in:1_hour,24_hours,7_days,custom',
                'custom_duration' => 'nullable|required_if:duration,custom|integer|min:1|max:168', // Max 168 hours (7 days)
                'reason' => 'required|string|max:1000',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all()),
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $user = User::findOrFail($userId);
            $admin = Auth::user();

            // Prevent locking yourself
            if ($user->id === $admin->id) {
                return response()->json(['success' => false, 'error' => 'You cannot lock your own account'], 403);
            }

            // Prevent locking other admins
            if ($user->hasRole('admin')) {
                return response()->json(['success' => false, 'error' => 'You cannot lock admin accounts'], 403);
            }

            // Calculate lock duration in minutes
            $durationMinutes = match($request->duration) {
                '1_hour' => 60,
                '24_hours' => 1440,
                '7_days' => 10080,
                'custom' => $request->custom_duration * 60,
            };

            // Lock user using stored procedure
            $result = $this->taufiqService->lockUser($userId, $durationMinutes, $request->reason);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            // Note: Database trigger will automatically log this action to audit_logs

            $lockedUntil = \Carbon\Carbon::parse($result['locked_until']);

            return response()->json([
                'success' => true,
                'message' => 'User account locked successfully until ' . $lockedUntil->format('Y-m-d H:i:s'),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'error' => 'User not found'], 404);
        } catch (\Exception $e) {
            \Log::error('Error locking user: ' . $e->getMessage(), [
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'error' => 'An error occurred while locking the user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Unlock a user account
     */
    public function unlockUser($userId)
    {
        $user = User::findOrFail($userId);
        $admin = Auth::user();

        if ($user->account_status !== 'locked' && $user->account_status !== 'suspended') {
            return response()->json(['error' => 'User account is not locked or suspended'], 400);
        }

        $previousStatus = $user->account_status;

        // Unlock user using stored procedure
        $result = $this->taufiqService->unlockUser($userId);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['message']
            ], 500);
        }

        // Note: Database trigger will automatically log this action to audit_logs

        return response()->json([
            'success' => true,
            'message' => 'User account unlocked successfully',
        ]);
    }

    /**
     * Reset a user's password by admin
     */
    public function forcePasswordReset(Request $request, $userId)
    {
        try {
            $request->validate([
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all()),
                'errors' => $e->errors(),
            ], 422);
        }

        $user = User::findOrFail($userId);
        $admin = Auth::user();

        // Prevent resetting yourself
        if ($user->id === $admin->id) {
            return response()->json(['error' => 'You cannot reset your own password through this method'], 403);
        }

        // Prevent resetting other admins
        if ($user->hasRole('admin')) {
            return response()->json(['error' => 'You cannot reset admin passwords'], 403);
        }

        // Update password using stored procedure
        $passwordResult = $this->taufiqService->updateUserPassword(
            $userId,
            Hash::make($request->password)
        );

        if (!$passwordResult['success']) {
            return response()->json([
                'success' => false,
                'error' => $passwordResult['message']
            ], 500);
        }

        // Force password reset using stored procedure
        $resetResult = $this->taufiqService->forcePasswordReset($userId);

        if (!$resetResult['success']) {
            return response()->json([
                'success' => false,
                'error' => $resetResult['message']
            ], 500);
        }

        // Note: Database trigger will automatically log this action to audit_logs

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully. User will be required to change it on next login.',
        ]);
    }


    /**
     * Detect suspicious activity patterns
     * OPTIMIZED: Reduced from 4 queries to 1 query using aggregations
     */
    private function detectSuspiciousActivityOptimized(User $user)
    {
        // OPTIMIZED: Get ALL suspicious patterns in ONE query
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

        // Pattern 1: Multiple failed logins in last 30 minutes
        if ($detection->failed_30min >= 3) {
            $patterns[] = [
                'type' => 'multiple_failed_logins',
                'severity' => 'high',
                'description' => "{$detection->failed_30min} failed login attempts in last 30 minutes",
                'count' => $detection->failed_30min,
            ];
        }

        // Pattern 2: Login from multiple IPs in last hour
        if ($detection->unique_ips_1hr > 2) {
            $patterns[] = [
                'type' => 'multiple_ip_addresses',
                'severity' => 'medium',
                'description' => "Login attempts from {$detection->unique_ips_1hr} different IP addresses in last hour",
            ];
        }

        // Pattern 3: Rapid login/logout cycles (5+ in 10 minutes)
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

    /**
     * Detect suspicious activity patterns (OLD - DEPRECATED)
     * Kept for reference - use detectSuspiciousActivityOptimized() instead
     */
    private function detectSuspiciousActivity(User $user)
    {
        return $this->detectSuspiciousActivityOptimized($user);
    }
}
