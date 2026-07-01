<?php

namespace App\Http\Controllers\Concerns\Profile;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

trait ManagesProfile
{
    public function storeOrUpdate(Request $request)
    {
        try {
            $validated = $request->validate([
                'housing_type' => ['required', Rule::in(['condo', 'landed', 'apartment', 'hdb'])],
                'has_children' => ['required', 'boolean'],
                'has_other_pets' => ['required', 'boolean'],
                'activity_level' => ['required', Rule::in(['low', 'medium', 'high'])],
                'experience' => ['required', Rule::in(['beginner', 'intermediate', 'expert'])],
                'preferred_species' => ['required', Rule::in(['cat', 'dog', 'both'])],
                'preferred_size' => ['required', Rule::in(['small', 'medium', 'large', 'any'])],
            ]);

            $result = $this->taufiqService->upsertAdopterProfile(Auth::id(), $validated);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $result['message']]);
            }

            return redirect()->back()->with('success', $result['message']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Please check the form and try again.', 'errors' => $e->errors()], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput()->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Error saving adopter profile: ' . $e->getMessage(), ['user_id' => Auth::id(), 'trace' => $e->getTraceAsString()]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to save Adopter Profile: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->withInput()->with('error', 'Failed to save Adopter Profile: ' . $e->getMessage());
        }
    }

    public function edit(Request $request): View
    {
        $user = $request->user();

        if ($user->hasRole('admin')) {
            return view('admin.profile.edit', ['user' => $user, 'stats' => $this->getAdminStats()]);
        }

        return view('profile.edit', ['user' => $user]);
    }

    private function getAdminStats(): array
    {
        try {
            $userStats = DB::connection('users')->select('SELECT * FROM get_user_account_stats()');
            $userStats = $userStats[0] ?? null;

            $adopterStats = DB::connection('users')->select('SELECT * FROM get_adopter_profile_stats()');
            $adopterStats = $adopterStats[0] ?? null;

            $recentRegistrations = DB::connection('users')->select('SELECT * FROM get_recent_registrations(7)');
            $highRiskUsers = DB::connection('users')->select('SELECT * FROM get_high_risk_users(3)');

            $totalReports = \App\Models\Report::count();
            $totalAnimals = \App\Models\Animal::count();

            return [
                'totalUsers' => $userStats->total_users ?? 0,
                'activeUsers' => $userStats->active_users ?? 0,
                'suspendedUsers' => $userStats->suspended_users ?? 0,
                'lockedUsers' => $userStats->locked_users ?? 0,
                'usersWithProfiles' => $userStats->users_with_profiles ?? 0,
                'avgFailedLoginAttempts' => $userStats->avg_failed_login_attempts ?? 0,
                'totalAdopterProfiles' => $adopterStats->total_profiles ?? 0,
                'profilesWithChildren' => $adopterStats->with_children ?? 0,
                'profilesWithOtherPets' => $adopterStats->with_other_pets ?? 0,
                'preferCats' => $adopterStats->prefer_cats ?? 0,
                'preferDogs' => $adopterStats->prefer_dogs ?? 0,
                'recentRegistrations' => count($recentRegistrations),
                'highRiskUsersCount' => count($highRiskUsers),
                'totalReports' => $totalReports,
                'totalAnimals' => $totalAnimals,
            ];
        } catch (\Exception $e) {
            \Log::error('Error fetching admin stats: ' . $e->getMessage());
            return ['totalUsers' => 0, 'activeUsers' => 0, 'suspendedUsers' => 0, 'lockedUsers' => 0, 'usersWithProfiles' => 0, 'avgFailedLoginAttempts' => 0, 'totalAdopterProfiles' => 0, 'profilesWithChildren' => 0, 'profilesWithOtherPets' => 0, 'preferCats' => 0, 'preferDogs' => 0, 'recentRegistrations' => 0, 'highRiskUsersCount' => 0, 'totalReports' => 0, 'totalAnimals' => 0];
        }
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            $result = $this->taufiqService->updateUser($request->user()->id, $request->validated());

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            return Redirect::route('profile.edit')->with('status', 'profile-updated');
        } catch (\Exception $e) {
            \Log::error('Error updating user profile: ' . $e->getMessage(), ['user_id' => $request->user()->id, 'trace' => $e->getTraceAsString()]);
            return Redirect::route('profile.edit')->withInput()->withErrors(['error' => 'Failed to update profile: ' . $e->getMessage()]);
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        try {
            $request->validateWithBag('userDeletion', ['password' => ['required', 'current_password']]);

            $user = $request->user();
            $userId = $user->id;

            Auth::logout();

            $result = $this->taufiqService->deleteUser($userId);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            \Log::info('User account deleted successfully', ['user_id' => $userId, 'user_name' => $result['user_name']]);

            return Redirect::to('/');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Redirect::route('profile.edit')->withErrors($e->errors(), 'userDeletion');
        } catch (\Exception $e) {
            \Log::error('Error deleting user account: ' . $e->getMessage(), ['user_id' => $request->user()->id ?? 'unknown', 'trace' => $e->getTraceAsString()]);
            return Redirect::route('profile.edit')->withErrors(['error' => 'Failed to delete account: ' . $e->getMessage()], 'userDeletion');
        }
    }
}
