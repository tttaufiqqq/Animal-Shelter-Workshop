<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Image;
use App\Models\AdopterProfile;
use App\Models\AnimalProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Rescue;
use App\Models\User;
use App\Models\Animal;
use App\DatabaseErrorHandler;

class StrayReportingManagementController extends Controller
{
    use DatabaseErrorHandler;
    public function home()
    {
        return view('stray-reporting.main');
    }

    public function indexUser()
    {
        $userReports = $this->safeQuery(
            fn() => Report::where('userID', auth()->id())
                ->with(['images'])
                ->orderBy('created_at', 'desc')
                ->paginate(50),
            new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50),
            'eilya' // Pre-check eilya database
        );

        $adopterProfile = $this->safeQuery(
            fn() => AdopterProfile::where('adopterID', auth()->id())->first(),
            null,
            'taufiq' // Pre-check taufiq database
        );

        if (!$adopterProfile) {
            // User has not completed adopter profile → show home but no matches
            $matches = collect();
        } else {
            $matches = $this->safeQuery(
                fn() => AnimalProfile::with('animal')
                    ->when($adopterProfile->preferred_species, function ($q) use ($adopterProfile) {
                        $q->whereHas('animal', fn($a) =>
                            $a->where('species', $adopterProfile->preferred_species)
                        );
                    })
                    ->when($adopterProfile->preferred_size, function ($q) use ($adopterProfile) {
                        $q->where('size', $adopterProfile->preferred_size);
                    })
                    ->get(),
                collect([]),
                'shafiqah' // Pre-check shafiqah database
            );
        }

