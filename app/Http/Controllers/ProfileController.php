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
        try {
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

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Error saving adopter profile: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to save Adopter Profile: ' . $e->getMessage());
        }
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
        try {
            $request->user()->fill($request->validated());

            $request->user()->save();

            return Redirect::route('profile.edit')->with('status', 'profile-updated');

        } catch (\Exception $e) {
            \Log::error('Error updating user profile: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'trace' => $e->getTraceAsString()
            ]);

            return Redirect::route('profile.edit')
                ->withInput()
                ->withErrors(['error' => 'Failed to update profile: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        try {
            $request->validateWithBag('userDeletion', [
                'password' => ['required', 'current_password'],
            ]);

            $user = $request->user();
            $userId = $user->id;

            Auth::logout();

            $user->delete();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            \Log::info('User account deleted successfully', ['user_id' => $userId]);

            return Redirect::to('/');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return Redirect::route('profile.edit')
                ->withErrors($e->errors(), 'userDeletion');
        } catch (\Exception $e) {
            \Log::error('Error deleting user account: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return Redirect::route('profile.edit')
                ->withErrors(['error' => 'Failed to delete account: ' . $e->getMessage()], 'userDeletion');
        }
    }
}
