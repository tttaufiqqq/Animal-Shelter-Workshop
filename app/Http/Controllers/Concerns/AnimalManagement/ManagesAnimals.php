<?php

namespace App\Http\Controllers\Concerns\AnimalManagement;

use App\Models\Animal;
use App\Models\Slot;
use App\Models\Image;
use App\Models\Rescue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait ManagesAnimals
{
    public function home()
    {
        return view('animal-management.main');
    }

    public function create($rescue_id = null)
    {
        $rescues = $this->safeQuery(fn() => Rescue::all(), collect([]), 'reporting');
        $slots = $this->safeQuery(fn() => Slot::where('status', 'Available')->get(), collect([]), 'shelter');

        return view('animal-management.create', ['slots' => $slots, 'rescues' => $rescues, 'rescue_id' => $rescue_id]);
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
            'rescueID' => 'required|exists:reporting.rescue,id',
            'slotID' => 'nullable|exists:shelter.slot,id',
            'images' => 'nullable|array|min:1',
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120',
        ], [
            'images.required' => 'Please upload at least one image.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be in JPEG, PNG, or GIF format.',
            'images.*.max' => 'Each image must not exceed 5MB.',
        ]);

        $uploadedFiles = [];
        DB::connection('reporting')->beginTransaction();

        try {
            $age = ucfirst($validated['age_category'] ?? '');
            $slotId = $validated['slotID'] ?? null;

            if ($slotId) {
                $slot = Slot::find($slotId);
                if (!$slot || $slot->status !== 'available') {
                    return back()->withInput()->withErrors(['slotID' => 'Selected slot is not available.']);
                }
            }

            $result = $this->procedureService->createAnimal([
                'name' => $validated['name'],
                'weight' => $validated['weight'],
                'species' => $validated['species'],
                'health_details' => $validated['health_details'],
                'age' => $age,
                'gender' => $validated['gender'],
                'adoption_status' => 'Not Adopted',
                'rescueID' => $validated['rescueID'],
                'slotID' => $slotId,
            ]);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $animalId = $result['animal_id'];

            if ($request->hasFile('images')) {
                $imageIndex = 1;
                foreach ($request->file('images') as $imageFile) {
                    $sanitizedName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $validated['name']));
                    $sanitizedSpecies = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $validated['species']));
                    $filename = "animal_{$animalId}_{$sanitizedName}_{$sanitizedSpecies}_{$imageIndex}";

                    $uploadResult = cloudinary()->uploadApi()->upload($imageFile->getRealPath(), ['folder' => 'animal_images', 'public_id' => $filename]);
                    $path = $uploadResult['public_id'];
                    $uploadedFiles[] = $path;

                    Image::create(['animalID' => $animalId, 'image_path' => $path, 'filename' => $filename, 'uploaded_at' => now()]);
                    $imageIndex++;
                }
            }

            DB::connection('reporting')->commit();

            return redirect()->route('animal-management.create', ['rescue_id' => $validated['rescueID']])
                ->with('success', 'Animal "' . $validated['name'] . '" added successfully with ' . count($request->file('images') ?? []) . ' image(s)! Do you want to add another animal? If so, please fill all the required fields again.');

        } catch (\Exception $e) {
            DB::connection('reporting')->rollBack();
            foreach ($uploadedFiles as $filePath) {
                cloudinary()->uploadApi()->destroy($filePath);
            }
            return back()->withInput()->withErrors(['error' => 'An error occurred while adding the animal: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('UPDATE REQUEST START', ['animal_id' => $id, 'has_images' => $request->hasFile('images'), 'image_count' => $request->file('images') ? count($request->file('images')) : 0, 'delete_images' => $request->delete_images ?? [], 'input' => $request->all()]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric',
            'species' => 'required|string|max:255',
            'health_details' => 'required|string',
            'age_category' => 'required|in:kitten,puppy,adult,senior',
            'gender' => 'required|in:Male,Female,Unknown',
            'slotID' => 'nullable|exists:shelter.slot,id',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'exists:reporting.image,id',
        ]);

        DB::connection('reporting')->beginTransaction();

        try {
            $animal = Animal::findOrFail($id);
            $uploadedFiles = [];

            $age = ucfirst($validated['age_category']);
            $slotID = $validated['slotID'] ?? $animal->slotID;

            if (($validated['slotID'] ?? null) !== null && $slotID != $animal->slotID) {
                $slot = Slot::find($slotID);
                if (!$slot || $slot->status !== 'available') {
                    return back()->withInput()->withErrors(['slotID' => 'Selected slot is not available.']);
                }
            }

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

            if (!empty($validated['delete_images'])) {
                foreach ($validated['delete_images'] as $imageId) {
                    $img = Image::where('id', $imageId)->where('animalID', $id)->first();
                    if ($img) {
                        try { cloudinary()->uploadApi()->destroy($img->image_path); } catch (\Exception $e) {}
                        $img->delete();
                    }
                }
            }

            if ($request->hasFile('images')) {
                $existingImageCount = Image::where('animalID', $id)->count();
                $imageIndex = $existingImageCount + 1;

                foreach ($request->file('images') as $file) {
                    $sanitizedName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $validated['name']));
                    $sanitizedSpecies = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $validated['species']));
                    $filename = "animal_{$id}_{$sanitizedName}_{$sanitizedSpecies}_{$imageIndex}";

                    $uploadResult = cloudinary()->uploadApi()->upload($file->getRealPath(), ['folder' => 'animal_images', 'public_id' => $filename]);
                    $path = $uploadResult['public_id'];
                    $uploadedFiles[] = $path;

                    Image::create(['animalID' => $id, 'image_path' => $path, 'filename' => $filename, 'uploaded_at' => now()]);
                    $imageIndex++;
                }
            }

            DB::connection('reporting')->commit();
            Log::info('UPDATE SUCCESSFUL', ['animal_id' => $id]);

            return redirect()->back()->with('success', 'Animal updated successfully!');

        } catch (\Exception $e) {
            DB::connection('reporting')->rollBack();
            Log::error('UPDATE FAILED', ['error' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile()]);
            foreach ($uploadedFiles ?? [] as $path) {
                cloudinary()->uploadApi()->destroy($path);
            }
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Animal $animal)
    {
        $eilyaOnline = $this->isDatabaseAvailable('reporting');
        $atiqahOnline = $this->isDatabaseAvailable('shelter');

        if ($eilyaOnline) {
            DB::connection('reporting')->beginTransaction();
        }
        if ($animal->slotID && $atiqahOnline) {
            DB::connection('shelter')->beginTransaction();
        }

        try {
            if ($eilyaOnline) {
                try {
                    foreach ($animal->images as $image) {
                        try { cloudinary()->uploadApi()->destroy($image->image_path); } catch (\Exception $e) {}
                        $image->delete();
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to delete images from eilya database', ['animal_id' => $animal->id, 'error' => $e->getMessage()]);
                }
            }

            $slotID = $animal->slotID;
            $result = $this->procedureService->deleteAnimal($animal->id);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $animalName = $result['animal_name'];

            if ($slotID && $atiqahOnline) {
                $slot = Slot::find($slotID);
                if ($slot) {
                    $remainingAnimals = Animal::where('slotID', $slotID)->count();
                    $slot->status = $remainingAnimals >= $slot->capacity ? 'occupied' : 'available';
                    $slot->save();
                }
            }

            if ($eilyaOnline) DB::connection('reporting')->commit();
            if ($animal->slotID && $atiqahOnline) DB::connection('shelter')->commit();

            $message = 'Animal "' . $animalName . '" deleted successfully!';
            if (!$eilyaOnline) $message .= ' (Note: Images could not be deleted - image database offline)';

            return redirect()->route('animal-management.index')->with('success', $message);

        } catch (\Exception $e) {
            if ($eilyaOnline) DB::connection('reporting')->rollBack();
            if ($animal->slotID && $atiqahOnline) DB::connection('shelter')->rollBack();
            Log::error('Error deleting animal: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to delete animal: ' . $e->getMessage() . ' [' . get_class($e) . ']']);
        }
    }
}