        return view('welcome', compact('userReports', 'adopterProfile', 'matches'));
    }


    public function store(Request $request)
    {
        // Start transactions on eilya database (Report and Image tables)
        DB::connection('eilya')->beginTransaction();
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
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Please check the form and try again.');
            }

            $validated = $validator->validated();

            // Combine description and additional notes
            $fullDescription = $validated['description'];
            if (!empty($validated['additional_notes'])) {
                $fullDescription .= "\n\nAdditional Notes: " . $validated['additional_notes'];
            }

            $report = Report::create([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'report_status' => 'Pending',
                'description' => $fullDescription,
                'userID' => Auth::id(),
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('reports', 'public');
                    $uploadedFiles[] = $path;

                    Image::create([
                        'image_path' => $path,
                        'reportID' => $report->id,
                        'animalID' => null,
                    ]);
                }
            }

            DB::connection('eilya')->commit();

            return redirect()->back()->with('success', 'Report submitted successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::connection('eilya')->rollBack();

            // Clean up uploaded files
            foreach ($uploadedFiles as $filePath) {
                Storage::disk('public')->delete($filePath);
            }

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            DB::connection('eilya')->rollBack();

            // Clean up uploaded files
            foreach ($uploadedFiles as $filePath) {
                Storage::disk('public')->delete($filePath);
            }

            \Log::error('Error submitting stray report: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to submit report: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $reports = $this->safeQuery(
            fn() => Report::with('images')
                ->orderBy('created_at', 'desc')
                ->paginate(50),
            new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50),
            'eilya' // Pre-check eilya database
        );

        return view('stray-reporting.index', compact('reports'));
    }

    public function show($id)
    {
        $report = $this->safeQuery(
            fn() => Report::with(['images', 'rescue.caretaker'])->findOrFail($id),
            null,
            'eilya' // Pre-check eilya database
        );

        if (!$report) {
            return redirect()->route('stray-reporting.index')
                ->with('error', 'Report not found or database connection unavailable.');
        }

        $caretakers = $this->safeQuery(
            fn() => User::role('caretaker')->orderBy('name')->get(),
            collect([]),
            'taufiq' // Pre-check taufiq database
        );

        return view('stray-reporting.show', compact('report', 'caretakers'));
    }

    public function adminIndex()
    {
        $reports = $this->safeQuery(
            fn() => Report::with(['images', 'user'])
                ->orderBy('created_at', 'desc')
                ->paginate(50),
            new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50),
            'eilya' // Pre-check eilya database
        );

        return view('stray-reporting.admin-index', compact('reports'));
    }

    public function destroy($id)
    {
        // Start transaction on eilya database (Report and Image tables)
        DB::connection('eilya')->beginTransaction();

        try {
            $report = Report::with('images')->findOrFail($id);

            // Check if report has an associated rescue
            if ($report->rescue) {
                return redirect()->back()->with('error', 'Cannot delete report with associated rescue. Please delete the rescue first.');
            }

            foreach ($report->images as $image) {
                if (Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
                $image->delete();
            }

            $report->delete();

            DB::connection('eilya')->commit();

            return redirect()->route('stray-reporting.index')->with('success', 'Report deleted successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::connection('eilya')->rollBack();

            \Log::error('Report not found for deletion: ' . $e->getMessage(), ['report_id' => $id]);
            return redirect()->back()->with('error', 'Report not found or database connection unavailable.');
        } catch (\Exception $e) {
            DB::connection('eilya')->rollBack();

            \Log::error('Error deleting report: ' . $e->getMessage(), [
                'report_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to delete report: ' . $e->getMessage());
        }
    }

    public function assignCaretaker(Request $request, $id)
    {
        // Start transaction on eilya database (Report and Rescue tables)
        DB::connection('eilya')->beginTransaction();

        try {
            $request->validate([
                'caretaker_id' => 'required|exists:taufiq.users,id'  // Cross-database: User on taufiq
            ]);

            $report = Report::findOrFail($id);

            // Verify caretaker exists in taufiq database
            $caretaker = $this->safeQuery(
                fn() => User::findOrFail($request->caretaker_id),
                null,
                'taufiq'
            );

            if (!$caretaker) {
                return redirect()->back()->with('error', 'Selected caretaker not found or database connection unavailable.');
            }

            $rescue = Rescue::where('reportID', $report->id)->first();

            if ($rescue) {
                $rescue->update([
                    'caretakerID' => $request->caretaker_id
                ]);
            } else {
                Rescue::create([
                    'reportID' => $report->id,
                    'caretakerID' => $request->caretaker_id,
                    'status' => Rescue::STATUS_SCHEDULED,
                    'date' => null
                ]);
            }

            $report->update([
                'report_status' => 'In Progress'
            ]);

            DB::connection('eilya')->commit();

            return redirect()->back()->with('success', 'Caretaker assigned successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::connection('eilya')->rollBack();

            \Log::error('Report not found for caretaker assignment: ' . $e->getMessage(), ['report_id' => $id]);
            return redirect()->back()->with('error', 'Report not found or database connection unavailable.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::connection('eilya')->rollBack();

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            DB::connection('eilya')->rollBack();

            \Log::error('Error assigning caretaker: ' . $e->getMessage(), [
                'report_id' => $id,
                'caretaker_id' => $request->caretaker_id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to assign caretaker: ' . $e->getMessage());
        }
    }

    public function indexcaretaker(Request $request)
    {
        $rescues = $this->safeQuery(function() use ($request) {
            $query = Rescue::with(['report.images', 'caretaker'])
                ->where('caretakerID', Auth::id());

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            return $query->orderBy('created_at', 'desc')->paginate(50);
        }, new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50), 'eilya'); // Pre-check eilya database

        return view('stray-reporting.index-caretaker', compact('rescues'));
    }

    public function updateStatusCaretaker(Request $request, $id)
    {
        // Start transaction on eilya database (Rescue and Report tables)
        DB::connection('eilya')->beginTransaction();

        try {
            $rules = [
                'status' => 'required|in:' . implode(',', [
                    Rescue::STATUS_SCHEDULED,
                    Rescue::STATUS_IN_PROGRESS,
                    Rescue::STATUS_SUCCESS,
                    Rescue::STATUS_FAILED
                ])
            ];

            if (in_array($request->status, [Rescue::STATUS_SUCCESS, Rescue::STATUS_FAILED])) {
                $rules['remarks'] = 'required|string|min:10|max:1000';
            }

            $request->validate($rules, [
                'remarks.required' => 'Remarks are required for this status.',
                'remarks.min' => 'Remarks must be at least 10 characters.',
                'remarks.max' => 'Remarks must not exceed 1000 characters.',
            ]);

            $rescue = Rescue::where('id', $id)
                ->where('caretakerID', Auth::id())
                ->firstOrFail();

            $updateData = ['status' => $request->status];

            if ($request->filled('remarks')) {
                $updateData['remarks'] = $request->remarks;
            }

            $rescue->update($updateData);

            // Case-insensitive status comparison for cross-RDBMS compatibility
            $currentStatus = strtolower($request->status);
            $successStatus = strtolower(Rescue::STATUS_SUCCESS);
            $failedStatus = strtolower(Rescue::STATUS_FAILED);

            if ($currentStatus === $successStatus || $currentStatus === $failedStatus) {
                $rescue->report->update([
                    'report_status' => 'Resolved'
                ]);
            }

            DB::connection('eilya')->commit();

            if ($currentStatus === $successStatus) {
                return redirect()
                    ->route('animal-management.create', ['rescue_id' => $rescue->id])
                    ->with('success', 'Rescue completed! You can now add the animal.');
            }

            return redirect()->back()->with('success', 'Rescue status updated successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::connection('eilya')->rollBack();

            \Log::error('Rescue not found or unauthorized: ' . $e->getMessage(), [
                'rescue_id' => $id,
                'user_id' => Auth::id()
            ]);
            return redirect()->back()->with('error', 'Rescue not found or you are not authorized to update it.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::connection('eilya')->rollBack();

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            DB::connection('eilya')->rollBack();

            \Log::error('Error updating rescue status: ' . $e->getMessage(), [
                'rescue_id' => $id,
                'user_id' => Auth::id(),
                'status' => $request->status,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update rescue status: ' . $e->getMessage());
        }
    }

    public function showCaretaker($id)
    {
        try {
            $rescue = Rescue::with(['report.images', 'caretaker'])
                ->where('id', $id)
                ->where('caretakerID', Auth::id())
                ->firstOrFail();

            return view('stray-reporting.show-caretaker', compact('rescue'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Rescue not found or unauthorized for caretaker view: ' . $e->getMessage(), [
                'rescue_id' => $id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('stray-reporting.index-caretaker')
                ->with('error', 'Rescue not found or you are not authorized to view it.');
        } catch (\Exception $e) {
            \Log::error('Error viewing rescue: ' . $e->getMessage(), [
                'rescue_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('stray-reporting.index-caretaker')
                ->with('error', 'Failed to load rescue details: ' . $e->getMessage());
        }
    }
}
