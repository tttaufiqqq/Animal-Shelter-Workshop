<?php

namespace App\Http\Controllers\Concerns\StrayReporting;

use App\Models\Report;
use App\Models\AdopterProfile;
use App\Models\AnimalProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

trait ManagesReports
{
    public function indexUser()
    {
        $userReports = $this->safeQuery(
            fn() => Report::where('userID', auth()->id())->with(['images'])->orderBy('created_at', 'desc')->paginate(50),
            new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50),
            'reporting'
        );

        $adopterProfile = $this->safeQuery(
            fn() => AdopterProfile::where('adopterID', auth()->id())->first(),
            null,
            'users'
        );

        if (!$adopterProfile) {
            $matches = collect();
        } else {
            $matches = $this->safeQuery(
                fn() => AnimalProfile::with('animal')
                    ->when($adopterProfile->preferred_species, fn($q) =>
                        $q->whereHas('animal', fn($a) => $a->where('species', $adopterProfile->preferred_species))
                    )
                    ->when($adopterProfile->preferred_size, fn($q) => $q->where('size', $adopterProfile->preferred_size))
                    ->get(),
                collect([]),
                'animals'
            );
        }

        return view('welcome', compact('userReports', 'adopterProfile', 'matches'));
    }

    public function store(Request $request)
    {
        $uploadedFiles = [];

        try {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'description' => 'required|string|max:500',
                'additional_notes' => 'nullable|string|max:1000',
                'images' => 'required|array|min:1|max:5',
                'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            ], [
                'latitude.required' => 'Please select a location on the map.',
                'longitude.required' => 'Please select a location on the map.',
                'images.required' => 'Please upload at least one image.',
                'images.min' => 'Please upload at least one image.',
                'images.*.max' => 'Each image must not exceed 5MB.',
                'description.required' => 'Please select a situation/urgency level.',
            ]);

            if ($validator->fails()) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Please check the form and try again.', 'errors' => $validator->errors()], 422);
                }
                return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Please check the form and try again.');
            }

            $validated = $validator->validated();

            $reportResult = $this->procedureService->createReport([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'description' => $validated['description'],
                'additional_notes' => $validated['additional_notes'] ?? null,
                'report_status' => Report::STATUS_PENDING,
            ]);

            if (!$reportResult['success']) {
                throw new \Exception($reportResult['message']);
            }

            $reportId = $reportResult['report_id'];

            if ($request->hasFile('images')) {
                $imageIndex = 1;
                foreach ($request->file('images') as $image) {
                    $sanitizedCity = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $validated['city']));
                    $sanitizedState = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $validated['state']));
                    $filename = "report_{$reportId}_{$sanitizedCity}_{$sanitizedState}_{$imageIndex}";

                    $uploadResult = cloudinary()->uploadApi()->upload($image->getRealPath(), [
                        'folder' => 'reports',
                        'public_id' => $filename,
                    ]);
                    $path = $uploadResult['public_id'];
                    $uploadedFiles[] = $path;

                    $imageResult = $this->procedureService->createImage([
                        'image_path' => $path,
                        'reportID' => $reportId,
                        'animalID' => null,
                        'clinicID' => null,
                    ]);

                    if (!$imageResult['success']) {
                        throw new \Exception($imageResult['message']);
                    }

                    $imageIndex++;
                }
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Report submitted successfully! Our team will review it shortly.']);
            }

            return redirect()->back()->with('success', 'Report submitted successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            foreach ($uploadedFiles as $filePath) {
                cloudinary()->uploadApi()->destroy($filePath);
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Please check the form and try again.', 'errors' => $e->errors()], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput()->with('error', 'Please check the form and try again.');

        } catch (\Exception $e) {
            foreach ($uploadedFiles as $filePath) {
                cloudinary()->uploadApi()->destroy($filePath);
            }

            \Log::error('Error submitting stray report: ' . $e->getMessage(), ['user_id' => Auth::id(), 'trace' => $e->getTraceAsString()]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to submit report: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->withInput()->with('error', 'Failed to submit report: ' . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        return view('stray-reporting.index');
    }

    public function show($id)
    {
        $report = $this->safeQuery(
            fn() => Report::with(['images', 'rescue.caretaker'])->findOrFail($id),
            null,
            'reporting'
        );

        if (!$report) {
            return redirect()->route('reports.index')->with('error', 'Report not found or database connection unavailable.');
        }

        $caretakers = $this->safeQuery(
            fn() => \App\Models\User::role('caretaker')->orderBy('name')->get(),
            collect([]),
            'users'
        );

        return view('stray-reporting.show', compact('report', 'caretakers'));
    }

    public function adminIndex()
    {
        $reports = $this->safeQuery(
            fn() => Report::with(['images', 'user'])->orderBy('created_at', 'desc')->paginate(50),
            new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50),
            'reporting'
        );

        return view('stray-reporting.admin-index', compact('reports'));
    }

    public function destroy($id)
    {
        try {
            $images = $this->procedureService->readImagesByReport($id);
            $deleteResult = $this->procedureService->deleteReport($id);

            if ($deleteResult['has_rescue']) {
                return redirect()->back()->with('error', 'Cannot delete report with associated rescue. Please delete the rescue first.');
            }

            if (!$deleteResult['success']) {
                throw new \Exception($deleteResult['message']);
            }

            foreach ($images as $image) {
                try {
                    cloudinary()->uploadApi()->destroy($image->image_path);
                } catch (\Exception $e) {
                    // Continue even if Cloudinary deletion fails
                }
            }

            return redirect()->route('reports.index')->with('success', 'Report deleted successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Report not found for deletion: ' . $e->getMessage(), ['report_id' => $id]);
            return redirect()->back()->with('error', 'Report not found or database connection unavailable.');
        } catch (\Exception $e) {
            \Log::error('Error deleting report: ' . $e->getMessage(), ['report_id' => $id, 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to delete report: ' . $e->getMessage());
        }
    }
}
