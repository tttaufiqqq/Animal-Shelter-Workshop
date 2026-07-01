<?php

namespace App\Http\Controllers\Concerns\AnimalManagement;

use App\Models\Clinic;
use App\Models\Vet;
use Illuminate\Http\Request;

trait ManagesClinicsVets
{
    public function indexClinic()
    {
        $clinics = $this->safeQuery(fn() => Clinic::all(), collect([]), 'animals');
        $vets = $this->safeQuery(fn() => Vet::with('clinic')->get(), collect([]), 'animals');

        return view('animal-management.main-manage-cv', ['clinics' => $clinics, 'vets' => $vets]);
    }

    public function storeClinic(Request $request)
    {
        set_time_limit(60);

        try {
            $validated = $request->validate([
                'clinic_name' => 'required|string|max:255',
                'address' => 'required|string|max:500',
                'phone' => 'required|string|max:20',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);

            $result = $this->procedureService->createClinic([
                'name' => $validated['clinic_name'],
                'address' => $validated['address'],
                'contactNum' => $validated['phone'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);

            if ($result['success']) return redirect()->back()->with('success', $result['message']);
            return redirect()->back()->withInput()->with('error', $result['message']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to add clinic: ' . $e->getMessage());
        }
    }

    public function editClinic($id)
    {
        try {
            $clinic = Clinic::findOrFail($id);
            return response()->json($clinic);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Clinic not found: ' . $e->getMessage(), ['clinic_id' => $id]);
            return response()->json(['error' => 'Clinic not found', 'message' => 'The requested clinic does not exist or database connection is unavailable.'], 404);
        } catch (\Exception $e) {
            \Log::error('Error fetching clinic: ' . $e->getMessage(), ['clinic_id' => $id, 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Failed to fetch clinic', 'message' => $e->getMessage()], 500);
        }
    }

    public function updateClinic(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'required|string',
                'contactNum' => 'required|string|max:20',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            $result = $this->procedureService->updateClinic($id, [
                'name' => $request->name,
                'address' => $request->address,
                'contactNum' => $request->contactNum,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            if ($result['success']) return redirect()->back()->with('success', $result['message']);
            return redirect()->back()->withInput()->with('error', $result['message']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Error updating clinic: ' . $e->getMessage(), ['clinic_id' => $id, 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', 'Failed to update clinic: ' . $e->getMessage());
        }
    }

    public function destroyClinic($id)
    {
        try {
            $result = $this->procedureService->deleteClinic($id);
            if ($result['success']) return redirect()->back()->with('success', $result['message']);
            return redirect()->back()->with('error', $result['message']);
        } catch (\Exception $e) {
            \Log::error('Error deleting clinic: ' . $e->getMessage(), ['clinic_id' => $id, 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to delete clinic: ' . $e->getMessage());
        }
    }

    public function storeVet(Request $request)
    {
        set_time_limit(60);
        \Log::info('storeVet: Controller method reached', ['request_data' => $request->except(['_token']), 'session_id' => session()->getId()]);

        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'specialization' => 'required|string|max:255',
                'license_no' => 'required|string|max:50',
                'clinicID' => 'nullable|exists:animals.clinic,id',
                'phone' => 'required|string|max:20',
                'email' => 'required|email|max:255',
            ]);

            $result = $this->procedureService->createVet([
                'name' => $validated['full_name'],
                'specialization' => $validated['specialization'],
                'license_no' => $validated['license_no'],
                'clinicID' => $validated['clinicID'] ?? null,
                'contactNum' => $validated['phone'],
                'email' => $validated['email'],
            ]);

            if ($result['success']) return redirect()->back()->with('success', $result['message']);
            return redirect()->back()->withInput()->with('error', $result['message']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('storeVet: Exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', 'Failed to add veterinarian: ' . $e->getMessage());
        }
    }

    public function editVet($id)
    {
        try {
            $vet = Vet::findOrFail($id);
            return response()->json(['id' => $vet->id, 'name' => $vet->name, 'specialization' => $vet->specialization, 'license_no' => $vet->license_no, 'clinicID' => $vet->clinicID, 'contactNum' => $vet->contactNum, 'email' => $vet->email]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Veterinarian not found', 'message' => $e->getMessage()], 404);
        }
    }

    public function updateVet(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'specialization' => 'required|string|max:255',
                'license_no' => 'required|string|max:50',
                'clinicID' => 'required|exists:animals.clinic,id',
                'contactNum' => 'required|string|max:20',
                'email' => 'required|email|max:255',
            ]);

            $result = $this->procedureService->updateVet($id, [
                'name' => $request->name,
                'specialization' => $request->specialization,
                'license_no' => $request->license_no,
                'clinicID' => $request->clinicID,
                'contactNum' => $request->contactNum,
                'email' => $request->email,
            ]);

            if ($result['success']) return redirect()->back()->with('success', $result['message']);
            return redirect()->back()->withInput()->with('error', $result['message']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Error updating veterinarian: ' . $e->getMessage(), ['vet_id' => $id, 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', 'Failed to update veterinarian: ' . $e->getMessage());
        }
    }

    public function destroyVet($id)
    {
        try {
            $result = $this->procedureService->deleteVet($id);
            if ($result['success']) return redirect()->back()->with('success', $result['message']);
            return redirect()->back()->with('error', $result['message']);
        } catch (\Exception $e) {
            \Log::error('Error deleting veterinarian: ' . $e->getMessage(), ['vet_id' => $id, 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to delete veterinarian: ' . $e->getMessage());
        }
    }
}
