<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ShelterManagementController as BaseShelterManagementController;

class ShelterManagementController extends BaseShelterManagementController
{
    /**
     * Display shelter management index for admin panel.
     * Uses the same logic as the base controller but returns admin view.
     */
    public function index()
    {
        // Get all data using parent controller's logic
        $result = $this->safeQuery(function() {
            $sections = \App\Models\Section::all();

            // Get counts BEFORE pagination
            $totalSlots = \App\Models\Slot::count();
            $availableSlots = \App\Models\Slot::where('status', 'available')->count();
            $occupiedSlots = \App\Models\Slot::where('status', 'occupied')->count();
            $maintenanceSlots = \App\Models\Slot::where('status', 'maintenance')->count();

            // Build query with conditional eager loading based on database availability
            $query = \App\Models\Slot::query();

            // Always load same-database relationships
            $with = ['section', 'inventories'];

            $slots = $query->with($with)
                ->orderBy('sectionID')
                ->orderBy('name')
                ->paginate(100);

            // Manually load animals for each slot to avoid cross-database JOIN
            if ($this->isDatabaseAvailable('shafiqah')) {
                $slotIds = $slots->pluck('id')->toArray();
                $animals = \App\Models\Animal::whereIn('slotID', $slotIds)->get()->groupBy('slotID');

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

            $categories = \App\Models\Category::all();

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

        // Return admin view instead of regular view
        return view('admin.shelter-management.index', $result);
    }
}
