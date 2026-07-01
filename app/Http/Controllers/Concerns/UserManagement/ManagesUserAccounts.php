<?php

namespace App\Http\Controllers\Concerns\UserManagement;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

trait ManagesUserAccounts
{
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

            if ($user->id === $admin->id) {
                return response()->json(['success' => false, 'error' => 'You cannot suspend your own account'], 403);
            }

            if ($user->hasRole('admin')) {
                return response()->json(['success' => false, 'error' => 'You cannot suspend admin accounts'], 403);
            }

            $result = $this->taufiqService->suspendUser($userId, $request->reason);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            return response()->json([
                'success' => true,
                'message' => 'User account suspended successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'error' => 'User not found'], 404);
        } catch (\Exception $e) {
            \Log::error('Error suspending user: ' . $e->getMessage(), [
                'user_id' => $userId,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'error' => 'An error occurred while suspending the user: ' . $e->getMessage()], 500);
        }
    }

    public function lockUser(Request $request, $userId)
    {
        try {
            $request->validate([
                'duration' => 'required|in:1_hour,24_hours,7_days,custom',
                'custom_duration' => 'nullable|required_if:duration,custom|integer|min:1|max:168',
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

            if ($user->id === $admin->id) {
                return response()->json(['success' => false, 'error' => 'You cannot lock your own account'], 403);
            }

            if ($user->hasRole('admin')) {
                return response()->json(['success' => false, 'error' => 'You cannot lock admin accounts'], 403);
            }

            $durationMinutes = match($request->duration) {
                '1_hour' => 60,
                '24_hours' => 1440,
                '7_days' => 10080,
                'custom' => $request->custom_duration * 60,
            };

            $result = $this->taufiqService->lockUser($userId, $durationMinutes, $request->reason);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

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
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'error' => 'An error occurred while locking the user: ' . $e->getMessage()], 500);
        }
    }

    public function unlockUser($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->account_status !== 'locked' && $user->account_status !== 'suspended') {
            return response()->json(['error' => 'User account is not locked or suspended'], 400);
        }

        $result = $this->taufiqService->unlockUser($userId);

        if (!$result['success']) {
            return response()->json(['success' => false, 'error' => $result['message']], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'User account unlocked successfully',
        ]);
    }

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

        if ($user->id === $admin->id) {
            return response()->json(['error' => 'You cannot reset your own password through this method'], 403);
        }

        if ($user->hasRole('admin')) {
            return response()->json(['error' => 'You cannot reset admin passwords'], 403);
        }

        $passwordResult = $this->taufiqService->updateUserPassword(
            $userId,
            Hash::make($request->password)
        );

        if (!$passwordResult['success']) {
            return response()->json(['success' => false, 'error' => $passwordResult['message']], 500);
        }

        $resetResult = $this->taufiqService->forcePasswordReset($userId);

        if (!$resetResult['success']) {
            return response()->json(['success' => false, 'error' => $resetResult['message']], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully. User will be required to change it on next login.',
        ]);
    }
}
