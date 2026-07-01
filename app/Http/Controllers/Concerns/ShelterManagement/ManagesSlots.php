<?php

namespace App\Http\Controllers\Concerns\ShelterManagement;

use App\Models\Slot;
use App\Models\Animal;
use App\Models\Section;
use App\Models\Category;
use Illuminate\Http\Request;

trait ManagesSlots
{
    public function indexSlot()
    {
        $result = $this->safeQuery(function() {
            $sections = Section::all();
            $totalSlots = Slot::count();
            $availableSlots = Slot::where('status', 'available')->count();
            $occupiedSlots = Slot::where('status', 'occupied')->count();
            $maintenanceSlots = Slot::where('status', 'maintenance')->count();

            $slots = Slot::query()->with(['section', 'inventories'])
                ->orderBy('sectionID')->orderBy('name')->paginate(100);

            if ($this->isDatabaseAvailable('animals')) {
                $slotIds = $slots->pluck('id')->toArray();
                $animals = Animal::whereIn('slotID', $slotIds)->get()->groupBy('slotID');
                $slots->each(fn($slot) => $slot->setRelation('animals', $animals->get($slot->id, collect([]))));
            } else {
                $slots->each(fn($slot) => $slot->setRelation('animals', collect([])));
            }

            $categories = Category::all();

            return compact('sections', 'slots', 'categories', 'totalSlots', 'availableSlots', 'occupiedSlots', 'maintenanceSlots');
        }, [
            'sections' => collect([]),
            'slots' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 9),
            'categories' => collect([]),
            'totalSlots' => 0, 'availableSlots' => 0, 'occupiedSlots' => 0, 'maintenanceSlots' => 0,
        ], 'shelter');

