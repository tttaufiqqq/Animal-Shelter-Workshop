<?php

namespace Database\Seeders\Concerns\RescueSeeder;

use Illuminate\Support\Facades\DB;

trait SyncsReportStatuses
{
    private function syncReportStatuses(): void
    {
        $this->command->info('Syncing report statuses with rescue statuses...');

        $scheduledReportIDs = DB::connection('reporting')
            ->table('rescue')->where('status', 'Scheduled')->pluck('reportID')->toArray();

        if (!empty($scheduledReportIDs)) {
            DB::connection('reporting')->table('report')
                ->whereIn('id', $scheduledReportIDs)
                ->update(['report_status' => 'Assigned', 'updated_at' => now()]);
            $this->command->info("  Updated " . count($scheduledReportIDs) . " reports to 'Assigned'");
        }

        $inProgressReportIDs = DB::connection('reporting')
            ->table('rescue')->where('status', 'In Progress')->pluck('reportID')->toArray();

        if (!empty($inProgressReportIDs)) {
            DB::connection('reporting')->table('report')
                ->whereIn('id', $inProgressReportIDs)
                ->update(['report_status' => 'In Progress', 'updated_at' => now()]);
            $this->command->info("  Updated " . count($inProgressReportIDs) . " reports to 'In Progress'");
        }

        $completedReportIDs = DB::connection('reporting')
            ->table('rescue')->whereIn('status', ['Success', 'Failed'])->pluck('reportID')->toArray();

        if (!empty($completedReportIDs)) {
            DB::connection('reporting')->table('report')
                ->whereIn('id', $completedReportIDs)
                ->update(['report_status' => 'Completed', 'updated_at' => now()]);
            $this->command->info("  Updated " . count($completedReportIDs) . " reports to 'Completed'");
        }
    }
}
