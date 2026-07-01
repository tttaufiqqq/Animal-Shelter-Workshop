<?php

namespace App\Livewire;

use App\Livewire\Concerns\Dashboard\DashboardSqlHelpers;
use App\Livewire\Concerns\Dashboard\DashboardBookingMetrics;
use App\Livewire\Concerns\Dashboard\DashboardRevenueMetrics;
use App\Livewire\Concerns\Dashboard\DashboardAdminStats;
use App\DatabaseErrorHandler;
use Livewire\Component;

class Dashboard extends Component
{
    use DatabaseErrorHandler,
        DashboardSqlHelpers,
        DashboardBookingMetrics,
        DashboardRevenueMetrics,
        DashboardAdminStats;

    public function render()
    {
        $totalBookings = $this->getTotalBookings();
        $successfulBookings = $this->getSuccessfulBookings();
        $cancelledBookings = $this->getCancelledBookings();
        $bookingSuccessRate = $totalBookings > 0 ?
            round(($successfulBookings / $totalBookings) * 100, 2) : 0;
        $repeatCustomerRate = $this->getRepeatCustomerRate();

        $topAnimals = $this->getTopAnimalsByRevenue();
        $bookingTypeBreakdown = $this->getBookingTypeBreakdown();
        $bookingsByMonth = $this->getBookingsByMonth();
        $volumeVsValue = $this->getVolumeVsAverageValue();

        $userStats = $this->getUserManagementStats();
        $auditSummary = $this->getAuditSummary();

        return view('livewire.dashboard', [
            'totalBookings' => $totalBookings,
            'successfulBookings' => $successfulBookings,
            'cancelledBookings' => $cancelledBookings,
            'bookingSuccessRate' => $bookingSuccessRate,
            'repeatCustomerRate' => $repeatCustomerRate,
            'topAnimals' => $topAnimals,
            'bookingTypeBreakdown' => $bookingTypeBreakdown,
            'bookingsByMonth' => $bookingsByMonth,
            'volumeVsValue' => $volumeVsValue,
            'userStats' => $userStats,
            'auditSummary' => $auditSummary,
        ]);
    }
}
