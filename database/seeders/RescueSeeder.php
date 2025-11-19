<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rescue;
use App\Models\Report;
use App\Models\User;

class RescueSeeder extends Seeder
{
    public function run()
    {
        // Get all reports
        $reports = Report::all();

        if ($reports->isEmpty()) {
            $this->command->info("No reports found. Seed Reports first.");
            return;
        }

        // Get all caretakers using Spatie roles
        $caretakers = User::role(['caretaker'])->get();

        if ($caretakers->isEmpty()) {
            $this->command->info("No caretakers found. Seed Users + assign roles first.");
            return;
        }

        foreach ($reports as $report) {

            $status = fake()->randomElement([
                'Scheduled',
                'In Progress',
                'Success',
                'Failed'
            ]);

            Rescue::create([
                'status'       => $status,
                'remarks'      => fake()->sentence(),
                'reportID'     => $report->id,
                'caretakerID'  => $caretakers->random()->id,
            ]);
        }

        $this->command->info("Rescue data created for all reports.");
    }
}
