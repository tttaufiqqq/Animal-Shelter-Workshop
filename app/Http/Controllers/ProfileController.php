<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\AdopterProfile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\DatabaseErrorHandler;
use App\Services\TaufiqProcedureService;

class ProfileController extends Controller
{
    use DatabaseErrorHandler;

    protected TaufiqProcedureService $taufiqService;

    public function __construct(TaufiqProcedureService $taufiqService)
    {
        $this->taufiqService = $taufiqService;
    }

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

            // 2. Upsert (Update or Insert) the Profile using stored procedure
            $result = $this->taufiqService->upsertAdopterProfile(Auth::id(), $validated);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            // 3. Use message from procedure
            $message = $result['message'];

            // 4. Return JSON for AJAX requests, redirect for regular requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please check the form and try again.',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Error saving adopter profile: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save Adopter Profile: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to save Adopter Profile: ' . $e->getMessage());
        }
    }
    public function edit(Request $request): View
    {
        $user = $request->user();

        // Check if user is an admin
        if ($user->hasRole('admin')) {
            // Get admin-specific statistics
            $stats = $this->getAdminStats();

            return view('admin.profile.edit', [
                'user' => $user,
                'stats' => $stats,
            ]);
        }

        // Return regular profile view for other roles
        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Get statistics for admin profile
     */
    private function getAdminStats(): array
    {
        try {
            // Get user account statistics using stored procedure
            $userStats = DB::connection('taufiq')->select('SELECT * FROM get_user_account_stats()');
            $userStats = $userStats[0] ?? null;

            // Get adopter profile statistics using stored procedure
            $adopterStats = DB::connection('taufiq')->select('SELECT * FROM get_adopter_profile_stats()');
            $adopterStats = $adopterStats[0] ?? null;

            // Get recent registrations (last 7 days)
            $recentRegistrations = DB::connection('taufiq')->select('SELECT * FROM get_recent_registrations(7)');

            // Get high-risk users
            $highRiskUsers = DB::connection('taufiq')->select('SELECT * FROM get_high_risk_users(3)');

            // Cross-database statistics (for other modules)
            $totalReports = \App\Models\Report::count();
            $totalAnimals = \App\Models\Animal::count();

            return [
                // User statistics from stored procedure
                'totalUsers' => $userStats->total_users ?? 0,
                'activeUsers' => $userStats->active_users ?? 0,
                'suspendedUsers' => $userStats->suspended_users ?? 0,
                'lockedUsers' => $userStats->locked_users ?? 0,
                'usersWithProfiles' => $userStats->users_with_profiles ?? 0,
                'avgFailedLoginAttempts' => $userStats->avg_failed_login_attempts ?? 0,

                // Adopter profile statistics from stored procedure
                'totalAdopterProfiles' => $adopterStats->total_profiles ?? 0,
                'profilesWithChildren' => $adopterStats->with_children ?? 0,
                'profilesWithOtherPets' => $adopterStats->with_other_pets ?? 0,
                'preferCats' => $adopterStats->prefer_cats ?? 0,
                'preferDogs' => $adopterStats->prefer_dogs ?? 0,

                // Recent activity
                'recentRegistrations' => count($recentRegistrations),
                'highRiskUsersCount' => count($highRiskUsers),

                // Cross-database statistics
                'totalReports' => $totalReports,
                'totalAnimals' => $totalAnimals,
            ];
        } catch (\Exception $e) {
            \Log::error('Error fetching admin stats: ' . $e->getMessage());

            // Return empty stats if there's an error
            return [
                'totalUsers' => 0,
                'activeUsers' => 0,
                'suspendedUsers' => 0,
                'lockedUsers' => 0,
                'usersWithProfiles' => 0,
                'avgFailedLoginAttempts' => 0,
                'totalAdopterProfiles' => 0,
                'profilesWithChildren' => 0,
                'profilesWithOtherPets' => 0,
                'preferCats' => 0,
                'preferDogs' => 0,
                'recentRegistrations' => 0,
                'highRiskUsersCount' => 0,
                'totalReports' => 0,
                'totalAnimals' => 0,
            ];
        }
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            $userId = $request->user()->id;
            $validated = $request->validated();

            // Update user using stored procedure
            $result = $this->taufiqService->updateUser($userId, $validated);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

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

            // Delete user using stored procedure
            $result = $this->taufiqService->deleteUser($userId);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            \Log::info('User account deleted successfully', [
                'user_id' => $userId,
                'user_name' => $result['user_name']
            ]);

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

    /**
     * Show the password change form (forced after admin reset)
     */
    public function showChangePasswordForm(): View|RedirectResponse
    {
        $user = Auth::user();

        // If user doesn't need to change password, redirect to homepage
        if (!$user->require_password_reset) {
            return redirect('/')
                ->with('info', 'You do not need to change your password.');
        }

        return view('auth.change-password');
    }

    /**
     * Update the user's password (forced change)
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        try {
            $user = Auth::user();

            // Validate the request
            $validated = $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'confirmed', Password::min(8)],
            ]);

            // Update password using stored procedure
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
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('password.change')
                ->with('error', 'Failed to change password. Please try again.');
        }
    }

    /**
     * Update the user's password from profile page
     */
    public function updateProfilePassword(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'current_password' => ['required', 'current_password:web'],
                'password' => ['required', 'confirmed', Password::min(8)],
            ], [
                'current_password.current_password' => 'The provided password does not match your current password.',
            ]);

            // Update password using stored procedure
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
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('profile.edit')
                ->withErrors(['password' => 'Failed to update password. Please try again.'], 'updatePassword');
        }
    }
}
