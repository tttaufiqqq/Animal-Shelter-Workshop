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
use Illuminate\Support\Facades\Log;
use App\Models\Rescue;
use App\Models\User;
use App\Models\Animal;
use App\Services\ForeignKeyValidator;

class StrayReportingManagementController extends Controller
{
    public function home()
    {
        return view('stray-reporting.main');
    }

    /**
     * Show user's reports and animal matches
     * Reports from Eilya's database
     * AdopterProfile and matches from Taufiq and Shafiqah's databases
     */
    public function indexUser()
    {
        // Get user reports from Eilya's database
        $userReports = Report::where('userID', auth()->id())
            ->with(['images']) // Images also in Eilya's database
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Get adopter profile from Taufiq's database
        $adopterProfile = AdopterProfile::where('adopterID', auth()->id())->first();

        if (!$adopterProfile) {
            // User has not completed adopter profile → show home but no matches
            $matches = collect();
        } else {
            // Get matching animal profiles from Shafiqah's database
            $matches = AnimalProfile::with('animal')
                ->when($adopterProfile->preferred_species, function ($q) use ($adopterProfile) {
                    $q->whereHas('animal', fn($a) =>
                    $a->where('species', $adopterProfile->preferred_species)
                    );
                })
                ->when($adopterProfile->preferred_size, function ($q) use ($adopterProfile) {
                    $q->where('size', $adopterProfile->preferred_size);
                })
                ->get();
        }

        return view('welcome', compact('userReports', 'adopterProfile', 'matches'));
    }

    /**
     * Store a new stray report
     * Creates report in Eilya's database with images
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'description' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        }

        $validated = $validator->validated();

        // Use transaction for Eilya's database
        DB::connection('eilya')->beginTransaction();

        try {
            // Create report in Eilya's database
            $report = Report::create([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'report_status' => 'Pending',
                'description' => $validated['description'] ?? null,
                'userID' => Auth::id(), // Cross-database reference to Taufiq
            ]);

            // Upload images to Eilya's database
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('reports', 'public');

                    Image::create([
                        'image_path' => $path,
                        'reportID' => $report->id,
                        'animalID' => null,
                        'clinicID' => null,
                    ]);
                }
            }

            DB::connection('eilya')->commit();

            Log::info('Stray report created', [
                'report_id' => $report->id,
                'user_id' => Auth::id(),
                'location' => $validated['city'] . ', ' . $validated['state'],
            ]);

            return redirect()->back()->with('success', 'Report submitted successfully!');

        } catch (\Exception $e) {
            DB::connection('eilya')->rollBack();

            Log::error('Error creating stray report: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to submit report. Please try again.');
        }
    }

    /**
     * Display all reports (public view)
     * Reports from Eilya's database
     */
    public function index()
    {
        // Get reports from Eilya's database
        $reports = Report::with('images')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('stray-reporting.index', compact('reports'));
    }

    /**
     * Show single report details
     * Report from Eilya's database, caretakers from Taufiq's database
     */
    public function show($id)
    {
        // Get report from Eilya's database with cross-database relationships
        $report = Report::with([
            'images',              // Eilya -> Eilya
            'rescue.caretaker'     // Eilya -> Eilya -> Taufiq (cross-database)
        ])->findOrFail($id);

        // Get caretakers from Taufiq's database
        $caretakers = User::role('caretaker')->orderBy('name')->get();

        return view('stray-reporting.show', compact('report', 'caretakers'));
    }

    /**
     * Display all reports for admin
     * Reports from Eilya's database with cross-database user relationship
     */
    public function adminIndex()
    {
        // Get reports from Eilya's database with cross-database relationships
        $reports = Report::with([
            'images',  // Eilya -> Eilya
            'user'     // Eilya -> Taufiq (cross-database)
        ])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('stray-reporting.admin-index', compact('reports'));
    }

