<?php

namespace App\Livewire\Concerns\Notifications;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait MapsNotificationTypes
{
    private function mapTransactionNotification($transaction): array
    {
        $userName = 'Guest';
        try {
            $userName = $transaction->user ? $transaction->user->name : 'Guest';
        } catch (\Exception $e) {
            Log::warning('Failed to fetch user for transaction: ' . $e->getMessage());
            $userName = 'User (DB Offline)';
        }

        return [
            'id' => 'transaction_' . $transaction->id,
            'type' => 'transaction',
            'title' => 'New Payment Received',
            'message' => $userName .
                         ' made a ' . strtolower($transaction->type ?? 'payment') .
                         ' of RM' . number_format($transaction->amount, 2),
            'status' => $transaction->status,
            'amount' => $transaction->amount,
            'reference' => $transaction->reference_no,
            'icon' => 'currency',
            'color' => $this->getStatusColor($transaction->status),
            'time' => $transaction->created_at,
            'read' => $this->isNotificationRead('transaction_' . $transaction->id),
            'url' => null,
        ];
    }

    private function mapAdoptionNotification($adoption): array
    {
        $userName = 'Unknown User';
        try {
            $userName = $adoption->transaction && $adoption->transaction->user
                ? $adoption->transaction->user->name
                : 'Unknown User';
        } catch (\Exception $e) {
            Log::warning('Failed to fetch user for adoption: ' . $e->getMessage());
            $userName = 'User (DB Offline)';
        }

        $animalName = 'Unknown Animal';
        try {
            $animalName = $adoption->animal ? $adoption->animal->name : 'Unknown Animal';
        } catch (\Exception $e) {
            Log::warning('Failed to fetch animal for adoption: ' . $e->getMessage());
            $animalName = 'Animal (DB Offline)';
        }

        return [
            'id' => 'adoption_' . $adoption->id,
            'type' => 'adoption',
            'title' => 'New Adoption Completed',
            'message' => $userName . ' adopted ' . $animalName .
                         ' (Fee: RM' . number_format($adoption->fee, 2) . ')',
            'status' => 'completed',
            'amount' => $adoption->fee,
            'icon' => 'heart',
            'color' => 'green',
            'time' => $adoption->created_at,
            'read' => $this->isNotificationRead('adoption_' . $adoption->id),
            'url' => null,
        ];
    }

    private function mapBookingNotification($booking): array
    {
        $userName = 'Unknown User';
        try {
            $userName = $booking->user ? $booking->user->name : 'Unknown User';
        } catch (\Exception $e) {
            Log::warning('Failed to fetch user for booking: ' . $e->getMessage());
            $userName = 'User (DB Offline)';
        }

        $animalCount = 0;
        try {
            // Booking::animals() joins the 'booking'-connection animal_booking
            // table against the 'animals'-connection animal table in one
            // query - a real cross-server join MySQL/MariaDB can't do (same
            // bug fixed in HandlesPayment::callback(), see handoff). Count
            // via the same-connection animalBookings() pivot relation instead.
            $animalCount = $booking->animalBookings->count();
        } catch (\Exception $e) {
            Log::warning('Failed to fetch animals for booking: ' . $e->getMessage());
        }

        $statusText = $booking->status ?? 'Pending';

        return [
            'id' => 'booking_' . $booking->id,
            'type' => 'booking',
            'title' => 'New Booking ' . $statusText,
            'message' => $userName . ' booked ' . ($animalCount > 0 ? $animalCount : '?') .
                         ' animal' . ($animalCount != 1 ? 's' : '') .
                         ' for ' . Carbon::parse($booking->appointment_date)->format('M d, Y'),
            'status' => $statusText,
            'icon' => 'calendar',
            'color' => $statusText === 'Confirmed' ? 'blue' : 'yellow',
            'time' => $booking->created_at,
            'read' => $this->isNotificationRead('booking_' . $booking->id),
            'url' => null,
        ];
    }
}
