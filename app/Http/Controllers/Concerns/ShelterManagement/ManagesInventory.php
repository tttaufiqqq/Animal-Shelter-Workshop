<?php

namespace App\Http\Controllers\Concerns\ShelterManagement;

use App\Models\Inventory;
use Illuminate\Http\Request;

trait ManagesInventory
{
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
            }

            return redirect()->back()->withInput()->with('error', $result['message']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Failed to create inventory: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'input' => $request->all()]);
            return redirect()->back()->withInput()->with('error', 'Failed to add inventory: ' . $e->getMessage());
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
            }

            return redirect()->back()->withInput()->with('error', $result['message']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Failed to update inventory: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'input' => $request->all(), 'inventory_id' => $id]);
            return redirect()->back()->withInput()->with('error', 'Failed to update inventory: ' . $e->getMessage());
        }
    }

    public function deleteInventory($id)
    {
        try {
            $result = $this->atiqahService->deleteInventory($id);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            }

            return redirect()->back()->with('error', $result['message']);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete inventory: ' . $e->getMessage());
        }
    }
}
