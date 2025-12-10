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
use App\Services\ForeignKeyValidator;

class AnimalManagementController extends Controller
{
    public function getMatches()
    {
        $user = Auth::user();

        // Get adopter profile (Taufiq's database)
        $adopterProfile = AdopterProfile::where('adopterID', $user->id)->first();

        if (!$adopterProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete your adopter profile first to see your matches.'
            ]);
        }

        // Get all available animals with their profiles (Shafiqah's database)
        $animals = Animal::with('profile')
            ->whereIn('adoption_status', ['Not Adopted', 'not adopted'])
            ->get();

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
        // 1. Validate other fields (excluding 'age')
        $validated = $request->validate([
            'size' => ['required', Rule::in(['small', 'medium', 'large'])],
            'energy_level' => ['required', Rule::in(['low', 'medium', 'high'])],
            'good_with_kids' => ['required', 'boolean'],
            'good_with_pets' => ['required', 'boolean'],
            'temperament' => ['required', Rule::in(['calm', 'active', 'shy', 'friendly', 'independent'])],
            'medical_needs' => ['required', Rule::in(['none', 'minor', 'moderate', 'special'])],
        ]);

        // 2. Find the animal (Shafiqah's database)
        $animal = Animal::find($animalId);
        if (!$animal) {
            return redirect()->back()->with('error', 'Animal not found.');
        }

        // 3. Validate age from the Animal table (case-insensitive)
        $allowedAges = ['kitten', 'puppy', 'adult', 'senior'];
        $ageFromAnimal = strtolower($animal->age); // convert to lower case

        if (!in_array($ageFromAnimal, $allowedAges)) {
            return redirect()->back()->with('error', 'Invalid age value in the Animal table.');
        }

        $validated['age'] = $ageFromAnimal;

        // 4. Check if profile exists (Shafiqah's database)
        $profile = AnimalProfile::firstOrNew(['animalID' => $animalId]);

        // 5. Fill and save
        $profile->fill($validated);
        $saved = $profile->save();

        if ($saved) {
            return redirect()->back()->with('success', 'Animal Profile saved successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to save Animal Profile.');
        }
    }

    public function home(){
        return view('animal-management.main');
    }

    public function create($rescue_id = null)
    {
        // Rescues from Eilya's database
        $rescues = Rescue::all();

        // Slots from Atiqah's database
        $slots = Slot::where('status', 'Available')->get();

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
            'rescueID' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Validate rescue exists in Eilya's database
                    if (!ForeignKeyValidator::validateRescue($value)) {
                        $fail('The selected rescue operation does not exist.');
                    }
                },
            ],
            'slotID' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        // Validate slot exists in Atiqah's database
                        if (!ForeignKeyValidator::validateSlot($value)) {
                            $fail('The selected slot does not exist.');
                        }

                        // Check slot capacity
                        if (!ForeignKeyValidator::slotHasCapacity($value)) {
                            $fail('The selected slot is at full capacity.');
                        }
                    }
                },
            ],
            'images' => 'nullable|array|min:1',
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120',
        ], [
            'images.required' => 'Please upload at least one image.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be in JPEG, PNG, or GIF format.',
            'images.*.max' => 'Each image must not exceed 5MB.',
        ]);

        $uploadedFiles = [];

        // Use transactions for both databases
        DB::connection('shafiqah')->beginTransaction();
        DB::connection('eilya')->beginTransaction();
        DB::connection('atiqah')->beginTransaction();

        try {
            // Use age category directly
            $age = ucfirst($validated['age_category']);

            // Validate slot availability from Atiqah's database
            $slot = null;
            if ($validated['slotID']) {
                $slot = Slot::find($validated['slotID']);
                if (!$slot || $slot->status !== 'available') {
                    throw new \Exception('Selected slot is not available.');
                }
            }

            // Create animal in Shafiqah's database
            $animal = Animal::create([
                'name' => $validated['name'],
                'weight' => $validated['weight'],
                'species' => $validated['species'],
                'health_details' => $validated['health_details'],
                'age' => $age,
                'gender' => $validated['gender'],
                'adoption_status' => 'Not Adopted',
                'rescueID' => $validated['rescueID'], // Cross-database reference to Eilya
                'slotID' => $validated['slotID'], // Cross-database reference to Atiqah
            ]);

            // Upload images to Eilya's database
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $imageFile) {
                    $filename = time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
                    $path = $imageFile->storeAs('animal_images', $filename, 'public');
                    $uploadedFiles[] = $path;

                    Image::create([
                        'animalID' => $animal->id, // Cross-database reference to Shafiqah
                        'image_path' => $path,
                        'reportID' => null,
                        'clinicID' => null,
                    ]);
                }
            }

            // Update slot status in Atiqah's database if needed
            if ($slot) {
                $animalCount = Animal::where('slotID', $slot->id)->count();
                if ($animalCount >= $slot->capacity) {
                    $slot->update(['status' => 'occupied']);
                }
            }

            DB::connection('shafiqah')->commit();
            DB::connection('eilya')->commit();
            DB::connection('atiqah')->commit();

            // Clear cache
            ForeignKeyValidator::clearAnimalCache($animal->id);

            return redirect()->route('animal-management.create', ['rescue_id' => $validated['rescueID']])
                ->with('success', 'Animal "' . $animal->name . '" added successfully with ' . count($request->file('images') ?? []) . ' image(s)! Do you want to add another animal? If so, please fill all the required fields again.');

        } catch (\Exception $e) {
            DB::connection('shafiqah')->rollBack();
            DB::connection('eilya')->rollBack();
            DB::connection('atiqah')->rollBack();

            foreach ($uploadedFiles as $filePath) {
                Storage::disk('public')->delete($filePath);
            }

            Log::error('Error creating animal: ' . $e->getMessage());

            return back()
                ->withInput()
                ->withErrors(['error' => 'An error occurred while adding the animal: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('UPDATE REQUEST START', [
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
            'slotID' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        if (!ForeignKeyValidator::validateSlot($value)) {
                            $fail('The selected slot does not exist.');
                        }
                    }
                },
            ],
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120',
            'delete_images' => 'nullable|array',
            'delete_images.*' => [
                function ($attribute, $value, $fail) {
                    // Validate image exists in Eilya's database
                    $exists = DB::connection('eilya')
                        ->table('image')
                        ->where('id', $value)
                        ->exists();

                    if (!$exists) {
                        $fail('The selected image does not exist.');
                    }
                },
            ],
        ]);

        // Use transactions for multiple databases
        DB::connection('shafiqah')->beginTransaction();
        DB::connection('eilya')->beginTransaction();
        DB::connection('atiqah')->beginTransaction();

        try {
            $animal = Animal::findOrFail($id);
            $uploadedFiles = [];

            // Age Category
            $age = ucfirst($validated['age_category']);

            // Safe slot logic
            $slotID = $validated['slotID'] ?? $animal->slotID;

            if (($validated['slotID'] ?? null) !== null && $slotID != $animal->slotID) {
                // Validate new slot from Atiqah's database
                $slot = Slot::find($slotID);

                if (!$slot || $slot->status !== 'available') {
                    throw new \Exception('Selected slot is not available.');
                }

                // Check capacity
                if (!ForeignKeyValidator::slotHasCapacity($slotID)) {
                    throw new \Exception('Selected slot is at full capacity.');
                }
            }

            // Update Animal in Shafiqah's database
            $animal->update([
                'name' => $validated['name'],
                'weight' => $validated['weight'],
                'species' => $validated['species'],
                'health_details' => $validated['health_details'],
                'age' => $age,
                'gender' => $validated['gender'],
                'slotID' => $slotID,
            ]);

            Log::info('Animal updated basic info.', [
                'id' => $animal->id,
                'slotID' => $slotID,
                'age_category' => $age
            ]);

            // Delete Images from Eilya's database
            if (!empty($validated['delete_images'])) {
                foreach ($validated['delete_images'] as $imageId) {
                    $img = Image::where('id', $imageId)
                        ->where('animalID', $animal->id)
                        ->first();

                    if ($img) {
                        Log::info('Deleting image', [
                            'image_id' => $imageId,
                            'path' => $img->image_path
                        ]);

                        Storage::disk('public')->delete($img->image_path);
                        $img->delete();
                    }
                }
            }

            Log::info('Remaining after delete', [
                'count' => Image::where('animalID', $animal->id)->count()
            ]);

            // Upload New Images to Eilya's database
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('animal_images', $filename, 'public');

                    $uploadedFiles[] = $path;

                    Image::create([
                        'animalID' => $animal->id,
                        'image_path' => $path,
                        'reportID' => null,
                        'clinicID' => null,
                    ]);
                }

                Log::info('Uploaded images:', [
                    'files' => $uploadedFiles
                ]);
            }

            // Update slot statuses in Atiqah's database
            $previousSlotId = $animal->getOriginal('slotID');

            // Update new slot status
            if ($slotID) {
                $newSlot = Slot::find($slotID);
                if ($newSlot) {
                    $newSlotAnimalCount = Animal::where('slotID', $slotID)->count();
                    $newSlot->status = ($newSlotAnimalCount >= $newSlot->capacity) ? 'occupied' : 'available';
                    $newSlot->save();
                }
            }

            // Update old slot status
            if ($previousSlotId && $previousSlotId != $slotID) {
                $oldSlot = Slot::find($previousSlotId);
                if ($oldSlot) {
                    $oldSlotAnimalCount = Animal::where('slotID', $previousSlotId)->count();
                    $oldSlot->status = ($oldSlotAnimalCount >= $oldSlot->capacity) ? 'occupied' : 'available';
                    $oldSlot->save();
                }
            }

            DB::connection('shafiqah')->commit();
            DB::connection('eilya')->commit();
            DB::connection('atiqah')->commit();

            // Clear cache
            ForeignKeyValidator::clearAnimalCache($animal->id);

            Log::info('UPDATE SUCCESSFUL', [
                'animal_id' => $animal->id
            ]);

            return redirect()->back()->with('success', 'Animal updated successfully!');

        } catch (\Exception $e) {
            DB::connection('shafiqah')->rollBack();
            DB::connection('eilya')->rollBack();
            DB::connection('atiqah')->rollBack();

            Log::error('UPDATE FAILED', [
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
        // Query from Shafiqah's database
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

        // Prioritize Not Adopted animals
        $query->orderByRaw("CASE WHEN adoption_status = 'Not Adopted' THEN 0 ELSE 1 END");

        // Secondary sorting
        $query->orderBy('created_at', 'desc');

        $animals = $query->paginate(12)->appends($request->query());

        // Get visit list from Danish's database (only for logged-in user)
        $animalList = collect();
        if (Auth::check()) {
            $user = Auth::user();

            $visitList = VisitList::with('animals')
                ->firstOrCreate(['userID' => $user->id]);

            $animalList = $visitList->animals;
        }

        return view('animal-management.main', compact('animals', 'animalList'));
    }

    public function show($id)
    {
        // Get animal from Shafiqah's database with cross-database relationships
        $animal = Animal::with([
            'images',     // Cross-database to Eilya
            'slot',       // Cross-database to Atiqah
            'rescue.report', // Cross-database to Eilya
        ])->findOrFail($id);

        // Get medical records from Shafiqah's database
        $medicals = Medical::with('vet')
            ->where('animalID', $id)
            ->get();

        // Get vaccinations from Shafiqah's database
        $vaccinations = Vaccination::with('vet')
            ->where('animalID', $id)
            ->get();

        // Get vets from Shafiqah's database
        $vets = Vet::all();

        // Get available slots + the current slot from Atiqah's database
        $slots = Slot::with('section')
            ->where(function($query) use ($animal) {
                $query->where('status', 'available')
                    ->orWhere('id', $animal->slotID);
            })
            ->orderBy('sectionID')
            ->orderBy('name')
            ->get();

        // Get bookings from Danish's database (cross-database query)
        $bookedSlots = $animal->bookings->map(function($booking) {
            $dateTime = \Carbon\Carbon::parse($booking->appointment_date . ' ' . $booking->appointment_time);

            return [
                'date' => $dateTime->format('Y-m-d'),
                'time' => $dateTime->format('H:i'),
                'datetime' => $dateTime->format('Y-m-d\TH:i'),
            ];
        });

        // Get animal profile from Shafiqah's database
        $animalProfile = AnimalProfile::where('animalID', $id)->first();

        // Get visit list from Danish's database (only for logged-in user)
        $animalList = collect();
        if (Auth::check()) {
            $user = Auth::user();

            $visitList = VisitList::with('animals')
                ->firstOrCreate(['userID' => $user->id]);

            $animalList = $visitList->animals;
        }

        return view('animal-management.show', compact('animal', 'vets', 'medicals', 'vaccinations', 'slots', 'bookedSlots', 'animalProfile', 'animalList'));
    }

    public function assignSlot(Request $request, $animalId)
    {
        $request->validate([
            'slot_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!ForeignKeyValidator::validateSlot($value)) {
                        $fail('The selected slot does not exist.');
                    }
                },
            ],
        ]);

        DB::connection('shafiqah')->beginTransaction();
        DB::connection('atiqah')->beginTransaction();

        try {
            // Get animal from Shafiqah's database
            $animal = Animal::findOrFail($animalId);
            $previousSlotId = $animal->slotID;

            // Update animal's slot (cross-database reference to Atiqah)
            $animal->slotID = $request->slot_id;
            $animal->save();

            // Update new slot status in Atiqah's database
            $newSlot = Slot::findOrFail($request->slot_id);
            $newSlotAnimalCount = Animal::where('slotID', $newSlot->id)->count();

            if ($newSlotAnimalCount >= $newSlot->capacity) {
                $newSlot->status = 'occupied';
            } else {
                $newSlot->status = 'available';
            }
            $newSlot->save();

            // Update old slot status in Atiqah's database
            if ($previousSlotId && $previousSlotId != $newSlot->id) {
                $oldSlot = Slot::find($previousSlotId);

                if ($oldSlot) {
                    $oldSlotAnimalCount = Animal::where('slotID', $oldSlot->id)->count();

                    if ($oldSlotAnimalCount >= $oldSlot->capacity) {
                        $oldSlot->status = 'occupied';
                    } else {
                        $oldSlot->status = 'available';
                    }

                    $oldSlot->save();
                }
            }

            DB::connection('shafiqah')->commit();
            DB::connection('atiqah')->commit();

            return back()->with('success', 'Slot assigned successfully!');

        } catch (\Exception $e) {
            DB::connection('shafiqah')->rollBack();
            DB::connection('atiqah')->rollBack();

            Log::error('Error assigning slot: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Failed to assign slot: ' . $e->getMessage()]);
        }
    }

    public function destroy(Animal $animal)
    {
        DB::connection('shafiqah')->beginTransaction();
        DB::connection('eilya')->beginTransaction();
        DB::connection('atiqah')->beginTransaction();

        try {
            // Delete images from Eilya's database
            foreach ($animal->images as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }

            // Update slot status in Atiqah's database
            $slot = Slot::find($animal->slotID);
            if ($slot) {
                $animalCount = Animal::where('slotID', $slot->id)->where('id', '!=', $animal->id)->count();
                $slot->status = ($animalCount >= $slot->capacity) ? 'occupied' : 'available';
                $slot->save();
            }

            $animalName = $animal->name;

            // Delete animal from Shafiqah's database
            $animal->delete();

            DB::connection('shafiqah')->commit();
            DB::connection('eilya')->commit();
            DB::connection('atiqah')->commit();

            // Clear cache
            ForeignKeyValidator::clearAnimalCache($animal->id);

            return redirect()->route('animal-management.index')
                ->with('success', 'Animal "' . $animalName . '" deleted successfully!');

        } catch (\Exception $e) {
            DB::connection('shafiqah')->rollBack();
            DB::connection('eilya')->rollBack();
            DB::connection('atiqah')->rollBack();

            Log::error('Error deleting animal: ' . $e->getMessage());

            return back()->withErrors(['error' => 'An error occurred while deleting the animal.']);
        }
    }

    public function indexClinic()
    {
        // Get clinics and vets from Shafiqah's database
        $clinics = Clinic::all();
        $vets = Vet::with('clinic')->get();

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

            // Create clinic in Shafiqah's database
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
                'clinicID' => 'nullable|exists:clinic,id', // Same database (Shafiqah)
                'phone' => 'required|string|max:20',
                'email' => 'required|email|max:255|unique:vet,email',
            ]);

            // Create vet in Shafiqah's database
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
                'animalID' => 'required|exists:animal,id', // Same database (Shafiqah)
                'treatment_type' => 'required|string|max:255',
                'diagnosis' => 'required|string',
                'action' => 'required|string',
                'remarks' => 'nullable|string',
                'vetID' => 'required|exists:vet,id', // Same database (Shafiqah)
                'costs' => 'nullable|numeric|min:0',
            ]);

            // Create medical record in Shafiqah's database
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
                'animalID' => 'required|exists:animal,id', // Same database (Shafiqah)
                'name' => 'required|string|max:255',
                'type' => 'required|string|max:255',
                'next_due_date' => 'nullable|date|after:today',
                'remarks' => 'nullable|string',
                'vetID' => 'required|exists:vet,id', // Same database (Shafiqah)
                'costs' => 'nullable|numeric|min:0',
            ]);

            // Create vaccination record in Shafiqah's database
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
        // Get clinic from Shafiqah's database
        $clinic = Clinic::findOrFail($id);
        return response()->json($clinic);
    }

    public function updateClinic(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'contactNum' => 'required|string|max:20',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Update clinic in Shafiqah's database
        $clinic = Clinic::findOrFail($id);
        $clinic->update([
            'name' => $request->name,
            'address' => $request->address,
            'contactNum' => $request->contactNum,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->back()->with('success', 'Clinic updated successfully!');
    }

    public function destroyClinic($id)
    {
        // Delete clinic from Shafiqah's database
        $clinic = Clinic::findOrFail($id);
        $clinic->delete();

        return redirect()->back()->with('success', 'Clinic deleted successfully!');
    }

    public function editVet($id)
    {
        try {
            // Get vet from Shafiqah's database
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
        $request->validate([
            'name' => 'required|string|max:255',
            'specialization' => 'required|string|max:255',
            'license_no' => 'required|string|max:50',
            'clinicID' => 'required|exists:clinic,id', // Same database (Shafiqah)
            'contactNum' => 'required|string|max:20',
            'email' => 'required|email|max:255',
        ]);

        // Update vet in Shafiqah's database
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
    }

    public function destroyVet($id)
    {
        // Delete vet from Shafiqah's database
        $vet = Vet::findOrFail($id);
        $vet->delete();

        return redirect()->back()->with('success', 'Veterinarian deleted successfully!');
    }
}
