<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Booking;
use App\Models\Animal;
use App\Models\Adoption;
use Illuminate\Support\Facades\DB;

class AdoptionSeeder extends Seeder
{
    public function run()
    {
        // Get all successful transactions
        $transactions = Transaction::where('status', 'Success')->get();

        $usedBookingIds = [];

        foreach ($transactions as $tx) {
            // Only consider bookings where status is completed or Completed
            $booking = Booking::where('userID', $tx->userID)
                ->whereIn('status', ['completed', 'Completed'])
                ->whereNotIn('id', $usedBookingIds)
                ->inRandomOrder()
                ->first();

            if (!$booking) continue;

            // Mark this booking as used
            $usedBookingIds[] = $booking->id;

            // Get related animal
            $animal = $booking->animal;
            if (!$animal) continue;

            // Create adoption record
            Adoption::create([
                'fee'           => $tx->amount,
                'remarks'       => $animal->name . ' Adopted',
                'bookingID'     => $booking->id,
                'transactionID' => $tx->id,
                'created_at'    => $tx->created_at,
                'updated_at'    => $tx->updated_at,
            ]);
        }

        $this->command->info('Adoptions seeded successfully (only completed bookings)!');
    }
}
