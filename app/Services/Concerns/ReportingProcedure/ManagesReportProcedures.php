<?php

namespace App\Services\Concerns\ReportingProcedure;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ManagesReportProcedures
{
    public function createReport(array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        $fullDescription = $data['description'];
        if (!empty($data['additional_notes'])) {
            $fullDescription .= "\n\nAdditional Notes: ".$data['additional_notes'];
        }

        DB::connection('reporting')->statement('CALL sp_report_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_report_id, @o_status, @o_message)', [
            $data['latitude'],
            $data['longitude'],
            $data['address'],
            $data['city'],
            $data['state'],
            $data['report_status'] ?? 'Pending',
            $fullDescription,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('reporting')->select('SELECT @o_report_id as report_id, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'report_id' => $result->report_id,
            'message' => $result->message,
        ];
    }

    public function readReport(int $reportId): ?object
    {
        DB::connection('reporting')->statement('CALL sp_report_read(?)', [$reportId]);

        $result = DB::connection('reporting')->select('SELECT * FROM report WHERE id = ?', [$reportId]);

        return $result[0] ?? null;
    }

    public function readPaginatedReports(array $filters, int $offset = 0, int $limit = 50): array
    {
        DB::connection('reporting')->statement('CALL sp_report_read_paginated(?, ?, ?, ?, ?, @o_total_count)', [
            $filters['user_id'] ?? null,
            $filters['status'] ?? null,
            $filters['city'] ?? null,
            $offset,
            $limit,
        ]);

        $totalResult = DB::connection('reporting')->select('SELECT @o_total_count as total')[0];

        $data = DB::connection('reporting')->select('
            SELECT * FROM report
            WHERE (? IS NULL OR userID = ?)
              AND (? IS NULL OR report_status = ?)
              AND (? IS NULL OR city = ?)
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ', [
            $filters['user_id'] ?? null, $filters['user_id'] ?? null,
            $filters['status'] ?? null, $filters['status'] ?? null,
            $filters['city'] ?? null, $filters['city'] ?? null,
            $limit, $offset,
        ]);

        return [
            'data' => $data,
            'total' => $totalResult->total,
        ];
    }

    public function updateReportStatus(int $reportId, string $newStatus): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('reporting')->statement('CALL sp_report_update_status(?, ?, ?, ?, ?, @o_status, @o_message)', [
            $reportId,
            $newStatus,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('reporting')->select('SELECT @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'message' => $result->message,
        ];
    }

    public function deleteReport(int $reportId): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('reporting')->statement('CALL sp_report_delete(?, ?, ?, ?, @o_has_rescue, @o_status, @o_message)', [
            $reportId,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('reporting')->select('SELECT @o_has_rescue as has_rescue, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'has_rescue' => (bool) $result->has_rescue,
            'message' => $result->message,
        ];
    }
}
