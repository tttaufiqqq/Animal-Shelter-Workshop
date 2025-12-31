<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class BookingStatusChart extends Component
{
    public $bookingTypeBreakdown;

    public function mount($bookingTypeBreakdown)
    {
        $this->bookingTypeBreakdown = $bookingTypeBreakdown;
    }

    public function render()
    {
        return view('livewire.dashboard.booking-status-chart');
    }
}
