<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Transaction;
use App\Models\Adoption;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public $selectedYear;
    public $selectedCategory = 'all';
    public $years = [];
    
    public function mount()
    {
        $this->selectedYear = date('Y');
        $this->years = Booking::select(DB::raw('EXTRACT(YEAR FROM created_at) as year'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
    }

    public function render()
    {
        // Key Metrics
        $totalBookings = $this->getTotalBookings();
        $successfulBookings = $this->getSuccessfulBookings();
        $cancelledBookings = $this->getCancelledBookings();
        $bookingSuccessRate = $totalBookings > 0 ? 
            round(($successfulBookings / $totalBookings) * 100, 2) : 0;
        $repeatCustomerRate = $this->getRepeatCustomerRate();

        // Top Animals by Revenue
        $topAnimals = $this->getTopAnimalsByRevenue();

        // Booking Type Breakdown
        $bookingTypeBreakdown = $this->getBookingTypeBreakdown();

        // Bookings by Month
        $bookingsByMonth = $this->getBookingsByMonth();

        // Booking Volume vs Average Value
        $volumeVsValue = $this->getVolumeVsAverageValue();
// dd([
//         'bookingTypeBreakdown' => $bookingTypeBreakdown,
//         'bookingsByMonth' => $bookingsByMonth,
//         'volumeVsValue' => $volumeVsValue,
//         'topAnimals' => $topAnimals
//     ]);

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
        ]);
    }

    private function getTotalBookings()
    {
        return Booking::whereRaw('EXTRACT(YEAR FROM created_at) = ?', [$this->selectedYear])->count();
    }

    private function getSuccessfulBookings()
    {
        return Booking::whereRaw('EXTRACT(YEAR FROM created_at) = ?', [$this->selectedYear])
            ->where('status', 'Completed')
            ->count();
    }

    private function getCancelledBookings()
    {
        return Booking::whereRaw('EXTRACT(YEAR FROM created_at) = ?', [$this->selectedYear])
            ->where('status', 'Cancelled')
            ->count();
    }

    private function getRepeatCustomerRate()
    {
        $totalCustomers = Booking::whereRaw('EXTRACT(YEAR FROM created_at) = ?', [$this->selectedYear])
            ->distinct('userID')
            ->count('userID');

        $repeatCustomers = Booking::whereRaw('EXTRACT(YEAR FROM created_at) = ?', [$this->selectedYear])
            ->select('userID')
            ->groupBy('userID')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        return $totalCustomers > 0 ? 
            round(($repeatCustomers / $totalCustomers) * 100, 2) : 0;
    }

    private function getTopAnimalsByRevenue()
    {
        return Adoption::join('booking', 'adoption.bookingID', '=', 'booking.id')
            ->join('animal', 'booking.animalID', '=', 'animal.id')
            ->whereRaw('EXTRACT(YEAR FROM adoption.created_at) = ?', [$this->selectedYear])
            ->select('animal.species as name', DB::raw('SUM(adoption.fee) as total_revenue'))
            ->groupBy('animal.species')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                $totalRevenue = Adoption::whereRaw('EXTRACT(YEAR FROM created_at) = ?', [$this->selectedYear])
                    ->sum('fee');
                $item->percentage = $totalRevenue > 0 ? 
                    round(($item->total_revenue / $totalRevenue) * 100, 2) : 0;
                return $item;
            });
    }

    private function getBookingTypeBreakdown()
    {

        $total = Booking::whereRaw('EXTRACT(YEAR FROM created_at) = ?', [$this->selectedYear])->count();
        $breakdown = Booking::whereRaw('EXTRACT(YEAR FROM created_at) = ?', [$this->selectedYear])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function($item) use ($total) {
                $item->percentage = $total > 0 ? 
                    round(($item->count / $total) * 100, 2) : 0;
                return $item;
            });

        return $breakdown;
    }

    private function getBookingsByMonth()
    {
        return Booking::whereRaw('EXTRACT(YEAR FROM created_at) = ?', [$this->selectedYear])
            ->select(
                DB::raw('EXTRACT(MONTH FROM created_at)::integer as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function($item) {
                $item->month_name = Carbon::create()->month((int)$item->month)->format('F');
                return $item;
            });
    }

    private function getVolumeVsAverageValue()
    {
        return Adoption::join('booking', 'adoption.bookingID', '=', 'booking.id')
            ->whereRaw('EXTRACT(YEAR FROM adoption.created_at) = ?', [$this->selectedYear])
            ->select(
                DB::raw('EXTRACT(MONTH FROM adoption.created_at)::integer as month'),
                DB::raw('COUNT(*) as volume'),
                DB::raw('AVG(adoption.fee) as avg_value')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function($item) {
                $item->month_name = Carbon::create()->month((int)$item->month)->format('M');
                return $item;
            });
    }
}