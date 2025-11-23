<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Animal;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run()
    {
        $users = User::pluck('id')->toArray();
        $animalIds = Animal::pluck('id')->toArray();
        $statuses = ['Pending', 'Completed', 'Confirmed', 'Cancelled'];

        for ($i = 0; $i < 600; $i++) {
            // Random date in past 6 months
            $date = Carbon::now()->subDays(rand(0, 180));
            $time = sprintf("%02d:%02d:00", rand(8, 17), rand(0, 59));

            // Create the booking
            $booking = Booking::create([
                'appointment_date' => $date->format('Y-m-d'),
                'appointment_time' => $time,
                'status'           => $statuses[array_rand($statuses)],
                'remarks'          => 'N/A',
                'userID'           => $users[array_rand($users)],
                'created_at'       => $date,
                'updated_at'       => $date,
            ]);

            // Attach 1-3 random animals to this booking via pivot table
            $randomAnimalIds = array_rand(array_flip($animalIds), rand(1, min(3, count($animalIds))));
            $randomAnimalIds = is_array($randomAnimalIds) ? $randomAnimalIds : [$randomAnimalIds];

            foreach ($randomAnimalIds as $animalId) {
                DB::table('animal_booking')->insert([
                    'bookingID'  => $booking->id,
                    'animalID'   => $animalId,
                    'remarks'    => null,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }

        $this->command->info('600 bookings with animals created successfully!');
    }
}
