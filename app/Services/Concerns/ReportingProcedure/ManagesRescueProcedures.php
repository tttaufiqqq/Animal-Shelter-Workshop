<?php

namespace App\Services\Concerns\ReportingProcedure;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ManagesRescueProcedures
{
    public function assignCaretaker(int $reportId, int $caretakerId, ?string $priority = null): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('reporting')->statement('CALL sp_rescue_assign_caretaker(?, ?, ?, ?, ?, ?, @o_rescue_id, @o_is_reassignment, @o_old_caretaker_id, @o_status, @o_message)', [
            $reportId,
            $caretakerId,
            $priority,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('reporting')->select('SELECT @o_rescue_id as rescue_id, @o_is_reassignment as is_reassignment, @o_old_caretaker_id as old_caretaker_id, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'rescue_id' => $result->rescue_id,
            'is_reassignment' => (bool) $result->is_reassignment,
            'old_caretaker_id' => $result->old_caretaker_id,
            'message' => $result->message,
        ];
    }

    public function updateRescueStatus(int $rescueId, string $newStatus, ?string $remarks = null): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('reporting')->statement('CALL sp_rescue_update_status(?, ?, ?, ?, ?, ?, @o_old_status, @o_report_id, @o_status, @o_message)', [
            $rescueId,
            $newStatus,
            $remarks,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('reporting')->select('SELECT @o_old_status as old_status, @o_report_id as report_id, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'old_status' => $result->old_status,
            'report_id' => $result->report_id,
            'message' => $result->message,
        ];
    }

    public function updateRescuePriority(int $rescueId, string $priority): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('reporting')->statement('CALL sp_rescue_update_priority(?, ?, ?, ?, ?, @o_old_priority, @o_status, @o_message)', [
            $rescueId,
            $priority,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('reporting')->select('SELECT @o_old_priority as old_priority, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'old_priority' => $result->old_priority,
            'message' => $result->message,
        ];
    }

    public function readRescuesByCaretaker(int $caretakerId, array $filters = [], int $offset = 0, int $limit = 50): array
    {
        DB::connection('reporting')->statement('CALL sp_rescue_read_by_caretaker(?, ?, ?, ?, ?, @o_total_count)', [
            $caretakerId,
            $filters['priority'] ?? null,
            $filters['status'] ?? null,
            $offset,
            $limit,
        ]);

        $totalResult = DB::connection('reporting')->select('SELECT @o_total_count as total')[0];

        $data = DB::connection('reporting')->select("
            SELECT * FROM rescue
            WHERE caretakerID = ?
              AND (? IS NULL OR priority = ?)
              AND (? IS NULL OR status = ?)
            ORDER BY
                CASE
                    WHEN priority = 'critical' THEN 1
                    WHEN priority = 'high' THEN 2
                    WHEN priority = 'normal' THEN 3
                    ELSE 4
                END,
                created_at DESC
            LIMIT ? OFFSET ?
        ", [
            $caretakerId,
            $filters['priority'] ?? null, $filters['priority'] ?? null,
            $filters['status'] ?? null, $filters['status'] ?? null,
            $limit, $offset,
        ]);

        return [
            'data' => $data,
            'total' => $totalResult->total,
        ];
    }

    public function getRescueStatusCounts(int $caretakerId): array
    {
        DB::connection('reporting')->statement('CALL sp_rescue_get_status_counts(?)', [$caretakerId]);

        $results = DB::connection('reporting')->select('
            SELECT status, COUNT(*) as total
            FROM rescue
            WHERE caretakerID = ?
            GROUP BY status
        ', [$caretakerId]);

        $counts = [];
        foreach ($results as $result) {
            $counts[$result->status] = $result->total;
        }

        return $counts;
    }
}
