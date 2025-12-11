<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use App\Services\ForeignKeyValidator;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     * Updates password in Taufiq's database
     */
    public function update(Request $request): RedirectResponse
    {
        // Validate input
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        // Use transaction for Taufiq's database
        DB::connection('taufiq')->beginTransaction();

        try {
            // Get authenticated user (from Taufiq's database)
            $user = $request->user();

            // Update password in Taufiq's database
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            DB::connection('taufiq')->commit();

            // Log password change
            Log::info('Password changed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            // Clear user cache
            ForeignKeyValidator::clearUserCache($user->id);

            return back()->with('status', 'password-updated');

        } catch (\Exception $e) {
            DB::connection('taufiq')->rollBack();

            Log::error('Password update failed: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withErrors(['updatePassword' => 'Failed to update password. Please try again.']);
        }
    }
}
