<?php

namespace App\Http\Controllers\Concerns\Audit;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

trait ExportsAuditLogs
{
    public function export(Request $request, $category)
    {
        if ($category === 'all') {
            $query = AuditLog::with('user')->orderBy('performed_at', 'desc');
        } else {
            $query = AuditLog::category($category)->with('user')->orderBy('performed_at', 'desc');
        }

        if ($request->filled('date_from')) {
            $query->where('performed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('performed_at', '<=', $request->date_to . ' 23:59:59');
        }

        if ($request->filled('category') && $category === 'all') {
            $query->category($request->category);
        }

        if ($request->filled('action')) {
            $query->action($request->action);
        }

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        $logs = $query->limit(10000)->get();

        $filename = $category . '_audit_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($logs, $category) {
            $file = fopen('php://output', 'w');

            if ($category === 'all') {
                fputcsv($file, ['Timestamp', 'Category', 'User', 'Email', 'Action', 'Entity ID', 'Status', 'IP Address', 'Details']);
            } elseif ($category === 'authentication') {
                fputcsv($file, ['Timestamp', 'User', 'Email', 'Action', 'IP Address', 'User Agent', 'Status', 'Error Message']);
            } elseif ($category === 'payment') {
                fputcsv($file, ['Timestamp', 'User', 'Action', 'Booking ID', 'Amount', 'Animals', 'Bill Code', 'Status']);
            } elseif ($category === 'animal') {
                fputcsv($file, ['Timestamp', 'User', 'Action', 'Animal ID', 'Animal Name', 'Old Values', 'New Values', 'Details']);
            } elseif ($category === 'rescue') {
                fputcsv($file, ['Timestamp', 'User', 'Action', 'Rescue ID', 'Priority', 'Old Status', 'New Status', 'Caretaker', 'Location']);
            }

            foreach ($logs as $log) {
                if ($category === 'all') {
                    fputcsv($file, [
                        $log->performed_at,
                        ucwords(str_replace('_', ' ', $log->category)),
                        $log->user_name,
                        $log->user_email,
                        ucwords(str_replace('_', ' ', $log->action)),
                        $log->entity_id,
                        $log->status,
                        $log->ip_address,
                        json_encode($log->metadata),
                    ]);
                } elseif ($category === 'authentication') {
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
