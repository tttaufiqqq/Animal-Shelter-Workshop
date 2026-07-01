<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Database\Seeders\Concerns\RescueSeeder\BuildsRescues;
use Database\Seeders\Concerns\RescueSeeder\SyncsReportStatuses;

class RescueSeeder extends Seeder
{
    use BuildsRescues, SyncsReportStatuses;

    public function run(): void
    {
        $this->command->info('Starting Rescue Seeder...');
        $this->command->info('========================================');

        $this->command->info("Fetching reports from Eilya - Stray Reporting database...");
        $reports = DB::connection('reporting')->table('report')->get();
        if ($reports->isEmpty()) {
            $this->command->error("No reports found. Seed Reports first.");
            return;
        }
        $this->command->info("Found " . $reports->count() . " reports");

        $this->command->info("Fetching caretakers from Taufiq - Users Management database...");
        $caretakers = DB::connection('users')->table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'caretaker')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('users.id')->toArray();

        if (empty($caretakers)) {
            $this->command->error("No caretakers found. Seed Users & assign caretaker role first.");
            return;
        }
        $this->command->info("Found " . count($caretakers) . " caretakers");

        $this->command->info('Generating rescue records...');
        [$rescues, $statusCounts] = $this->buildRescues($reports, $caretakers);

        DB::connection('reporting')->beginTransaction();
        try {
            if (!empty($rescues)) {
                $this->command->info("Inserting rescue records into Eilya - Stray Reporting - Stray Reporting database...");
                $totalInserted = 0;
                foreach (array_chunk($rescues, 300) as $chunk) {
                    DB::connection('reporting')->table('rescue')->insert($chunk);
                    $totalInserted += count($chunk);
                    $this->command->info("  Inserted {$totalInserted} / " . count($rescues) . " rescues...");
                }

                $this->command->info('');
                $this->syncReportStatuses();
            }

            DB::connection('reporting')->commit();

            $this->command->info('');
            $this->command->info('=================================');
            $this->command->info('✓ Rescue Seeding Completed!');
            $this->command->info('=================================');
            $this->command->info("Total rescue records created: " . count($rescues));
            $this->command->info("Database: Eilya - Stray Reporting (MariaDB) | Caretaker references: Taufiq - Users Management (PostgreSQL)");
            $this->command->info('');
            $this->command->info('Status Distribution:');
            foreach ($statusCounts as $status => $count) {
                $percentage = count($rescues) > 0 ? round(($count / count($rescues)) * 100, 1) : 0;
                $this->command->info("  - {$status}: {$count} ({$percentage}%)");
            }
            $withoutRescue = $reports->count() - count($rescues);
            $pctWithout    = $reports->count() > 0 ? round(($withoutRescue / $reports->count()) * 100, 1) : 0;
            $this->command->info("  - No Rescue (Pending): {$withoutRescue} ({$pctWithout}%)");
            $this->command->info('=================================');

        } catch (\Exception $e) {
            DB::connection('reporting')->rollBack();
            $this->command->error('Error seeding rescues: ' . $e->getMessage());
            $this->command->error('Transaction rolled back');
            throw $e;
        }
    }
}
