<?php

namespace App\Http\Controllers\Concerns\Audit;

use App\Models\AuditLog;
use Illuminate\Http\Request;

trait ViewsAuditLogs
{
    public function index()
    {
        $stats = [
            'total_logs' => AuditLog::count(),
            'today_logs' => AuditLog::whereDate('performed_at', today())->count(),
            'authentication' => AuditLog::category('authentication')->count(),
            'payment' => AuditLog::category('payment')->count(),
            'animal' => AuditLog::category('animal')->count(),
            'rescue' => AuditLog::category('rescue')->count(),
            'failed_actions' => AuditLog::where('status', 'failure')->count(),
        ];

        $recentLogs = AuditLog::with('user')
            ->orderBy('performed_at', 'desc')
            ->limit(20)
            ->get();

        $failedLogins = AuditLog::category('authentication')
            ->action('login_failed')
            ->where('performed_at', '>=', now()->subDay())
            ->count();

        return view('admin.audit.index', compact('stats', 'recentLogs', 'failedLogins'));
    }

    public function all(Request $request)
    {
        $query = AuditLog::with('user')->orderBy('performed_at', 'desc');

        if ($request->filled('date_from')) {
            $query->where('performed_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('performed_at', '<=', $request->date_to . ' 23:59:59');
        }
        if ($request->filled('category')) {
            $query->category($request->category);
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

        $logs = $query->paginate(100)->withQueryString();
        $categories = AuditLog::distinct('category')->pluck('category')->filter()->sort()->values();
        $actions = AuditLog::distinct('action')->pluck('action')->filter()->sort()->values();

        return view('admin.audit.all', compact('logs', 'categories', 'actions'));
    }

    public function authentication(Request $request)
    {
        $query = AuditLog::category('authentication')->with('user.roles')->orderBy('performed_at', 'desc');

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
        $suspiciousUsers = $this->detectSuspiciousUsers($logs);

        return view('admin.audit.authentication', compact('logs', 'suspiciousUsers'));
    }

    public function payments(Request $request)
    {
        $query = AuditLog::category('payment')->with('user')->orderBy('performed_at', 'desc');

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

        $totalRevenue = AuditLog::category('payment')
            ->action('payment_completed')
            ->status('success')
            ->when($request->filled('date_from'), fn($q) => $q->where('performed_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->where('performed_at', '<=', $request->date_to . ' 23:59:59'))
            ->get()
            ->sum(fn($log) => $log->metadata['amount'] ?? 0);

        return view('admin.audit.payments', compact('logs', 'totalRevenue'));
    }

    public function animals(Request $request)
    {
        $query = AuditLog::category('animal')->with('user')->orderBy('performed_at', 'desc');

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

    public function rescues(Request $request)
    {
        $query = AuditLog::category('rescue')->with('user')->orderBy('performed_at', 'desc');

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

        $logsByDatabase = $logs->groupBy('source_database');
        $duration = $logs->first()->performed_at->diffInSeconds($logs->last()->performed_at);
        $statusCounts = $logs->groupBy('status')->map->count();

        return view('admin.audit.timeline', compact('logs', 'logsByDatabase', 'correlationId', 'duration', 'statusCounts'));
    }
}
