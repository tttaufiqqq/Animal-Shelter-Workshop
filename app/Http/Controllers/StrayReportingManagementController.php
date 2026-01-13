<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Image;
use App\Models\AdopterProfile;
use App\Models\AnimalProfile;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Rescue;
use App\Models\User;
use App\Models\Animal;
use App\DatabaseErrorHandler;
use App\Services\EilyaProcedureService;

class StrayReportingManagementController extends Controller
{
    use DatabaseErrorHandler;

    protected $procedureService;

    public function __construct(EilyaProcedureService $procedureService)
    {
        $this->procedureService = $procedureService;
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
        // NOTE: Transactions are handled internally by stored procedures (sp_report_create, sp_image_create)
        // No manual transaction management needed here
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
                    return response()->json([
                        'success' => false,
                        'message' => 'Please check the form and try again.',
                        'errors' => $validator->errors()
                    ], 422);
                }

                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Please check the form and try again.');
            }

            $validated = $validator->validated();

            // Create report using procedure service
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
                    // Create descriptive filename: report_1_kuala_lumpur_selangor_1
                    $sanitizedCity = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $validated['city']));
                    $sanitizedState = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $validated['state']));
                    $filename = "report_{$reportId}_{$sanitizedCity}_{$sanitizedState}_{$imageIndex}";

                    // Upload to Cloudinary and get the public_id
                    $uploadResult = cloudinary()->uploadApi()->upload($image->getRealPath(), [
                        'folder' => 'reports',
                        'public_id' => $filename,
                    ]);
                    $path = $uploadResult['public_id'];
                    $uploadedFiles[] = $path;

                    // Create image using procedure service
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

            // NOTE: No commit needed - stored procedures handle their own transactions

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Report submitted successfully! Our team will review it shortly.'
                ]);
            }

            return redirect()->back()->with('success', 'Report submitted successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // NOTE: No rollback needed - stored procedures handle their own transactions

            // Clean up uploaded files
            foreach ($uploadedFiles as $filePath) {
                cloudinary()->uploadApi()->destroy($filePath);
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please check the form and try again.',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            // NOTE: No rollback needed - stored procedures handle their own transactions

            // Clean up uploaded files
            foreach ($uploadedFiles as $filePath) {
                cloudinary()->uploadApi()->destroy($filePath);
            }

            \Log::error('Error submitting stray report: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to submit report: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to submit report: ' . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        // Livewire component handles all data loading and real-time updates
        // Controller now just returns the view
        return view('stray-reporting.index');
    }

    public function show($id)
    {
        $report = $this->safeQuery(
            fn() => Report::with(['images', 'rescue.caretaker'])->findOrFail($id),
            null,
            'eilya' // Pre-check eilya database
        );

        if (!$report) {
            return redirect()->route('reports.index')
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
        // NOTE: Transactions are handled internally by stored procedure (sp_report_delete)
        // No manual transaction management needed here

        try {
            // Get images before deletion for Cloudinary cleanup
            $images = $this->procedureService->readImagesByReport($id);

            // Attempt to delete report using procedure service
            $deleteResult = $this->procedureService->deleteReport($id);

            // Check if report has rescue (procedure returns flag)
            if ($deleteResult['has_rescue']) {
                return redirect()->back()->with('error', 'Cannot delete report with associated rescue. Please delete the rescue first.');
            }

            if (!$deleteResult['success']) {
                throw new \Exception($deleteResult['message']);
            }

            // Clean up Cloudinary images
            foreach ($images as $image) {
                try {
                    cloudinary()->uploadApi()->destroy($image->image_path);
                } catch (\Exception $e) {
                    // Continue even if Cloudinary deletion fails
                }
            }

            // NOTE: No commit needed - stored procedure handles its own transaction

            return redirect()->route('reports.index')->with('success', 'Report deleted successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // NOTE: No rollback needed - stored procedure handles its own transaction

            \Log::error('Report not found for deletion: ' . $e->getMessage(), ['report_id' => $id]);
            return redirect()->back()->with('error', 'Report not found or database connection unavailable.');
        } catch (\Exception $e) {
            // NOTE: No rollback needed - stored procedure handles its own transaction

            \Log::error('Error deleting report: ' . $e->getMessage(), [
                'report_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to delete report: ' . $e->getMessage());
        }
    }

    public function assignCaretaker(Request $request, $id)
    {
        // NOTE: Transactions are handled internally by stored procedure (sp_rescue_assign_caretaker)
        // No manual transaction management needed here

        try {
            // Validate basic input (removed cross-database exists rule as it doesn't work with PostgreSQL)
            $validated = $request->validate([
                'caretaker_id' => 'required|integer|min:1'
            ]);

            $report = Report::findOrFail($id);

            // Verify caretaker exists in taufiq database (cross-database validation)
            $caretaker = $this->safeQuery(
                fn() => User::findOrFail($request->caretaker_id),
                null,
                'taufiq'
            );

            if (!$caretaker) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Selected caretaker not found or database connection unavailable.');
            }

            // Calculate priority from report description (urgency level)
            $priority = Rescue::getPriorityFromDescription($report->description);

            // Assign caretaker using procedure service with calculated priority
            $assignResult = $this->procedureService->assignCaretaker($report->id, $request->caretaker_id, $priority);

            if (!$assignResult['success']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $assignResult['message']);
            }

            $rescueId = $assignResult['rescue_id'];
            $isReassignment = $assignResult['is_reassignment'];
            $oldCaretakerId = $assignResult['old_caretaker_id'];

            // AUDIT: Log based on whether this was a reassignment or new assignment
            if ($isReassignment) {
                AuditService::logRescue(
                    'caretaker_reassigned',
                    $rescueId,
                    ['caretaker_id' => $oldCaretakerId],
                    ['caretaker_id' => $request->caretaker_id],
                    [
                        'report_id' => $report->id,
                        'new_caretaker_name' => $caretaker->name,
                        'address' => $report->address,
                        'priority' => $priority,
                    ]
                );
            } else {
                AuditService::logRescue(
                    'caretaker_assigned',
                    $rescueId,
                    null,
                    ['caretaker_id' => $request->caretaker_id, 'status' => Rescue::STATUS_SCHEDULED, 'priority' => $priority],
                    [
                        'report_id' => $report->id,
                        'caretaker_name' => $caretaker->name,
                        'address' => $report->address,
                        'priority' => $priority,
                    ]
                );
            }

            // NOTE: No commit needed - stored procedure handles its own transaction

            return redirect()->back()->with('success', 'Caretaker assigned successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // NOTE: No rollback needed - stored procedure handles its own transaction

            \Log::error('Report not found for caretaker assignment: ' . $e->getMessage(), ['report_id' => $id]);
            return redirect()->back()->with('error', 'Report not found or database connection unavailable.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // NOTE: No rollback needed - stored procedure handles its own transaction

            \Log::error('Validation failed for caretaker assignment', [
                'report_id' => $id,
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            // NOTE: No rollback needed - stored procedure handles its own transaction

            \Log::error('Error assigning caretaker: ' . $e->getMessage(), [
                'report_id' => $id,
                'input' => $request->all(),
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

            // Filter by priority if provided
            if ($request->has('priority') && in_array($request->priority, ['critical', 'high', 'normal'])) {
                $query->where('priority', $request->priority);
            }

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Order by latest submitted report (report.created_at descending)
            return $query
                ->join('report', 'rescue.reportID', '=', 'report.id')
                ->select('rescue.*')
                ->orderBy('report.created_at', 'desc')
                ->paginate(50);
        }, new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50), 'eilya'); // Pre-check eilya database

        // Get status counts for filter cards
        $statusCounts = $this->safeQuery(
            fn() => Rescue::select('status', DB::connection('eilya')->raw('COUNT(*) as total'))
                ->where('caretakerID', Auth::id())
                ->groupBy('status')
                ->pluck('total', 'status'),
            collect([]),
            'eilya'
        );

        return view('stray-reporting.index-caretaker', compact('rescues', 'statusCounts'));
    }

    public function updateStatusCaretaker(Request $request, $id)
    {
        // Check if atiqah database (Slots) is required and available
        if ($request->status === Rescue::STATUS_SUCCESS) {
            if (!$this->isDatabaseAvailable('atiqah')) {
                return redirect()->back()->with('error',
                    'Cannot complete rescue: Shelter Management database (slots) is currently offline. ' .
                    'Animals must be assigned to shelter slots. Please try again later or contact the system administrator.'
                );
            }

            if (!$this->isDatabaseAvailable('shafiqah')) {
                return redirect()->back()->with('error',
                    'Cannot complete rescue: Animal Management database is currently offline. ' .
                    'Please try again later or contact the system administrator.'
                );
            }
        }

        // NOTE: Transactions are handled internally by stored procedure (sp_rescue_update_status)
        // No manual transaction management needed here

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

            // Verify authorization: rescue belongs to current user (caretaker)
            $rescue = Rescue::where('id', $id)
                ->where('caretakerID', Auth::id())
                ->firstOrFail();

            // Update rescue status using procedure service
            $updateResult = $this->procedureService->updateRescueStatus(
                $id,
                $request->status,
                $request->remarks ?? null
            );

            if (!$updateResult['success']) {
                throw new \Exception($updateResult['message']);
            }

            $oldStatus = $updateResult['old_status'];
            $reportId = $updateResult['report_id'];

            // AUDIT: Rescue status updated
            AuditService::logRescue(
                'status_updated',
                $id,
                ['status' => $oldStatus, 'remarks' => $rescue->remarks],
                ['status' => $request->status, 'remarks' => $request->remarks ?? $rescue->remarks],
                [
                    'priority' => $rescue->priority ?? 'normal',
                    'report_id' => $reportId,
                    'caretaker_name' => Auth::user()->name,
                    'address' => $rescue->report->address ?? 'Unknown',
                ]
            );

            // NOTE: Report status synchronization is now handled automatically by database triggers
            // No manual status sync needed - triggers handle:
            // - 'In Progress' → Report: 'In Progress'
            // - 'Success'/'Failed' → Report: 'Completed'

            // Log the automatic synchronization for audit trail
            $currentStatus = strtolower($request->status);
            $inProgressStatus = strtolower(Rescue::STATUS_IN_PROGRESS);
            $successStatus = strtolower(Rescue::STATUS_SUCCESS);
            $failedStatus = strtolower(Rescue::STATUS_FAILED);

            if ($currentStatus === $inProgressStatus) {
                AuditService::logRescue(
                    'report_status_synced_in_progress',
                    $id,
                    null,
                    null,
                    [
                        'report_id' => $reportId,
                        'rescue_status' => $request->status,
                        'caretaker_name' => Auth::user()->name,
                        'address' => $rescue->report->address ?? 'Unknown',
                        'sync_trigger' => 'Automatic trigger synchronization',
                    ]
                );
            }

            if ($currentStatus === $successStatus || $currentStatus === $failedStatus) {
                AuditService::logRescue(
                    'report_status_synced_completed',
                    $id,
                    null,
                    null,
                    [
                        'report_id' => $reportId,
                        'rescue_status' => $request->status,
                        'rescue_final_status' => $request->status,
                        'caretaker_name' => Auth::user()->name,
                        'address' => $rescue->report->address ?? 'Unknown',
                        'sync_trigger' => 'Automatic trigger synchronization',
                    ]
                );
            }

            // NOTE: No commit needed - stored procedure handles its own transaction

            if ($currentStatus === $successStatus) {
                return redirect()
                    ->route('animal-management.create', ['rescue_id' => $rescue->id])
                    ->with('success', 'Rescue completed! You can now add the animal.');
            }

            return redirect()->back()->with('success', 'Rescue status updated successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // NOTE: No rollback needed - stored procedure handles its own transaction

            \Log::error('Rescue not found or unauthorized: ' . $e->getMessage(), [
                'rescue_id' => $id,
                'user_id' => Auth::id()
            ]);
            return redirect()->back()->with('error', 'Rescue not found or you are not authorized to update it.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // NOTE: No rollback needed - stored procedure handles its own transaction

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            // NOTE: No rollback needed - stored procedure handles its own transaction

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

            // Check if databases required for animal addition are available
            $canCompleteRescue = $this->isDatabaseAvailable('atiqah') && $this->isDatabaseAvailable('shafiqah');
            $dbWarning = null;

            if (!$canCompleteRescue) {
                $offlineDbs = [];
                if (!$this->isDatabaseAvailable('atiqah')) {
                    $offlineDbs[] = 'Shelter Management (Slots)';
                }
                if (!$this->isDatabaseAvailable('shafiqah')) {
                    $offlineDbs[] = 'Animal Management';
                }
                $dbWarning = 'Cannot complete rescue and add animals: ' . implode(', ', $offlineDbs) . ' database is offline.';
            }

            // Fetch available slots for animal assignment (only if atiqah is online)
            $availableSlots = collect([]);
            if ($this->isDatabaseAvailable('atiqah')) {
                $availableSlots = $this->safeQuery(
                    fn() => \App\Models\Slot::with('section')
                        ->where('status', 'available')
                        ->orderBy('sectionID')
                        ->orderBy('id')
                        ->get(),
                    collect([]),
                    'atiqah'
                );
            }

            return view('stray-reporting.show-caretaker', compact('rescue', 'canCompleteRescue', 'dbWarning', 'availableSlots'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Rescue not found or unauthorized for caretaker view: ' . $e->getMessage(), [
                'rescue_id' => $id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route(' rescues.index')
                ->with('error', 'Rescue not found or you are not authorized to view it.');
        } catch (\Exception $e) {
            \Log::error('Error viewing rescue: ' . $e->getMessage(), [
                'rescue_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('rescues.index')
                ->with('error', 'Failed to load rescue details: ' . $e->getMessage());
        }
    }

    /**
     * Get count of new assigned rescues for caretaker (AJAX endpoint)
     * Returns count of rescues with "Scheduled" status
     */
    public function getNewRescueCount()
    {
        try {
            $count = Rescue::where('caretakerID', Auth::id())
                ->where('status', Rescue::STATUS_SCHEDULED)
                ->count();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'count' => 0,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle successful rescue with animal creation (AJAX endpoint)
     * This method processes the multi-step animal addition form
     */
    public function updateStatusWithAnimals(Request $request, $id)
    {
        // Check critical database availability before processing
        if (!$this->isDatabaseAvailable('atiqah')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot add animals: Shelter Management database (slots) is currently offline. Animals must be assigned to shelter slots. Please try again later.'
            ], 503);
        }

        if (!$this->isDatabaseAvailable('shafiqah')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot add animals: Animal Management database is currently offline. Please try again later.'
            ], 503);
        }

        if (!$this->isDatabaseAvailable('eilya')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update rescue: Rescue database is currently offline. Please try again later.'
            ], 503);
        }

        // Start transactions on both eilya (Rescue) and shafiqah (Animal) databases
        DB::connection('eilya')->beginTransaction();
        DB::connection('shafiqah')->beginTransaction();

        $uploadedFiles = [];

        try {
            // Validate rescue authorization
            $rescue = Rescue::where('id', $id)
                ->where('caretakerID', Auth::id())
                ->firstOrFail();

            // Validate input
            $request->validate([
                'status' => 'required|in:Success',
                'remarks' => 'required|string|min:10|max:1000',
                'animals' => 'required|json',
            ]);

            // Parse animals data
            $animalsData = json_decode($request->animals, true);

            if (empty($animalsData)) {
                throw new \Exception('No animals data provided');
            }

            // Update rescue status
            $oldRescueStatus = $rescue->status;
            $rescue->update([
                'status' => $request->status,
                'remarks' => $request->remarks
            ]);

            // Update report status
            $oldReportStatus = $rescue->report->report_status;
            $rescue->report->update([
                'report_status' => Report::STATUS_COMPLETED
            ]);

            // AUDIT: Report status synced to "Completed" (with animals added)
            AuditService::logRescue(
                'report_status_synced_completed_with_animals',
                $rescue->id,
                ['report_status' => $oldReportStatus],
                ['report_status' => Report::STATUS_COMPLETED],
                [
                    'report_id' => $rescue->reportID,
                    'rescue_status' => $request->status,
                    'rescue_final_status' => 'Success',
                    'caretaker_name' => Auth::user()->name,
                    'address' => $rescue->report->address ?? 'Unknown',
                    'sync_trigger' => 'Rescue completed successfully with animals added',
                    'animals_count' => count($animalsData),
                ]
            );

            // Create animals
            foreach ($animalsData as $index => $animalData) {
                // Create animal record
                $animal = Animal::create([
                    'name' => $animalData['name'],
                    'species' => $animalData['species'],
                    'gender' => $animalData['gender'],
                    'age' => $animalData['age'],
                    'weight' => $animalData['weight'],
                    'health_details' => $animalData['health_details'],
                    'adoption_status' => $animalData['adoption_status'], // 'Not Adopted'
                    'rescueID' => $animalData['rescueID'],
                    'slotID' => $animalData['slotID'] ?? null, // Shelter slot assignment
                ]);

                // Handle image uploads for this animal
                $imageFiles = $request->file("animal_{$index}_images", []);

                foreach ($imageFiles as $imageFile) {
                    // Upload to Cloudinary
                    $uploadResult = cloudinary()->uploadApi()->upload($imageFile->getRealPath(), [
                        'folder' => 'animals',
                        'public_id' => pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME)
                    ]);

                    $path = $uploadResult['public_id'];
                    $uploadedFiles[] = $path;

                    // Create image record
                    Image::create([
                        'image_path' => $path,
                        'animalID' => $animal->id,
                        'reportID' => null,
                    ]);
                }
            }

            // Audit log
            AuditService::logRescue(
                'rescue_completed_with_animals',
                $rescue->id,
                ['status' => $rescue->status],
                ['status' => 'Success', 'animals_added' => count($animalsData)],
                [
                    'report_id' => $rescue->reportID,
                    'caretaker_name' => Auth::user()->name,
                    'animal_count' => count($animalsData),
                ]
            );

            DB::connection('eilya')->commit();
            DB::connection('shafiqah')->commit();

            // Store success message in session for display after redirect
            session()->flash('success', 'Rescue completed successfully! ' . count($animalsData) . ' animal(s) added to the shelter.');

            return response()->json([
                'success' => true,
                'message' => 'Rescue completed successfully! ' . count($animalsData) . ' animal(s) added to the shelter.',
                'redirect' => route('rescues.show', $rescue->id)
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::connection('eilya')->rollBack();
            DB::connection('shafiqah')->rollBack();

            // Clean up uploaded files
            foreach ($uploadedFiles as $filePath) {
                try {
                    cloudinary()->uploadApi()->destroy($filePath);
                } catch (\Exception $e) {
                    // Continue cleanup
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Rescue not found or you are not authorized.'
            ], 404);

        } catch (\Exception $e) {
            DB::connection('eilya')->rollBack();
            DB::connection('shafiqah')->rollBack();

            // Clean up uploaded files
            foreach ($uploadedFiles as $filePath) {
                try {
                    cloudinary()->uploadApi()->destroy($filePath);
                } catch (\Exception $e) {
                    // Continue cleanup
                }
            }

            \Log::error('Error updating rescue with animals: ' . $e->getMessage(), [
                'rescue_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete rescue: ' . $e->getMessage()
            ], 500);
        }
    }
}
