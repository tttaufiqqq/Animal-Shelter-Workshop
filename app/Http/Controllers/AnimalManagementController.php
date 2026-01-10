<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Animal;
use App\Models\Slot;
use App\Models\Image;
use App\Models\Rescue;
use App\Models\Clinic;
use App\Models\Vet;
use App\Models\Medical;
use App\Models\Vaccination;
use App\Models\VisitList;
use App\Services\AuditService;
use App\Services\ShafiqahProcedureService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AnimalProfile;
use App\Models\AdopterProfile;
use Illuminate\Validation\Rule;
use App\DatabaseErrorHandler;

class AnimalManagementController extends Controller
{
    use DatabaseErrorHandler;

    protected $procedureService;

    public function __construct(ShafiqahProcedureService $procedureService)
    {
        $this->procedureService = $procedureService;
    }
    public function getMatches()
    {
        try {
            // Set maximum execution time to 25 seconds (less than frontend's 30s timeout)
            set_time_limit(25);

            $user = Auth::user();

            Log::info('Starting match calculation', ['user_id' => $user->id]);

            // OPTIMIZED: Check all databases ONCE using existing cache (don't clear)
            // This prevents multiple 5-second timeouts per database
            // Only do fresh check if request explicitly asks for it
            $forceRefresh = request()->has('force_refresh');

            if ($forceRefresh) {
                Log::info('Force refresh requested - clearing database cache');
                $checker = app(\App\Services\DatabaseConnectionChecker::class);
                $checker->clearCache();
            }

            // Check if required databases are available (uses cache if available)
            if (!$this->isDatabaseAvailable('taufiq')) {
                Log::warning('Taufiq database unavailable during match check');
                return response()->json([
                    'success' => false,
                    'message' => 'User database is currently offline. Please try again later.'
                ], 503);
            }

            if (!$this->isDatabaseAvailable('shafiqah')) {
                Log::warning('Shafiqah database unavailable during match check');
                return response()->json([
                    'success' => false,
                    'message' => 'Animal database is currently offline. Please try again later.'
                ], 503);
            }

            // CRITICAL FIX: Check eilya ONCE at the start (not inside loop)
            // Use cached status to avoid repeated 5-second timeouts
            $isEilyaAvailable = $this->isDatabaseAvailable('eilya');

            Log::info('Database availability check complete', [
                'taufiq' => true,
                'shafiqah' => true,
                'eilya' => $isEilyaAvailable
            ]);

            // Get adopter profile using safeQuery with timeout protection
            $adopterProfile = $this->safeQuery(
                fn() => AdopterProfile::where('adopterID', $user->id)->first(),
                null,
                'taufiq'
            );

            if (!$adopterProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please complete your adopter profile first to see your matches.'
                ]);
            }

            Log::info('Adopter profile found', ['profile_id' => $adopterProfile->id]);

            // PERFORMANCE FIX: Cache the query results for 5 minutes to prevent repeated slow queries
            $cacheKey = "animal_matches_user_{$user->id}";
            $cacheDuration = 300; // 5 minutes

            // Check if we have cached matches (unless force_refresh is requested)
            if (!$forceRefresh && \Cache::has($cacheKey)) {
                $cachedMatches = \Cache::get($cacheKey);
                Log::info('Returning cached matches', ['count' => count($cachedMatches)]);

                return response()->json([
                    'success' => true,
                    'matches' => $cachedMatches,
                    'cached' => true,
                ]);
            }

            // PERFORMANCE FIX: Limit to 20 animals instead of 50 to prevent timeout
            // OPTIMIZATION: Use whereHas to only fetch animals that have profiles (prevents wasted queries)
            // OPTIMIZATION: Only select needed columns to reduce data transfer
            $animals = $this->safeQuery(
                fn() => Animal::with(['profile' => function($query) {
                        // Only fetch needed profile columns
                        $query->select('id', 'animalID', 'size', 'energy_level', 'temperament', 'good_with_kids', 'good_with_pets');
                    }])
                    ->select('id', 'name', 'species', 'age', 'gender', 'adoption_status')
                    ->whereIn('adoption_status', ['Not Adopted', 'not adopted'])
                    ->whereHas('profile') // Only fetch animals that have profiles
                    ->limit(20) // REDUCED from 50 to 20 for faster response
                    ->get(),
                collect([]),
                'shafiqah'
            );

            Log::info('Animals fetched from database', ['count' => $animals->count()]);

            // All animals now have profiles due to whereHas
            $animalsWithProfiles = $animals;

            // Log if no animals have profiles
            if ($animalsWithProfiles->isEmpty()) {
                Log::info('No animals with profiles found for matching', [
                    'total_animals' => $animals->count(),
                    'animals_with_profiles' => 0,
                    'user_id' => $user->id
                ]);

                return response()->json([
                    'success' => true,
                    'matches' => []
                ]);
            }

            Log::info('Animals available for matching', [
                'total_animals' => $animals->count(),
                'animals_with_profiles' => $animalsWithProfiles->count(),
                'user_id' => $user->id
            ]);

            // Calculate match scores
            $matches = [];

