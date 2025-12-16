<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    public function __construct()
    {
        // Only admins can access audit logs
        $this->middleware(function ($request, $next) {
            if (! Auth::user()->hasRole('admin')) {
                abort(403, 'Unauthorized access to audit logs');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = AuditLog::query()->with('user')->latest();

        // Filter by user
        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }

        // Filter by action type
        if ($request->filled('action_type')) {
            $query->forAction($request->action_type);
        }

        // Filter by model type
        if ($request->filled('model_type')) {
            $query->forModel($request->model_type);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        } elseif ($request->filled('days')) {
            $query->recent($request->days);
        } else {
            $query->recent(30); // Default: last 30 days
        }

        // Search by email or name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_email', 'ILIKE', "%{$search}%")
                    ->orWhere('user_name', 'ILIKE', "%{$search}%");
            });
        }

        $logs = $query->paginate(50);

        // Get filter options
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        $actionTypes = AuditLog::distinct()->pluck('action_type');
        $modelTypes = AuditLog::distinct()->whereNotNull('auditable_type')->pluck('auditable_type');

        return view('audit-logs.index', compact('logs', 'users', 'actionTypes', 'modelTypes'));
    }

    public function show($id)
    {
        $log = AuditLog::with('user')->findOrFail($id);

        return view('audit-logs.show', compact('log'));
    }
}
