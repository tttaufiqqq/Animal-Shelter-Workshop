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
    public function getMatches()
    {
        $user = Auth::user();

        // Check database availability first
        if (!$this->isDatabaseAvailable('taufiq') || !$this->isDatabaseAvailable('shafiqah')) {
            return response()->json([
                'success' => false,
                'message' => 'Database connection unavailable. Please check your internet connection.',
                'offline' => true
            ]);
        }

        // Get adopter profile with error handling
        $adopterProfile = $this->safeQuery(
            fn() => AdopterProfile::where('adopterID', $user->id)->first(),
            null
        );

        if (!$adopterProfile) {
           return response()->json([
                'success' => false,
                'message' => 'Please complete your adopter profile first to see your matches.'
            ]);
        }

        // Get all available animals with their profiles
        $animals = $this->safeQuery(
            fn() => Animal::with('profile')
                ->whereIn('adoption_status', ['Not Adopted', 'not adopted'])
                ->get(),
            collect([])
        );

        // Calculate match scores
        $matches = [];
        foreach ($animals as $animal) {
            if ($animal->profile) {
                $score = $this->calculateMatchScore($adopterProfile, $animal->profile);
                $matches[] = [
                    'animal' => $animal,
                    'profile' => $animal->profile,
                    'score' => $score,
                    'match_details' => $this->getMatchDetails($adopterProfile, $animal->profile)
                ];
            }
        }

        // Sort by score (highest first)
        usort($matches, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Get top 5 matches
        $topMatches = array_slice($matches, 0, 5);

        return response()->json([
            'success' => true,
            'matches' => $topMatches
        ]);
    }

    private function calculateMatchScore($adopterProfile, $animalProfile)
    {
        $score = 0;
        $maxScore = 100;

        // Species match (20 points)
        if ($adopterProfile->preferred_species === $animalProfile->animal->species) {
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

    private function getMatchDetails($adopterProfile, $animalProfile)
    {
        $details = [];

        if ($adopterProfile->preferred_species === $animalProfile->animal->species) {
            $details[] = "Matches your preferred species";
        }

        if ($adopterProfile->preferred_size === $animalProfile->size) {
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

            // 2. Find the animal
            $animal = Animal::find($animalId);
            if (!$animal) {
                return redirect()->back()->with('error', 'Animal not found or database connection unavailable.');
            }

            // 3. Validate age from the Animal table (case-insensitive)
            $allowedAges = ['kitten', 'puppy', 'adult', 'senior'];
            $ageFromAnimal = strtolower($animal->age); // convert to lower case

            if (!in_array($ageFromAnimal, $allowedAges)) {
                return redirect()->back()->with('error', 'Invalid age value in the Animal table.');
            }

            $validated['age'] = $ageFromAnimal;

            // 4. Check if profile exists
            $profile = AnimalProfile::firstOrNew(['animalID' => $animalId]);

            // 5. Fill and save
            $profile->fill($validated);
            $saved = $profile->save();

            if ($saved) {
                return redirect()->back()->with('success', 'Animal Profile saved successfully!');
            } else {
                return redirect()->back()->with('error', 'Failed to save Animal Profile.');
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
            collect([])
        );

        $slots = $this->safeQuery(
            fn() => Slot::where('status', 'Available')->get(),
            collect([])
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

        // Start transactions on both databases involved
        DB::connection('shafiqah')->beginTransaction();  // Animal database
        DB::connection('eilya')->beginTransaction();      // Image database

        try {
            // Use age category directly
            $age = ucfirst($validated['age_category']);

            $slot = null;
            if ($validated['slotID']) {
                $slot = Slot::find($validated['slotID']);
                if (!$slot || $slot->status !== 'available') {
                    return back()
                        ->withInput()
                        ->withErrors(['slotID' => 'Selected slot is not available.']);
                }
            }

            $animal = Animal::create([
                'name' => $validated['name'],
                'weight' => $validated['weight'],
                'species' => $validated['species'],
                'health_details' => $validated['health_details'],
                'age' => $age, // Store the category directly
                'gender' => $validated['gender'],
                'adoption_status' => 'Not Adopted',
                'rescueID' => $validated['rescueID'],
                'slotID' => $validated['slotID'],
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $imageFile) {
                    $filename = time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
                    $path = $imageFile->storeAs('animal_images', $filename, 'public');
                    $uploadedFiles[] = $path;

                    Image::create([
                        'animalID' => $animal->id,
                        'image_path' => $path,
                        'filename' => $filename,
                        'uploaded_at' => now(),
                    ]);
                }
            }

            // Commit both transactions
            DB::connection('shafiqah')->commit();
            DB::connection('eilya')->commit();

            return redirect()->route('animal-management.create', ['rescue_id' => $validated['rescueID']])
                ->with('success', 'Animal "' . $animal->name . '" added successfully with ' . count($request->file('images') ?? []) . ' image(s)! Do you want to add another animal? If so, please fill all the required fields again.');

        } catch (\Exception $e) {
            // Rollback both database transactions
            DB::connection('shafiqah')->rollBack();
            DB::connection('eilya')->rollBack();

            foreach ($uploadedFiles as $filePath) {
                Storage::disk('public')->delete($filePath);
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

    // Start transactions on both databases involved
    DB::connection('shafiqah')->beginTransaction();  // Animal database
    DB::connection('eilya')->beginTransaction();      // Image database

   try {
        $animal = Animal::findOrFail($id);
        $uploadedFiles = [];

        // ----- Age Category -----
        $age = ucfirst($validated['age_category']); // Directly use the category

        // ----- Safe slot logic -----
        $slotID = $validated['slotID'] ?? $animal->slotID;

        if (($validated['slotID'] ?? null) !== null && $slotID != $animal->slotID) {
            $slot = Slot::find($slotID);

            if (!$slot || $slot->status !== 'available') {
                return back()->withInput()
                    ->withErrors(['slotID' => 'Selected slot is not available.']);
            }
        }

        // ----- Update Animal -----
        $animal->update([
            'name' => $validated['name'],
            'weight' => $validated['weight'],
            'species' => $validated['species'],
            'health_details' => $validated['health_details'],
            'age' => $age, // Store the category directly
            'gender' => $validated['gender'],
            'slotID' => $slotID,
        ]);

        \Log::info('Animal updated basic info.', [
            'id' => $animal->id,
            'slotID' => $slotID,
            'age_category' => $age
        ]);

        // ----- Delete Images Optional -----
        if (!empty($validated['delete_images'])) {
            foreach ($validated['delete_images'] as $imageId) {
                $img = Image::where('id', $imageId)
                    ->where('animalID', $animal->id)
                    ->first();

                if ($img) {
                    \Log::info('Deleting image', [
                        'image_id' => $imageId,
                        'path' => $img->image_path
                    ]);

                    Storage::disk('public')->delete($img->image_path);
                    $img->delete();
                }
            }
        }

        \Log::info('Remaining after delete', [
            'count' => Image::where('animalID', $animal->id)->count()
        ]);

        // ----- Upload New Images Optional -----
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('animal_images', $filename, 'public');

                $uploadedFiles[] = $path;

                Image::create([
                    'animalID' => $animal->id,
                    'image_path' => $path,
                    'filename' => $filename,
                    'uploaded_at' => now(),
                ]);
            }

            \Log::info('Uploaded images:', [
                'files' => $uploadedFiles
            ]);
        }

        // ----- NO MORE "must have at least one image" -----

        // Commit both transactions
        DB::connection('shafiqah')->commit();
        DB::connection('eilya')->commit();

        \Log::info('UPDATE SUCCESSFUL', [
            'animal_id' => $animal->id
        ]);

        return redirect()->back()->with('success', 'Animal updated successfully!');

    } catch (\Exception $e) {
        // Rollback both database transactions
        DB::connection('shafiqah')->rollBack();
        DB::connection('eilya')->rollBack();

        \Log::error('UPDATE FAILED', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);

        foreach ($uploadedFiles as $path) {
            Storage::disk('public')->delete($path);
        }

        return back()->withInput()
            ->withErrors(['error' => $e->getMessage()]);
    }
}


    public function index(Request $request)
    {
        // Safe query for animals with fallback
        $animals = $this->safeQuery(function() use ($request) {
            $query = Animal::with(['images', 'slot']);

            // Filters
            if ($request->filled('search')) {
                $query->where(DB::raw('LOWER(name)'), 'LIKE', '%' . strtolower($request->search) . '%');
            }

            if ($request->filled('species')) {
                $query->where(DB::raw('LOWER(species)'), 'LIKE', '%' . strtolower($request->species) . '%');
            }

            if ($request->filled('adoption_status')) {
                $query->where('adoption_status', $request->adoption_status);
            }

            if ($request->filled('gender')) {
                $query->where('gender', $request->gender);
            }

            // ⭐ PRIORITIZE Not Adopted animals
            $query->orderByRaw("CASE WHEN adoption_status = 'Not Adopted' THEN 0 ELSE 1 END");

            // Secondary sorting
            $query->orderBy('created_at', 'desc');

            return $query->paginate(12)->appends($request->query());
        }, new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12), 'shafiqah'); // Pre-check shafiqah database

        // ===== Only for logged-in user =====
        $animalList = collect();
        if (Auth::check()) {
            $user = Auth::user();

            $animalList = $this->safeQuery(
                fn() => VisitList::with('animals')
                    ->firstOrCreate(['userID' => $user->id])
                    ->animals,
                collect([]),
                'danish' // Pre-check danish database
            );
        }

        return view('animal-management.main', compact('animals', 'animalList'));
    }


    public function show($id)
    {
        // Safe query for animal data
        $animal = $this->safeQuery(
            fn() => Animal::with([
                'images',
                'slot',
                'rescue.report',
            ])->findOrFail($id),
            null
        );

        // If animal not found or database offline, redirect back
        if (!$animal) {
            return redirect()->route('animal-management.index')
                ->with('error', 'Animal not found or database connection unavailable.');
        }

        $medicals = $this->safeQuery(
            fn() => Medical::with('vet')->where('animalID', $id)->get(),
            collect([])
        );

        $vaccinations = $this->safeQuery(
            fn() => Vaccination::with('vet')->where('animalID', $id)->get(),
            collect([])
        );

        $vets = $this->safeQuery(
            fn() => Vet::all(),
            collect([])
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
            collect([])
        );

        // Get all bookings for this animal (for calendar/time blocking)
        $bookedSlots = $this->safeQuery(
            fn() => $animal->bookings->map(function($booking) {
                $dateTime = \Carbon\Carbon::parse($booking->appointment_date . ' ' . $booking->appointment_time);

                return [
                    'date' => $dateTime->format('Y-m-d'),
                    'time' => $dateTime->format('H:i'),
                    'datetime' => $dateTime->format('Y-m-d\TH:i'),
                ];
            }),
            collect([])
        );

        // Get all active bookings for this animal (Pending or Confirmed)
        $activeBookings = $this->safeQuery(
            fn() => $animal->bookings()
                ->whereIn('status', ['Pending', 'Confirmed'])
                ->with('user')
                ->orderBy('appointment_date', 'asc')
                ->orderBy('appointment_time', 'asc')
                ->get(),
            collect([])
        );

        $animalProfile = $this->safeQuery(
            fn() => AnimalProfile::where('animalID', $id)->first(),
            null
        );

        // ===== Only for logged-in user =====
        $animalList = collect(); // default empty
        if (Auth::check()) {
            $user = Auth::user();

            $animalList = $this->safeQuery(
                fn() => VisitList::with('animals')
                    ->firstOrCreate(['userID' => $user->id])
                    ->animals,
                collect([])
            );
        }

        return view('animal-management.show', compact('animal', 'vets', 'medicals', 'vaccinations', 'slots', 'bookedSlots', 'animalProfile', 'animalList', 'activeBookings'));
    }

    public function assignSlot(Request $request, $animalId)
    {
        try {
            $request->validate([
                'slot_id' => 'required|exists:atiqah.slot,id',  // Cross-database: Slot on atiqah
            ]);

            $animal = Animal::findOrFail($animalId);
            $previousSlotId = $animal->slotID;

            $animal->slotID = $request->slot_id;
            $animal->save();

            $newSlot = Slot::findOrFail($request->slot_id);
            $newSlotAnimalCount = $newSlot->animals()->count();

            if ($newSlotAnimalCount >= $newSlot->capacity) {
                $newSlot->status = 'occupied';
            } else {
                $newSlot->status = 'available';
            }
            $newSlot->save();

            if ($previousSlotId && $previousSlotId != $newSlot->id) {
                $oldSlot = Slot::find($previousSlotId);

                if ($oldSlot) {
                    $oldSlotAnimalCount = $oldSlot->animals()->count();

                    if ($oldSlotAnimalCount >= $oldSlot->capacity) {
                        $oldSlot->status = 'occupied';
                    } else {
                        $oldSlot->status = 'available';
                    }

                    $oldSlot->save();
                }
            }

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
        // Start transactions on databases involved
        DB::connection('shafiqah')->beginTransaction();  // Animal database
        DB::connection('eilya')->beginTransaction();      // Image database
        if ($animal->slotID) {
            DB::connection('atiqah')->beginTransaction();  // Slot database
        }

        try {
            foreach ($animal->images as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }

            $slot = Slot::find($animal->slotID);
            if ($slot) {
                $slot->update(['status' => 'available']);
            }

            $animalName = $animal->name;
            $animal->delete();

            // Commit all transactions
            DB::connection('shafiqah')->commit();
            DB::connection('eilya')->commit();
            if ($animal->slotID) {
                DB::connection('atiqah')->commit();
            }

            return redirect()->route('animal-management.index')
                ->with('success', 'Animal "' . $animalName . '" deleted successfully!');

        } catch (\Exception $e) {
            // Rollback all database transactions
            DB::connection('shafiqah')->rollBack();
            DB::connection('eilya')->rollBack();
            if ($animal->slotID) {
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
        try {
            $validated = $request->validate([
                'clinic_name' => 'required|string|max:255',
                'address' => 'required|string|max:500',
                'phone' => 'required|string|max:20',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);

            $clinic = Clinic::create([
                'name' => $validated['clinic_name'],
                'address' => $validated['address'],
                'contactNum' => $validated['phone'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);

            return redirect()->back()->with('success', 'Clinic added successfully!');

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
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'specialization' => 'required|string|max:255',
                'license_no' => 'required|string|max:50',
                'clinicID' => 'nullable|exists:clinic,id',
                'phone' => 'required|string|max:20',
                'email' => 'required|email|max:255|unique:vet,email',
            ]);

            $dataToInsert = [
                'name' => $validated['full_name'],
                'specialization' => $validated['specialization'],
                'license_no' => $validated['license_no'],
                'clinicID' => $validated['clinicID'] ?? null,
                'contactNum' => $validated['phone'],
                'email' => $validated['email'],
            ];

            $vet = Vet::create($dataToInsert);

            return redirect()->back()->with('success', 'Veterinarian added successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add veterinarian: ' . $e->getMessage());
        }
    }

    public function storeMedical(Request $request)
    {
        try {
            $validated = $request->validate([
                'animalID' => 'required|exists:animal,id',
                'treatment_type' => 'required|string|max:255',
                'diagnosis' => 'required|string',
                'action' => 'required|string',
                'remarks' => 'nullable|string',
                'vetID' => 'required|exists:vet,id',
                'costs' => 'nullable|numeric|min:0',
            ]);

            $medical = Medical::create($validated);

            return redirect()->back()->with('success', 'Medical record added successfully!');
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
        try {
            $validated = $request->validate([
                'animalID' => 'required|exists:animal,id',
                'name' => 'required|string|max:255',
                'type' => 'required|string|max:255',
                'next_due_date' => 'nullable|date|after:today',
                'remarks' => 'nullable|string',
                'vetID' => 'required|exists:vet,id',
                'costs' => 'nullable|numeric|min:0',
            ]);

            $vaccination = Vaccination::create($validated);

            return redirect()->back()->with('success', 'Vaccination record added successfully!');
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

            $clinic = Clinic::findOrFail($id);
            $clinic->update([
                'name' => $request->name,
                'address' => $request->address,
                'contactNum' => $request->contactNum,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            return redirect()->back()->with('success', 'Clinic updated successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Clinic not found for update: ' . $e->getMessage(), ['clinic_id' => $id]);
            return redirect()->back()->with('error', 'Clinic not found or database connection unavailable.');
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
            $clinic = Clinic::findOrFail($id);

            // Check if clinic has associated vets
            if ($clinic->vets()->count() > 0) {
                return redirect()->back()->with('error', 'Cannot delete clinic with associated veterinarians. Please reassign or remove vets first.');
            }

            $clinic->delete();

            return redirect()->back()->with('success', 'Clinic deleted successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Clinic not found for deletion: ' . $e->getMessage(), ['clinic_id' => $id]);
            return redirect()->back()->with('error', 'Clinic not found or database connection unavailable.');
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
                'clinicID' => 'required|exists:clinic,id',
                'contactNum' => 'required|string|max:20',
                'email' => 'required|email|max:255',
            ]);

            $vet = Vet::findOrFail($id);
            $vet->update([
                'name' => $request->name,
                'specialization' => $request->specialization,
                'license_no' => $request->license_no,
                'clinicID' => $request->clinicID,
                'contactNum' => $request->contactNum,
                'email' => $request->email,
            ]);

            return redirect()->back()->with('success', 'Veterinarian updated successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Veterinarian not found for update: ' . $e->getMessage(), ['vet_id' => $id]);
            return redirect()->back()->with('error', 'Veterinarian not found or database connection unavailable.');
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
            $vet = Vet::findOrFail($id);

            // Check if vet has associated medical records or vaccinations
            $medicalCount = $vet->medicals()->count();
            $vaccinationCount = $vet->vaccinations()->count();

            if ($medicalCount > 0 || $vaccinationCount > 0) {
                return redirect()->back()->with('error',
                    'Cannot delete veterinarian with associated medical records (' . $medicalCount . ') or vaccination records (' . $vaccinationCount . '). Please reassign these records first.');
            }

            $vet->delete();

            return redirect()->back()->with('success', 'Veterinarian deleted successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Veterinarian not found for deletion: ' . $e->getMessage(), ['vet_id' => $id]);
            return redirect()->back()->with('error', 'Veterinarian not found or database connection unavailable.');
        } catch (\Exception $e) {
            \Log::error('Error deleting veterinarian: ' . $e->getMessage(), [
                'vet_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to delete veterinarian: ' . $e->getMessage());
        }
    }
}