            foreach ($animalsWithProfiles as $animal) {
                try {
                    // CRITICAL FIX: Pass $animal to avoid N+1 query problem
                    // (accessing $animalProfile->animal inside causes 20 separate DB queries!)
                    $score = $this->calculateMatchScore($adopterProfile, $animal->profile, $animal);

                    // DEBUG: Log matching details for first few animals
                    if (count($matches) < 3) {
                        Log::info("Matching animal #{$animal->id}", [
                            'animal_name' => $animal->name,
                            'animal_species' => $animal->species,
                            'profile_exists' => $animal->profile ? 'yes' : 'no',
                            'adopter_preferred_species' => $adopterProfile->preferred_species ?? 'null',
                            'adopter_preferred_size' => $adopterProfile->preferred_size ?? 'null',
                            'animal_size' => $animal->profile->size ?? 'null',
                            'match_score' => $score
                        ]);
                    }

                    // OPTIMIZED: Skip images entirely if eilya is offline
                    // This prevents any image-related delays
                    $imageUrl = null;
                    if ($isEilyaAvailable) {
                        try {
                            // Simple query - eilya is already confirmed available
                            $firstImage = $animal->images()->first();
                            $imageUrl = $firstImage?->url ?? null;
                        } catch (\Exception $e) {
                            // If individual image query fails, log it but continue
                            Log::warning("Failed to load image for animal {$animal->id}: " . $e->getMessage());
                            $imageUrl = null;
                        }
                    }

                    $matchDetails = $this->getMatchDetails($adopterProfile, $animal->profile, $animal);

                    $matches[] = [
                        'id' => $animal->id,
                        'name' => $animal->name,
                        'species' => $animal->species,
                        'age' => $animal->age,
                        'gender' => $animal->gender,
                        'image' => $imageUrl,
                        'score' => $score,
                        'match_details' => $matchDetails
                    ];

                } catch (\Exception $e) {
                    Log::error("Error matching animal #{$animal->id}: " . $e->getMessage(), [
                        'animal_id' => $animal->id,
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Continue to next animal instead of breaking entire process
                    continue;
                }
            }

            Log::info('Match calculation completed', [
                'matches_count' => count($matches),
                'sample_scores' => array_slice(array_column($matches, 'score'), 0, 5)
            ]);

            // Sort by score (highest first)
            usort($matches, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            // Get top 5 matches
            $topMatches = array_slice($matches, 0, 5);

            Log::info('Returning matches to frontend', [
                'total_matches' => count($matches),
                'top_5_count' => count($topMatches),
                'top_match_scores' => array_column($topMatches, 'score'),
                'top_match_names' => array_column($topMatches, 'name')
            ]);

            // Cache the results for 5 minutes to improve performance on subsequent requests
            try {
                \Cache::put($cacheKey, $topMatches, $cacheDuration);
                Log::info('Cached matches for user', ['cache_key' => $cacheKey, 'duration' => $cacheDuration]);
            } catch (\Exception $e) {
                // If caching fails, just log it and continue (not critical)
                Log::warning('Failed to cache matches: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'matches' => $topMatches
            ]);

        } catch (\Exception $e) {
            Log::error('Match calculation error: ' . $e->getMessage(), [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to calculate matches at this time. Please try again later.'
            ], 500);
        }
    }

    private function calculateMatchScore($adopterProfile, $animalProfile, $animal)
    {
        $score = 0;
        $maxScore = 100;

        // Species match (20 points)
        // FIXED: Use passed $animal instead of $animalProfile->animal (prevents N+1 query)
        if ($adopterProfile->preferred_species === $animal->species) {
            $score += 20;
        } elseif ($adopterProfile->preferred_species === 'both') {
            // "both" means no preference, give some points
            $score += 20;
        }

        // Size match (15 points)
        if ($adopterProfile->preferred_size === $animalProfile->size) {
            $score += 15;
        }

        // Energy level / Activity level match (20 points)
        $energyMatch = $this->matchEnergyLevel($adopterProfile->activity_level, $animalProfile->energy_level);
        $score += $energyMatch * 20;

        // Kids compatibility (15 points)
        if ($adopterProfile->has_children) {
            if ($animalProfile->good_with_kids) {
                $score += 15;
            }
        } else {
            $score += 15; // No constraint
        }

        // Pets compatibility (15 points)
        if ($adopterProfile->has_other_pets) {
            if ($animalProfile->good_with_pets) {
                $score += 15;
            }
        } else {
            $score += 15; // No constraint
        }

        // Housing type consideration (15 points)
        $housingScore = $this->matchHousingType($adopterProfile->housing_type, $animalProfile);
        $score += $housingScore;

        return round(($score / $maxScore) * 100);
    }

    private function matchEnergyLevel($activityLevel, $energyLevel)
    {
        $levels = ['low' => 1, 'medium' => 2, 'high' => 3];

        $activityValue = $levels[$activityLevel] ?? 2;
        $energyValue = $levels[$energyLevel] ?? 2;

        $difference = abs($activityValue - $energyValue);

        if ($difference === 0) return 1.0;
        if ($difference === 1) return 0.6;
        return 0.2;
    }

    private function matchHousingType($housingType, $animalProfile)
    {
        // Apartment dwellers better with smaller, lower energy animals
        if ($housingType === 'apartment') {
            if (in_array($animalProfile->size, ['small', 'medium']) &&
                $animalProfile->energy_level !== 'high') {
                return 15;
            }
            return 5;
        }

        // House with yard - most animals suitable
        if ($housingType === 'house_with_yard') {
            return 15;
        }

        return 10; // Default
    }

    private function getMatchDetails($adopterProfile, $animalProfile, $animal)
    {
        $details = [];

        // FIXED: Use passed $animal instead of $animalProfile->animal (prevents N+1 query)
        if ($adopterProfile->preferred_species === $animal->species || $adopterProfile->preferred_species === 'both') {
            $details[] = "Matches your preferred species";
        }

        if ($adopterProfile->preferred_size === $animalProfile->size || $adopterProfile->preferred_size === 'any') {
            $details[] = "Perfect size match";
        }

        if ($adopterProfile->has_children && $animalProfile->good_with_kids) {
            $details[] = "Great with children";
        }

        if ($adopterProfile->has_other_pets && $animalProfile->good_with_pets) {
            $details[] = "Gets along with other pets";
        }

        $energyMatch = $this->matchEnergyLevel($adopterProfile->activity_level, $animalProfile->energy_level);
        if ($energyMatch >= 0.6) {
            $details[] = "Energy level matches your lifestyle";
        }

        return $details;
    }

    public function storeOrUpdate(Request $request, $animalId)
    {
        // CRITICAL FIX: Increase execution time for stored procedures
        // Stored procedures with triggers can take 5-30 seconds
        set_time_limit(60);

        try {
            // 1. Validate other fields (excluding 'age')
            $validated = $request->validate([
                'size' => ['required', Rule::in(['small', 'medium', 'large'])],
                'energy_level' => ['required', Rule::in(['low', 'medium', 'high'])],
                'good_with_kids' => ['required', 'boolean'],
                'good_with_pets' => ['required', 'boolean'],
                'temperament' => ['required', Rule::in(['calm', 'active', 'shy', 'friendly', 'independent'])],
                'medical_needs' => ['required', Rule::in(['none', 'minor', 'moderate', 'special'])],
            ]);

            // 2. Find the animal to validate age
            $animal = Animal::find($animalId);
            if (!$animal) {
                return redirect()->back()->with('error', 'Animal not found or database connection unavailable.');
            }

            // 3. Validate age from the Animal table (case-insensitive)
            $allowedAges = ['kitten', 'puppy', 'adult', 'senior'];
            $ageFromAnimal = strtolower($animal->age);

            if (!in_array($ageFromAnimal, $allowedAges)) {
                return redirect()->back()->with('error', 'Invalid age value in the Animal table.');
            }

            $validated['age'] = $ageFromAnimal;

            // 4. Use stored procedure to upsert animal profile
            $result = $this->procedureService->upsertAnimalProfile($animalId, $validated);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Error saving animal profile: ' . $e->getMessage(), [
                'animal_id' => $animalId,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to save Animal Profile: ' . $e->getMessage());
        }
    }

    public function home(){
        return view('animal-management.main');
    }

    public function create($rescue_id = null)
    {
        // Fetch data with fallbacks for offline databases
        $rescues = $this->safeQuery(
            fn() => Rescue::all(),
            collect([]),
            'eilya'
        );

        $slots = $this->safeQuery(
            fn() => Slot::where('status', 'Available')->get(),
            collect([]),
            'atiqah'
        );

        return view('animal-management.create', [
            'slots' => $slots,
            'rescues' => $rescues,
            'rescue_id' => $rescue_id,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric',
            'species' => 'required|string|max:255',
            'health_details' => 'required|string',
            'age_category' => 'nullable|in:kitten,puppy,adult,senior',
            'gender' => 'required|in:Male,Female,Unknown',
            'rescueID' => 'required|exists:eilya.rescue,id',  // Cross-database: Rescue on eilya
            'slotID' => 'nullable|exists:atiqah.slot,id',     // Cross-database: Slot on atiqah
            'images' => 'nullable|array|min:1',
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120',
        ], [
            'images.required' => 'Please upload at least one image.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be in JPEG, PNG, or GIF format.',
            'images.*.max' => 'Each image must not exceed 5MB.',
        ]);

        $uploadedFiles = [];

        // NOTE: shafiqah transaction removed - sp_animal_create stored procedure handles its own transaction
        // Only manage eilya transaction for Image operations (direct Eloquent)
        DB::connection('eilya')->beginTransaction();      // Image database

        try {
            // Use age category directly
            $age = ucfirst($validated['age_category']);

            // Validate slot availability (application layer - cross-database)
            $slot = null;
            if ($validated['slotID']) {
                $slot = Slot::find($validated['slotID']);
                if (!$slot || $slot->status !== 'available') {
                    return back()
                        ->withInput()
                        ->withErrors(['slotID' => 'Selected slot is not available.']);
                }
            }

            // Create animal using stored procedure
            $result = $this->procedureService->createAnimal([
                'name' => $validated['name'],
                'weight' => $validated['weight'],
                'species' => $validated['species'],
                'health_details' => $validated['health_details'],
                'age' => $age,
                'gender' => $validated['gender'],
                'adoption_status' => 'Not Adopted',
                'rescueID' => $validated['rescueID'],
                'slotID' => $validated['slotID'],
            ]);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $animalId = $result['animal_id'];

            // Upload images (kept in application layer - requires Cloudinary API)
            if ($request->hasFile('images')) {
                $imageIndex = 1;
                foreach ($request->file('images') as $imageFile) {
                    // Create descriptive filename: animal_1_fluffy_dog_1
                    $sanitizedName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $validated['name']));
                    $sanitizedSpecies = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $validated['species']));
                    $filename = "animal_{$animalId}_{$sanitizedName}_{$sanitizedSpecies}_{$imageIndex}";

                    // Upload to Cloudinary
                    $uploadResult = cloudinary()->uploadApi()->upload($imageFile->getRealPath(), [
                        'folder' => 'animal_images',
                        'public_id' => $filename,
                    ]);
                    $path = $uploadResult['public_id'];
                    $uploadedFiles[] = $path;

                    Image::create([
                        'animalID' => $animalId,
                        'image_path' => $path,
                        'filename' => $filename,
                        'uploaded_at' => now(),
                    ]);

                    $imageIndex++;
                }
            }

            // Commit eilya transaction (shafiqah handled by stored procedure)
            DB::connection('eilya')->commit();

            // Note: Audit logging now handled by database triggers

            return redirect()->route('animal-management.create', ['rescue_id' => $validated['rescueID']])
                ->with('success', 'Animal "' . $validated['name'] . '" added successfully with ' . count($request->file('images') ?? []) . ' image(s)! Do you want to add another animal? If so, please fill all the required fields again.');

        } catch (\Exception $e) {
            // Rollback eilya transaction (shafiqah handled by stored procedure)
            DB::connection('eilya')->rollBack();

            foreach ($uploadedFiles as $filePath) {
                cloudinary()->uploadApi()->destroy($filePath);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'An error occurred while adding the animal: ' . $e->getMessage()]);
        }
    }

