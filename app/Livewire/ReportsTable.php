<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Report;
use Illuminate\Support\Facades\DB;
use App\DatabaseErrorHandler;

class ReportsTable extends Component
{
    use WithPagination, DatabaseErrorHandler;

    // Public properties for filters
    public $userSearch = '';
    public $status = '';
    public $latestReportId = 0;
    public $hasNewReports = false;
    public $autoRefresh = true; // Auto-refresh new reports by default
    public $newReportIds = []; // Track which reports are new

    // Polling interval in milliseconds (10 seconds)
    protected $pollInterval = 10000;

    // Query string parameters
    protected $queryString = [
        'userSearch' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function mount()
    {
        // Initialize with URL parameters
        $this->userSearch = request('user_search', '');
        $this->status = request('status', '');

        // Get the latest report ID when component loads
        $this->latestReportId = $this->safeQuery(
            fn() => Report::max('id') ?? 0,
            0,
            'eilya'
        );
    }

    public function updatingUserSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->userSearch = '';
        $this->status = '';
        $this->resetPage();
    }

    /**
     * Check for new reports (called by polling)
     */
    public function checkForNewReports()
    {
        $currentLatestId = $this->safeQuery(
            fn() => Report::max('id') ?? 0,
            0,
            'eilya'
        );

        if ($currentLatestId > $this->latestReportId) {
            // Get IDs of new reports
            $newReports = $this->safeQuery(
                fn() => Report::where('id', '>', $this->latestReportId)
                    ->pluck('id')
                    ->toArray(),
                [],
                'eilya'
            );

            if ($this->autoRefresh) {
                // Automatically show new reports
                $this->newReportIds = $newReports;
                $this->latestReportId = $currentLatestId;

                // Dispatch event to show notification
                $this->dispatch('new-reports-loaded', [
                    'count' => count($newReports),
                    'reportIds' => $newReports
                ]);

                // Clear highlight after 5 seconds
                $this->dispatch('clear-highlights-after-delay');
            } else {
                // Show notification banner (manual refresh mode)
                $this->hasNewReports = true;
            }
        }
    }

    /**
     * Toggle auto-refresh mode
     */
    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;

        if ($this->autoRefresh && $this->hasNewReports) {
            // If enabling auto-refresh and there are pending new reports, load them
            $this->refreshReports();
        }
    }

    /**
     * Refresh reports and clear new reports badge
     */
    public function refreshReports()
    {
        $this->latestReportId = $this->safeQuery(
            fn() => Report::max('id') ?? 0,
            0,
            'eilya'
        );
        $this->hasNewReports = false;
        $this->newReportIds = [];
        $this->resetPage();

        // Dispatch browser event for success notification
        $this->dispatch('reports-refreshed');
    }

    /**
     * Clear new report highlights
     */
    public function clearNewReportHighlights()
    {
        $this->newReportIds = [];
    }

    public function render()
    {
        $reports = $this->safeQuery(function() {
            $query = Report::with('images');

            // Check if taufiq database is online for user filtering
            $taufiqOnline = $this->isDatabaseAvailable('taufiq');

            // Search by user name or email (cross-database search)
            if (!empty($this->userSearch) && $taufiqOnline) {
                $userSearch = $this->userSearch;

                // Get user IDs from taufiq database that match search
                $userIds = DB::connection('taufiq')
                    ->table('users')
                    ->where(function($q) use ($userSearch) {
                        $q->where('name', 'LIKE', "%{$userSearch}%")
                          ->orWhere('email', 'LIKE', "%{$userSearch}%");
                    })
                    ->pluck('id')
                    ->toArray();

                if (!empty($userIds)) {
                    $query->whereIn('userID', $userIds);
                } else {
                    // No users found, return empty result
                    $query->whereRaw('1 = 0');
                }
            }

            // Filter by status
            if (!empty($this->status)) {
                $query->where('report_status', $this->status);
            }

            return $query->orderBy('created_at', 'desc')
                ->paginate(50);
        }, new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50), 'eilya');

        // Get status counts for filter badges
        $statusCounts = $this->safeQuery(
            fn() => Report::select('report_status', DB::connection('eilya')->raw('COUNT(*) as total'))
                ->groupBy('report_status')
                ->pluck('total', 'report_status'),
            collect([]),
            'eilya'
        );

        $totalReports = $statusCounts->sum();

        return view('livewire.reports-table', [
            'reports' => $reports,
            'statusCounts' => $statusCounts,
            'totalReports' => $totalReports,
        ]);
    }
}
