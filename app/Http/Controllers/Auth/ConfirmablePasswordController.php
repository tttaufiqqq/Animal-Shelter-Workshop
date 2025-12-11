<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirm the user's password.
     * Password validation queries Taufiq's database (User model connection)
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            // Validate password against Taufiq's database
            // Auth::guard('web')->validate() automatically uses User model's connection (taufiq)
            if (!Auth::guard('web')->validate([
                'email' => $request->user()->email,
                'password' => $request->password,
            ])) {
                throw ValidationException::withMessages([
                    'password' => __('auth.password'),
                ]);
            }

            // Store password confirmation timestamp in session
            $request->session()->put('auth.password_confirmed_at', time());

            // Log password confirmation for security audit
            Log::info('Password confirmed', [
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
                'ip' => $request->ip(),
            ]);

            return redirect()->intended(route('dashboard', absolute: false));

        } catch (ValidationException $e) {
            // Log failed password confirmation attempt
            Log::warning('Password confirmation failed', [
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
                'ip' => $request->ip(),
            ]);

            throw $e;
        } catch (\Exception $e) {
            // Log unexpected errors
            Log::error('Password confirmation error: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
                'trace' => $e->getTraceAsString(),
            ]);

            throw ValidationException::withMessages([
                'password' => 'An error occurred. Please try again.',
            ]);
        }
    }
}
