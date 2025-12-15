<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slot;
use App\Models\Inventory;
use App\Models\Animal;
use App\Models\Section;
use App\Models\Category;
use App\DatabaseErrorHandler;

class ShelterManagementController extends Controller
{
    use DatabaseErrorHandler;
    // In your controller
    public function indexSlot()
    {
        $result = $this->safeQuery(function() {
            $sections = Section::all();

            // Get counts BEFORE pagination
            $totalSlots = Slot::count();
            $availableSlots = Slot::where('status', 'available')->count();
            $occupiedSlots = Slot::where('status', 'occupied')->count();
            $maintenanceSlots = Slot::where('status', 'maintenance')->count();

            // Then paginate
            $slots = Slot::with(['animals', 'inventories'])
                ->orderBy('sectionID')
                ->orderBy('name')
                ->paginate(30);

            $categories = Category::all();

            return compact(
                'sections',
                'slots',
                'categories',
                'totalSlots',
                'availableSlots',
                'occupiedSlots',
                'maintenanceSlots'
            );
        }, [
            'sections' => collect([]),
            'slots' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 9),
            'categories' => collect([]),
            'totalSlots' => 0,
            'availableSlots' => 0,
            'occupiedSlots' => 0,
            'maintenanceSlots' => 0,
        ], 'atiqah'); // Pre-check atiqah database for faster loading

