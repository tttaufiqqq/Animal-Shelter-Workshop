<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EilyaProcedureService
{
    /**
     * Set audit context variables for MySQL triggers
     */
    protected function setAuditContext(): void
    {
        $user = Auth::user();

        DB::connection('eilya')->statement('SET @audit_user_id = ?', [$user->id ?? null]);
        DB::connection('eilya')->statement('SET @audit_user_name = ?', [$user->name ?? null]);
        DB::connection('eilya')->statement('SET @audit_user_email = ?', [$user->email ?? null]);
        DB::connection('eilya')->statement('SET @audit_user_role = ?', [$user ? $user->getRoleNames()->first() : null]);
    }

    // ==========================================
    // REPORT PROCEDURES
    // ==========================================

    /**
     * Create a new report
     *
     * @param array $data ['latitude', 'longitude', 'address', 'city', 'state', 'description', 'additional_notes', 'report_status']
     * @return array ['success' => bool, 'report_id' => int|null, 'message' => string]
     */
    public function createReport(array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        // Combine description with additional notes (controller logic)
        $fullDescription = $data['description'];
        if (!empty($data['additional_notes'])) {
            $fullDescription .= "\n\nAdditional Notes: ".$data['additional_notes'];
        }

        DB::connection('eilya')->statement('CALL sp_report_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_report_id, @o_status, @o_message)', [
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

        $result = DB::connection('eilya')->select('SELECT @o_report_id as report_id, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'report_id' => $result->report_id,
            'message' => $result->message,
        ];
    }

    /**
     * Read a single report by ID
     *
     * @param  int  $reportId
     * @return object|null
     */
    public function readReport(int $reportId): ?object
    {
        DB::connection('eilya')->statement('CALL sp_report_read(?)', [$reportId]);

        $result = DB::connection('eilya')->select('SELECT * FROM report WHERE id = ?', [$reportId]);

        return $result[0] ?? null;
    }

    /**
     * Read paginated reports with filters
     *
     * @param  array  $filters  ['user_id', 'status', 'city']
     * @param  int  $offset
     * @param  int  $limit
     * @return array ['data' => array, 'total' => int]
     */
    public function readPaginatedReports(array $filters, int $offset = 0, int $limit = 50): array
    {
        DB::connection('eilya')->statement('CALL sp_report_read_paginated(?, ?, ?, ?, ?, @o_total_count)', [
            $filters['user_id'] ?? null,
            $filters['status'] ?? null,
            $filters['city'] ?? null,
            $offset,
            $limit,
        ]);

        // Get total count
        $totalResult = DB::connection('eilya')->select('SELECT @o_total_count as total')[0];

        // Get paginated data
        $data = DB::connection('eilya')->select('
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

    /**
     * Update report status
     *
     * @param  int  $reportId
     * @param  string  $newStatus
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateReportStatus(int $reportId, string $newStatus): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('eilya')->statement('CALL sp_report_update_status(?, ?, ?, ?, ?, @o_status, @o_message)', [
            $reportId,
            $newStatus,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('eilya')->select('SELECT @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'message' => $result->message,
        ];
    }

    /**
     * Delete a report
     *
     * @param  int  $reportId
     * @return array ['success' => bool, 'has_rescue' => bool, 'message' => string]
     */
    public function deleteReport(int $reportId): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('eilya')->statement('CALL sp_report_delete(?, ?, ?, ?, @o_has_rescue, @o_status, @o_message)', [
            $reportId,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('eilya')->select('SELECT @o_has_rescue as has_rescue, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'has_rescue' => (bool) $result->has_rescue,
            'message' => $result->message,
        ];
    }

    // ==========================================
    // RESCUE PROCEDURES
    // ==========================================

    /**
     * Assign caretaker to report (create or update rescue)
     *
     * @param  int  $reportId
     * @param  int  $caretakerId
     * @param  string|null  $priority  Priority level (critical, high, normal) - defaults to 'normal' if null
     * @return array ['success' => bool, 'rescue_id' => int|null, 'is_reassignment' => bool, 'old_caretaker_id' => int|null, 'message' => string]
     */
    public function assignCaretaker(int $reportId, int $caretakerId, ?string $priority = null): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('eilya')->statement('CALL sp_rescue_assign_caretaker(?, ?, ?, ?, ?, ?, @o_rescue_id, @o_is_reassignment, @o_old_caretaker_id, @o_status, @o_message)', [
            $reportId,
            $caretakerId,
            $priority,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('eilya')->select('SELECT @o_rescue_id as rescue_id, @o_is_reassignment as is_reassignment, @o_old_caretaker_id as old_caretaker_id, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'rescue_id' => $result->rescue_id,
            'is_reassignment' => (bool) $result->is_reassignment,
            'old_caretaker_id' => $result->old_caretaker_id,
            'message' => $result->message,
        ];
    }

    /**
     * Update rescue status
     *
     * @param  int  $rescueId
     * @param  string  $newStatus
     * @param  string|null  $remarks
     * @return array ['success' => bool, 'old_status' => string, 'report_id' => int, 'message' => string]
     */
    public function updateRescueStatus(int $rescueId, string $newStatus, ?string $remarks = null): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('eilya')->statement('CALL sp_rescue_update_status(?, ?, ?, ?, ?, ?, @o_old_status, @o_report_id, @o_status, @o_message)', [
            $rescueId,
            $newStatus,
            $remarks,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('eilya')->select('SELECT @o_old_status as old_status, @o_report_id as report_id, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'old_status' => $result->old_status,
            'report_id' => $result->report_id,
            'message' => $result->message,
        ];
    }

    /**
     * Update rescue priority
     *
     * @param  int  $rescueId
     * @param  string  $priority
     * @return array ['success' => bool, 'old_priority' => string, 'message' => string]
     */
    public function updateRescuePriority(int $rescueId, string $priority): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('eilya')->statement('CALL sp_rescue_update_priority(?, ?, ?, ?, ?, @o_old_priority, @o_status, @o_message)', [
            $rescueId,
            $priority,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('eilya')->select('SELECT @o_old_priority as old_priority, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'old_priority' => $result->old_priority,
            'message' => $result->message,
        ];
    }

    /**
     * Read rescues by caretaker with filters
     *
     * @param  int  $caretakerId
     * @param  array  $filters  ['priority', 'status']
     * @param  int  $offset
     * @param  int  $limit
     * @return array ['data' => array, 'total' => int]
     */
    public function readRescuesByCaretaker(int $caretakerId, array $filters = [], int $offset = 0, int $limit = 50): array
    {
        DB::connection('eilya')->statement('CALL sp_rescue_read_by_caretaker(?, ?, ?, ?, ?, @o_total_count)', [
            $caretakerId,
            $filters['priority'] ?? null,
            $filters['status'] ?? null,
            $offset,
            $limit,
        ]);

        // Get total count
        $totalResult = DB::connection('eilya')->select('SELECT @o_total_count as total')[0];

        // Get paginated data
        $data = DB::connection('eilya')->select("
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

    /**
     * Get rescue status counts for caretaker
     *
     * @param  int  $caretakerId
     * @return array ['Scheduled' => int, 'In Progress' => int, etc.]
     */
    public function getRescueStatusCounts(int $caretakerId): array
    {
        DB::connection('eilya')->statement('CALL sp_rescue_get_status_counts(?)', [$caretakerId]);

        $results = DB::connection('eilya')->select('
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

    // ==========================================
    // IMAGE PROCEDURES
    // ==========================================

    /**
     * Create a new image record
     *
     * @param  array  $data  ['image_path', 'reportID', 'animalID', 'clinicID']
     * @return array ['success' => bool, 'image_id' => int|null, 'message' => string]
     */
    public function createImage(array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('eilya')->statement('CALL sp_image_create(?, ?, ?, ?, ?, ?, ?, @o_image_id, @o_status, @o_message)', [
            $data['image_path'],
            $data['reportID'] ?? null,
            $data['animalID'] ?? null,
            $data['clinicID'] ?? null,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('eilya')->select('SELECT @o_image_id as image_id, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'image_id' => $result->image_id,
            'message' => $result->message,
        ];
    }

    /**
     * Delete an image
     *
     * @param  int  $imageId
     * @return array ['success' => bool, 'image_path' => string|null, 'message' => string]
     */
    public function deleteImage(int $imageId): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('eilya')->statement('CALL sp_image_delete(?, ?, ?, ?, @o_image_path, @o_status, @o_message)', [
            $imageId,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('eilya')->select('SELECT @o_image_path as image_path, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'image_path' => $result->image_path,
            'message' => $result->message,
        ];
    }

    /**
     * Read images by report
     *
     * @param  int  $reportId
     * @return array
     */
    public function readImagesByReport(int $reportId): array
    {
        DB::connection('eilya')->statement('CALL sp_image_read_by_report(?)', [$reportId]);

        return DB::connection('eilya')->select('
            SELECT * FROM image
            WHERE reportID = ?
            ORDER BY created_at
        ', [$reportId]);
    }

    /**
     * Read images by animal
     *
     * @param  int  $animalId
     * @return array
     */
    public function readImagesByAnimal(int $animalId): array
    {
        DB::connection('eilya')->statement('CALL sp_image_read_by_animal(?)', [$animalId]);

        return DB::connection('eilya')->select('
            SELECT * FROM image
            WHERE animalID = ?
            ORDER BY created_at
        ', [$animalId]);
    }
}
