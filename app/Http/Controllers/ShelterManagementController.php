<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slot;
use App\Models\Inventory;
use App\Models\Animal;
use App\Models\Section;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ForeignKeyValidator;

class ShelterManagementController extends Controller
{
    /**
     * Display shelter management index
     * Sections, Slots, Categories, Inventory from Atiqah's database
     * Animals from Shafiqah's database (cross-database relationship)
     */
    public function indexSlot()
    {
        try {
            // Get sections from Atiqah's database
            $sections = Section::all();

            // Get slot counts from Atiqah's database (BEFORE pagination)
            $totalSlots = Slot::count();
            $availableSlots = Slot::where('status', 'available')->count();
            $occupiedSlots = Slot::where('status', 'occupied')->count();
            $maintenanceSlots = Slot::where('status', 'maintenance')->count();

            // Get slots with relationships from Atiqah's database
            // animals() is a cross-database relationship to Shafiqah
            $slots = Slot::with(['animals', 'inventories'])
                ->orderBy('sectionID')
                ->orderBy('name')
                ->paginate(9);

            // Get categories from Atiqah's database
            $categories = Category::all();

            return view('shelter-management.index', compact(
                'sections',
                'slots',
                'categories',
                'totalSlots',
                'availableSlots',
                'occupiedSlots',
                'maintenanceSlots'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading shelter management index: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return view('shelter-management.index', [
                'sections' => collect(),
                'slots' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 9),
                'categories' => collect(),
                'totalSlots' => 0,
                'availableSlots' => 0,
                'occupiedSlots' => 0,
                'maintenanceSlots' => 0,
            ])->with('error', 'Unable to load shelter data. Please try again.');
        }
    }

    // ========================================
    // SECTION METHODS (Atiqah's database)
    // ========================================

    public function storeSection(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
        ]);

        // Create section in Atiqah's database
        Section::create($validated);

