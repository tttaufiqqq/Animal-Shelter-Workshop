<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaufiqProcedureService
{
    /**
     * Get audit context for passing to PostgreSQL procedures
     * Note: PostgreSQL doesn't support session variables like MySQL (@variable)
     * So we pass these as parameters to each procedure
     *
     * @return array
     */
    protected function getAuditContext(): array
    {
        $user = Auth::user();

        return [
            'user_id' => $user->id ?? null,
            'user_name' => $user->name ?? null,
            'user_email' => $user->email ?? null,
            'user_role' => $user ? $user->getRoleNames()->first() : null,
        ];
    }

    // ==========================================
    // USER PROCEDURES
    // ==========================================

    /**
     * Create a new user
     * Uses wrapper function fn_user_create() which calls procedure sp_user_create_proc()
     *
     * @param  array  $data  ['name', 'email', 'password', 'phoneNum', 'address', 'city', 'state']
     * @return array ['success' => bool, 'user_id' => int|null, 'message' => string]
     */
    public function createUser(array $data): array
    {
        $audit = $this->getAuditContext();

        // Call wrapper function (which internally calls the TRUE PROCEDURE)
        $result = DB::connection('taufiq')->select('SELECT * FROM fn_user_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $data['name'],
            $data['email'],
            $data['password'],
            $data['phoneNum'] ?? null,
            $data['address'] ?? null,
            $data['city'] ?? null,
            $data['state'] ?? null,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'user_id' => $row->o_user_id ?? null,
            'message' => $row->o_message ?? 'Failed to create user',
        ];
    }

    /**
     * Read a single user by ID
     *
     * @param  int  $userId
     * @return object|null
     */
    public function readUser(int $userId): ?object
    {
        $result = DB::connection('taufiq')->select('SELECT * FROM sp_user_read(?)', [$userId]);

        return $result[0] ?? null;
    }

    /**
     * Update a user
     * Uses wrapper function fn_user_update() which calls procedure sp_user_update_proc()
     *
     * @param  int  $userId
     * @param  array  $data  ['name', 'email', 'phoneNum', 'address', 'city', 'state']
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateUser(int $userId, array $data): array
    {
        $audit = $this->getAuditContext();

        // Call wrapper function (which internally calls the TRUE PROCEDURE)
        $result = DB::connection('taufiq')->select('SELECT * FROM fn_user_update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $userId,
            $data['name'],
            $data['email'],
            $data['phoneNum'] ?? null,
            $data['address'] ?? null,
            $data['city'] ?? null,
            $data['state'] ?? null,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'message' => $row->o_message ?? 'Failed to update user',
        ];
    }

    /**
     * Update user password
     * Uses wrapper function fn_user_update_password() which calls procedure sp_user_update_password_proc()
     *
     * @param  int  $userId
     * @param  string  $newPassword
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateUserPassword(int $userId, string $newPassword): array
    {
        $audit = $this->getAuditContext();

        // Call wrapper function (which internally calls the TRUE PROCEDURE)
        $result = DB::connection('taufiq')->select('SELECT * FROM fn_user_update_password(?, ?, ?, ?, ?, ?)', [
            $userId,
            $newPassword,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'message' => $row->o_message ?? 'Failed to update password',
        ];
    }

    /**
     * Delete a user
     * Uses wrapper function fn_user_delete() which calls procedure sp_user_delete_proc()
     *
     * @param  int  $userId
     * @return array ['success' => bool, 'user_name' => string|null, 'message' => string]
     */
    public function deleteUser(int $userId): array
    {
        $audit = $this->getAuditContext();

        // Call wrapper function (which internally calls the TRUE PROCEDURE)
        $result = DB::connection('taufiq')->select('SELECT * FROM fn_user_delete(?, ?, ?, ?, ?)', [
            $userId,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'user_name' => $row->o_user_name ?? null,
            'message' => $row->o_message ?? 'Failed to delete user',
        ];
    }

    /**
     * Suspend a user account
     * Uses wrapper function fn_user_suspend() which calls procedure sp_user_suspend_proc()
     *
     * @param  int  $userId
     * @param  string  $reason
     * @return array ['success' => bool, 'message' => string]
     */
    public function suspendUser(int $userId, string $reason): array
    {
        $audit = $this->getAuditContext();

        // Call wrapper function (which internally calls the TRUE PROCEDURE)
        $result = DB::connection('taufiq')->select('SELECT * FROM fn_user_suspend(?, ?, ?, ?, ?, ?)', [
            $userId,
            $reason,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'message' => $row->o_message ?? 'Failed to suspend user',
        ];
    }

    /**
     * Lock a user account temporarily
     * Uses wrapper function fn_user_lock() which calls procedure sp_user_lock_proc()
     *
     * @param  int  $userId
     * @param  int  $durationMinutes
     * @param  string  $reason
     * @return array ['success' => bool, 'locked_until' => string|null, 'message' => string]
     */
    public function lockUser(int $userId, int $durationMinutes, string $reason): array
    {
        $audit = $this->getAuditContext();

        // Call wrapper function (which internally calls the TRUE PROCEDURE)
        $result = DB::connection('taufiq')->select('SELECT * FROM fn_user_lock(?, ?, ?, ?, ?, ?, ?)', [
            $userId,
            $durationMinutes,
            $reason,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'locked_until' => $row->o_locked_until ?? null,
            'message' => $row->o_message ?? 'Failed to lock user',
        ];
    }

    /**
     * Unlock a user account
     * Uses wrapper function fn_user_unlock() which calls procedure sp_user_unlock_proc()
     *
     * @param  int  $userId
     * @return array ['success' => bool, 'message' => string]
     */
    public function unlockUser(int $userId): array
    {
        $audit = $this->getAuditContext();

        // Call wrapper function (which internally calls the TRUE PROCEDURE)
        $result = DB::connection('taufiq')->select('SELECT * FROM fn_user_unlock(?, ?, ?, ?, ?)', [
            $userId,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'message' => $row->o_message ?? 'Failed to unlock user',
        ];
    }

    /**
     * Force password reset for a user
     * Uses wrapper function fn_user_force_password_reset() which calls procedure sp_user_force_password_reset_proc()
     *
     * @param  int  $userId
     * @return array ['success' => bool, 'message' => string]
     */
    public function forcePasswordReset(int $userId): array
    {
        $audit = $this->getAuditContext();

        // Call wrapper function (which internally calls the TRUE PROCEDURE)
        $result = DB::connection('taufiq')->select('SELECT * FROM fn_user_force_password_reset(?, ?, ?, ?, ?)', [
            $userId,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'message' => $row->o_message ?? 'Failed to force password reset',
        ];
    }

    // ==========================================
    // ADOPTER PROFILE PROCEDURES
    // ==========================================

    /**
     * Create or update an adopter profile
     * Uses wrapper function fn_adopter_profile_upsert() which calls procedure sp_adopter_profile_upsert_proc()
     *
     * @param  int  $adopterId
     * @param  array  $data  ['housing_type', 'has_children', 'has_other_pets', 'activity_level', 'experience', 'preferred_species', 'preferred_size']
     * @return array ['success' => bool, 'profile_id' => int|null, 'message' => string]
     */
    public function upsertAdopterProfile(int $adopterId, array $data): array
    {
        $audit = $this->getAuditContext();

        // Call wrapper function (which internally calls the TRUE PROCEDURE)
        $result = DB::connection('taufiq')->select('SELECT * FROM fn_adopter_profile_upsert(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $adopterId,
            $data['housing_type'] ?? null,
            $data['has_children'] ?? false,
            $data['has_other_pets'] ?? false,
            $data['activity_level'] ?? null,
            $data['experience'] ?? null,
            $data['preferred_species'] ?? null,
            $data['preferred_size'] ?? null,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'profile_id' => $row->o_profile_id ?? null,
            'message' => $row->o_message ?? 'Failed to save adopter profile',
        ];
    }

    /**
     * Read adopter profile by user ID
     *
     * @param  int  $adopterId
     * @return object|null
     */
    public function readAdopterProfile(int $adopterId): ?object
    {
        $result = DB::connection('taufiq')->select('SELECT * FROM sp_adopter_profile_read(?)', [$adopterId]);

        return $result[0] ?? null;
    }

    /**
     * Delete adopter profile
     * Uses wrapper function fn_adopter_profile_delete() which calls procedure sp_adopter_profile_delete_proc()
     *
     * @param  int  $adopterId
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteAdopterProfile(int $adopterId): array
    {
        $audit = $this->getAuditContext();

        // Call wrapper function (which internally calls the TRUE PROCEDURE)
        $result = DB::connection('taufiq')->select('SELECT * FROM fn_adopter_profile_delete(?, ?, ?, ?, ?)', [
            $adopterId,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'message' => $row->o_message ?? 'Failed to delete adopter profile',
        ];
    }

    // ==========================================
    // ROLE ASSIGNMENT PROCEDURES
    // ==========================================

    /**
     * Assign role to user
     * Uses wrapper function fn_user_assign_role() which calls procedure sp_user_assign_role_proc()
     *
     * @param  int  $userId
     * @param  int  $roleId
     * @return array ['success' => bool, 'message' => string]
     */
    public function assignRole(int $userId, int $roleId): array
    {
        $audit = $this->getAuditContext();

        // Call wrapper function (which internally calls the TRUE PROCEDURE)
        $result = DB::connection('taufiq')->select('SELECT * FROM fn_user_assign_role(?, ?, ?, ?, ?, ?)', [
            $userId,
            $roleId,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'message' => $row->o_message ?? 'Failed to assign role',
        ];
    }

    /**
     * Revoke role from user
     * Uses wrapper function fn_user_revoke_role() which calls procedure sp_user_revoke_role_proc()
     *
     * @param  int  $userId
     * @param  int  $roleId
     * @return array ['success' => bool, 'message' => string]
     */
    public function revokeRole(int $userId, int $roleId): array
    {
        $audit = $this->getAuditContext();

        // Call wrapper function (which internally calls the TRUE PROCEDURE)
        $result = DB::connection('taufiq')->select('SELECT * FROM fn_user_revoke_role(?, ?, ?, ?, ?, ?)', [
            $userId,
            $roleId,
            $audit['user_id'],
            $audit['user_name'],
            $audit['user_email'],
            $audit['user_role'],
        ]);

        $row = $result[0] ?? null;

        return [
            'success' => $row && $row->o_status === 'success',
            'message' => $row->o_message ?? 'Failed to revoke role',
        ];
    }

    // ==========================================
    // ANALYTICS & REPORTING (Using existing procedures)
    // ==========================================

    /**
     * Get user account statistics
     *
     * @return object|null
     */
    public function getUserAccountStats(): ?object
    {
        $result = DB::connection('taufiq')->select('SELECT * FROM get_user_account_stats()');

        return $result[0] ?? null;
    }

    /**
     * Get adopter profile statistics
     *
     * @return object|null
     */
    public function getAdopterProfileStats(): ?object
    {
        $result = DB::connection('taufiq')->select('SELECT * FROM get_adopter_profile_stats()');

        return $result[0] ?? null;
    }

    /**
     * Get recent registrations
     *
     * @param  int  $daysBack
     * @return array
     */
    public function getRecentRegistrations(int $daysBack = 7): array
    {
        return DB::connection('taufiq')->select('SELECT * FROM get_recent_registrations(?)', [$daysBack]);
    }

    /**
     * Get high risk users
     *
     * @param  int  $threshold
     * @return array
     */
    public function getHighRiskUsers(int $threshold = 3): array
    {
        return DB::connection('taufiq')->select('SELECT * FROM get_high_risk_users(?)', [$threshold]);
    }

    /**
     * Get user activity summary
     *
     * @param  int  $userId
     * @param  int  $daysBack
     * @return object|null
     */
    public function getUserActivitySummary(int $userId, int $daysBack = 30): ?object
    {
        $result = DB::connection('taufiq')->select('SELECT * FROM get_user_activity_summary(?, ?)', [$userId, $daysBack]);

        return $result[0] ?? null;
    }

    // ==========================================
    // SECURITY PROCEDURES (Using existing procedures)
    // ==========================================

    /**
     * Reset failed login attempts for a user
     *
     * @param  int  $userId
     * @return void
     */
    public function resetFailedLoginAttempts(int $userId): void
    {
        DB::connection('taufiq')->select('SELECT reset_failed_login_attempts(?)', [$userId]);
    }

    /**
     * Increment failed login attempts
     *
     * @param  int  $userId
     * @return object|null ['new_attempt_count', 'is_locked', 'locked_until_time']
     */
    public function incrementFailedLoginAttempts(int $userId): ?object
    {
        $result = DB::connection('taufiq')->select('SELECT * FROM increment_failed_login_attempts(?)', [$userId]);

        return $result[0] ?? null;
    }

    /**
     * Unlock expired accounts (batch operation)
     *
     * @return int Number of unlocked accounts
     */
    public function unlockExpiredAccounts(): int
    {
        $result = DB::connection('taufiq')->select('SELECT * FROM unlock_expired_accounts()');

        return $result[0]->unlocked_count ?? 0;
    }

    // ==========================================
    // MAINTENANCE PROCEDURES (Using existing procedures)
    // ==========================================

    /**
     * Run scheduled maintenance tasks
     *
     * @return array
     */
    public function runScheduledMaintenance(): array
    {
        return DB::connection('taufiq')->select('SELECT * FROM run_scheduled_maintenance()');
    }

    /**
     * Cleanup old audit logs
     *
     * @param  int  $retentionDays
     * @return int Number of deleted records
     */
    public function cleanupOldAuditLogs(int $retentionDays = 90): int
    {
        $result = DB::connection('taufiq')->select('SELECT * FROM cleanup_old_audit_logs(?)', [$retentionDays]);

        return $result[0]->deleted_count ?? 0;
    }
}