        return view('shelter-management.index', $result);
    }

    public function storeSlot(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'sectionID' => 'required|integer',
                'capacity' => 'required|integer|min:1',
            ]);

            $validated['status'] = 'available';
            $result = $this->atiqahService->createSlot($validated);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            }

            return redirect()->back()->withInput()->with('error', $result['message']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Failed to create slot: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'input' => $request->all()]);
            return redirect()->back()->withInput()->with('error', 'Failed to add slot: ' . $e->getMessage());
        }
    }

    public function editSlot($id)
    {
        try {
            $data = $this->safeQuery(function() use ($id) {
                $slot = Slot::findOrFail($id);
                return ['id' => $slot->id, 'name' => $slot->name, 'sectionID' => $slot->sectionID, 'capacity' => $slot->capacity, 'status' => $slot->status];
            }, null, 'shelter');

            if ($data === null) {
                \Log::error('Failed to load slot for editing', ['slot_id' => $id, 'database' => 'shelter']);
                return response()->json(['error' => 'Failed to load slot data', 'message' => 'Database connection unavailable or slot not found'], 500);
            }

            return response()->json($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning('Slot not found for editing', ['slot_id' => $id]);
            return response()->json(['error' => 'Slot not found', 'message' => 'The requested slot does not exist'], 404);
        }
    }

    public function updateSlot(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'sectionID' => 'required|integer',
                'capacity' => 'required|integer|min:1',
                'status' => 'nullable|in:available,occupied,maintenance',
            ]);

            if (isset($validated['status']) && $validated['status'] === 'maintenance') {
                $statusToSet = 'maintenance';
            } else {
                $animalCount = 0;
                if ($this->isDatabaseAvailable('animals')) {
                    $animalCount = Animal::where('slotID', $id)->count();
                }
                $statusToSet = $animalCount >= $validated['capacity'] ? 'occupied' : 'available';
            }

            $validated['status'] = $statusToSet;
            $result = $this->atiqahService->updateSlot($id, $validated);

            if ($result['success']) {
                \Log::info('Slot updated successfully via procedure', ['slot_id' => $id, 'status' => $statusToSet]);
                return redirect()->back()->with('success', $result['message']);
            }

            return redirect()->back()->withInput()->with('error', $result['message']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update slot: ' . $e->getMessage());
        }
    }

    public function deleteSlot($id)
    {
        try {
            \Log::info('Attempting to delete slot', ['slot_id' => $id]);

            $slot = $this->atiqahService->readSlot($id);

            if (!$slot) {
                \Log::error('Slot not found for deletion', ['slot_id' => $id]);
                return redirect()->back()->with('error', 'Slot not found or already deleted.');
            }

            $animalCount = 0;
            if ($this->isDatabaseAvailable('animals')) {
                try {
                    $animalCount = Animal::where('slotID', $id)->count();
                    \Log::info('Animal count for slot', ['slot_id' => $id, 'animal_count' => $animalCount]);
                } catch (\Exception $e) {
                    \Log::warning('Failed to count animals for slot deletion', ['slot_id' => $id, 'error' => $e->getMessage()]);
                }
            }

            if ($animalCount > 0) {
                \Log::warning('Cannot delete slot - has animals', ['slot_id' => $id, 'animal_count' => $animalCount]);
                return redirect()->back()
                    ->with('error', "Cannot delete slot '{$slot->name}' - it has {$animalCount} animal(s). Please relocate them first.");
            }

            $result = $this->atiqahService->deleteSlot($id);

            if ($result['success']) {
                \Log::info('Slot deleted successfully', ['slot_id' => $id]);
                return redirect()->back()->with('success', $result['message']);
            }

            return redirect()->back()->with('error', $result['message']);

        } catch (\Exception $e) {
            \Log::error('Failed to delete slot', ['slot_id' => $id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to delete slot: ' . $e->getMessage());
        }
    }

    public function getSlotDetails($id)
    {
        $data = $this->safeQuery(function() use ($id) {
            $slot = Slot::with(['section', 'inventories.category'])->findOrFail($id);

            if ($this->isDatabaseAvailable('animals')) {
                $animals = Animal::where('slotID', $slot->id)->with(['vaccinations', 'medicals'])->get();
                $slot->setRelation('animals', $animals);
            } else {
                $slot->setRelation('animals', collect([]));
            }

            $animalsData = [];
            if ($this->isDatabaseAvailable('animals') && $slot->relationLoaded('animals')) {
                $animalsData = $slot->animals->map(fn($animal) => [
                    'id' => $animal->id, 'name' => $animal->name, 'species' => $animal->species,
                    'age' => $animal->age, 'gender' => $animal->gender,
                    'adoption_status' => $animal->adoption_status, 'health_details' => $animal->health_details,
                    'vaccinations_count' => $animal->relationLoaded('vaccinations') ? $animal->vaccinations->count() : 0,
                    'medicals_count' => $animal->relationLoaded('medicals') ? $animal->medicals->count() : 0,
                ]);
            }

            return [
                'id' => $slot->id, 'name' => $slot->name,
                'section' => $slot->section ? ['id' => $slot->section->id, 'name' => $slot->section->name, 'description' => $slot->section->description] : null,
                'capacity' => $slot->capacity, 'status' => $slot->status,
                'animals' => $animalsData,
                'inventories' => $slot->inventories->map(fn($inventory) => [
                    'id' => $inventory->id, 'name' => $inventory->item_name,
                    'category' => $inventory->category ? ['main' => $inventory->category->main, 'sub' => $inventory->category->sub] : null,
                    'quantity' => $inventory->quantity, 'unit' => $inventory->unit,
                    'description' => $inventory->description, 'brand' => $inventory->brand, 'status' => $inventory->status,
                ]),
            ];
        }, null, 'shelter');

        if ($data === null) {
            \Log::error('Slot details fetch failed - database unavailable', ['slot_id' => $id]);
            return response()->json(['error' => 'Slot not found', 'message' => 'Database connection unavailable or slot not found'], 404);
        }

        return response()->json($data);
    }
}
