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
            $this->command->info("No caretakers found. Seed Users + assign roles first.");
            return;
        }

        $rescueStatuses = ['Scheduled', 'In Progress', 'Success', 'Failed', 'Cancelled'];
        $rescues = [];

        foreach ($reports as $report) {
            $status = fake()->randomElement($rescueStatuses);
            
            // Rescue should be created shortly after report
            $rescueDate = Carbon::parse($report->created_at)->addHours(rand(1, 48));

            $rescues[] = [
                'status'       => $status,
                'remarks'      => fake()->sentence(),
                'reportID'     => $report->id,
                'caretakerID'  => $caretakers[array_rand($caretakers)],
                'created_at'   => $rescueDate,
                'updated_at'   => $rescueDate,
            ];
        }

        DB::table('rescue')->insert($rescues);
        $this->command->info("Rescue data created for all reports.");
    }
}
