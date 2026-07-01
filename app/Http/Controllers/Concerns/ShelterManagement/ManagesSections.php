<?php

namespace App\Http\Controllers\Concerns\ShelterManagement;

use App\Models\Section;
use Illuminate\Http\Request;

trait ManagesSections
{
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
            }

            return redirect()->back()->withInput()->with('error', $result['message']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Error creating section: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', 'Failed to create section: ' . $e->getMessage());
        }
    }

    public function editSection($id)
    {
        try {
            $data = $this->safeQuery(function() use ($id) {
                $section = Section::findOrFail($id);
                return ['id' => $section->id, 'name' => $section->name, 'description' => $section->description];
            }, null, 'shelter');

            if ($data === null) {
                \Log::error('Failed to load section for editing', ['section_id' => $id, 'database' => 'shelter']);
                return response()->json(['error' => 'Failed to load section data', 'message' => 'Database connection unavailable or section not found'], 500);
            }

            return response()->json($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning('Section not found for editing', ['section_id' => $id]);
            return response()->json(['error' => 'Section not found', 'message' => 'The requested section does not exist'], 404);
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
            }

            return redirect()->back()->withInput()->with('error', $result['message']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Error updating section: ' . $e->getMessage(), ['section_id' => $id, 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', 'Failed to update section: ' . $e->getMessage());
        }
    }

    public function deleteSection($id)
    {
        try {
            $result = $this->atiqahService->deleteSection($id);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            }

            return redirect()->back()->with('error', $result['message']);

        } catch (\Exception $e) {
            \Log::error('Error deleting section: ' . $e->getMessage(), ['section_id' => $id, 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to delete section: ' . $e->getMessage());
        }
    }
}
