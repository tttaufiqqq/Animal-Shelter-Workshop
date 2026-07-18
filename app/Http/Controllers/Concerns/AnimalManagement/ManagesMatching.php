<?php

namespace App\Http\Controllers\Concerns\AnimalManagement;

use App\Models\Animal;
use App\Models\AnimalProfile;
use App\Models\AdopterProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

trait ManagesMatching
{
    use CalculatesMatchScore;

    public function getMatches()
    {
        try {
            // Skipped in tests - see PreventDatabaseTimeout for why an unconditional
            // set_time_limit() breaks the long-lived test process on Windows.
            if (!app()->runningUnitTests()) {
                set_time_limit(25);
            }
            $user = Auth::user();
            Log::info('Starting match calculation', ['user_id' => $user->id]);

            $forceRefresh = request()->has('force_refresh');
            if ($forceRefresh) {
                Log::info('Force refresh requested - clearing database cache');
                app(\App\Services\DatabaseConnectionChecker::class)->clearCache();
            }

            if (!$this->isDatabaseAvailable('users')) {
                return response()->json(['success' => false, 'message' => 'User database is currently offline. Please try again later.'], 503);
            }
            if (!$this->isDatabaseAvailable('animals')) {
                return response()->json(['success' => false, 'message' => 'Animal database is currently offline. Please try again later.'], 503);
            }

            $isEilyaAvailable = $this->isDatabaseAvailable('reporting');

            $adopterProfile = $this->safeQuery(fn() => AdopterProfile::where('adopterID', $user->id)->first(), null, 'users');

            if (!$adopterProfile) {
                return response()->json(['success' => false, 'message' => 'Please complete your adopter profile first to see your matches.']);
            }

            $cacheKey = "animal_matches_user_{$user->id}";
            $cacheDuration = 300;

            if (!$forceRefresh && \Cache::has($cacheKey)) {
                $cachedMatches = \Cache::get($cacheKey);
                Log::info('Returning cached matches', ['count' => count($cachedMatches)]);
                return response()->json(['success' => true, 'matches' => $cachedMatches, 'cached' => true]);
            }

            $animals = $this->safeQuery(
                fn() => Animal::with(['profile' => fn($q) => $q->select('id', 'animalID', 'size', 'energy_level', 'temperament', 'good_with_kids', 'good_with_pets')])
                    ->select('id', 'name', 'species', 'age', 'gender', 'adoption_status')
                    ->whereIn('adoption_status', ['Not Adopted', 'not adopted'])
                    ->whereHas('profile')
                    ->limit(20)
                    ->get(),
                collect([]),
                'animals'
            );

            $animalsWithProfiles = $animals;

            if ($animalsWithProfiles->isEmpty()) {
                return response()->json(['success' => true, 'matches' => []]);
            }

            $matches = [];
            foreach ($animalsWithProfiles as $animal) {
                try {
                    $score = $this->calculateMatchScore($adopterProfile, $animal->profile, $animal);
                    $imageUrl = null;
                    if ($isEilyaAvailable) {
                        try {
                            $imageUrl = $animal->images()->first()?->url ?? null;
                        } catch (\Exception $e) {
                            Log::warning("Failed to load image for animal {$animal->id}: " . $e->getMessage());
                        }
                    }
                    $matchDetails = $this->getMatchDetails($adopterProfile, $animal->profile, $animal);
                    $matches[] = ['id' => $animal->id, 'name' => $animal->name, 'species' => $animal->species, 'age' => $animal->age, 'gender' => $animal->gender, 'image' => $imageUrl, 'score' => $score, 'match_details' => $matchDetails];
                } catch (\Exception $e) {
                    Log::error("Error matching animal #{$animal->id}: " . $e->getMessage(), ['animal_id' => $animal->id, 'trace' => $e->getTraceAsString()]);
                    continue;
                }
            }

            usort($matches, fn($a, $b) => $b['score'] <=> $a['score']);
            $topMatches = array_slice($matches, 0, 5);

            try {
                \Cache::put($cacheKey, $topMatches, $cacheDuration);
            } catch (\Exception $e) {
                Log::warning('Failed to cache matches: ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'matches' => $topMatches]);

        } catch (\Exception $e) {
            Log::error('Match calculation error: ' . $e->getMessage(), ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'user_id' => Auth::id()]);
            return response()->json(['success' => false, 'message' => 'Unable to calculate matches at this time. Please try again later.'], 500);
        }
    }

    public function storeOrUpdate(Request $request, $animalId)
    {
        if (!app()->runningUnitTests()) {
            set_time_limit(60);
        }

        try {
            $validated = $request->validate([
                'size' => ['required', Rule::in(['small', 'medium', 'large'])],
                'energy_level' => ['required', Rule::in(['low', 'medium', 'high'])],
                'good_with_kids' => ['required', 'boolean'],
                'good_with_pets' => ['required', 'boolean'],
                'temperament' => ['required', Rule::in(['calm', 'active', 'shy', 'friendly', 'independent'])],
                'medical_needs' => ['required', Rule::in(['none', 'minor', 'moderate', 'special'])],
            ]);

            $animal = Animal::find($animalId);
            if (!$animal) {
                return redirect()->back()->with('error', 'Animal not found or database connection unavailable.');
            }

            $ageFromAnimal = strtolower($animal->age);
            if (!in_array($ageFromAnimal, ['kitten', 'puppy', 'adult', 'senior'])) {
                return redirect()->back()->with('error', 'Invalid age value in the Animal table.');
            }

            $validated['age'] = $ageFromAnimal;
            $result = $this->procedureService->upsertAnimalProfile($animalId, $validated);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            }
            return redirect()->back()->with('error', $result['message']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Error saving animal profile: ' . $e->getMessage(), ['animal_id' => $animalId, 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', 'Failed to save Animal Profile: ' . $e->getMessage());
        }
    }
}