        return redirect()->back()->with('success', 'Section created successfully!');
    }

    public function editSection($id)
    {
        // Get section from Atiqah's database
        $section = Section::findOrFail($id);
        return response()->json($section);
    }

    public function updateSection(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
        ]);

        // Update section in Atiqah's database
        $section = Section::findOrFail($id);
        $section->update($validated);

        return redirect()->back()->with('success', 'Section updated successfully!');
    }

    public function deleteSection($id)
    {
        // Get section from Atiqah's database
        $section = Section::findOrFail($id);

        // Check if section has slots (same database check)
        if ($section->slots()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete section with existing slots!');
        }

        $section->delete();

        return redirect()->back()->with('success', 'Section deleted successfully!');
    }

    // ========================================
    // SLOT METHODS (Atiqah's database)
    // ========================================

    public function storeSlot(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'sectionID' => 'required|exists:section,id', // Same database validation
                'capacity' => 'required|integer|min:1',
            ]);

            // Create slot in Atiqah's database
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
            // Get slot from Atiqah's database
            $slot = Slot::findOrFail($id);

            return response()->json([
                'id' => $slot->id,
                'name' => $slot->name,
                'sectionID' => $slot->sectionID,
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
        // Use multi-database transaction (Atiqah for slot, Shafiqah for animal count)
        DB::connection('atiqah')->beginTransaction();

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'sectionID' => 'required|exists:section,id',
                'capacity' => 'required|integer|min:1',
                'status' => 'nullable|in:available,occupied,maintenance',
            ]);

            // Get slot from Atiqah's database
            $slot = Slot::findOrFail($id);

            // Get current animal count (cross-database query to Shafiqah)
            $animalCount = Animal::where('slotID', $slot->id)->count();

            // Auto-calculate status based on capacity and animal count
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

            // Update slot in Atiqah's database
            $slot->update($validated);

            DB::connection('atiqah')->commit();

            return redirect()->back()->with('success', 'Slot updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::connection('atiqah')->rollBack();

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            DB::connection('atiqah')->rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update slot: ' . $e->getMessage());
        }
    }

    public function deleteSlot($id)
    {
        try {
            // Get slot from Atiqah's database
            $slot = Slot::findOrFail($id);

            // Check if slot has animals (cross-database query to Shafiqah)
            $animalCount = Animal::where('slotID', $slot->id)->count();

            if ($animalCount > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete slot with animals. Please relocate them first.');
            }

            // Delete slot from Atiqah's database
            $slot->delete();

            return redirect()->back()->with('success', 'Slot deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete slot: ' . $e->getMessage());
        }
    }

    // ========================================
    // CATEGORY METHODS (Atiqah's database)
    // ========================================

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'main' => 'required|string|max:255',
            'sub' => 'required|string|max:255',
        ]);

        // Create category in Atiqah's database
        Category::create($validated);

        return redirect()->back()->with('success', 'Category created successfully!');
    }

    public function editCategory($id)
    {
        // Get category from Atiqah's database
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    public function updateCategory(Request $request, $id)
    {
        $validated = $request->validate([
            'main' => 'required|string|max:255',
            'sub' => 'required|string|max:255',
        ]);

        // Update category in Atiqah's database
        $category = Category::findOrFail($id);
        $category->update($validated);

        return redirect()->back()->with('success', 'Category updated successfully!');
    }

    public function deleteCategory($id)
    {
        // Get category from Atiqah's database
        $category = Category::findOrFail($id);

        // Check if category has inventories (same database check)
        if ($category->inventories()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category with existing inventory items!');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully!');
    }

    // ========================================
    // ANIMAL DETAILS (Shafiqah's database)
    // ========================================

    public function getAnimalDetails($id)
    {
        try {
            // Validate animal exists in Shafiqah's database
            if (!ForeignKeyValidator::validateAnimal($id)) {
                return response()->json([
                    'error' => 'Animal not found'
                ], 404);
            }

            // Get animal from Shafiqah's database with relationships
            $animal = Animal::with([
                'medicals.vet',      // Shafiqah -> Shafiqah
                'vaccinations.vet',  // Shafiqah -> Shafiqah
                'images'             // Cross-database to Eilya
            ])->findOrFail($id);

            return response()->json([
                'id' => $animal->id,
                'name' => $animal->name ?? 'Unknown',
                'species' => $animal->species ?? 'Unknown',
                'age' => $animal->age ?? 'Unknown',
                'gender' => $animal->gender ?? 'Unknown',
                'adoption_status' => $animal->adoption_status ?? 'unknown',
                'health_details' => $animal->health_details ?? 'No health details available.',
                'images' => $animal->images->map(function($image) {
                    return [
                        'id' => $image->id,
                        'path' => $image->image_path,
                    ];
                }),
                'medicals' => $animal->medicals->map(function($medical) {
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
                'vaccinations' => $animal->vaccinations->map(function($vaccination) {
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
            ]);

        } catch (\Exception $e) {
            Log::error('Animal details error for ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load animal details',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }

    // ========================================
    // SLOT DETAILS (Atiqah + Shafiqah cross-database)
    // ========================================

    public function getSlotDetails($id)
    {
        try {
            // Validate slot exists in Atiqah's database
            if (!ForeignKeyValidator::validateSlot($id)) {
                return response()->json([
                    'error' => 'Slot not found'
                ], 404);
            }

            // Get slot from Atiqah's database with relationships
            $slot = Slot::with([
                'section',                     // Atiqah -> Atiqah
                'animals.vaccinations',        // Atiqah -> Shafiqah -> Shafiqah
                'animals.medicals',            // Atiqah -> Shafiqah -> Shafiqah
                'inventories.category'         // Atiqah -> Atiqah -> Atiqah
            ])->findOrFail($id);

            return response()->json([
                'id' => $slot->id,
                'name' => $slot->name,
                'section' => $slot->section ? [
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
                        'brand' => $inventory->brand,
                        'weight' => $inventory->weight,
                        'status' => $inventory->status,
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            Log::error('Slot details fetch error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Slot not found',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 404);
        }
    }

    // ========================================
    // INVENTORY METHODS (Atiqah's database)
    // ========================================

    public function storeInventory(Request $request)
    {
        try {
            $validated = $request->validate([
                'slotID' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        // Validate slot exists in Atiqah's database
                        if (!ForeignKeyValidator::validateSlot($value)) {
                            $fail('The selected slot does not exist.');
                        }
                    },
                ],
                'item_name' => 'required|string|max:255',
                'categoryID' => 'required|exists:category,id', // Same database
                'quantity' => 'required|integer|min:0',
                'weight' => 'nullable|numeric|min:0',
                'brand' => 'nullable|string|max:255',
                'status' => 'required|in:available,low,out',
            ]);

            // Create inventory in Atiqah's database
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
            // Get inventory from Atiqah's database with relationships
            $inventory = Inventory::with(['category', 'slot.section'])->findOrFail($id);

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
                'slotID' => $inventory->slotID,
                'slot_name' => $inventory->slot->name ?? null,
                'slot_section' => $inventory->slot->section->name ?? null,
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

            // Update inventory in Atiqah's database
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
            // Delete inventory from Atiqah's database
            $inventory = Inventory::findOrFail($id);
            $inventory->delete();

            return redirect()->back()->with('success', 'Inventory deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete inventory: ' . $e->getMessage());
        }
    }
}
