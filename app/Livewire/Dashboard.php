<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Transaction;
use App\Models\Adoption;
use App\Models\Animal;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\DatabaseErrorHandler;

class Dashboard extends Component
{
    use DatabaseErrorHandler;

    public function render()
    {

        // Key Metrics (all from Danish's database)
        $totalBookings = $this->getTotalBookings();
        $successfulBookings = $this->getSuccessfulBookings();
        $cancelledBookings = $this->getCancelledBookings();
        $bookingSuccessRate = $totalBookings > 0 ?
            round(($successfulBookings / $totalBookings) * 100, 2) : 0;
        $repeatCustomerRate = $this->getRepeatCustomerRate();

        // Top Animals by Revenue (cross-database query: Danish + Shafiqah)
        $topAnimals = $this->getTopAnimalsByRevenue();

        // Booking Type Breakdown (Danish's database)
        $bookingTypeBreakdown = $this->getBookingTypeBreakdown();

        // Bookings by Month (Danish's database)
        $bookingsByMonth = $this->getBookingsByMonth();

        // Booking Volume vs Average Value (Danish's database)
        $volumeVsValue = $this->getVolumeVsAverageValue();

        // User Management Statistics (Taufiq's database - using stored procedures)
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

    /**
     * Get database-specific MONTH extraction expression
     */
    private function getMonthExpression($column, $connection = 'danish')
    {
        $driver = DB::connection($connection)->getDriverName();

        return match($driver) {
            'pgsql' => "EXTRACT(MONTH FROM {$column})",
            'mysql' => "MONTH({$column})",
            'sqlite' => "CAST(strftime('%m', {$column}) AS INTEGER)",
            'sqlsrv' => "MONTH({$column})",
            default => "MONTH({$column})",
        };
    }

    /**
     * Get database-specific YEAR extraction expression
     */
    private function getYearExpression($column, $connection = 'danish')
    {
        $driver = DB::connection($connection)->getDriverName();

        return match($driver) {
            'pgsql' => "EXTRACT(YEAR FROM {$column})",
            'mysql' => "YEAR({$column})",
            'sqlite' => "strftime('%Y', {$column})",
            'sqlsrv' => "YEAR({$column})",
            default => "YEAR({$column})",
        };
    }

    /**
     * Get total bookings from Danish's database (all time)
     */
    private function getTotalBookings()
    {
        return $this->safeQuery(
            fn() => Booking::count(),
            0,
            'danish'
        );
    }

    /**
     * Get successful bookings from Danish's database (all time)
     */
    private function getSuccessfulBookings()
    {
        return $this->safeQuery(
            fn() => Booking::where('status', 'Completed')->count(),
            0,
            'danish'
        );
    }

    /**
     * Get cancelled bookings from Danish's database (all time)
     */
    private function getCancelledBookings()
    {
        return $this->safeQuery(
            fn() => Booking::where('status', 'Cancelled')->count(),
            0,
            'danish'
        );
    }

    /**
     * Get repeat customer rate from Danish's database (all time)
     */
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
        }, 0, 'danish');
    }

    /**
     * Get top animals by revenue (all time)
     * Complex cross-database query: Danish (adoptions, bookings) + Shafiqah (animals)
     */
    private function getTopAnimalsByRevenue()
    {
        // Pre-check both databases before attempting query
        if (!$this->isDatabaseAvailable('danish') || !$this->isDatabaseAvailable('shafiqah')) {
            Log::debug('Skipping getTopAnimalsByRevenue - required databases offline');
            return collect();
        }

        try {
            // Step 1: Get all adoptions with animal IDs from Danish's database
            $adoptionData = Adoption::on('danish')
                ->join('animal_booking', 'adoption.bookingID', '=', 'animal_booking.bookingID')
                ->select('animal_booking.animalID', 'adoption.fee')
                ->get();

            // Step 2: Group by animal ID and sum fees
            $animalRevenues = $adoptionData->groupBy('animalID')->map(function($group) {
                return $group->sum('fee');
            });

            // Step 3: Get animal details from Shafiqah's database
            $animalIds = $animalRevenues->keys()->toArray();

            if (empty($animalIds)) {
                return collect();
            }

            $animals = Animal::on('shafiqah')
                ->whereIn('id', $animalIds)
                ->get()
                ->keyBy('id');

            // Step 4: Combine data and group by species
            $speciesRevenue = collect();
            foreach ($animalRevenues as $animalId => $revenue) {
                $animal = $animals->get($animalId);
                if ($animal) {
                    $species = $animal->species;
                    $current = $speciesRevenue->get($species, 0);
                    $speciesRevenue->put($species, $current + $revenue);
                }
            }

            // Step 5: Sort and limit
            $topSpecies = $speciesRevenue
                ->sortDesc()
                ->take(5);

            // Step 6: Calculate percentages
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

    /**
     * Get booking type breakdown from Danish's database (all time)
     */
    private function getBookingTypeBreakdown()
    {
        return $this->safeQuery(function() {
            $total = Booking::count();

            $breakdown = Booking::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->map(function($item) use ($total) {
                    $item->percentage = $total > 0 ?
                        round(($item->count / $total) * 100, 2) : 0;
                    return $item;
                });

            return $breakdown;
        }, collect([]), 'danish');
    }

    /**
     * Get bookings by month from Danish's database (last 12 months)
     */
    private function getBookingsByMonth()
    {
        return $this->safeQuery(function() {
            $monthExpression = $this->getMonthExpression('created_at', 'danish');
            $yearExpression = $this->getYearExpression('created_at', 'danish');

            // Get last 12 months of data
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
        }, collect([]), 'danish');
    }

    /**
     * Get volume vs average value from Danish's database (last 6 months)
     */
    private function getVolumeVsAverageValue()
    {
        return $this->safeQuery(function() {
            $monthExpression = $this->getMonthExpression('created_at', 'danish');
            $yearExpression = $this->getYearExpression('created_at', 'danish');

            // Get last 6 months of data
            $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();

            $results = Adoption::on('danish')
                ->where('created_at', '>=', $sixMonthsAgo)
                ->selectRaw("{$yearExpression} as year")
                ->selectRaw("{$monthExpression} as month")
                ->selectRaw('COUNT(*) as volume')
                ->selectRaw('AVG(fee) as avg_value')
                ->groupBy(DB::raw($yearExpression), DB::raw($monthExpression))
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            return $results->map(function($item) {
                $item->month_name = Carbon::create($item->year, $item->month, 1)->format('M Y');
                $item->avg_value = round((float) $item->avg_value, 2);
                return $item;
            });
        }, collect([]), 'danish');
    }

    /**
     * Get additional statistics for enhanced dashboard (all time)
     */
    public function getAdditionalStats()
    {
        try {
            return [
                'pending_bookings' => Booking::where('status', 'Pending')->count(),
                'confirmed_bookings' => Booking::where('status', 'Confirmed')->count(),
                'total_revenue' => Adoption::sum('fee'),
                'average_booking_value' => Adoption::avg('fee'),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting additional stats: ' . $e->getMessage());

            return [
                'pending_bookings' => 0,
                'confirmed_bookings' => 0,
                'total_revenue' => 0,
                'average_booking_value' => 0,
            ];
        }
    }

    /**
     * Get user management statistics using PostgreSQL stored procedures
     * From Taufiq's database
     */
    private function getUserManagementStats()
    {
        return $this->safeQuery(function() {
            // Get user account statistics using stored procedure
            $userStats = DB::connection('taufiq')->select('SELECT * FROM get_user_account_stats()');
            $stats = $userStats[0] ?? null;

            // Get adopter profile statistics using stored procedure
            $adopterStats = DB::connection('taufiq')->select('SELECT * FROM get_adopter_profile_stats()');
            $adopter = $adopterStats[0] ?? null;

            // Get high-risk users count
            $highRiskUsers = DB::connection('taufiq')->select('SELECT COUNT(*) as count FROM get_high_risk_users(3)');
            $highRiskCount = $highRiskUsers[0]->count ?? 0;

            return (object) [
                'total_users' => $stats->total_users ?? 0,
                'active_users' => $stats->active_users ?? 0,
                'suspended_users' => $stats->suspended_users ?? 0,
                'locked_users' => $stats->locked_users ?? 0,
                'users_with_profiles' => $stats->users_with_profiles ?? 0,
                'avg_failed_attempts' => $stats->avg_failed_login_attempts ?? 0,
                'total_adopter_profiles' => $adopter->total_profiles ?? 0,
                'high_risk_users' => $highRiskCount,
            ];
        }, (object) [
            'total_users' => 0,
            'active_users' => 0,
            'suspended_users' => 0,
            'locked_users' => 0,
            'users_with_profiles' => 0,
            'avg_failed_attempts' => 0,
            'total_adopter_profiles' => 0,
            'high_risk_users' => 0,
        ], 'taufiq');
    }

    /**
     * Get audit log summary using PostgreSQL stored procedure
     * From Taufiq's database
     */
    private function getAuditSummary()
    {
        return $this->safeQuery(function() {
            $results = DB::connection('taufiq')->select('SELECT * FROM get_audit_summary_by_category(30)');

            return collect($results)->map(function($item) {
                return (object) [
                    'category' => $item->category,
                    'total_actions' => $item->total_actions,
                    'success_count' => $item->success_count,
                    'failure_count' => $item->failure_count,
                    'unique_users' => $item->unique_users,
                    'most_common_action' => $item->most_common_action,
                    'success_rate' => $item->total_actions > 0
                        ? round(($item->success_count / $item->total_actions) * 100, 2)
                        : 0,
                ];
            });
        }, collect([]), 'taufiq');
    }
}
