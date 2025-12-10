<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use App\Models\AdopterProfile;
use App\Models\User;
use Illuminate\Validation\Rule;
use App\Services\ForeignKeyValidator;

class ProfileController extends Controller
{
    /**
     * Store or update adopter profile
     * AdopterProfile and User are both in Taufiq's database
     */
    public function storeOrUpdate(Request $request)
    {
        // Validation Rules
        $validated = $request->validate([
            'housing_type' => ['required', Rule::in(['condo', 'landed', 'apartment', 'hdb', 'house_with_yard'])],
            'has_children' => ['required', 'boolean'],
            'has_other_pets' => ['required', 'boolean'],
            'activity_level' => ['required', Rule::in(['low', 'medium', 'high'])],
            'experience' => ['required', Rule::in(['beginner', 'intermediate', 'expert'])],
            'preferred_species' => ['required', Rule::in(['cat', 'dog', 'both'])],
            'preferred_size' => ['required', Rule::in(['small', 'medium', 'large', 'any'])],
        ]);

        // Use transaction for Taufiq's database
        DB::connection('taufiq')->beginTransaction();

        try {
            // Upsert (Update or Insert) the Profile in Taufiq's database
            // We look for a profile linked to the authenticated user's ID
            $wasExisting = AdopterProfile::where('adopterID', Auth::id())->exists();

            AdopterProfile::updateOrCreate(
                ['adopterID' => Auth::id()], // Key to find the record
                $validated                   // Data to create or update
            );

            DB::connection('taufiq')->commit();

            // Clear cache for this user
            ForeignKeyValidator::clearUserCache(Auth::id());

            // Determine message based on action
            $message = $wasExisting ?
                'Adopter Profile updated successfully!' :
                'Adopter Profile created successfully!';

            Log::info('Adopter profile saved', [
                'user_id' => Auth::id(),
                'action' => $wasExisting ? 'updated' : 'created',
            ]);

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::connection('taufiq')->rollBack();

            Log::error('Error saving adopter profile: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to save adopter profile. Please try again.']);
        }
    }

    /**
     * Display the user's profile form.
     * User and AdopterProfile are both in Taufiq's database
     */
    public function edit(Request $request): View
    {
        // Get user from Taufiq's database
        $user = $request->user();

        // Get adopter profile from Taufiq's database (if exists)
        $adopterProfile = AdopterProfile::where('adopterID', $user->id)->first();

        return view('profile.edit', [
            'user' => $user,
            'adopterProfile' => $adopterProfile,
        ]);
    }

    /**
     * Update the user's profile information.
     * User is in Taufiq's database
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Use transaction for Taufiq's database
        DB::connection('taufiq')->beginTransaction();

        try {
            $user = $request->user();

            // Fill and save user data in Taufiq's database
            $user->fill($request->validated());
            $user->save();

            DB::connection('taufiq')->commit();

            // Clear cache for this user
            ForeignKeyValidator::clearUserCache($user->id);

            Log::info('User profile updated', [
                'user_id' => $user->id,
            ]);

            return Redirect::route('profile.edit')->with('status', 'profile-updated');

        } catch (\Exception $e) {
            DB::connection('taufiq')->rollBack();

            Log::error('Error updating user profile: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Redirect::route('profile.edit')
                ->withErrors(['error' => 'Failed to update profile. Please try again.']);
        }
    }

    /**
     * Delete the user's account.
     * This is a complex operation affecting multiple databases:
     * - User and AdopterProfile (Taufiq's database)
     * - Related bookings, transactions (Danish's database)
     * - Visit lists (Danish's database)
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $userId = $user->id;

        // Use multi-database transactions
        DB::connection('taufiq')->beginTransaction();
        DB::connection('danish')->beginTransaction();

        try {
            // Log the deletion attempt
            Log::warning('User account deletion initiated', [
                'user_id' => $userId,
                'user_email' => $user->email,
            ]);

            // Delete user's visit lists from Danish's database
            DB::connection('danish')->table('visit_list')->where('userID', $userId)->delete();
            Log::info('Deleted visit lists for user', ['user_id' => $userId]);

            // Note: Bookings and transactions remain for record-keeping
            // but you might want to anonymize them instead of deleting
            // Update bookings to remove user reference (set to null or anonymize)
            DB::connection('danish')
                ->table('booking')
                ->where('userID', $userId)
                ->update(['userID' => null]); // Or keep for records

            // Update transactions to remove user reference
            DB::connection('danish')
                ->table('transaction')
                ->where('userID', $userId)
                ->update(['userID' => null]); // Or keep for records

            Log::info('Anonymized bookings and transactions for user', ['user_id' => $userId]);

            // Delete adopter profile from Taufiq's database
            AdopterProfile::where('adopterID', $userId)->delete();
            Log::info('Deleted adopter profile for user', ['user_id' => $userId]);

            // Logout before deleting
            Auth::logout();

            // Delete user from Taufiq's database
            $user->delete();

            // Commit all transactions
            DB::connection('taufiq')->commit();
            DB::connection('danish')->commit();

            // Clear cache
            ForeignKeyValidator::clearUserCache($userId);

            Log::warning('User account deleted successfully', [
                'user_id' => $userId,
            ]);

            // Invalidate session
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return Redirect::to('/')->with('success', 'Your account has been deleted successfully.');

        } catch (\Exception $e) {
            // Rollback all transactions
            DB::connection('taufiq')->rollBack();
            DB::connection('danish')->rollBack();

            Log::error('Error deleting user account: ' . $e->getMessage(), [
                'user_id' => $userId,
                'trace' => $e->getTraceAsString(),
            ]);

            return Redirect::route('profile.edit')
                ->withErrors(['error' => 'Failed to delete account. Please try again or contact support.']);
        }
    }

    /**
     * Show user's profile with all related data
     * Cross-database query to show complete user information
     */
    public function show(Request $request): View
    {
        $user = $request->user();

        // Get adopter profile from Taufiq's database
        $adopterProfile = AdopterProfile::where('adopterID', $user->id)->first();

        // Get user's bookings from Danish's database (with cross-database relationships)
        $bookings = DB::connection('danish')
            ->table('booking')
            ->where('userID', $user->id)
            ->orderBy('appointment_date', 'desc')
            ->limit(5)
            ->get();

        // Get user's transactions from Danish's database
        $transactions = DB::connection('danish')
            ->table('transaction')
            ->where('userID', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get user's visit list from Danish's database
        $visitListCount = DB::connection('danish')
            ->table('visit_list')
            ->join('visit_list_animal', 'visit_list.id', '=', 'visit_list_animal.listID')
            ->where('visit_list.userID', $user->id)
            ->count();

        // Get reports submitted by user from Eilya's database
        $reportsCount = DB::connection('eilya')
            ->table('report')
            ->where('userID', $user->id)
            ->count();

        return view('profile.show', compact(
            'user',
            'adopterProfile',
            'bookings',
            'transactions',
            'visitListCount',
            'reportsCount'
        ));
    }
}