        return view('shelter-management.index', $result);
    }

    // SECTION METHODS
    public function storeSection(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
        ]);

        Section::create($validated);

        return redirect()->back()->with('success', 'Section created successfully!');
    }

    public function editSection($id)
    {
        $section = Section::findOrFail($id);
        return response()->json($section);
    }

    public function updateSection(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
        ]);

        $section = Section::findOrFail($id);
        $section->update($validated);

        return redirect()->back()->with('success', 'Section updated successfully!');
    }

    public function deleteSection($id)
    {
        $section = Section::findOrFail($id);

        // Check if section has slots
        if ($section->slots()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete section with existing slots!');
        }

        $section->delete();

        return redirect()->back()->with('success', 'Section deleted successfully!');
    }

    public function storeSlot(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'sectionID' => 'required|exists:section,id', // Changed to sectionID and correct table name
                'capacity' => 'required|integer|min:1',
            ]);

            $validated['status'] = 'available';
            Slot::create($validated);

            return redirect()->back()->with('success', 'Slot added successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add slot: ' . $e->getMessage());
        }
    }

    public function editSlot($id)
    {
        try {
            $slot = Slot::findOrFail($id);

            return response()->json([
                'id' => $slot->id,
                'name' => $slot->name,
                'sectionID' => $slot->sectionID, // Changed from 'section' to 'sectionID'
                'capacity' => $slot->capacity,
                'status' => $slot->status,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Slot not found'
            ], 404);
        }
    }

    public function updateSlot(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'sectionID' => 'required|exists:section,id',
                'capacity' => 'required|integer|min:1',
                'status' => 'nullable|in:available,occupied,maintenance', // Made nullable since we'll auto-calculate
            ]);

            $slot = Slot::findOrFail($id);

            // Get current animal count
            $animalCount = $slot->animals()->count();

            // Auto-calculate status based on capacity and animal count
            // Only respect manual 'maintenance' status, otherwise auto-calculate
            if ($request->status === 'maintenance') {
                $validated['status'] = 'maintenance';
            } else {
                // Auto-calculate status
                if ($animalCount === 0) {
                    $validated['status'] = 'available';
                } elseif ($animalCount >= $validated['capacity']) {
                    $validated['status'] = 'occupied';
                } else {
                    $validated['status'] = 'available';
                }
            }

            $slot->update($validated);

            return redirect()->back()->with('success', 'Slot updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update slot: ' . $e->getMessage());
        }
    }

    public function deleteSlot($id)
    {
        try {
            $slot = Slot::findOrFail($id);

            if ($slot->animals()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete slot with animals. Please relocate them first.');
            }

            $slot->delete();

            return redirect()->back()->with('success', 'Slot deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete slot: ' . $e->getMessage());
        }
    }

    // CATEGORY METHODS
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'main' => 'required|string|max:255',
            'sub' => 'required|string|max:255',
        ]);

        Category::create($validated);

        return redirect()->back()->with('success', 'Category created successfully!');
    }

    public function editCategory($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    public function updateCategory(Request $request, $id)
    {
        $validated = $request->validate([
            'main' => 'required|string|max:255',
            'sub' => 'required|string|max:255',
        ]);

        $category = Category::findOrFail($id);
        $category->update($validated);

        return redirect()->back()->with('success', 'Category updated successfully!');
    }

    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);

        // Check if category has inventories
        if ($category->inventories()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category with existing inventory items!');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully!');
    }

    public function getAnimalDetails($id)
    {
        $data = $this->safeQuery(function() use ($id) {
            $animal = Animal::findOrFail($id);

            $medicals = $animal->medicals()->with('vet')->get();
            $vaccinations = $animal->vaccinations()->with('vet')->get();
            $images = $animal->images()->get();

            return [
                'id' => $animal->id,
                'name' => $animal->name ?? 'Unknown',
                'species' => $animal->species ?? 'Unknown',
                'breed' => $animal->breed ?? 'Unknown',
                'adoption_status' => $animal->adoption_status ?? 'unknown',
                'health_details' => $animal->health_details ?? 'No health details available.',
                'images' => $images->map(function($image) {
                    return [
                        'id' => $image->id,
                        'path' => $image->image_path,
                    ];
                }),
                'medicals' => $medicals->map(function($medical) {
                    return [
                        'id' => $medical->id,
                        'treatment' => $medical->treatment_type ?? 'N/A',
                        'diagnosis' => $medical->diagnosis ?? 'N/A',
                        'date' => $medical->created_at ? $medical->created_at->format('Y-m-d') : date('Y-m-d'),
                        'cost' => $medical->costs ?? 0,
                        'notes' => $medical->action ?? $medical->remarks ?? '',
                        'vet_name' => $medical->vet->name ?? 'Unknown',
                    ];
                }),
                'vaccinations' => $vaccinations->map(function($vaccination) {
                    return [
                        'id' => $vaccination->id,
                        'vaccine_name' => $vaccination->name ?? 'N/A',
                        'date' => $vaccination->created_at ? $vaccination->created_at->format('Y-m-d') : date('Y-m-d'),
                        'next_due_date' => $vaccination->next_due_date ?? null,
                        'batch_number' => $vaccination->type ?? 'N/A',
                        'administered_by' => $vaccination->vet->name ?? 'Unknown',
                        'notes' => $vaccination->remarks ?? '',
                        'cost' => $vaccination->costs ?? 0,
                    ];
                }),
            ];
        }, null, 'shafiqah'); // Properly close safeQuery with connection parameter

        if ($data === null) {
            return response()->json([
                'error' => 'Failed to load animal details',
                'message' => 'Database connection unavailable or animal not found'
            ], 500);
        }

        return response()->json($data);
    }

    public function getSlotDetails($id)
    {
        try {
            // Add 'section' relationship to eager load
            $slot = Slot::with([
                'section', // ADD THIS - to load section relationship
                'animals.vaccinations',
                'animals.medicals',
                'inventories.category'
            ])->findOrFail($id);

            return response()->json([
                'id' => $slot->id,
                'name' => $slot->name,
                'section' => $slot->section ? [ // CHANGED - return section object
                    'id' => $slot->section->id,
                    'name' => $slot->section->name,
                    'description' => $slot->section->description,
                ] : null,
                'capacity' => $slot->capacity,
                'status' => $slot->status,
                'animals' => $slot->animals->map(function($animal) {
                    return [
                        'id' => $animal->id,
                        'name' => $animal->name,
                        'species' => $animal->species,
                        'age' => $animal->age,
                        'gender' => $animal->gender,
                        'adoption_status' => $animal->adoption_status,
                        'health_details' => $animal->health_details,
                        // Optional: Add vaccination and medical counts
                        'vaccinations_count' => $animal->vaccinations->count(),
                        'medicals_count' => $animal->medicals->count(),
                    ];
                }),
                'inventories' => $slot->inventories->map(function($inventory) {
                    return [
                        'id' => $inventory->id,
                        'name' => $inventory->item_name,
                        'category' => $inventory->category ? [
                            'main' => $inventory->category->main,
                            'sub' => $inventory->category->sub,
                        ] : null,
                        'quantity' => $inventory->quantity,
                        'unit' => $inventory->unit,
                        'description' => $inventory->description,
                        'brand' => $inventory->brand,
                        'status' => $inventory->status,
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            \Log::error('Slot details fetch error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Slot not found',
                'message' => $e->getMessage() // Add this for debugging
            ], 404);
        }
    }

    public function storeInventory(Request $request)
    {
        try {
            $validated = $request->validate([
                'slotID' => 'required|exists:slot,id',
                'item_name' => 'required|string|max:255',
                'categoryID' => 'required|exists:category,id',
                'quantity' => 'required|integer|min:0',
                'weight' => 'nullable|numeric|min:0',
                'brand' => 'nullable|string|max:255',
                'status' => 'required|in:available,low,out',
            ]);

            Inventory::create($validated);

            return redirect()->back()->with('success', 'Inventory item added successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add inventory: ' . $e->getMessage());
        }
    }

    public function getInventoryDetails($id)
    {
        try {
            $inventory = Inventory::with(['category', 'slot'])->findOrFail($id);

            return response()->json([
                'id' => $inventory->id,
                'item_name' => $inventory->item_name,
                'quantity' => $inventory->quantity,
                'weight' => $inventory->weight,
                'brand' => $inventory->brand,
                'status' => $inventory->status,
                'categoryID' => $inventory->categoryID,
                'category_main' => $inventory->category->main ?? null,
                'category_sub' => $inventory->category->sub ?? null,
                'slot_name' => $inventory->slot->name ?? null,
                'slot_section' => $inventory->slot->section ?? null,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Inventory not found'], 404);
        }
    }

    public function updateInventory(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'item_name' => 'required|string|max:255',
                'categoryID' => 'required|exists:category,id',
                'quantity' => 'required|integer|min:0',
                'weight' => 'nullable|numeric|min:0',
                'brand' => 'nullable|string|max:255',
                'status' => 'required|in:available,low,out',
            ]);

            $inventory = Inventory::findOrFail($id);
            $inventory->update($validated);

            return redirect()->back()->with('success', 'Inventory updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update inventory: ' . $e->getMessage());
        }
    }

    public function deleteInventory($id)
    {
        try {
            $inventory = Inventory::findOrFail($id);
            $inventory->delete();

            return redirect()->back()->with('success', 'Inventory deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete inventory: ' . $e->getMessage());
        }
    }
}
