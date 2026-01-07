<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slot;
use App\Models\Inventory;
use App\Models\Animal;
use App\Models\Section;
use App\Models\Category;
use App\DatabaseErrorHandler;
use App\Services\AtiqahProcedureService;

class ShelterManagementController extends Controller
{
    use DatabaseErrorHandler;

    protected $atiqahService;

    public function __construct(AtiqahProcedureService $atiqahService)
    {
        $this->atiqahService = $atiqahService;
    }
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

            // Build query with conditional eager loading based on database availability
            $query = Slot::query();

            // Always load same-database relationships
            $with = ['section', 'inventories'];

            $slots = $query->with($with)
                ->orderBy('sectionID')
                ->orderBy('name')
                ->paginate(100);

            // Manually load animals for each slot to avoid cross-database JOIN
            if ($this->isDatabaseAvailable('shafiqah')) {
                $slotIds = $slots->pluck('id')->toArray();
                $animals = Animal::whereIn('slotID', $slotIds)->get()->groupBy('slotID');

                $slots->each(function($slot) use ($animals) {
                    $slotAnimals = $animals->get($slot->id, collect([]));
                    $slot->setRelation('animals', $slotAnimals);
                });
            } else {
                // Set empty animals collection if shafiqah is offline
                $slots->each(function($slot) {
                    $slot->setRelation('animals', collect([]));
                });
            }

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
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
            ]);

            $result = $this->atiqahService->createSection($validated);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message']);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Error creating section: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create section: ' . $e->getMessage());
        }
    }

    public function editSection($id)
    {
        try {
            // Use safeQuery to handle database failures gracefully
            $data = $this->safeQuery(function() use ($id) {
                $section = Section::findOrFail($id);
                return [
                    'id' => $section->id,
                    'name' => $section->name,
                    'description' => $section->description,
                ];
            }, null, 'atiqah');

            if ($data === null) {
                \Log::error('Failed to load section for editing', [
                    'section_id' => $id,
                    'database' => 'atiqah'
                ]);

                return response()->json([
                    'error' => 'Failed to load section data',
                    'message' => 'Database connection unavailable or section not found'
                ], 500);
            }

            return response()->json($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning('Section not found for editing', ['section_id' => $id]);
            return response()->json([
                'error' => 'Section not found',
                'message' => 'The requested section does not exist'
            ], 404);
        }
    }

    public function updateSection(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
            ]);

            $result = $this->atiqahService->updateSection($id, $validated);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message']);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Error updating section: ' . $e->getMessage(), [
                'section_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update section: ' . $e->getMessage());
        }
    }

    public function deleteSection($id)
    {
        try {
            $result = $this->atiqahService->deleteSection($id);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            \Log::error('Error deleting section: ' . $e->getMessage(), [
                'section_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to delete section: ' . $e->getMessage());
        }
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
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message']);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Failed to create slot: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add slot: ' . $e->getMessage());
        }
    }

    public function editSlot($id)
    {
        try {
            // Use safeQuery to handle database failures gracefully
            $data = $this->safeQuery(function() use ($id) {
                $slot = Slot::findOrFail($id);

                return [
                    'id' => $slot->id,
                    'name' => $slot->name,
                    'sectionID' => $slot->sectionID,
                    'capacity' => $slot->capacity,
                    'status' => $slot->status,
                ];
            }, null, 'atiqah');

            if ($data === null) {
                \Log::error('Failed to load slot for editing', [
                    'slot_id' => $id,
                    'database' => 'atiqah'
                ]);

                return response()->json([
                    'error' => 'Failed to load slot data',
                    'message' => 'Database connection unavailable or slot not found'
                ], 500);
            }

            return response()->json($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning('Slot not found for editing', ['slot_id' => $id]);
            return response()->json([
                'error' => 'Slot not found',
                'message' => 'The requested slot does not exist'
            ], 404);
        }
    }

    public function updateSlot(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'sectionID' => 'required|integer',
                'capacity' => 'required|integer|min:1',
                'status' => 'nullable|in:available,occupied,maintenance', // Make status optional for auto-calculation
            ]);

            // Auto-calculate status based on actual occupancy (unless manually set to 'maintenance')
            $statusToSet = null;
            if (isset($validated['status']) && $validated['status'] === 'maintenance') {
                // Allow manual override for maintenance status only
                $statusToSet = 'maintenance';
            } else {
                // Auto-calculate status based on animal count
                // Count animals in this slot (cross-database query to shafiqah)
                $animalCount = 0;
                if ($this->isDatabaseAvailable('shafiqah')) {
                    $animalCount = Animal::where('slotID', $id)->count();
                }

                // Set status based on occupancy
                if ($animalCount >= $validated['capacity']) {
                    $statusToSet = 'occupied';
                } else {
                    $statusToSet = 'available';
                }
            }

            // Update slot using procedure
            $validated['status'] = $statusToSet;
            $result = $this->atiqahService->updateSlot($id, $validated);

            if ($result['success']) {
                \Log::info('Slot updated successfully via procedure', [
                    'slot_id' => $id,
                    'name' => $validated['name'],
                    'sectionID' => $validated['sectionID'],
                    'capacity' => $validated['capacity'],
                    'status' => $statusToSet,
                ]);
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message']);
            }

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
            \Log::info('Attempting to delete slot', ['slot_id' => $id]);

            // Read slot name for error message (use procedure)
            $slot = $this->atiqahService->readSlot($id);

            if (!$slot) {
                \Log::error('Slot not found for deletion', ['slot_id' => $id]);
                return redirect()->back()->with('error', 'Slot not found or already deleted.');
            }

            \Log::info('Slot found', [
                'slot_id' => $id,
                'slot_name' => $slot->name,
            ]);

            // Check if slot has animals (cross-database check - must be done at application layer)
            $animalCount = 0;
            if ($this->isDatabaseAvailable('shafiqah')) {
                try {
                    // Count animals in slot directly from shafiqah database (avoid cross-database JOIN)
                    $animalCount = Animal::where('slotID', $id)->count();
                    \Log::info('Animal count for slot', [
                        'slot_id' => $id,
                        'animal_count' => $animalCount
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('Failed to count animals for slot deletion', [
                        'slot_id' => $id,
                        'error' => $e->getMessage()
                    ]);
                    // If we can't check animals, allow deletion with warning
                    \Log::warning('Proceeding with deletion despite animal count error');
                }
            }

            if ($animalCount > 0) {
                \Log::warning('Cannot delete slot - has animals', [
                    'slot_id' => $id,
                    'animal_count' => $animalCount
                ]);
                return redirect()->back()
                    ->with('error', "Cannot delete slot '{$slot->name}' - it has {$animalCount} animal(s). Please relocate them first.");
            }

            // Delete using procedure (will also check for inventory items)
            $result = $this->atiqahService->deleteSlot($id);

            if ($result['success']) {
                \Log::info('Slot deleted successfully', ['slot_id' => $id]);
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            \Log::error('Failed to delete slot', [
                'slot_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', 'Failed to delete slot: ' . $e->getMessage());
        }
    }

    // CATEGORY METHODS
    public function storeCategory(Request $request)
    {
        try {
            $validated = $request->validate([
                'main' => 'required|string|max:255',
                'sub' => 'required|string|max:255',
            ]);

            $result = $this->atiqahService->createCategory($validated);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message']);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Error creating category: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create category: ' . $e->getMessage());
        }
    }

    public function editCategory($id)
    {
        try {
            // Use safeQuery to handle database failures gracefully
            $data = $this->safeQuery(function() use ($id) {
                $category = Category::findOrFail($id);
                return [
                    'id' => $category->id,
                    'main' => $category->main,
                    'sub' => $category->sub,
                ];
            }, null, 'atiqah');

            if ($data === null) {
                \Log::error('Failed to load category for editing', [
                    'category_id' => $id,
                    'database' => 'atiqah'
                ]);

                return response()->json([
                    'error' => 'Failed to load category data',
                    'message' => 'Database connection unavailable or category not found'
                ], 500);
            }

            return response()->json($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning('Category not found for editing', ['category_id' => $id]);
            return response()->json([
                'error' => 'Category not found',
                'message' => 'The requested category does not exist'
            ], 404);
        }
    }

    public function updateCategory(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'main' => 'required|string|max:255',
                'sub' => 'required|string|max:255',
            ]);

            $result = $this->atiqahService->updateCategory($id, $validated);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message']);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Error updating category: ' . $e->getMessage(), [
                'category_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update category: ' . $e->getMessage());
        }
    }

    public function deleteCategory($id)
    {
        try {
            $result = $this->atiqahService->deleteCategory($id);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            \Log::error('Error deleting category: ' . $e->getMessage(), [
                'category_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to delete category: ' . $e->getMessage());
        }
    }

    public function getAnimalDetails($id)
    {
        $data = $this->safeQuery(function() use ($id) {
            $animal = Animal::findOrFail($id);

            $medicals = $animal->medicals()->with('vet')->get();
            $vaccinations = $animal->vaccinations()->with('vet')->get();

            // Only load images if eilya database is online (cross-database relationship)
            $images = collect([]);
            if ($this->isDatabaseAvailable('eilya')) {
                try {
                    $images = $animal->images()->get();
                } catch (\Exception $e) {
                    \Log::warning('Failed to load animal images from eilya database: ' . $e->getMessage());
                }
            }

            return [
                'id' => $animal->id,
                'name' => $animal->name ?? 'Unknown',
                'species' => $animal->species ?? 'Unknown',
                'adoption_status' => $animal->adoption_status ?? 'unknown',
                'health_details' => $animal->health_details ?? 'No health details available.',
                'images' => $images->map(function($image) {
                    return [
                        'id' => $image->id,
                        'url' => $image->url,  // Use the computed Cloudinary URL attribute
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
        // Use safeQuery to handle database failures gracefully
        $data = $this->safeQuery(function() use ($id) {
            // Build with array based on database availability
            $with = ['section', 'inventories.category'];

            $slot = Slot::with($with)->findOrFail($id);

            // Manually load animals to avoid cross-database JOIN
            if ($this->isDatabaseAvailable('shafiqah')) {
                $animals = Animal::where('slotID', $slot->id)
                    ->with(['vaccinations', 'medicals'])
                    ->get();
                $slot->setRelation('animals', $animals);
            } else {
                $slot->setRelation('animals', collect([]));
            }

            // Prepare animals data (empty if shafiqah is offline)
            $animalsData = [];
            if ($this->isDatabaseAvailable('shafiqah') && $slot->relationLoaded('animals')) {
                $animalsData = $slot->animals->map(function($animal) {
                    return [
                        'id' => $animal->id,
                        'name' => $animal->name,
                        'species' => $animal->species,
                        'age' => $animal->age,
                        'gender' => $animal->gender,
                        'adoption_status' => $animal->adoption_status,
                        'health_details' => $animal->health_details,
                        'vaccinations_count' => $animal->relationLoaded('vaccinations') ? $animal->vaccinations->count() : 0,
                        'medicals_count' => $animal->relationLoaded('medicals') ? $animal->medicals->count() : 0,
                    ];
                });
            }

            return [
                'id' => $slot->id,
                'name' => $slot->name,
                'section' => $slot->section ? [
                    'id' => $slot->section->id,
                    'name' => $slot->section->name,
                    'description' => $slot->section->description,
                ] : null,
                'capacity' => $slot->capacity,
                'status' => $slot->status,
                'animals' => $animalsData,
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
            ];
        }, null, 'atiqah');

        if ($data === null) {
            \Log::error('Slot details fetch failed - database unavailable', ['slot_id' => $id]);
            return response()->json([
                'error' => 'Slot not found',
                'message' => 'Database connection unavailable or slot not found'
            ], 404);
        }

        return response()->json($data);
    }

    public function storeInventory(Request $request)
    {
        try {
            $validated = $request->validate([
                'slotID' => 'required|integer',
                'item_name' => 'required|string|max:255',
                'categoryID' => 'required|integer',
                'quantity' => 'required|integer|min:0',
                'weight' => 'nullable|numeric|min:0',
                'brand' => 'nullable|string|max:255',
                'status' => 'required|in:available,low,out',
            ]);

            $result = $this->atiqahService->createInventory($validated);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message']);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Failed to create inventory: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);

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
                'categoryID' => 'required|integer',
                'quantity' => 'required|integer|min:0',
                'weight' => 'nullable|numeric|min:0',
                'brand' => 'nullable|string|max:255',
                'status' => 'required|in:available,low,out',
            ]);

            $result = $this->atiqahService->updateInventory($id, $validated);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message']);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Failed to update inventory: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'input' => $request->all(),
                'inventory_id' => $id
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update inventory: ' . $e->getMessage());
        }
    }

    public function deleteInventory($id)
    {
        try {
            $result = $this->atiqahService->deleteInventory($id);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete inventory: ' . $e->getMessage());
        }
    }
}
