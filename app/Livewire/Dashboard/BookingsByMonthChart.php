<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class BookingsByMonthChart extends Component
{
    public $bookingsByMonth;

    public function mount($bookingsByMonth)
    {
        $this->bookingsByMonth = $bookingsByMonth;
    }

    public function render()
    {
        return view('livewire.dashboard.bookings-by-month-chart');
    }
}
