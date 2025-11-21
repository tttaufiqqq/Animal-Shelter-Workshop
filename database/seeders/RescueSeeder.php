<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RescueSeeder extends Seeder
{
    public function run()
    {
        $reports = DB::table('report')->get();

        if ($reports->isEmpty()) {
            $this->command->info("No reports found. Seed Reports first.");
            return;
        }

        // Get all caretakers
        $caretakers = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'caretaker')
            ->pluck('users.id')
            ->toArray();

        if (empty($caretakers)) {
            $this->command->info("No caretakers found. Seed Users & assign caretaker role first.");
            return;
        }

        // Other statuses (except Success)
        $otherStatuses = ['Scheduled', 'In Progress', 'Failed', 'Pending'];

        $rescues = [];

        foreach ($reports as $report) {

            // 🎯 Keep 20% of reports PENDING (no rescue record)
            if (rand(1, 100) <= 20) { 
                continue;
            }

            // 🎯 SUCCESS = 40% chance
            if (rand(1, 100) <= 40) {
                $status = 'Success';
            } 
            else {
                // Remaining 60% get random non-success status
                $status = $otherStatuses[array_rand($otherStatuses)];
            }

            // Rescue should be created some hours after the report
            $rescueDate = Carbon::parse($report->created_at)->addHours(rand(1, 48));

            $rescues[] = [
                'status'      => $status,
                'remarks'     => fake()->sentence(),
                'reportID'    => $report->id,
                'caretakerID' => $caretakers[array_rand($caretakers)],
                'created_at'  => $rescueDate,
                'updated_at'  => $rescueDate,
            ];
        }

        if (!empty($rescues)) {
            DB::table('rescue')->insert($rescues);
        }

        $this->command->info(count($rescues) . " rescue records created (approx. 40% success, 20% pending).");
    }
}
