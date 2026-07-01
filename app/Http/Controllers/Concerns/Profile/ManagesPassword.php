<?php

namespace App\Http\Controllers\Concerns\Profile;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

trait ManagesPassword
{
    public function showChangePasswordForm(): View|RedirectResponse
    {
        $user = Auth::user();

        if (!$user->require_password_reset) {
            return redirect('/')
                ->with('info', 'You do not need to change your password.');
        }

        return view('auth.change-password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'confirmed', Password::min(8)],
            ]);

            $result = $this->taufiqService->updateUserPassword(
                $user->id,
                Hash::make($validated['password'])
            );

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            return redirect('/')
                ->with('success', 'Your password has been changed successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('password.change')
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Error changing password: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('password.change')
                ->with('error', 'Failed to change password: ' . $e->getMessage() . ' [' . get_class($e) . ']');
        }
    }

    public function updateProfilePassword(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'current_password' => ['required', 'current_password:web'],
                'password' => ['required', 'confirmed', Password::min(8)],
            ], [
                'current_password.current_password' => 'The provided password does not match your current password.',
            ]);

            $result = $this->taufiqService->updateUserPassword(
                $request->user()->id,
                Hash::make($validated['password'])
            );

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            return redirect()->route('profile.edit')
                ->with('status', 'password-updated');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('profile.edit')
                ->withErrors($e->errors(), 'updatePassword')
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Error updating password: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('profile.edit')
                ->withErrors(['password' => 'Failed to update password: ' . $e->getMessage() . ' [' . get_class($e) . ']'], 'updatePassword');
        }
    }
}
