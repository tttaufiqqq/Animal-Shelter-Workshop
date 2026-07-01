<?php

namespace App\Http\Controllers\Concerns\AnimalManagement;

use Illuminate\Http\Request;

trait ManagesMedicalRecords
{
    public function storeMedical(Request $request)
    {
        set_time_limit(60);
        \Log::info('storeMedical: Controller method reached', ['animalID' => $request->input('animalID'), 'has_csrf_token' => $request->has('_token'), 'session_id' => session()->getId()]);

        try {
            $validated = $request->validate([
                'animalID' => 'required|exists:animals.animal,id',
                'treatment_type' => 'required|string|max:255',
                'diagnosis' => 'required|string',
                'action' => 'required|string',
                'remarks' => 'nullable|string',
                'vetID' => 'required|exists:animals.vet,id',
                'costs' => 'nullable|numeric|min:0',
            ]);

            $result = $this->procedureService->createMedical($validated);

            if ($result['success']) return redirect()->back()->with('success', $result['message']);
            return redirect()->back()->withInput()->with('error', $result['message']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to add medical record: ' . $e->getMessage());
        }
    }

    public function storeVaccination(Request $request)
    {
        set_time_limit(60);

        try {
            $validated = $request->validate([
                'animalID' => 'required|exists:animals.animal,id',
                'name' => 'required|string|max:255',
                'type' => 'required|string|max:255',
                'next_due_date' => 'nullable|date|after:today',
                'remarks' => 'nullable|string',
                'vetID' => 'required|exists:animals.vet,id',
                'costs' => 'nullable|numeric|min:0',
            ]);

            $result = $this->procedureService->createVaccination($validated);

            if ($result['success']) return redirect()->back()->with('success', $result['message']);
            return redirect()->back()->withInput()->with('error', $result['message']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to add vaccination record: ' . $e->getMessage());
        }
    }
}
