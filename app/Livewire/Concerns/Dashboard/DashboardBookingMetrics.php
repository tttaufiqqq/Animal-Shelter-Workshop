<?php

namespace App\Livewire\Concerns\Dashboard;

use App\Models\Booking;

trait DashboardBookingMetrics
{
    private function getTotalBookings()
    {
        return $this->safeQuery(
            fn() => Booking::count(),
            0,
            'booking'
        );
    }

    private function getSuccessfulBookings()
    {
        return $this->safeQuery(
            fn() => Booking::where('status', 'Completed')->count(),
            0,
            'booking'
        );
    }

    private function getCancelledBookings()
    {
        return $this->safeQuery(
            fn() => Booking::where('status', 'Cancelled')->count(),
            0,
            'booking'
        );
    }

    private function getRepeatCustomerRate()
    {
        return $this->safeQuery(function() {
            $totalCustomers = Booking::distinct()->count('userID');

            $repeatCustomers = Booking::select('userID')
                ->groupBy('userID')
                ->havingRaw('COUNT(*) > 1')
                ->get()
                ->count();

            return $totalCustomers > 0 ?
                round(($repeatCustomers / $totalCustomers) * 100, 2) : 0;
        }, 0, 'booking');
    }
}
