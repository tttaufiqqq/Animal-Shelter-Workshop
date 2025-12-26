<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AuditController extends Controller
{
    /**
     * Display audit dashboard with summary statistics.
     */
    public function index()
    {
        // Get summary statistics for each category
        $stats = [
            'total_logs' => AuditLog::count(),
            'today_logs' => AuditLog::whereDate('performed_at', today())->count(),
            'authentication' => AuditLog::category('authentication')->count(),
            'payment' => AuditLog::category('payment')->count(),
            'animal' => AuditLog::category('animal')->count(),
            'rescue' => AuditLog::category('rescue')->count(),
            'failed_actions' => AuditLog::where('status', 'failure')->count(),
        ];

        // Recent audit logs (last 20)
        $recentLogs = AuditLog::with('user')
            ->orderBy('performed_at', 'desc')
            ->limit(20)
            ->get();

        // Failed login attempts in last 24 hours
        $failedLogins = AuditLog::category('authentication')
            ->action('login_failed')
            ->where('performed_at', '>=', now()->subDay())
            ->count();

        return view('admin.audit.index', compact('stats', 'recentLogs', 'failedLogins'));
    }

    /**
     * Display authentication audit logs.
     */
    public function authentication(Request $request)
    {
        $query = AuditLog::category('authentication')
            ->with('user.roles')
            ->orderBy('performed_at', 'desc');

        // Apply filters
        if ($request->filled('date_from')) {
            $query->where('performed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('performed_at', '<=', $request->date_to . ' 23:59:59');
        }

        if ($request->filled('action')) {
            $query->action($request->action);
        }

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'LIKE', '%' . $request->ip_address . '%');
        }

        $logs = $query->paginate(100)->withQueryString();

        // Detect suspicious activity for each unique user email in current page
        $suspiciousUsers = $this->detectSuspiciousUsers($logs);

        return view('admin.audit.authentication', compact('logs', 'suspiciousUsers'));
    }

    /**
     * Detect users with suspicious activity patterns
     */
    private function detectSuspiciousUsers($logs)
    {
        $suspicious = [];
        $uniqueEmails = $logs->pluck('user_email')->unique();

        foreach ($uniqueEmails as $email) {
            if (!$email) continue;

            $patterns = [];

            // Pattern 1: Multiple failed logins in last 30 minutes
            $recentFailedLogins = AuditLog::category('authentication')
                ->action('login_failed')
                ->where('user_email', $email)
                ->where('performed_at', '>=', now()->subMinutes(30))
                ->count();

            if ($recentFailedLogins >= 3) {
                $patterns[] = [
                    'type' => 'multiple_failed_logins',
                    'severity' => 'high',
                    'count' => $recentFailedLogins,
                ];
            }

            // Pattern 2: Login from multiple IPs in last hour
            $recentIPs = AuditLog::category('authentication')
                ->where('user_email', $email)
                ->where('performed_at', '>=', now()->subHour())
                ->distinct('ip_address')
                ->count('ip_address');

            if ($recentIPs > 2) {
                $patterns[] = [
                    'type' => 'multiple_ip_addresses',
                    'severity' => 'medium',
                    'count' => $recentIPs,
                ];
            }

            // Pattern 3: Rapid login/logout cycles
            $recentAuthEvents = AuditLog::category('authentication')
                ->whereIn('action', ['login_success', 'logout'])
                ->where('user_email', $email)
                ->where('performed_at', '>=', now()->subMinutes(10))
                ->count();

            if ($recentAuthEvents >= 5) {
                $patterns[] = [
                    'type' => 'rapid_login_logout',
                    'severity' => 'medium',
                    'count' => $recentAuthEvents,
                ];
            }

            if (!empty($patterns)) {
                $suspicious[$email] = $patterns;
            }
        }

        return $suspicious;
    }

    /**
     * Display payment & adoption audit logs.
     */
    public function payments(Request $request)
    {
        $query = AuditLog::category('payment')
            ->with('user')
            ->orderBy('performed_at', 'desc');

        // Apply filters
        if ($request->filled('date_from')) {
            $query->where('performed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('performed_at', '<=', $request->date_to . ' 23:59:59');
        }

        if ($request->filled('action')) {
            $query->action($request->action);
        }

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('booking_id')) {
            $query->where('entity_id', $request->booking_id);
        }

        if ($request->filled('amount_min')) {
            $query->whereRaw("(metadata->>'amount')::numeric >= ?", [$request->amount_min]);
        }

        if ($request->filled('amount_max')) {
            $query->whereRaw("(metadata->>'amount')::numeric <= ?", [$request->amount_max]);
        }

        $logs = $query->paginate(100)->withQueryString();

        // Calculate total revenue from successful payments
        $totalRevenue = AuditLog::category('payment')
            ->action('payment_completed')
            ->status('success')
            ->when($request->filled('date_from'), function ($q) use ($request) {
                return $q->where('performed_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                return $q->where('performed_at', '<=', $request->date_to . ' 23:59:59');
            })
            ->get()
            ->sum(function ($log) {
                return $log->metadata['amount'] ?? 0;
            });

        return view('admin.audit.payments', compact('logs', 'totalRevenue'));
    }

    /**
     * Display animal welfare audit logs.
     */
    public function animals(Request $request)
    {
        $query = AuditLog::category('animal')
            ->with('user')
            ->orderBy('performed_at', 'desc');

        // Apply filters
        if ($request->filled('date_from')) {
            $query->where('performed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('performed_at', '<=', $request->date_to . ' 23:59:59');
        }

        if ($request->filled('action')) {
            $query->action($request->action);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('animal_id')) {
            $query->where('entity_id', $request->animal_id)
                ->orWhereRaw("metadata->>'animal_id' = ?", [$request->animal_id]);
        }

        $logs = $query->paginate(100)->withQueryString();

        return view('admin.audit.animals', compact('logs'));
    }

    /**
     * Display rescue operations audit logs.
     */
    public function rescues(Request $request)
    {
        $query = AuditLog::category('rescue')
            ->with('user')
            ->orderBy('performed_at', 'desc');

        // Apply filters
        if ($request->filled('date_from')) {
            $query->where('performed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('performed_at', '<=', $request->date_to . ' 23:59:59');
        }

        if ($request->filled('action')) {
            $query->action($request->action);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('rescue_id')) {
            $query->where('entity_id', $request->rescue_id);
        }

        if ($request->filled('priority')) {
            $query->whereRaw("metadata->>'priority' = ?", [$request->priority]);
        }

        $logs = $query->paginate(100)->withQueryString();

        return view('admin.audit.rescues', compact('logs'));
    }

    /**
     * Display correlated timeline of audit logs.
     */
    public function timeline($correlationId)
    {
        $logs = AuditLog::whereRaw("metadata->>'correlation_id' = ?", [$correlationId])
            ->with('user')
            ->orderBy('performed_at', 'asc')
            ->get();

        if ($logs->isEmpty()) {
            return redirect()->route('admin.audit.index')
                ->with('error', 'No audit logs found with this correlation ID.');
        }

        // Group by database for visualization
        $logsByDatabase = $logs->groupBy('source_database');

        // Calculate duration
        $duration = $logs->first()->performed_at->diffInSeconds($logs->last()->performed_at);

        // Count successful vs failed operations
        $statusCounts = $logs->groupBy('status')->map->count();

        return view('admin.audit.timeline', compact('logs', 'logsByDatabase', 'correlationId', 'duration', 'statusCounts'));
    }

    /**
     * Export audit logs to CSV.
     */
    public function export(Request $request, $category)
    {
        $query = AuditLog::category($category)
            ->with('user')
            ->orderBy('performed_at', 'desc');

        // Apply same filters as the view
        if ($request->filled('date_from')) {
            $query->where('performed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('performed_at', '<=', $request->date_to . ' 23:59:59');
        }

        if ($request->filled('action')) {
            $query->action($request->action);
        }

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        // Limit to last 10,000 records for performance
        $logs = $query->limit(10000)->get();

        $filename = $category . '_audit_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($logs, $category) {
            $file = fopen('php://output', 'w');

            // CSV Headers based on category
            if ($category === 'authentication') {
                fputcsv($file, ['Timestamp', 'User', 'Email', 'Action', 'IP Address', 'User Agent', 'Status', 'Error Message']);
            } elseif ($category === 'payment') {
                fputcsv($file, ['Timestamp', 'User', 'Action', 'Booking ID', 'Amount', 'Animals', 'Bill Code', 'Status']);
            } elseif ($category === 'animal') {
                fputcsv($file, ['Timestamp', 'User', 'Action', 'Animal ID', 'Animal Name', 'Old Values', 'New Values', 'Details']);
            } elseif ($category === 'rescue') {
                fputcsv($file, ['Timestamp', 'User', 'Action', 'Rescue ID', 'Priority', 'Old Status', 'New Status', 'Caretaker', 'Location']);
            }

            // CSV Data
            foreach ($logs as $log) {
                if ($category === 'authentication') {
                    fputcsv($file, [
                        $log->performed_at,
                        $log->user_name,
                        $log->user_email,
                        $log->action,
                        $log->ip_address,
                        $log->user_agent,
                        $log->status,
                        $log->error_message,
                    ]);
                } elseif ($category === 'payment') {
                    fputcsv($file, [
                        $log->performed_at,
                        $log->user_name,
                        $log->action,
                        $log->entity_id,
                        $log->metadata['amount'] ?? '',
                        implode(', ', $log->metadata['animal_names'] ?? []),
                        $log->metadata['bill_code'] ?? '',
                        $log->status,
                    ]);
                } elseif ($category === 'animal') {
                    fputcsv($file, [
                        $log->performed_at,
                        $log->user_name,
                        $log->action,
                        $log->entity_id,
                        $log->metadata['animal_name'] ?? '',
                        json_encode($log->old_values),
                        json_encode($log->new_values),
                        json_encode($log->metadata),
                    ]);
                } elseif ($category === 'rescue') {
                    fputcsv($file, [
                        $log->performed_at,
                        $log->user_name,
                        $log->action,
                        $log->entity_id,
                        $log->metadata['priority'] ?? '',
                        $log->old_values['status'] ?? '',
                        $log->new_values['status'] ?? '',
                        $log->metadata['caretaker_name'] ?? '',
                        $log->metadata['address'] ?? '',
                    ]);
                }
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
