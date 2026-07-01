<?php

namespace App\Livewire\Concerns\Dashboard;

use App\Models\Booking;
use App\Models\Adoption;
use App\Models\Animal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait DashboardRevenueMetrics
{
    private function getTopAnimalsByRevenue()
    {
        if (!$this->isDatabaseAvailable('booking') || !$this->isDatabaseAvailable('animals')) {
            Log::debug('Skipping getTopAnimalsByRevenue - required databases offline');
            return collect();
        }

        try {
            $adoptionData = Adoption::on('booking')
                ->join('animal_booking', 'adoption.bookingID', '=', 'animal_booking.bookingID')
                ->select('animal_booking.animalID', 'adoption.fee')
                ->get();

            $animalRevenues = $adoptionData->groupBy('animalID')->map(function($group) {
                return $group->sum('fee');
            });

            $animalIds = $animalRevenues->keys()->toArray();

            if (empty($animalIds)) {
                return collect();
            }

            $animals = Animal::on('animals')->whereIn('id', $animalIds)->get()->keyBy('id');

            $speciesRevenue = collect();
            foreach ($animalRevenues as $animalId => $revenue) {
                $animal = $animals->get($animalId);
                if ($animal) {
                    $species = $animal->species;
                    $current = $speciesRevenue->get($species, 0);
                    $speciesRevenue->put($species, $current + $revenue);
                }
            }

            $topSpecies = $speciesRevenue->sortDesc()->take(5);
            $totalRevenue = $topSpecies->sum();

            return $topSpecies->map(function($revenue, $species) use ($totalRevenue) {
                return (object) [
                    'name' => $species,
                    'total_revenue' => $revenue,
                    'percentage' => $totalRevenue > 0
                        ? round(($revenue / $totalRevenue) * 100, 2)
                        : 0,
                ];
            })->values();
        } catch (\Exception $e) {
            Log::error('Error getting top animals by revenue: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return collect();
        }
    }

    private function getBookingTypeBreakdown()
    {
        return $this->safeQuery(function() {
            $total = Booking::count();

            return Booking::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->map(function($item) use ($total) {
                    $item->percentage = $total > 0 ?
                        round(($item->count / $total) * 100, 2) : 0;
                    return $item;
                });
        }, collect([]), 'booking');
    }

    private function getBookingsByMonth()
    {
        return $this->safeQuery(function() {
            $monthExpression = $this->getMonthExpression('created_at', 'booking');
            $yearExpression = $this->getYearExpression('created_at', 'booking');
            $twelveMonthsAgo = Carbon::now()->subMonths(11)->startOfMonth();

            return Booking::where('created_at', '>=', $twelveMonthsAgo)
                ->selectRaw("{$yearExpression} as year")
                ->selectRaw("{$monthExpression} as month")
                ->selectRaw('COUNT(*) as count')
                ->groupBy(DB::raw($yearExpression), DB::raw($monthExpression))
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->map(function($item) {
                    $item->month_name = Carbon::create($item->year, $item->month, 1)->format('M Y');
                    return $item;
                });
        }, collect([]), 'booking');
    }

    private function getVolumeVsAverageValue()
    {
        return $this->safeQuery(function() {
            $monthExpression = $this->getMonthExpression('created_at', 'booking');
            $yearExpression = $this->getYearExpression('created_at', 'booking');
            $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();

            return Adoption::on('booking')
                ->where('created_at', '>=', $sixMonthsAgo)
                ->selectRaw("{$yearExpression} as year")
                ->selectRaw("{$monthExpression} as month")
                ->selectRaw('COUNT(*) as volume')
                ->selectRaw('AVG(fee) as avg_value')
                ->groupBy(DB::raw($yearExpression), DB::raw($monthExpression))
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->map(function($item) {
                    $item->month_name = Carbon::create($item->year, $item->month, 1)->format('M Y');
                    $item->avg_value = round((float) $item->avg_value, 2);
                    return $item;
                });
        }, collect([]), 'booking');
    }
}
