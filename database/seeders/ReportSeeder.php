<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

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

        $speciesList = ['Cat', 'Dog'];
        $statuses = ['Not Adopted', 'Adopted'];

        for ($i = 0; $i < 400; $i++) {

            // choose random Malaysian region
            $state = $faker->randomElement(array_keys($locations));
            $loc = $locations[$state];

            // generate Malaysia-only coordinates
            $lat = $faker->randomFloat(8, $loc['lat_min'], $loc['lat_max']);
            $lng = $faker->randomFloat(8, $loc['lng_min'], $loc['lng_max']);

            DB::table('report')->insert([
                'latitude'      => $lat,
                'longitude'     => $lng,
                'address'       => $faker->streetAddress(),
                'city'          => $faker->city(),
                'state'         => $state,
                'report_status' => $faker->randomElement($statuses),
                'description'   => $faker->sentence(10),
                'userID'        => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }
}
