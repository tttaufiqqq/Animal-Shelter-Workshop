<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class CaretakerController extends Controller
{
    /**
     * Store a newly created caretaker in storage.
     */
    public function store(Request $request)
    {
        // Validate the request using a named error bag
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phoneNum' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return redirect()->route('welcome')
                ->withErrors($validator, 'caretaker')
                ->withInput();
        }

        try {
            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phoneNum' => $request->phoneNum,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'password' => Hash::make($request->password),
            ]);

            // Assign the caretaker role
            $caretakerRole = Role::firstOrCreate(['name' => 'caretaker']);
            $user->assignRole($caretakerRole);

            return redirect()->route('welcome')
                ->with('caretaker_success', 'Caretaker account created successfully for ' . $user->name . '!');
        } catch (\Exception $e) {
            return redirect()->route('welcome')
                ->with('caretaker_error', 'Failed to create caretaker account. ' . $e->getMessage())
                ->withInput();
        }
    }
}
