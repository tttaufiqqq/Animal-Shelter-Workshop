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
use App\Models\Booking;  
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class AnimalManagementController extends Controller
{
    public function home(){
        return view('animal-management.main');
    }
    
    public function create($rescue_id = null)
    {
        $rescues = Rescue::all();
        $slots = Slot::all();
        
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
            'species' => 'required|string|max:255',
            'health_details' => 'required|string',
            'age_number' => 'required|numeric|min:0',
            'age_unit' => 'required|in:months,years',
            'gender' => 'required|in:Male,Female,Unknown',
            'rescueID' => 'required|exists:rescue,id',
            'slotID' => 'nullable|exists:slot,id',
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|mimes:jpeg,jpg,png,gif|max:5120',
        ], [
            'images.required' => 'Please upload at least one image.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be in JPEG, PNG, or GIF format.',
            'images.*.max' => 'Each image must not exceed 5MB.',
        ]);

        $uploadedFiles = [];

        DB::beginTransaction();

        try {
            $age = $validated['age_number'] . ' ' . $validated['age_unit'];

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
                'species' => $validated['species'],
                'health_details' => $validated['health_details'],
                'age' => $age,
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

            DB::commit();

            return redirect()->route('animal-management.create', ['rescue_id' => $validated['rescueID']])
                ->with('success', 'Animal "' . $animal->name . '" added successfully with ' . count($request->file('images')) . ' image(s)! Do you want to add another animal? If so, please fill all the required fields again.');

        } catch (\Exception $e) {
            DB::rollBack();

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
        'species' => 'required|string|max:255',
        'health_details' => 'required|string',
        'age_number' => 'required|numeric|min:0',
        'age_unit' => 'required|in:months,years',
        'gender' => 'required|in:Male,Female,Unknown',
        'slotID' => 'nullable|exists:slot,id',
        'images' => 'nullable|array',
        'images.*' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120',
        'delete_images' => 'nullable|array',
        'delete_images.*' => 'exists:image,id',
    ]);

    DB::beginTransaction();

    try {
        $animal = Animal::findOrFail($id);
        $uploadedFiles = [];

        // ----- Combine Age -----
        $age = $validated['age_number'] . ' ' . $validated['age_unit'];

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
            'species' => $validated['species'],
            'health_details' => $validated['health_details'],
            'age' => $age,
            'gender' => $validated['gender'],
            'slotID' => $slotID,
        ]);

        \Log::info('Animal updated basic info.', [
            'id' => $animal->id,
            'slotID' => $slotID
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

        DB::commit();

        \Log::info('UPDATE SUCCESSFUL', [
            'animal_id' => $animal->id
        ]);

        return redirect()->back()->with('success', 'Animal updated successfully!');

    } catch (\Exception $e) {
        DB::rollBack();

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
        $query = Animal::with(['images', 'slot']);

        // Cross-RDBMS case-insensitive search
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

        $query->orderBy('created_at', 'desc');

        $animals = $query->paginate(12)->appends($request->query());

        return view('animal-management.main', compact('animals'));
    }

    public function show($id)
    {
        $animal = Animal::with([
            'images',
            'slot',
            'rescue.report',
        ])->findOrFail($id);
        
        $medicals = Medical::with('vet')
            ->where('animalID', $id)
            ->get();
        
        $vaccinations = Vaccination::with('vet')
            ->where('animalID', $id)
            ->get();
        
        $vets = Vet::all();
        $slots = Slot::where('status', 'available')->get();
        
        $bookedSlots = $animal->bookings->map(function($booking) {
            $dateTime = \Carbon\Carbon::parse($booking->appointment_date . ' ' . $booking->appointment_time);
            
            return [
                'date' => $dateTime->format('Y-m-d'),
                'time' => $dateTime->format('H:i'),
                'datetime' => $dateTime->format('Y-m-d\TH:i'),
            ];
        });
        
        return view('animal-management.show', compact('animal', 'vets', 'medicals', 'vaccinations', 'slots', 'bookedSlots'));
    }

    public function assignSlot(Request $request, $animalId)
    {
        $request->validate([
            'slot_id' => 'required|exists:slot,id',
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
    }

    public function destroy(Animal $animal)
    {
        DB::beginTransaction();

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

            DB::commit();

            return redirect()->route('animal-management.index')
                ->with('success', 'Animal "' . $animalName . '" deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error deleting animal: ' . $e->getMessage());

            return back()->withErrors(['error' => 'An error occurred while deleting the animal.']);
        }
    }

    public function indexClinic()
    {
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