    /**
     * Delete a report
     * Deletes from Eilya's database
     */
    public function destroy($id)
    {
        // Use transaction for Eilya's database
        DB::connection('eilya')->beginTransaction();

        try {
            // Get report from Eilya's database
            $report = Report::with('images')->findOrFail($id);

            // Delete images from storage and database
            foreach ($report->images as $image) {
                if (Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
                $image->delete();
            }

            // Delete report from Eilya's database
            $report->delete();

            DB::connection('eilya')->commit();

            Log::info('Report deleted', [
                'report_id' => $id,
                'deleted_by' => Auth::id(),
            ]);

            return redirect()->route('stray-reporting.index')
                ->with('success', 'Report deleted successfully!');

        } catch (\Exception $e) {
            DB::connection('eilya')->rollBack();

            Log::error('Error deleting report: ' . $e->getMessage(), [
                'report_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to delete report. Please try again.');
        }
    }

    /**
     * Assign caretaker to a report
     * Creates/updates rescue in Eilya's database
     */
    public function assignCaretaker(Request $request, $id)
    {
        $request->validate([
            'caretaker_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Validate caretaker exists in Taufiq's database
                    if (!ForeignKeyValidator::validateUser($value)) {
                        $fail('The selected caretaker does not exist.');
                    }

                    // Verify user has caretaker role (explicitly use taufiq connection)
                    $user = User::on('taufiq')->find($value);
                    if (!$user || !$user->hasRole('caretaker')) {
                        $fail('The selected user is not a caretaker.');
                    }
                },
            ]
        ]);

        // Use transaction for Eilya's database
        DB::connection('eilya')->beginTransaction();

        try {
            // Get report from Eilya's database
            $report = Report::findOrFail($id);

            // Check if rescue already exists in Eilya's database
            $rescue = Rescue::where('reportID', $report->id)->first();

            if ($rescue) {
                // Update existing rescue
                $rescue->update([
                    'caretakerID' => $request->caretaker_id // Cross-database reference to Taufiq
                ]);
            } else {
                // Create new rescue in Eilya's database
                Rescue::create([
                    'reportID' => $report->id,
                    'caretakerID' => $request->caretaker_id, // Cross-database reference to Taufiq
                    'status' => Rescue::STATUS_SCHEDULED,
                    'remarks' => null,
                ]);
            }

            // Update report status in Eilya's database
            $report->update([
                'report_status' => 'In Progress'
            ]);

            DB::connection('eilya')->commit();

            Log::info('Caretaker assigned to report', [
                'report_id' => $id,
                'caretaker_id' => $request->caretaker_id,
            ]);

            return redirect()->back()->with('success', 'Caretaker assigned successfully!');

        } catch (\Exception $e) {
            DB::connection('eilya')->rollBack();

            Log::error('Error assigning caretaker: ' . $e->getMessage(), [
                'report_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to assign caretaker. Please try again.');
        }
    }

    /**
     * Display rescues for logged-in caretaker
     * Rescues from Eilya's database
     */
    public function indexcaretaker(Request $request)
    {
        // Get rescues from Eilya's database for this caretaker
        $query = Rescue::with([
            'report.images',  // Eilya -> Eilya -> Eilya
            'caretaker'       // Eilya -> Taufiq (cross-database)
        ])
            ->where('caretakerID', Auth::id());

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $rescues = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('stray-reporting.index-caretaker', compact('rescues'));
    }

    /**
     * Update rescue status by caretaker
     * Updates Eilya's database
     */
    public function updateStatusCaretaker(Request $request, $id)
    {
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

        // Use transaction for Eilya's database
        DB::connection('eilya')->beginTransaction();

        try {
            // Get rescue from Eilya's database
            $rescue = Rescue::where('id', $id)
                ->where('caretakerID', Auth::id())
                ->firstOrFail();

            $updateData = ['status' => $request->status];

            if ($request->filled('remarks')) {
                $updateData['remarks'] = $request->remarks;
            }

            // Update rescue in Eilya's database
            $rescue->update($updateData);

            // Case-insensitive status comparison for cross-RDBMS compatibility
            $currentStatus = strtolower($request->status);
            $successStatus = strtolower(Rescue::STATUS_SUCCESS);
            $failedStatus = strtolower(Rescue::STATUS_FAILED);

            if ($currentStatus === $successStatus || $currentStatus === $failedStatus) {
                // Update report status in Eilya's database
                $rescue->report->update([
                    'report_status' => 'Resolved'
                ]);
            }

            DB::connection('eilya')->commit();

            Log::info('Rescue status updated', [
                'rescue_id' => $id,
                'new_status' => $request->status,
                'caretaker_id' => Auth::id(),
            ]);

            // If successful, redirect to add animal
            if ($currentStatus === $successStatus) {
                return redirect()
                    ->route('animal-management.create', ['rescue_id' => $rescue->id])
                    ->with('success', 'Rescue completed! You can now add the animal.');
            }

            return redirect()->back()->with('success', 'Rescue status updated successfully!');

        } catch (\Exception $e) {
            DB::connection('eilya')->rollBack();

            Log::error('Error updating rescue status: ' . $e->getMessage(), [
                'rescue_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update rescue status. Please try again.');
        }
    }

    /**
     * Show rescue details for caretaker
     * Rescue from Eilya's database
     */
    public function showCaretaker($id)
    {
        // Get rescue from Eilya's database with cross-database relationships
        $rescue = Rescue::with([
            'report.images',  // Eilya -> Eilya -> Eilya
            'caretaker',      // Eilya -> Taufiq (cross-database)
            'animals'         // Eilya -> Shafiqah (cross-database)
        ])
            ->where('id', $id)
            ->where('caretakerID', Auth::id())
            ->firstOrFail();

        return view('stray-reporting.show-caretaker', compact('rescue'));
    }
}
