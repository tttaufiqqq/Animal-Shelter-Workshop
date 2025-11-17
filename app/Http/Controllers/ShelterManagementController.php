<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slot;

class ShelterManagementController extends Controller
{
    public function home(){
        return view('shelter-management.main');
    }
    public function indexSlot()
    {
        $slots = Slot::with(['animals', 'inventories'])->get();
        
        return view('shelter-management.index', compact('slots'));
    }
     public function storeSlot(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'section' => 'required|string|max:255',
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
                'section' => $slot->section,
                'capacity' => $slot->capacity,
                'status' => $slot->status,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Slot not found'
            ], 404);
        }
    }

    /**
     * Update an existing slot
     */
    public function updateSlot(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'section' => 'required|string|max:255',
                'capacity' => 'required|integer|min:1',
                'status' => 'required|in:available,occupied,maintenance',
            ]);

            $slot = Slot::findOrFail($id);
            $slot->update($validated);

            return redirect()->back()->with('success', 'Slot updated successfully!');
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update slot: ' . $e->getMessage());
        }
    }

    /**
     * Delete a slot
     */
    public function deleteSlot($id)
    {
        try {
            $slot = Slot::findOrFail($id);
            
            // Check if slot has animals
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
}
