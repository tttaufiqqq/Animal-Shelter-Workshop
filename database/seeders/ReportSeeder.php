<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class ReportSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Malaysia states with rough coordinate bounds
        $locations = [
            'Selangor'       => ['lat_min' => 2.70, 'lat_max' => 3.60, 'lng_min' => 101.30, 'lng_max' => 101.90],
            'Kuala Lumpur'   => ['lat_min' => 3.00, 'lat_max' => 3.25, 'lng_min' => 101.60, 'lng_max' => 101.80],
            'Johor'          => ['lat_min' => 1.30, 'lat_max' => 2.70, 'lng_min' => 103.30, 'lng_max' => 104.20],
            'Penang'         => ['lat_min' => 5.20, 'lat_max' => 5.50, 'lng_min' => 100.20, 'lng_max' => 100.50],
            'Perak'          => ['lat_min' => 4.00, 'lat_max' => 5.80, 'lng_min' => 100.60, 'lng_max' => 101.20],
            'Sabah'          => ['lat_min' => 5.70, 'lat_max' => 6.40, 'lng_min' => 116.00, 'lng_max' => 116.30],
            'Sarawak'        => ['lat_min' => 1.20, 'lat_max' => 1.70, 'lng_min' => 110.20, 'lng_max' => 110.50],
            'Melaka'         => ['lat_min' => 2.10, 'lat_max' => 2.40, 'lng_min' => 102.10, 'lng_max' => 102.30]
        ];

        $reportStatuses = ['Pending', 'In Progress', 'Resolved', 'Closed'];

        // Get all public users (exclude admin and caretaker)
        $excludedRoles = ['admin', 'caretaker'];
        $userIDs = User::whereDoesntHave('roles', function ($query) use ($excludedRoles) {
            $query->whereIn('name', $excludedRoles);
        })->pluck('id')->toArray();

        if (empty($userIDs)) {
            $this->command->info('No eligible public users found!');
            return;
        }

        $reports = [];

        // Create 150 random reports over the past 2 years
        for ($i = 0; $i < 150; $i++) {
            $state = $faker->randomElement(array_keys($locations));
            $loc = $locations[$state];

            // Random date in past 2 years
            $createdAt = Carbon::now()->subDays(rand(0, 365 * 2));

            $reports[] = [
                'latitude'      => $faker->randomFloat(8, $loc['lat_min'], $loc['lat_max']),
                'longitude'     => $faker->randomFloat(8, $loc['lng_min'], $loc['lng_max']),
                'address'       => $faker->streetAddress(),
                'city'          => $faker->city(),
                'state'         => $state,
                'report_status' => $faker->randomElement($reportStatuses),
                'description'   => $faker->sentence(10),
                'userID'        => $faker->randomElement($userIDs),
                'created_at'    => $createdAt,
                'updated_at'    => $createdAt,
            ];
        }

        DB::table('report')->insert($reports);

        $this->command->info(count($reports) . ' reports generated successfully!');
    }
}
