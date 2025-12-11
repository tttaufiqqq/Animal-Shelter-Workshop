<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use App\Services\ForeignKeyValidator;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     * Creates user in Taufiq's database and assigns role
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate input
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'phoneNum' => ['required', 'string', 'max:20'],
        ]);

        // Use transaction for Taufiq's database (user and role assignment)
        DB::connection('taufiq')->beginTransaction();

        try {
            // Create user in Taufiq's database
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'city' => $request->city,
                'state' => $request->state,
                'address' => $request->address,
                'phoneNum' => $request->phoneNum,
            ]);

            // Assign the "public user" role to the newly created user
            // Role assignment is also in Taufiq's database (Spatie permissions)
            $user->assignRole('public user');

            // Commit transaction
            DB::connection('taufiq')->commit();

            // Log successful registration
            Log::info('New user registered', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => 'public user',
                'ip' => $request->ip(),
            ]);

            // Fire the Registered event (for email verification if enabled)
            event(new Registered($user));

            // Log the user in
            Auth::login($user);

            return redirect()->intended(route('welcome', absolute: false))
                ->with('success', 'Registration successful! Welcome to our platform.');

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::connection('taufiq')->rollBack();

            Log::error('User registration failed: ' . $e->getMessage(), [
                'email' => $request->email,
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['error' => 'Registration failed. Please try again or contact support.']);
        }
    }
}
