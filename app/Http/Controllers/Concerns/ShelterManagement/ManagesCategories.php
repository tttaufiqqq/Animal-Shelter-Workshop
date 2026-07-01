<?php

namespace App\Http\Controllers\Concerns\ShelterManagement;

use App\Models\Category;
use Illuminate\Http\Request;

trait ManagesCategories
{
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
            }

            return redirect()->back()->withInput()->with('error', $result['message']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Error creating category: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', 'Failed to create category: ' . $e->getMessage());
        }
    }

    public function editCategory($id)
    {
        try {
            $data = $this->safeQuery(function() use ($id) {
                $category = Category::findOrFail($id);
                return ['id' => $category->id, 'main' => $category->main, 'sub' => $category->sub];
            }, null, 'shelter');

            if ($data === null) {
                \Log::error('Failed to load category for editing', ['category_id' => $id, 'database' => 'shelter']);
                return response()->json(['error' => 'Failed to load category data', 'message' => 'Database connection unavailable or category not found'], 500);
            }

            return response()->json($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning('Category not found for editing', ['category_id' => $id]);
            return response()->json(['error' => 'Category not found', 'message' => 'The requested category does not exist'], 404);
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
            }

            return redirect()->back()->withInput()->with('error', $result['message']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Error updating category: ' . $e->getMessage(), ['category_id' => $id, 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', 'Failed to update category: ' . $e->getMessage());
        }
    }

    public function deleteCategory($id)
    {
        try {
            $result = $this->atiqahService->deleteCategory($id);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            }

            return redirect()->back()->with('error', $result['message']);

        } catch (\Exception $e) {
            \Log::error('Error deleting category: ' . $e->getMessage(), ['category_id' => $id, 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to delete category: ' . $e->getMessage());
        }
    }
}