public function update(Request $request, $id)
{
    \Log::info('UPDATE REQUEST START', [
        'animal_id' => $id,
        'has_images' => $request->hasFile('images'),
        'image_count' => $request->file('images') ? count($request->file('images')) : 0,
        'delete_images' => $request->delete_images ?? [],
        'input' => $request->all(),
    ]);

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'weight' => 'required|numeric',
        'species' => 'required|string|max:255',
        'health_details' => 'required|string',
        'age_category' => 'required|in:kitten,puppy,adult,senior',
        'gender' => 'required|in:Male,Female,Unknown',
        'slotID' => 'nullable|exists:atiqah.slot,id',      // Cross-database: Slot on atiqah
        'images' => 'nullable|array',
        'images.*' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120',
        'delete_images' => 'nullable|array',
        'delete_images.*' => 'exists:eilya.image,id',      // Cross-database: Image on eilya
    ]);

    // NOTE: shafiqah transaction removed - sp_animal_update stored procedure handles its own transaction
    // Only manage eilya transaction for Image operations (direct Eloquent)
    DB::connection('eilya')->beginTransaction();      // Image database

   try {
        $animal = Animal::findOrFail($id);
        $uploadedFiles = [];

        // ----- Age Category -----
        $age = ucfirst($validated['age_category']);

        // ----- Safe slot logic (application layer - cross-database) -----
        $slotID = $validated['slotID'] ?? $animal->slotID;

        if (($validated['slotID'] ?? null) !== null && $slotID != $animal->slotID) {
            $slot = Slot::find($slotID);

            if (!$slot || $slot->status !== 'available') {
                return back()->withInput()
                    ->withErrors(['slotID' => 'Selected slot is not available.']);
            }
        }

        // ----- Update Animal using stored procedure -----
        $result = $this->procedureService->updateAnimal($id, [
            'name' => $validated['name'],
            'weight' => $validated['weight'],
            'species' => $validated['species'],
            'health_details' => $validated['health_details'],
            'age' => $age,
            'gender' => $validated['gender'],
            'slotID' => $slotID,
        ]);

        if (!$result['success']) {
            throw new \Exception($result['message']);
        }

        \Log::info('Animal updated basic info.', [
            'id' => $id,
            'slotID' => $slotID,
            'age_category' => $age
        ]);

        // ----- Delete Images Optional (application layer - Cloudinary API) -----
        if (!empty($validated['delete_images'])) {
            foreach ($validated['delete_images'] as $imageId) {
                $img = Image::where('id', $imageId)
                    ->where('animalID', $id)
                    ->first();

                if ($img) {
                    \Log::info('Deleting image', [
                        'image_id' => $imageId,
                        'path' => $img->image_path
                    ]);

                    try {
                        cloudinary()->uploadApi()->destroy($img->image_path);
                    } catch (\Exception $e) {
                        // Continue even if Cloudinary deletion fails
                    }
                    $img->delete();
                }
            }
        }

        \Log::info('Remaining after delete', [
            'count' => Image::where('animalID', $id)->count()
        ]);

        // ----- Upload New Images Optional (application layer - Cloudinary API) -----
        if ($request->hasFile('images')) {
            // Get current image count to continue numbering
            $existingImageCount = Image::where('animalID', $id)->count();
            $imageIndex = $existingImageCount + 1;

            foreach ($request->file('images') as $file) {
                // Create descriptive filename: animal_1_fluffy_dog_3
                $sanitizedName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $validated['name']));
                $sanitizedSpecies = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $validated['species']));
                $filename = "animal_{$id}_{$sanitizedName}_{$sanitizedSpecies}_{$imageIndex}";

                // Upload to Cloudinary
                $uploadResult = cloudinary()->uploadApi()->upload($file->getRealPath(), [
                    'folder' => 'animal_images',
                    'public_id' => $filename,
                ]);
                $path = $uploadResult['public_id'];

                $uploadedFiles[] = $path;

                Image::create([
                    'animalID' => $id,
                    'image_path' => $path,
                    'filename' => $filename,
                    'uploaded_at' => now(),
                ]);

                $imageIndex++;
            }

            \Log::info('Uploaded images:', [
                'files' => $uploadedFiles
            ]);
        }

        // Commit eilya transaction (shafiqah handled by stored procedure)
        DB::connection('eilya')->commit();

        \Log::info('UPDATE SUCCESSFUL', [
            'animal_id' => $id
        ]);

        // Note: Audit logging now handled by database triggers

        return redirect()->back()->with('success', 'Animal updated successfully!');

    } catch (\Exception $e) {
        // Rollback eilya transaction (shafiqah handled by stored procedure)
        DB::connection('eilya')->rollBack();

        \Log::error('UPDATE FAILED', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);

        foreach ($uploadedFiles as $path) {
            cloudinary()->uploadApi()->destroy($path);
        }

        return back()->withInput()
            ->withErrors(['error' => $e->getMessage()]);
    }
}


    public function index(Request $request)
    {
        // Safe query for animals with fallback
        $animals = $this->safeQuery(function() use ($request) {
            // Build with array based on database availability
            $with = [];

            // Only load cross-database relationships if those databases are online
            if ($this->isDatabaseAvailable('eilya')) {
                $with[] = 'images';
                $with[] = 'rescue'; // Load rescue relationship for caretaker filter
            }

            if ($this->isDatabaseAvailable('atiqah')) {
                $with[] = 'slot';
            }

            $query = Animal::with($with);

            // Caretaker Filter: Show only animals rescued by the logged-in caretaker
            if ($request->filled('rescued_by_me') && $request->rescued_by_me === 'true' && Auth::check()) {
                $user = Auth::user();

                // Check if user has caretaker role
                if ($user->hasRole('caretaker')) {
                    // Get rescue IDs where the current user is the caretaker
                    $rescueIds = $this->safeQuery(
                        fn() => DB::connection('eilya')
                            ->table('rescue')
                            ->where('caretakerID', $user->id)
                            ->pluck('id')
                            ->toArray(),
                        [],
                        'eilya'
                    );

                    if (!empty($rescueIds)) {
                        $query->whereIn('rescueID', $rescueIds);
                    } else {
                        // No rescues by this caretaker, return empty result
                        $query->whereRaw('1 = 0');
                    }
                }
            }

            // Filters
            if ($request->filled('search')) {
                $query->where(DB::raw('LOWER(name)'), 'LIKE', '%' . strtolower($request->search) . '%');
            }

            if ($request->filled('species')) {
                $query->where(DB::raw('LOWER(species)'), 'LIKE', '%' . strtolower($request->species) . '%');
            }

            if ($request->filled('health_details')) {
                $query->where('health_details', $request->health_details);
            }

            if ($request->filled('adoption_status')) {
                $query->where('adoption_status', $request->adoption_status);
            }

            if ($request->filled('gender')) {
                $query->where('gender', $request->gender);
            }

            // ⭐ Sort by newest animals first (most recently added)
            $query->orderBy('created_at', 'desc');

            // Pagination: 50 items for caretaker table view, 12 for card view
            $perPage = ($request->filled('rescued_by_me') && $request->rescued_by_me === 'true' && Auth::check() && Auth::user()->hasRole('caretaker')) ? 50 : 12;

            return $query->paginate($perPage)->appends($request->query());
        }, new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12), 'shafiqah'); // Pre-check shafiqah database

        // ===== Only for logged-in user =====
        $animalList = collect();
        if (Auth::check()) {
            $user = Auth::user();

            $animalList = $this->safeQuery(function() use ($user) {
                $visitList = VisitList::firstOrCreate(['userID' => $user->id]);

                // Get animal IDs from pivot table directly (avoid cross-database JOIN)
                $animalIds = DB::connection('danish')
                    ->table('visit_list_animal')
                    ->where('listID', $visitList->id)
                    ->pluck('animalID')
                    ->toArray();

                if (empty($animalIds)) {
                    return collect([]);
                }

                // Load animals from shafiqah database if available
                if ($this->isDatabaseAvailable('shafiqah')) {
                    return Animal::whereIn('id', $animalIds)->get();
                }

                return collect([]);
            }, collect([]), 'danish');
        }

        return view('animal-management.main', compact('animals', 'animalList'));
    }


    public function show($id)
    {
        // Check which databases are available
        $eilyaOnline = $this->isDatabaseAvailable('eilya');
        $atiqahOnline = $this->isDatabaseAvailable('atiqah');
        $danishOnline = $this->isDatabaseAvailable('danish');

        // Build with array based on database availability
        $with = [];

        // rescue and report are both on eilya database
        if ($eilyaOnline) {
            $with[] = 'rescue.report';
            $with[] = 'images';
        }

        if ($atiqahOnline) {
            $with[] = 'slot.section';  // Load nested relationship to prevent lazy loading
        }

        // bookings are on danish database
        if ($danishOnline) {
            $with[] = 'bookings.user';
        }

        // Safe query for animal data
        $animal = $this->safeQuery(
            fn() => Animal::with($with)->findOrFail($id),
            null,
            'shafiqah'
        );

        // If animal not found or database offline, redirect back
        if (!$animal) {
            return redirect()->route('animal-management.index')
                ->with('error', 'Animal not found or database connection unavailable.');
        }

        // Add flag to indicate if images are available
        $imagesAvailable = $eilyaOnline && $animal->relationLoaded('images');

        // Get images collection for view (empty if not available)
        $animalImages = $imagesAvailable ? $animal->images : collect([]);
        $hasImages = $animalImages->isNotEmpty();

        $medicals = $this->safeQuery(
            fn() => Medical::with('vet')->where('animalID', $id)->get(),
            collect([]),
            'shafiqah'
        );

        $vaccinations = $this->safeQuery(
            fn() => Vaccination::with('vet')->where('animalID', $id)->get(),
            collect([]),
            'shafiqah'
        );

        $vets = $this->safeQuery(
            fn() => Vet::all(),
            collect([]),
            'shafiqah'
        );

        // Get available slots + the current slot (if any)
        $slots = $this->safeQuery(
            fn() => Slot::with('section')
                ->where(function($query) use ($animal) {
                    $query->where('status', 'available')
                        ->orWhere('id', $animal->slotID);
                })
                ->orderBy('sectionID')
                ->orderBy('name')
                ->get(),
            collect([]),
            'atiqah'
        );

        // Get all bookings for this animal (for calendar/time blocking) - only if danish database is online
        $bookedSlots = collect([]);
        if ($danishOnline && $animal->relationLoaded('bookings')) {
            $bookedSlots = $animal->bookings->map(function($booking) {
                // Parse date and time separately to avoid concatenation issues
                $date = \Carbon\Carbon::parse($booking->appointment_date)->format('Y-m-d');
                $time = \Carbon\Carbon::parse($booking->appointment_time)->format('H:i:s');
                $dateTime = \Carbon\Carbon::parse($date . ' ' . $time);

                return [
                    'date' => $dateTime->format('Y-m-d'),
                    'time' => $dateTime->format('H:i'),
                    'datetime' => $dateTime->format('Y-m-d\TH:i'),
                ];
            });
        }

        // Get all active bookings for this animal (Pending or Confirmed) - only if danish database is online
        $activeBookings = collect([]);
        $activeBooking = null;
        if ($danishOnline && $animal->relationLoaded('bookings')) {
            $activeBookings = $animal->bookings
                ->whereIn('status', ['Pending', 'Confirmed'])
                ->sortBy([
                    ['appointment_date', 'asc'],
                    ['appointment_time', 'asc'],
                ]);

            // Get the first active booking for the page header
            $activeBooking = $activeBookings->first();
        }

        $animalProfile = $this->safeQuery(
            fn() => AnimalProfile::where('animalID', $id)->first(),
            null,
            'shafiqah'
        );

        // ===== Only for logged-in user =====
        $animalList = collect(); // default empty
        if (Auth::check()) {
            $user = Auth::user();

            $animalList = $this->safeQuery(function() use ($user) {
                $visitList = VisitList::firstOrCreate(['userID' => $user->id]);

                // Get animal IDs from pivot table directly (avoid cross-database JOIN)
                $animalIds = DB::connection('danish')
                    ->table('visit_list_animal')
                    ->where('listID', $visitList->id)
                    ->pluck('animalID')
                    ->toArray();

                if (empty($animalIds)) {
                    return collect([]);
                }

                // Load animals from shafiqah database if available
                if ($this->isDatabaseAvailable('shafiqah')) {
                    return Animal::whereIn('id', $animalIds)->get();
                }

                return collect([]);
            }, collect([]), 'danish');
        }

        // Pass database availability status to view for graceful degradation
        return view('animal-management.show', compact(
            'animal',
            'vets',
            'medicals',
            'vaccinations',
            'slots',
            'bookedSlots',
            'animalProfile',
            'animalList',
            'activeBookings',
            'activeBooking',
            'imagesAvailable',
            'animalImages',
            'hasImages',
            'eilyaOnline',
            'atiqahOnline',
            'danishOnline'
        ));
    }

    public function assignSlot(Request $request, $animalId)
    {
        try {
            $request->validate([
                'slot_id' => 'required|exists:atiqah.slot,id',  // Cross-database: Slot on atiqah
            ]);

            // Assign slot using stored procedure
            $result = $this->procedureService->assignSlot($animalId, $request->slot_id);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $previousSlotId = $result['previous_slot_id'];

            // Update slot status (application layer - cross-database operation)
            $newSlot = Slot::findOrFail($request->slot_id);
            // Count animals in slot directly from shafiqah database (avoid cross-database JOIN)
            $newSlotAnimalCount = Animal::where('slotID', $newSlot->id)->count();

            if ($newSlotAnimalCount >= $newSlot->capacity) {
                $newSlot->status = 'occupied';
            } else {
                $newSlot->status = 'available';
            }
            $newSlot->save();

            if ($previousSlotId && $previousSlotId != $newSlot->id) {
                $oldSlot = Slot::find($previousSlotId);

                if ($oldSlot) {
                    // Count animals in slot directly from shafiqah database (avoid cross-database JOIN)
                    $oldSlotAnimalCount = Animal::where('slotID', $oldSlot->id)->count();

                    if ($oldSlotAnimalCount >= $oldSlot->capacity) {
                        $oldSlot->status = 'occupied';
                    } else {
                        $oldSlot->status = 'available';
                    }

                    $oldSlot->save();
                }
            }

            // Note: Audit logging now handled by database triggers

            return back()->with('success', 'Slot assigned successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Animal or Slot not found: ' . $e->getMessage(), [
                'animal_id' => $animalId,
                'slot_id' => $request->slot_id
            ]);

            return back()->with('error', 'Animal or Slot not found. Please check the database connection.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Error assigning slot: ' . $e->getMessage(), [
                'animal_id' => $animalId,
                'slot_id' => $request->slot_id,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to assign slot: ' . $e->getMessage());
        }
    }

    public function destroy(Animal $animal)
    {
        // Check which databases are available
        $eilyaOnline = $this->isDatabaseAvailable('eilya');
        $atiqahOnline = $this->isDatabaseAvailable('atiqah');

        // NOTE: shafiqah transaction removed - sp_animal_delete stored procedure handles its own transaction
        // Only manage eilya and atiqah transactions for Image/Slot operations (direct Eloquent)

        if ($eilyaOnline) {
            DB::connection('eilya')->beginTransaction();  // Image database
        }

        if ($animal->slotID && $atiqahOnline) {
            DB::connection('atiqah')->beginTransaction();  // Slot database
        }

        try {
            // Only delete images if eilya database is online (application layer - Cloudinary API)
            if ($eilyaOnline) {
                try {
                    foreach ($animal->images as $image) {
                        try {
                            cloudinary()->uploadApi()->destroy($image->image_path);
                        } catch (\Exception $e) {
                            // Continue even if Cloudinary deletion fails
                        }
                        $image->delete();
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to delete images from eilya database', [
                        'animal_id' => $animal->id,
                        'error' => $e->getMessage()
                    ]);
                    // Continue with animal deletion even if image deletion fails
                }
            } else {
                \Log::info('Skipping image deletion - eilya database offline', [
                    'animal_id' => $animal->id
                ]);
            }

            $slotID = $animal->slotID; // Store slot ID before deletion

            // Delete animal using stored procedure
            $result = $this->procedureService->deleteAnimal($animal->id);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $animalName = $result['animal_name'];

            // Update slot status based on remaining animal count if atiqah is online (application layer - cross-database)
            if ($slotID && $atiqahOnline) {
                $slot = Slot::find($slotID);
                if ($slot) {
                    // Count remaining animals in this slot (after deletion)
                    $remainingAnimals = Animal::where('slotID', $slotID)->count();

                    // Auto-calculate status based on remaining occupancy
                    if ($remainingAnimals >= $slot->capacity) {
                        $slot->status = 'occupied';
                    } else {
                        $slot->status = 'available';
                    }
                    $slot->save();

                    \Log::info('Slot status updated after animal deletion', [
                        'slot_id' => $slotID,
                        'remaining_animals' => $remainingAnimals,
                        'capacity' => $slot->capacity,
                        'new_status' => $slot->status,
                    ]);
                }
            }

            // Commit transactions (shafiqah handled by stored procedure)
            if ($eilyaOnline) {
                DB::connection('eilya')->commit();
            }

            if ($slotID && $atiqahOnline) {
                DB::connection('atiqah')->commit();
            }

            $message = 'Animal "' . $animalName . '" deleted successfully!';
            if (!$eilyaOnline) {
                $message .= ' (Note: Images could not be deleted - image database offline)';
            }

            // Note: Audit logging now handled by database triggers

            return redirect()->route('animal-management.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            // Rollback transactions (shafiqah handled by stored procedure)
            if ($eilyaOnline) {
                DB::connection('eilya')->rollBack();
            }

            if ($animal->slotID && $atiqahOnline) {
                DB::connection('atiqah')->rollBack();
            }

            \Log::error('Error deleting animal: ' . $e->getMessage());

            return back()->withErrors(['error' => 'An error occurred while deleting the animal.']);
        }
    }

    public function indexClinic()
    {
        $clinics = $this->safeQuery(
            fn() => Clinic::all(),
            collect([]),
            'shafiqah' // Pre-check shafiqah database
        );

        $vets = $this->safeQuery(
            fn() => Vet::with('clinic')->get(),
            collect([]),
            'shafiqah' // Pre-check shafiqah database
        );

        return view('animal-management.main-manage-cv', ['clinics' => $clinics, 'vets' => $vets]);
    }

    public function storeClinic(Request $request)
    {
        // CRITICAL FIX: Increase execution time for stored procedures
        // Stored procedures with triggers can take 5-30 seconds
        set_time_limit(60);

        try {
            $validated = $request->validate([
                'clinic_name' => 'required|string|max:255',
                'address' => 'required|string|max:500',
                'phone' => 'required|string|max:20',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);

            // Create clinic using stored procedure
            $result = $this->procedureService->createClinic([
                'name' => $validated['clinic_name'],
                'address' => $validated['address'],
                'contactNum' => $validated['phone'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->withInput()->with('error', $result['message']);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add clinic: ' . $e->getMessage());
        }
    }

    public function storeVet(Request $request)
    {
        // CRITICAL FIX: Increase execution time for stored procedures
        // Stored procedures with triggers can take 5-30 seconds
        set_time_limit(60);

        // DEBUG: Log that we reached the controller
        \Log::info('storeVet: Controller method reached', [
            'request_data' => $request->except(['_token']),
            'session_id' => session()->getId()
        ]);

        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'specialization' => 'required|string|max:255',
                'license_no' => 'required|string|max:50',
                'clinicID' => 'nullable|exists:shafiqah.clinic,id',
                'phone' => 'required|string|max:20',
                'email' => 'required|email|max:255',
            ]);

            \Log::info('storeVet: Validation passed', ['validated' => $validated]);

            // Create vet using stored procedure (includes email uniqueness check)
            $result = $this->procedureService->createVet([
                'name' => $validated['full_name'],
                'specialization' => $validated['specialization'],
                'license_no' => $validated['license_no'],
                'clinicID' => $validated['clinicID'] ?? null,
                'contactNum' => $validated['phone'],
                'email' => $validated['email'],
            ]);

            \Log::info('storeVet: Procedure result', ['result' => $result]);

            if ($result['success']) {
                \Log::info('storeVet: Success - redirecting with success message');
                return redirect()->back()->with('success', $result['message']);
            } else {
                \Log::warning('storeVet: Failed - redirecting with error message', ['message' => $result['message']]);
                return redirect()->back()->withInput()->with('error', $result['message']);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('storeVet: Validation exception', ['errors' => $e->errors()]);
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('storeVet: Exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add veterinarian: ' . $e->getMessage());
        }
    }

    public function storeMedical(Request $request)
    {
        // CRITICAL FIX: Increase execution time for stored procedures
        // Stored procedures with triggers can take 5-30 seconds
        set_time_limit(60);

        // DEBUG: Log that we reached the controller
        \Log::info('storeMedical: Controller method reached', [
            'animalID' => $request->input('animalID'),
            'has_csrf_token' => $request->has('_token'),
            'session_id' => session()->getId()
        ]);

        try {
            $validated = $request->validate([
                'animalID' => 'required|exists:shafiqah.animal,id',
                'treatment_type' => 'required|string|max:255',
                'diagnosis' => 'required|string',
                'action' => 'required|string',
                'remarks' => 'nullable|string',
                'vetID' => 'required|exists:shafiqah.vet,id',
                'costs' => 'nullable|numeric|min:0',
            ]);

            // Create medical record using stored procedure
            $result = $this->procedureService->createMedical($validated);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->withInput()->with('error', $result['message']);
            }

            // Note: Audit logging now handled by database triggers

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add medical record: ' . $e->getMessage());
        }
    }

    public function storeVaccination(Request $request)
    {
        // CRITICAL FIX: Increase execution time for stored procedures
        // Stored procedures with triggers can take 5-30 seconds
        set_time_limit(60);

        try {
            $validated = $request->validate([
                'animalID' => 'required|exists:shafiqah.animal,id',
                'name' => 'required|string|max:255',
                'type' => 'required|string|max:255',
                'next_due_date' => 'nullable|date|after:today',
                'remarks' => 'nullable|string',
                'vetID' => 'required|exists:shafiqah.vet,id',
                'costs' => 'nullable|numeric|min:0',
            ]);

            // Create vaccination record using stored procedure
            $result = $this->procedureService->createVaccination($validated);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->withInput()->with('error', $result['message']);
            }

            // Note: Audit logging now handled by database triggers

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add vaccination record: ' . $e->getMessage());
        }
    }

    public function editClinic($id)
    {
        try {
            $clinic = Clinic::findOrFail($id);
            return response()->json($clinic);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Clinic not found: ' . $e->getMessage(), ['clinic_id' => $id]);
            return response()->json([
                'error' => 'Clinic not found',
                'message' => 'The requested clinic does not exist or database connection is unavailable.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error fetching clinic: ' . $e->getMessage(), [
                'clinic_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to fetch clinic',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateClinic(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'required|string',
                'contactNum' => 'required|string|max:20',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            // Update clinic using stored procedure
            $result = $this->procedureService->updateClinic($id, [
                'name' => $request->name,
                'address' => $request->address,
                'contactNum' => $request->contactNum,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->withInput()->with('error', $result['message']);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Error updating clinic: ' . $e->getMessage(), [
                'clinic_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update clinic: ' . $e->getMessage());
        }
    }

    public function destroyClinic($id)
    {
        try {
            // Delete clinic using stored procedure (includes vet count check)
            $result = $this->procedureService->deleteClinic($id);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            \Log::error('Error deleting clinic: ' . $e->getMessage(), [
                'clinic_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to delete clinic: ' . $e->getMessage());
        }
    }

    public function editVet($id)
    {
        try {
            $vet = Vet::findOrFail($id);

            return response()->json([
                'id' => $vet->id,
                'name' => $vet->name,
                'specialization' => $vet->specialization,
                'license_no' => $vet->license_no,
                'clinicID' => $vet->clinicID,
                'contactNum' => $vet->contactNum,
                'email' => $vet->email,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Veterinarian not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function updateVet(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'specialization' => 'required|string|max:255',
                'license_no' => 'required|string|max:50',
                'clinicID' => 'required|exists:shafiqah.clinic,id',
                'contactNum' => 'required|string|max:20',
                'email' => 'required|email|max:255',
            ]);

            // Update vet using stored procedure (includes email uniqueness check)
            $result = $this->procedureService->updateVet($id, [
                'name' => $request->name,
                'specialization' => $request->specialization,
                'license_no' => $request->license_no,
                'clinicID' => $request->clinicID,
                'contactNum' => $request->contactNum,
                'email' => $request->email,
            ]);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->withInput()->with('error', $result['message']);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Error updating veterinarian: ' . $e->getMessage(), [
                'vet_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update veterinarian: ' . $e->getMessage());
        }
    }

    public function destroyVet($id)
    {
        try {
            // Delete vet using stored procedure (includes medical/vaccination count check)
            $result = $this->procedureService->deleteVet($id);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            \Log::error('Error deleting veterinarian: ' . $e->getMessage(), [
                'vet_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to delete veterinarian: ' . $e->getMessage());
        }
    }
}
