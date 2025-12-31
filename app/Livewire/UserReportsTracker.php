<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Report;
use App\DatabaseErrorHandler;

class UserReportsTracker extends Component
{
    use WithPagination, DatabaseErrorHandler;

    // Track status changes for notifications
    public $statusChanges = [];
    public $hasStatusChanges = false;

    // Store report statuses to detect changes
    protected $reportStatuses = [];

    // Polling interval in milliseconds (15 seconds)
    protected $pollInterval = 15000;

    public function mount()
    {
        // Initialize report statuses on component load
        $this->initializeReportStatuses();
    }

    /**
     * Initialize report statuses tracking
     */
    protected function initializeReportStatuses()
    {
        $reports = $this->safeQuery(
            fn() => Report::where('userID', auth()->id())
                ->select('id', 'report_status')
                ->get(),
            collect([]),
            'eilya'
        );

        foreach ($reports as $report) {
            $this->reportStatuses[$report->id] = $report->report_status;
        }
    }

    /**
     * Check for status changes (called by polling)
     */
    public function checkForStatusChanges()
    {
        $currentReports = $this->safeQuery(
            fn() => Report::where('userID', auth()->id())
                ->select('id', 'report_status')
                ->get(),
            collect([]),
            'eilya'
        );

        $changes = [];

        foreach ($currentReports as $report) {
            $reportId = $report->id;
            $newStatus = $report->report_status;

            // Check if status changed
            if (isset($this->reportStatuses[$reportId]) &&
                $this->reportStatuses[$reportId] !== $newStatus) {

                $changes[] = [
                    'report_id' => $reportId,
                    'old_status' => $this->reportStatuses[$reportId],
                    'new_status' => $newStatus,
                ];

                // Update tracked status
                $this->reportStatuses[$reportId] = $newStatus;
            }
            // Track new reports (shouldn't happen for user view, but just in case)
            elseif (!isset($this->reportStatuses[$reportId])) {
                $this->reportStatuses[$reportId] = $newStatus;
            }
        }

        if (!empty($changes)) {
            $this->statusChanges = $changes;
            $this->hasStatusChanges = true;

            // Dispatch browser event for notification
            $this->dispatch('status-changed', changes: $changes);
        }
    }

    /**
     * Acknowledge status changes (clear notification)
     */
    public function acknowledgeChanges()
    {
        $this->statusChanges = [];
        $this->hasStatusChanges = false;

        // Dispatch event to show success message
        $this->dispatch('changes-acknowledged');
    }

    /**
     * Manually refresh reports
     */
    public function refreshReports()
    {
        $this->initializeReportStatuses();
        $this->resetPage();
        $this->dispatch('reports-refreshed');
    }

    public function render()
    {
        $userReports = $this->safeQuery(
            fn() => Report::where('userID', auth()->id())
                ->with(['images'])
                ->orderBy('created_at', 'desc')
                ->paginate(50),
            new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50),
            'eilya'
        );

        return view('livewire.user-reports-tracker', [
            'userReports' => $userReports,
        ]);
    }
}
