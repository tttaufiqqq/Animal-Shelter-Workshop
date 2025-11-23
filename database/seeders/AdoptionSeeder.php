<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Booking;
use App\Models\Adoption;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdoptionSeeder extends Seeder
{
    public function run()
    {
        // Get all completed bookings that have animals
        $completedBookings = Booking::whereIn('status', ['completed', 'Completed'])
            ->whereHas('animals')
            ->get();

        $adoptionCount = 0;

        foreach ($completedBookings as $booking) {
            $animals = $booking->animals;

            // Calculate total fee based on number of animals
            $totalFee = $animals->count() * rand(50, 150);
            $feePerAnimal = round($totalFee / $animals->count(), 2);

            // Find existing transaction or create one
            $transaction = Transaction::where('userID', $booking->userID)
                ->where('status', 'Success')
                ->whereDoesntHave('adoptions')
                ->first();

            if (!$transaction) {
                // Create a transaction for this booking
                $transaction = Transaction::create([
                    'amount'       => $totalFee,
                    'status'       => 'Success',
                    'remarks'      => 'Adoption fee for booking #' . $booking->id,
                    'type'         => 'FPX Online Banking',
                    'bill_code'    => 'BILL-' . strtoupper(Str::random(8)),
                    'reference_no' => 'REF-' . $booking->created_at->format('Ymd') . '-' . rand(1000, 9999),
                    'userID'       => $booking->userID,
                    'created_at'   => $booking->created_at,
                    'updated_at'   => $booking->created_at,
                ]);
            }

            // Create one adoption per animal
            foreach ($animals as $animal) {
                Adoption::create([
                    'fee'           => $feePerAnimal,
                    'remarks'       => $animal->name . ' Adopted',
                    'bookingID'     => $booking->id,
                    'transactionID' => $transaction->id,
                    'created_at'    => $booking->created_at,
                    'updated_at'    => $booking->created_at,
                ]);

                // Update animal status
                $animal->update(['adoption_status' => 'Adopted']);
                $adoptionCount++;
            }
        }

        $this->command->info("{$adoptionCount} adoptions created from {$completedBookings->count()} completed bookings!");
    }
}
