<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Rescue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\DatabaseErrorHandler;

class RescueMapController extends Controller
{
    use DatabaseErrorHandler;

    public function index()
    {
        // Get all reports with their rescue information
        $reports = $this->safeQuery(
            fn() => Report::with(['rescue'])
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
                        'report_status' => $report->report_status
                    ];
                }),
            collect([])
        );

        // Calculate overall statistics with fallback
        $statistics = $this->safeQuery(
            fn() => [
                'total' => Report::count(),
                'success' => Rescue::where('status', Rescue::STATUS_SUCCESS)->count(),
                'failed' => Rescue::where('status', Rescue::STATUS_FAILED)->count(),
                'scheduled' => Rescue::where('status', Rescue::STATUS_SCHEDULED)->count(),
                'in_progress' => Rescue::where('status', Rescue::STATUS_IN_PROGRESS)->count(),
                'pending' => Report::doesntHave('rescue')->count()
            ],
            [
                'total' => 0,
                'success' => 0,
                'failed' => 0,
                'scheduled' => 0,
                'in_progress' => 0,
                'pending' => 0
            ]
        );

        // Calculate success rate
        $totalWithRescue = $this->safeQuery(
            fn() => Rescue::count(),
            0
        );

        $statistics['success_rate'] = $totalWithRescue > 0
            ? number_format(($statistics['success'] / $totalWithRescue) * 100, 1)
            : 0;

        return view('rescue-map', compact('reports', 'statistics'));
    }
}