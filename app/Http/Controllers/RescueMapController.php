<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Rescue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ForeignKeyValidator;

class RescueMapController extends Controller
{
    /**
     * Display rescue map with all reports and rescues
     * Reports and Rescues are both in Eilya's database
     */
    public function index()
    {
        try {
            // Get all reports with their rescue information from Eilya's database
            // Both Report and Rescue are in the same database, so this is efficient
            $reports = Report::with(['rescue'])
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get()
                ->map(function($report) {
                    return [
                        'id' => $report->id,
                        'lat' => (float) $report->latitude,
                        'lng' => (float) $report->longitude,
                        'city' => $report->city,
                        'state' => $report->state,
                        'address' => $report->address,
                        'status' => $report->rescue?->status ?? 'Pending',
                        'description' => $report->description,
                        'report_status' => $report->report_status,
                        'created_at' => $report->created_at?->format('Y-m-d H:i'),
                        'rescue_remarks' => $report->rescue?->remarks ?? null,
                    ];
                });

            // Calculate overall statistics from Eilya's database
            // All queries are within the same database for better performance
            $statistics = [
                'total' => Report::count(),
                'success' => Rescue::where('status', Rescue::STATUS_SUCCESS)->count(),
                'failed' => Rescue::where('status', Rescue::STATUS_FAILED)->count(),
                'scheduled' => Rescue::where('status', Rescue::STATUS_SCHEDULED)->count(),
                'in_progress' => Rescue::where('status', Rescue::STATUS_IN_PROGRESS)->count(),
                'pending' => Report::doesntHave('rescue')->count()
            ];

            // Calculate success rate
            $totalWithRescue = Rescue::count();
            $statistics['success_rate'] = $totalWithRescue > 0
                ? number_format(($statistics['success'] / $totalWithRescue) * 100, 1)
                : 0;

            // Additional statistics for better insights
            $statistics['completion_rate'] = $statistics['total'] > 0
                ? number_format((($statistics['success'] + $statistics['failed']) / $statistics['total']) * 100, 1)
                : 0;

            Log::info('Rescue map data loaded', [
                'total_reports' => $statistics['total'],
                'reports_with_location' => $reports->count(),
                'success_count' => $statistics['success'],
            ]);

            return view('rescue-map', compact('reports', 'statistics'));

        } catch (\Exception $e) {
            Log::error('Error loading rescue map: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Return view with empty data and error message
            return view('rescue-map', [
                'reports' => collect(),
                'statistics' => [
                    'total' => 0,
                    'success' => 0,
                    'failed' => 0,
                    'scheduled' => 0,
                    'in_progress' => 0,
                    'pending' => 0,
                    'success_rate' => 0,
                    'completion_rate' => 0,
                ],
            ])->with('error', 'Unable to load rescue map data. Please try again.');
        }
    }

    /**
     * Get detailed information about a specific report
     * Report from Eilya's database with cross-database relationships
     */
    public function show($id)
    {
        try {
            // Get report from Eilya's database with relationships
            $report = Report::with([
                'rescue.caretaker', // Eilya -> Eilya -> Taufiq (cross-database)
                'rescue.animals',   // Eilya -> Eilya -> Shafiqah (cross-database)
                'images',           // Eilya -> Eilya (same database)
                'user',             // Eilya -> Taufiq (cross-database)
            ])->findOrFail($id);

            return view('rescue-map.show', compact('report'));

        } catch (\Exception $e) {
            Log::error('Error loading report details: ' . $e->getMessage(), [
                'report_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('rescue-map.index')
                ->with('error', 'Unable to load report details.');
        }
    }

    /**
     * Get reports by status (AJAX endpoint)
     * Reports and Rescues from Eilya's database
     */
    public function getByStatus(Request $request)
    {
        try {
            $status = $request->input('status');

            $query = Report::with(['rescue'])
                ->whereNotNull('latitude')
                ->whereNotNull('longitude');

            // Filter based on status
            switch ($status) {
                case 'pending':
                    $query->doesntHave('rescue');
                    break;
                case 'scheduled':
                    $query->whereHas('rescue', function($q) {
                        $q->where('status', Rescue::STATUS_SCHEDULED);
                    });
                    break;
                case 'in_progress':
                    $query->whereHas('rescue', function($q) {
                        $q->where('status', Rescue::STATUS_IN_PROGRESS);
                    });
                    break;
                case 'success':
                    $query->whereHas('rescue', function($q) {
                        $q->where('status', Rescue::STATUS_SUCCESS);
                    });
                    break;
                case 'failed':
                    $query->whereHas('rescue', function($q) {
                        $q->where('status', Rescue::STATUS_FAILED);
                    });
                    break;
            }

            $reports = $query->get()->map(function($report) {
                return [
                    'id' => $report->id,
                    'lat' => (float) $report->latitude,
                    'lng' => (float) $report->longitude,
                    'city' => $report->city,
                    'state' => $report->state,
                    'address' => $report->address,
                    'status' => $report->rescue?->status ?? 'Pending',
                    'description' => $report->description,
                    'report_status' => $report->report_status,
                    'created_at' => $report->created_at?->format('Y-m-d H:i'),
                ];
            });

            return response()->json([
                'success' => true,
                'reports' => $reports,
                'count' => $reports->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error filtering reports by status: ' . $e->getMessage(), [
                'status' => $request->input('status'),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to filter reports.',
            ], 500);
        }
    }

    /**
     * Get reports within a specific geographic area (AJAX endpoint)
     * Reports from Eilya's database
     */
    public function getInArea(Request $request)
    {
        try {
            $validated = $request->validate([
                'min_lat' => 'required|numeric',
                'max_lat' => 'required|numeric',
                'min_lng' => 'required|numeric',
                'max_lng' => 'required|numeric',
            ]);

            // Query reports within geographic bounds from Eilya's database
            $reports = Report::with(['rescue'])
                ->whereBetween('latitude', [$validated['min_lat'], $validated['max_lat']])
                ->whereBetween('longitude', [$validated['min_lng'], $validated['max_lng']])
                ->get()
                ->map(function($report) {
                    return [
                        'id' => $report->id,
                        'lat' => (float) $report->latitude,
                        'lng' => (float) $report->longitude,
                        'city' => $report->city,
                        'state' => $report->state,
                        'address' => $report->address,
                        'status' => $report->rescue?->status ?? 'Pending',
                        'description' => $report->description,
                        'report_status' => $report->report_status,
                    ];
                });

            return response()->json([
                'success' => true,
                'reports' => $reports,
                'count' => $reports->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting reports in area: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to get reports in area.',
            ], 500);
        }
    }

    /**
     * Get statistics for a specific city or state (AJAX endpoint)
     * Reports and Rescues from Eilya's database
     */
    public function getLocationStats(Request $request)
    {
        try {
            $validated = $request->validate([
                'city' => 'nullable|string',
                'state' => 'nullable|string',
            ]);

            $query = Report::query();

            if (!empty($validated['city'])) {
                $query->where('city', $validated['city']);
            }

            if (!empty($validated['state'])) {
                $query->where('state', $validated['state']);
            }

            $totalReports = $query->count();

            // Get rescue statistics for this location
            $reportIds = $query->pluck('id');

            $rescueStats = [
                'success' => Rescue::whereIn('reportID', $reportIds)
                    ->where('status', Rescue::STATUS_SUCCESS)
                    ->count(),
                'failed' => Rescue::whereIn('reportID', $reportIds)
                    ->where('status', Rescue::STATUS_FAILED)
                    ->count(),
                'scheduled' => Rescue::whereIn('reportID', $reportIds)
                    ->where('status', Rescue::STATUS_SCHEDULED)
                    ->count(),
                'in_progress' => Rescue::whereIn('reportID', $reportIds)
                    ->where('status', Rescue::STATUS_IN_PROGRESS)
                    ->count(),
                'pending' => $totalReports - Rescue::whereIn('reportID', $reportIds)->count(),
            ];

            return response()->json([
                'success' => true,
                'location' => [
                    'city' => $validated['city'] ?? null,
                    'state' => $validated['state'] ?? null,
                ],
                'total_reports' => $totalReports,
                'statistics' => $rescueStats,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting location statistics: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to get location statistics.',
            ], 500);
        }
    }
}
