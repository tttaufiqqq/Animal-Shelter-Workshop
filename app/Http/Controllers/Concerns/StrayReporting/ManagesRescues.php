<?php

namespace App\Http\Controllers\Concerns\StrayReporting;

use App\Models\Report;
use App\Models\Rescue;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ManagesRescues
{
    public function assignCaretaker(Request $request, $id)
    {
        try {
            $validated = $request->validate(['caretaker_id' => 'required|integer|min:1']);

            $report = Report::findOrFail($id);

            $caretaker = $this->safeQuery(fn() => User::findOrFail($request->caretaker_id), null, 'users');

            if (!$caretaker) {
                return redirect()->back()->withInput()->with('error', 'Selected caretaker not found or database connection unavailable.');
            }

            $priority = Rescue::getPriorityFromDescription($report->description);
            $assignResult = $this->procedureService->assignCaretaker($report->id, $request->caretaker_id, $priority);

            if (!$assignResult['success']) {
                return redirect()->back()->withInput()->with('error', $assignResult['message']);
            }

            $rescueId = $assignResult['rescue_id'];
            $isReassignment = $assignResult['is_reassignment'];
            $oldCaretakerId = $assignResult['old_caretaker_id'];

            if ($isReassignment) {
                AuditService::logRescue('caretaker_reassigned', $rescueId,
                    ['caretaker_id' => $oldCaretakerId],
                    ['caretaker_id' => $request->caretaker_id],
                    ['report_id' => $report->id, 'new_caretaker_name' => $caretaker->name, 'address' => $report->address, 'priority' => $priority]
                );
            } else {
                AuditService::logRescue('caretaker_assigned', $rescueId, null,
                    ['caretaker_id' => $request->caretaker_id, 'status' => Rescue::STATUS_SCHEDULED, 'priority' => $priority],
                    ['report_id' => $report->id, 'caretaker_name' => $caretaker->name, 'address' => $report->address, 'priority' => $priority]
                );
            }

            return redirect()->back()->with('success', 'Caretaker assigned successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Report not found for caretaker assignment: ' . $e->getMessage(), ['report_id' => $id]);
            return redirect()->back()->with('error', 'Report not found or database connection unavailable.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed for caretaker assignment', ['report_id' => $id, 'errors' => $e->errors(), 'input' => $request->all()]);
            return redirect()->back()->withErrors($e->errors())->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            \Log::error('Error assigning caretaker: ' . $e->getMessage(), ['report_id' => $id, 'input' => $request->all(), 'caretaker_id' => $request->caretaker_id, 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', 'Failed to assign caretaker: ' . $e->getMessage());
        }
    }

    public function indexcaretaker(Request $request)
    {
        $rescues = $this->safeQuery(function() use ($request) {
            $query = Rescue::with(['report.images', 'caretaker'])->where('caretakerID', Auth::id());

            if ($request->has('priority') && in_array($request->priority, ['critical', 'high', 'normal'])) {
                $query->where('priority', $request->priority);
            }
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            return $query->join('report', 'rescue.reportID', '=', 'report.id')
                ->select('rescue.*')->orderBy('report.created_at', 'desc')->paginate(50);
        }, new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50), 'reporting');

        $statusCounts = $this->safeQuery(
            fn() => Rescue::select('status', DB::connection('reporting')->raw('COUNT(*) as total'))
                ->where('caretakerID', Auth::id())->groupBy('status')->pluck('total', 'status'),
            collect([]),
            'reporting'
        );

        return view('stray-reporting.index-caretaker', compact('rescues', 'statusCounts'));
    }

    public function updateStatusCaretaker(Request $request, $id)
    {
        if ($request->status === Rescue::STATUS_SUCCESS) {
            if (!$this->isDatabaseAvailable('shelter')) {
                return redirect()->back()->with('error',
                    'Cannot complete rescue: Shelter Management database (slots) is currently offline. Animals must be assigned to shelter slots. Please try again later or contact the system administrator.'
                );
            }
            if (!$this->isDatabaseAvailable('animals')) {
                return redirect()->back()->with('error',
                    'Cannot complete rescue: Animal Management database is currently offline. Please try again later or contact the system administrator.'
                );
            }
        }

        try {
            $rules = ['status' => 'required|in:' . implode(',', [Rescue::STATUS_SCHEDULED, Rescue::STATUS_IN_PROGRESS, Rescue::STATUS_SUCCESS, Rescue::STATUS_FAILED])];
            if (in_array($request->status, [Rescue::STATUS_SUCCESS, Rescue::STATUS_FAILED])) {
                $rules['remarks'] = 'required|string|min:10|max:1000';
            }
            $request->validate($rules, ['remarks.required' => 'Remarks are required for this status.', 'remarks.min' => 'Remarks must be at least 10 characters.', 'remarks.max' => 'Remarks must not exceed 1000 characters.']);

            $rescue = Rescue::where('id', $id)->where('caretakerID', Auth::id())->firstOrFail();

            $updateResult = $this->procedureService->updateRescueStatus($id, $request->status, $request->remarks ?? null);

            if (!$updateResult['success']) {
                throw new \Exception($updateResult['message']);
            }

            $oldStatus = $updateResult['old_status'];
            $reportId = $updateResult['report_id'];

            AuditService::logRescue('status_updated', $id,
                ['status' => $oldStatus, 'remarks' => $rescue->remarks],
                ['status' => $request->status, 'remarks' => $request->remarks ?? $rescue->remarks],
                ['priority' => $rescue->priority ?? 'normal', 'report_id' => $reportId, 'caretaker_name' => Auth::user()->name, 'address' => $rescue->report->address ?? 'Unknown']
            );

            $currentStatus = strtolower($request->status);
            $inProgressStatus = strtolower(Rescue::STATUS_IN_PROGRESS);
            $successStatus = strtolower(Rescue::STATUS_SUCCESS);
            $failedStatus = strtolower(Rescue::STATUS_FAILED);

            if ($currentStatus === $inProgressStatus) {
                AuditService::logRescue('report_status_synced_in_progress', $id, null, null,
                    ['report_id' => $reportId, 'rescue_status' => $request->status, 'caretaker_name' => Auth::user()->name, 'address' => $rescue->report->address ?? 'Unknown', 'sync_trigger' => 'Automatic trigger synchronization']
                );
            }

            if ($currentStatus === $successStatus || $currentStatus === $failedStatus) {
                AuditService::logRescue('report_status_synced_completed', $id, null, null,
                    ['report_id' => $reportId, 'rescue_status' => $request->status, 'rescue_final_status' => $request->status, 'caretaker_name' => Auth::user()->name, 'address' => $rescue->report->address ?? 'Unknown', 'sync_trigger' => 'Automatic trigger synchronization']
                );
            }

            if ($currentStatus === $successStatus) {
                return redirect()->route('animal-management.create', ['rescue_id' => $rescue->id])
                    ->with('success', 'Rescue completed! You can now add the animal.');
            }

            return redirect()->back()->with('success', 'Rescue status updated successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Rescue not found or unauthorized: ' . $e->getMessage(), ['rescue_id' => $id, 'user_id' => Auth::id()]);
            return redirect()->back()->with('error', 'Rescue not found or you are not authorized to update it.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Error updating rescue status: ' . $e->getMessage(), ['rescue_id' => $id, 'user_id' => Auth::id(), 'status' => $request->status, 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', 'Failed to update rescue status: ' . $e->getMessage());
        }
    }

    public function showCaretaker($id)
    {
        try {
            $rescue = Rescue::with(['report.images', 'caretaker'])->where('id', $id)->where('caretakerID', Auth::id())->firstOrFail();

            $canCompleteRescue = $this->isDatabaseAvailable('shelter') && $this->isDatabaseAvailable('animals');
            $dbWarning = null;

            if (!$canCompleteRescue) {
                $offlineDbs = [];
                if (!$this->isDatabaseAvailable('shelter')) $offlineDbs[] = 'Shelter Management (Slots)';
                if (!$this->isDatabaseAvailable('animals')) $offlineDbs[] = 'Animal Management';
                $dbWarning = 'Cannot complete rescue and add animals: ' . implode(', ', $offlineDbs) . ' database is offline.';
            }

            $availableSlots = collect([]);
            if ($this->isDatabaseAvailable('shelter')) {
                $availableSlots = $this->safeQuery(
                    fn() => \App\Models\Slot::with('section')->where('status', 'available')->orderBy('sectionID')->orderBy('id')->get(),
                    collect([]),
                    'shelter'
                );
            }

            return view('stray-reporting.show-caretaker', compact('rescue', 'canCompleteRescue', 'dbWarning', 'availableSlots'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Rescue not found or unauthorized for caretaker view: ' . $e->getMessage(), ['rescue_id' => $id, 'user_id' => Auth::id()]);
            return redirect()->route(' rescues.index')->with('error', 'Rescue not found or you are not authorized to view it.');
        } catch (\Exception $e) {
            \Log::error('Error viewing rescue: ' . $e->getMessage(), ['rescue_id' => $id, 'user_id' => Auth::id(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('rescues.index')->with('error', 'Failed to load rescue details: ' . $e->getMessage());
        }
    }

    public function getNewRescueCount()
    {
        try {
            $count = Rescue::where('caretakerID', Auth::id())->where('status', Rescue::STATUS_SCHEDULED)->count();
            return response()->json(['success' => true, 'count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'count' => 0, 'error' => $e->getMessage()]);
        }
    }
}
