<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Services\ForeignKeyValidator;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     * Resets password in Taufiq's database
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Use transaction for Taufiq's database
        DB::connection('taufiq')->beginTransaction();

        try {
            // Password::reset() automatically uses User model's connection (taufiq)
            // It queries password_resets table and updates users table in Taufiq's database
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user) use ($request) {
                    // Update user in Taufiq's database
                    $user->forceFill([
                        'password' => Hash::make($request->password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    // Fire password reset event
                    event(new PasswordReset($user));

                    // Log successful password reset
                    Log::info('Password reset successful', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'ip' => $request->ip(),
                    ]);

                    // Clear user cache
                    ForeignKeyValidator::clearUserCache($user->id);
                }
            );

            DB::connection('taufiq')->commit();

            // If the password was successfully reset, redirect to login
            if ($status == Password::PASSWORD_RESET) {
                return redirect()->route('login')
                    ->with('status', __($status))
                    ->with('success', 'Your password has been reset successfully. Please login with your new password.');
            }

            // If reset failed, go back with error
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);

        } catch (\Exception $e) {
            DB::connection('taufiq')->rollBack();

            Log::error('Password reset failed: ' . $e->getMessage(), [
                'email' => $request->email,
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Password reset failed. Please try again or contact support.']);
        }
    }
}
