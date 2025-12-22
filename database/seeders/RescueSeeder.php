<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Rescue;

class RescueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Rescues and Reports are in Eilya's database
     * Users (caretakers) are in Taufiq's database (cross-database reference)
     */
    public function run()
    {
        $this->command->info('Starting Rescue Seeder...');
        $this->command->info('========================================');

        // Get reports from Eilya's database
        $this->command->info('Fetching reports from Eilya\'s database...');
        $reports = DB::connection('eilya')->table('report')->get();

        if ($reports->isEmpty()) {
            $this->command->error("No reports found. Seed Reports first.");
            return;
        }

        $this->command->info("Found " . $reports->count() . " reports");

        // Get all caretakers from Taufiq's database (cross-database query)
        $this->command->info('Fetching caretakers from Taufiq\'s database...');

        $caretakers = DB::connection('taufiq')->table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'caretaker')
            ->where('model_has_roles.model_type', 'App\\Models\\User') // Ensure correct model type
            ->pluck('users.id')
            ->toArray();

        if (empty($caretakers)) {
            $this->command->error("No caretakers found. Seed Users & assign caretaker role first.");
            return;
        }

        $this->command->info("Found " . count($caretakers) . " caretakers");

        // Remarks templates
        $successRemarks = [
            'Animal(s) successfully rescued and brought to shelter. All animals are in stable condition.',
            'Rescue operation completed. Animals secured and transported safely to the facility.',
            'Successfully rescued and relocated animals to shelter. Initial health check completed.',
            'Animals rescued without complications. Currently under observation at the shelter.',
            'Rescue mission accomplished. All animals have been safely recovered and are receiving care.',
            'Operation successful. Animals are now safe at the shelter and receiving medical attention.',
            'Animals successfully rescued from reported location. No injuries sustained during rescue.',
            'Rescue completed successfully. Animals are calm and adapting well to shelter environment.',
            'All animals safely rescued and transported. Veterinary assessment scheduled.',
            'Successful rescue operation. Animals are healthy and have been assigned to shelter sections.',
        ];

        $failedRemarks = [
            'Animals could not be located at the reported address. Area searched thoroughly.',
            'Rescue operation failed. Animals had already left the location before team arrival.',
            'Unable to complete rescue due to dangerous location conditions. Will retry with proper equipment.',
            'Animals were too scared and fled before rescue team could secure them safely.',
            'Location access denied by property owner. Legal intervention required.',
            'Rescue attempt failed. Animals were already rescued by another organization.',
            'Could not locate animals despite multiple search attempts in the reported area.',
            'Weather conditions made rescue unsafe. Operation postponed for animal and team safety.',
            'Animals in location too aggressive to approach safely. Specialist team required.',
            'Report deemed inaccurate upon arrival. No animals found at specified location.',
        ];

        $scheduledRemarks = [
            'Rescue operation scheduled. Team will be dispatched within 24-48 hours.',
            'Rescue date and time confirmed. Caretaker team assigned and notified.',
            'Operation planned for next available window. Resources being prepared.',
            'Scheduled for rescue. Awaiting optimal conditions and team availability.',
        ];

        $inProgressRemarks = [
            'Rescue team currently on-site. Operation in progress.',
            'Caretakers are actively working to secure the animals safely.',
            'Rescue operation underway. Team is assessing situation and planning approach.',
            'In the process of rescuing animals. Updates will be provided upon completion.',
        ];

        $pendingRemarks = [
            'Awaiting initial assessment and resource allocation.',
            'Report received and under review. Rescue team will be assigned shortly.',
            'Pending approval and scheduling. Priority level being determined.',
            'Awaiting caretaker availability for rescue operation.',
        ];

        $rescues = [];
        $statusCounts = [
            'Success' => 0,
            'Failed' => 0,
            'Scheduled' => 0,
            'In Progress' => 0,
        ];

        $this->command->info('Generating rescue records...');

        foreach ($reports as $report) {
            // Keep 20% of reports PENDING (no rescue record)
            if (rand(1, 100) <= 20) {
                continue;
            }

            $remarks = '';

            // SUCCESS = 40% chance
            if (rand(1, 100) <= 40) {
                $status = 'Success';
                $remarks = $successRemarks[array_rand($successRemarks)];
                $statusCounts['Success']++;
            }
            else {
                // Remaining 60% get random non-success status
                $statusOptions = [
                    'Failed' => 30,      // 30% chance
                    'Scheduled' => 25,   // 25% chance
                    'In Progress' => 45, // 45% chance (to total 100%)
                ];

                $rand = rand(1, 100);
                $cumulative = 0;

                foreach ($statusOptions as $statusOption => $probability) {
                    $cumulative += $probability;
                    if ($rand <= $cumulative) {
                        $status = $statusOption;
                        break;
                    }
                }

                // Assign appropriate remarks based on status
                switch ($status) {
                    case 'Failed':
                        $remarks = $failedRemarks[array_rand($failedRemarks)];
                        break;
                    case 'Scheduled':
                        $remarks = $scheduledRemarks[array_rand($scheduledRemarks)];
                        break;
                    case 'In Progress':
                        $remarks = $inProgressRemarks[array_rand($inProgressRemarks)];
                        break;
                }

                $statusCounts[$status]++;
            }

            // Rescue should be created some hours after the report
            $rescueDate = Carbon::parse($report->created_at)->addHours(rand(1, 48));

            // Determine priority from report description
            $priority = Rescue::getPriorityFromDescription($report->description);

            $rescues[] = [
                'status'      => $status,
                'priority'    => $priority,
                'remarks'     => $remarks,
                'reportID'    => $report->id, // Same database reference to Eilya
                'caretakerID' => $caretakers[array_rand($caretakers)], // Cross-database reference to Taufiq
                'created_at'  => $rescueDate,
                'updated_at'  => $rescueDate,
            ];
        }

        // Use transaction for Eilya's database
        DB::connection('eilya')->beginTransaction();

        try {
            if (!empty($rescues)) {
                $this->command->info('Inserting rescue records into Eilya\'s database...');

                // Insert rescues in chunks
                $chunkSize = 300;
                $totalInserted = 0;

                foreach (array_chunk($rescues, $chunkSize) as $chunk) {
                    DB::connection('eilya')->table('rescue')->insert($chunk);
                    $totalInserted += count($chunk);
                    $this->command->info("  Inserted {$totalInserted} / " . count($rescues) . " rescues...");
                }

                // 🔥 Update related report statuses for successful rescues
                $this->command->info('');
                $this->command->info('Updating report statuses for successful rescues...');

                $successfulRescueReportIDs = DB::connection('eilya')
                    ->table('rescue')
                    ->where('status', 'Success')
                    ->pluck('reportID')
                    ->toArray();

                if (!empty($successfulRescueReportIDs)) {
                    DB::connection('eilya')
                        ->table('report')
                        ->whereIn('id', $successfulRescueReportIDs)
                        ->update([
                            'report_status' => 'Resolved',
                            'updated_at'    => now(),
                        ]);

                    $this->command->info("  Updated " . count($successfulRescueReportIDs) . " reports to 'Resolved' status");
                }
            }

            DB::connection('eilya')->commit();

            $this->command->info('');
            $this->command->info('=================================');
            $this->command->info('✓ Rescue Seeding Completed!');
            $this->command->info('=================================');
            $this->command->info("Total rescue records created: " . count($rescues));
            $this->command->info("Database: Eilya (MySQL)");
            $this->command->info("Caretaker references: Taufiq (PostgreSQL)");
            $this->command->info('');
            $this->command->info('Status Distribution:');
            foreach ($statusCounts as $status => $count) {
                $percentage = count($rescues) > 0 ? round(($count / count($rescues)) * 100, 1) : 0;
                $this->command->info("  - {$status}: {$count} ({$percentage}%)");
            }
            $reportedReportsWithoutRescue = $reports->count() - count($rescues);
            $percentageWithoutRescue = $reports->count() > 0 ? round(($reportedReportsWithoutRescue / $reports->count()) * 100, 1) : 0;
            $this->command->info("  - No Rescue (Pending): {$reportedReportsWithoutRescue} ({$percentageWithoutRescue}%)");
            $this->command->info('=================================');

        } catch (\Exception $e) {
            DB::connection('eilya')->rollBack();

            $this->command->error('');
            $this->command->error('Error seeding rescues: ' . $e->getMessage());
            $this->command->error('Transaction rolled back');

            throw $e;
        }
    }
}
