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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AnimalManagementController extends Controller
{
    public function home(){
        return view('animal-management.main');
    }
     public function create($rescue_id = null)
    {
        $rescues = Rescue::all();
        $slots = Slot::all(); // Only show available slots
        
        return view('animal-management.create', ['slots' => $slots, 'rescues' =>$rescues, 'rescue_id' => $rescue_id,]);
    }

    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'required|string|max:255',
            'health_details' => 'required|string',
            'age_number' => 'required|numeric|min:0',
            'age_unit' => 'required|in:months,years',
            'gender' => 'required|in:Male,Female,Unknown',
            'rescueID' => 'required|exists:rescue,id',
            'slotID' => 'nullable|exists:slot,id',
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|mimes:jpeg,jpg,png,gif|max:5120', // 5MB max
        ], [
            'images.required' => 'Please upload at least one image.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be in JPEG, PNG, or GIF format.',
            'images.*.max' => 'Each image must not exceed 5MB.',
        ]);

        $uploadedFiles = [];

        DB::beginTransaction();

        try {
            // Combine age number and unit
            $age = $validated['age_number'] . ' ' . $validated['age_unit'];

            // Check if slot is available (only if provided)
            $slot = null;
            if ($validated['slotID']) {
                $slot = Slot::find($validated['slotID']);
                if (!$slot || $slot->status !== 'available') {
                    return back()
                        ->withInput()
                        ->withErrors(['slotID' => 'Selected slot is not available.']);
                }
            }

            // Create the animal record
            $animal = Animal::create([
                'name' => $validated['name'],
                'species' => $validated['species'],
                'health_details' => $validated['health_details'],
                'age' => $age, // combined age string
                'gender' => $validated['gender'],
                'adoption_status' => 'Not Adopted',
                'arrival_date' => now(),
                'medical_status' => 'Pending',
                'rescueID' => $validated['rescueID'],
                'slotID' => $validated['slotID'],
            ]);

            // Handle image uploads
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

            DB::commit();

            return redirect()->route('animal-management.create', ['rescue_id' => $validated['rescueID']])
    ->with('success', 'Animal "' . $animal->name . '" added successfully with ' . count($request->file('images')) . ' image(s)! Do you want to add another animal? If so, please fill all the required fields again.');

        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded files if any error occurs
            foreach ($uploadedFiles as $filePath) {
                Storage::disk('public')->delete($filePath);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'An error occurred while adding the animal: ' . $e->getMessage()]);
        }
    }


    /**
     * Display a listing of animals
     */
    public function index(Request $request)
    {
        $query = Animal::with(['images', 'slot']);

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'ILIKE', '%' . $request->search . '%');
        }

        // Filter by species
        if ($request->filled('species')) {
            $query->where('species', 'ILIKE', '%' . $request->species . '%');
        }

        // Filter by adoption status
        if ($request->filled('adoption_status')) {
            $query->where('adoption_status', $request->adoption_status);
        }

        // Filter by gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Order by most recent first
        $query->orderBy('created_at', 'desc');

        // Paginate results (12 per page)
        $animals = $query->paginate(12)->appends($request->query());

        return view('animal-management.main', compact('animals'));
    }
    /**
     * Display the specified animal
     */
    public function show($id)
    {
        // Fetch the animal with all related data
        $animal = Animal::with([
            'images',
            'slot',
            'rescue.report'
        ])->findOrFail($id);
        
        // Get medical and vaccination records for THIS animal only
        $medicals = Medical::with('vet')
            ->where('animalID', $id)
            ->get();
        
        $vaccinations = Vaccination::with('vet')
            ->where('animalID', $id)
            ->get();
        
        $vets = Vet::all();
        $slots = Slot::where('status', 'available')->get();
        
        return view('animal-management.show', compact('animal', 'vets', 'medicals', 'vaccinations', 'slots'));
    }

    public function assignSlot(Request $request, $animalId)
    {
        $request->validate([
            'slot_id' => 'required|exists:slot,id',
        ]);

        $animal = Animal::findOrFail($animalId);

        $slot = Slot::findOrFail($request->slot_id);

        // Assign slot to animal
        $animal->slotID = $slot->id;
        $animal->save();

        // Optionally: update slot status to occupied
        $slot->status = 'occupied';
        $slot->save();

        return back()->with('success', 'Slot assigned successfully!');
    }


    /**
     * Remove the specified animal from storage
     */
    public function destroy(Animal $animal)
    {
        DB::beginTransaction();

        try {
            // Delete all associated images from storage
            foreach ($animal->images as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }

            // Free up the slot
            $slot = Slot::find($animal->slotID);
            if ($slot) {
                $slot->update(['status' => 'available']);
            }

            // Delete the animal
            $animalName = $animal->name;
            $animal->delete();

            DB::commit();

            return redirect()->route('animal-management.index')
                ->with('success', 'Animal "' . $animalName . '" deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error deleting animal: ' . $e->getMessage());

            return back()->withErrors(['error' => 'An error occurred while deleting the animal.']);
        }
    }

    public function indexClinic(){
        $clinics = Clinic::all();
        $vets = Vet::with('clinic')->get(); 
        return view('animal-management.main-manage-cv', ['clinics' => $clinics, 'vets' => $vets]);
    }

     public function storeClinic(Request $request)
    {
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'clinic_name' => 'required|string|max:255',
                'address' => 'required|string|max:500',
                'phone' => 'required|string|max:20',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);

            // Create new clinic record
            $clinic = Clinic::create([
                'name' => $validated['clinic_name'],
                'address' => $validated['address'],
                'contactNum' => $validated['phone'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);

            // Redirect back with success message
            return redirect()->back()->with('success', 'Clinic added successfully!');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            // Handle any other errors
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add clinic: ' . $e->getMessage());
        }
    }

    public function storeVet(Request $request)
    {
       
        try {
            // Validate the incoming request
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
            // Create new vet record
            $vet = Vet::create($dataToInsert);
            // Redirect back with success message
            return redirect()->back()->with('success', 'Veterinarian added successfully!');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            dd('Validation Error', [
                'errors' => $e->errors(),
                'message' => $e->getMessage()
            ]);
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            // Handle any other errors
            dd('General Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]); 
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add veterinarian: ' . $e->getMessage());
        }
    }
    public function storeMedical(Request $request)
    {
        // Debug: Check incoming request data
        // dd($request->all());
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
            // Debug: Check validated data
            // dd($validated);
            $medical = Medical::create($validated);
            // Debug: Check created record
            // dd($medical);
            return redirect()->back()->with('success', 'Medical record added successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Debug: Check validation errors
            dd([
                'validation_errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            // Debug: Check exception details
            dd([
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add medical record: ' . $e->getMessage());
        }
    }

    /**
     * Store vaccination record
     */
    public function storeVaccination(Request $request)
    {
        // Debug: Check incoming request data
        // dd($request->all());
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
            // Debug: Check validated data
            // dd($validated);
            $vaccination = Vaccination::create($validated);
            // Debug: Check created record
            // dd($vaccination);
            return redirect()->back()->with('success', 'Vaccination record added successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Debug: Check validation errors
            dd([
                'validation_errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            // Debug: Check exception details
            dd([
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add vaccination record: ' . $e->getMessage());
        }
    }

    public function editClinic($id)
    {
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
        $clinic = Clinic::findOrFail($id);
        $clinic->delete();

        return redirect()->back()->with('success', 'Clinic deleted successfully!');
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
    }

    public function destroyVet($id)
    {
        $vet = Vet::findOrFail($id);
        $vet->delete();

        return redirect()->back()->with('success', 'Veterinarian deleted successfully!');
    }
}
