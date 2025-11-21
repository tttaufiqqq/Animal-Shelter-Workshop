<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class ReportSeeder extends Seeder
{
    public function run()
    {
        // Load CSV
        $csvPath = database_path('seeders/report.csv');
        if (!file_exists($csvPath)) {
            $this->command->error('CSV file not found at: ' . $csvPath);
            return;
        }

        $rows = array_map('str_getcsv', file($csvPath));
        $header = array_shift($rows); // first row as header

        $data = [];
        foreach ($rows as $row) {
            $data[] = array_combine($header, $row);
        }

        // Get public users (exclude admin & caretaker)
        $excludedRoles = ['admin', 'caretaker'];
        $userIDs = User::whereDoesntHave('roles', function ($q) use ($excludedRoles) {
            $q->whereIn('name', $excludedRoles);
        })->pluck('id')->toArray();

        if (empty($userIDs)) {
            $this->command->info('No eligible users found!');
            return;
        }

        $reportStatuses = ['Pending', 'In Progress', 'Resolved', 'Closed'];
        $reports = [];

        // Maximum coordinate offset (~1 km)
        $maxOffset = 0.01;

        // Generate 300 reports
        for ($i = 0; $i < 600; $i++) {

            $row = $data[array_rand($data)]; // pick random CSV row

            // Random date in last 2 years
            $createdAt = Carbon::now()->subDays(rand(0, 730));

            // Randomize coordinates slightly
            $latitude  = $row['latitude']  + (rand(-1000, 1000) / 100000); // ±0.01
            $longitude = $row['longitude'] + (rand(-1000, 1000) / 100000); // ±0.01

            $reports[] = [
                'latitude'      => $latitude,
                'longitude'     => $longitude,
                'address'       => $row['address'],
                'city'          => $row['city'],
                'state'         => $row['state'],
                'report_status' => in_array($row['report_status'], $reportStatuses) ? $row['report_status'] : $reportStatuses[array_rand($reportStatuses)],
                'description'   => $row['description'],

                // Assign random public user if not in CSV
                'userID'        => isset($row['userID']) && in_array($row['userID'], $userIDs) ? $row['userID'] : $userIDs[array_rand($userIDs)],

                'created_at'    => $createdAt,
                'updated_at'    => $createdAt,
            ];
        }

        DB::table('report')->insert($reports);

        $this->command->info("300 reports generated successfully with randomized coordinates!");
    }
}
