<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\AdopterProfile;
use Illuminate\Validation\Rule;
use App\DatabaseErrorHandler;

class ProfileController extends Controller
{
    use DatabaseErrorHandler;
    /**
     * Display the user's profile form.
     */
    public function storeOrUpdate(Request $request)
    {
        // 1. Validation Rules
        $validated = $request->validate([
            'housing_type' => ['required', Rule::in(['condo', 'landed', 'apartment', 'hdb'])],
            'has_children' => ['required', 'boolean'],
            'has_other_pets' => ['required', 'boolean'],
            'activity_level' => ['required', Rule::in(['low', 'medium', 'high'])],
            'experience' => ['required', Rule::in(['beginner', 'intermediate', 'expert'])],
            'preferred_species' => ['required', Rule::in(['cat', 'dog', 'both'])],
            'preferred_size' => ['required', Rule::in(['small', 'medium', 'large', 'any'])],
        ]);

        // 2. Upsert (Update or Insert) the Profile
        // We look for a profile linked to the authenticated user's ID.
        AdopterProfile::updateOrCreate(
            ['adopterID' => Auth::id()], // Key to find the record
            $validated                 // Data to create or update
        );

        // 3. Determine message based on action
        $message = AdopterProfile::where('adopterID', Auth::id())->exists() ?
                   'Adopter Profile updated successfully!' :
                   'Adopter Profile created successfully!';

        // 4. Redirect back with success message
        return redirect()->back()->with('success', $message);
    }
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
