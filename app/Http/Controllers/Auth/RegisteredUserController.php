<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use App\Services\TaufiqProcedureService;

class RegisteredUserController extends Controller
{
    protected TaufiqProcedureService $taufiqService;

    public function __construct(TaufiqProcedureService $taufiqService)
    {
        $this->taufiqService = $taufiqService;
    }

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'phoneNum' => ['required', 'string', 'max:20'],
        ]);

        // Create user using stored procedure
        $result = $this->taufiqService->createUser([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'city' => $request->city,
            'state' => $request->state,
            'address' => $request->address,
            'phoneNum' => $request->phoneNum,
        ]);

        if (!$result['success']) {
            return back()->withErrors(['email' => $result['message']])->withInput();
        }

        // Get the created user
        $user = User::find($result['user_id']);

        // Get "public user" role ID
        $publicUserRole = Role::where('name', 'public user')->first();

        if ($publicUserRole) {
            // Assign role using stored procedure
            $roleResult = $this->taufiqService->assignRole($user->id, $publicUserRole->id);

            if (!$roleResult['success']) {
                \Log::warning('Failed to assign role to new user: ' . $roleResult['message'], [
                    'user_id' => $user->id
                ]);
            }
        }

        // Log the user in or return a response
        auth()->login($user);

         return redirect()->intended(route('welcome', absolute: false));
    }
}
