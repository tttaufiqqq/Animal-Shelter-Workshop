<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slot;
use App\Models\Category;
use App\Models\Inventory;

class ShelterManagementController extends Controller
{
    public function home()
    {
        return view('shelter-management.main');
    }

    public function create()
    {
        $slots = Slot::all();
        $categories = Category::all();

        return view('inventory.create', compact('slots', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name'   => 'required|string|max:255',
            'quantity'    => 'required|integer|min:1',
            'brand'       => 'nullable|string|max:255',
            'weight'      => 'nullable|string|max:50',
            'status'      => 'required|string|max:50',
            'slotID'      => 'required|exists:slots,id',
            'categoryID'  => 'required|exists:categories,id',
        ]);

        Inventory::create($validated);

        return redirect()->route('inventory.create')
                         ->with('success', 'Inventory item added successfully!');
    }
}

