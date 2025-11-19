<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Animal;
use App\Models\Booking;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run()
    {
        $users = User::pluck('id')->toArray();
        $animalIds = Animal::pluck('id')->toArray();
        $statuses = ['Pending', 'Completed', 'Confirmed', 'Cancelled'];

        $bookings = [];

        for ($i = 0; $i < 600; $i++) {
            // Random date in past 6 months
            $date = Carbon::now()->subDays(rand(0, 180));
            $time = sprintf("%02d:%02d:00", rand(8, 17), rand(0, 59));

            $bookings[] = [
                'appointment_date' => $date->format('Y-m-d'),
                'appointment_time' => $time,
                'status'           => $statuses[array_rand($statuses)],
                'remarks'          => 'N/A',
                'animalID'         => $animalIds[array_rand($animalIds)],
                'userID'           => $users[array_rand($users)],
                'created_at'       => $date,
                'updated_at'       => $date,
            ];
        }

        // Batch insert for efficiency
        Booking::insert($bookings);

        $this->command->info(count($bookings) . ' bookings created successfully!');
    }
}
