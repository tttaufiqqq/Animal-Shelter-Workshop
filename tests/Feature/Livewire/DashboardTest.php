<?php

use App\Livewire\Dashboard;
use App\Livewire\Dashboard\DatabaseWarningBanner;
use App\Livewire\Dashboard\YearFilter;
use App\Models\Adoption;
use App\Models\AuditLog;
use App\Models\Booking;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;

/**
 * NOTE: app/Livewire/Dashboard/{YearFilter,DatabaseWarningBanner,MetricCard,
 * *Chart}.php and their README document an aspirational sub-component
 * architecture ("Migration from Monolithic Dashboard") that was never
 * actually wired up - grep confirms zero `@livewire('dashboard.*')` calls
 * anywhere in resources/views, and the real App\Livewire\Dashboard (this
 * file's main subject) has no $listeners/$selectedYear to receive
 * YearFilter's dispatched event. Same "phantom feature" shape as the
 * RoleSeeder/inventory-recommendation findings in earlier phases. YearFilter
 * and DatabaseWarningBanner are still tested below in isolation, since they
 * are simple, real, self-contained components - just not currently reachable
 * from any route.
 */
function forceBookingOfflineForDashboard(): void
{
    Config::set('database.connections.booking.host', '127.0.0.1');
    Config::set('database.connections.booking.port', 1);
}

function forceUsersOfflineForDashboard(): void
{
    Config::set('database.connections.users.host', '127.0.0.1');
    Config::set('database.connections.users.port', 1);
}

it('computes booking totals and the success rate from real booking rows', function () {
    Booking::factory()->create(['status' => 'Completed']);
    Booking::factory()->create(['status' => 'Completed']);
    Booking::factory()->create(['status' => 'Cancelled']);
    Booking::factory()->create(['status' => 'Pending']);

    Livewire::test(Dashboard::class)
        ->assertViewHas('totalBookings', 4)
        ->assertViewHas('successfulBookings', 2)
        ->assertViewHas('cancelledBookings', 1)
        ->assertViewHas('bookingSuccessRate', 50.0);
});

it('computes the repeat customer rate across distinct booking users', function () {
    $repeatUser = $this->makeAdopter();
    $oneTimeUser = $this->makeAdopter();
    Booking::factory()->count(2)->create(['userID' => $repeatUser->id]);
    Booking::factory()->create(['userID' => $oneTimeUser->id]);

    // 1 of 2 distinct customers is a repeat customer -> 50%.
    Livewire::test(Dashboard::class)
        ->assertViewHas('repeatCustomerRate', 50.0);
});

it('breaks bookings down by status with percentages', function () {
    Booking::factory()->create(['status' => 'Confirmed']);
    Booking::factory()->create(['status' => 'Confirmed']);
    Booking::factory()->create(['status' => 'Pending']);

    Livewire::test(Dashboard::class)
        ->assertViewHas('bookingTypeBreakdown', function ($breakdown) {
            $confirmed = $breakdown->firstWhere('status', 'Confirmed');
            $pending = $breakdown->firstWhere('status', 'Pending');

            return $confirmed->count == 2 && $confirmed->percentage == 66.67
                && $pending->count == 1 && $pending->percentage == 33.33;
        });
});

it('groups top-animals-by-revenue by species via the same-connection animal_booking join', function () {
    $dog = $this->makeAnimalWithProfile(['species' => 'Dog']);
    $cat = $this->makeAnimalWithProfile(['species' => 'Cat']);
    $user = $this->makeAdopter();

    $dogBooking = $this->makeBookingFor($user, [$dog]);
    $catBooking = $this->makeBookingFor($user, [$cat]);
    Adoption::factory()->create(['bookingID' => $dogBooking->id, 'animalID' => $dog->id, 'fee' => 100, 'transactionID' => null]);
    Adoption::factory()->create(['bookingID' => $catBooking->id, 'animalID' => $cat->id, 'fee' => 50, 'transactionID' => null]);

    Livewire::test(Dashboard::class)
        ->assertViewHas('topAnimals', function ($topAnimals) {
            $dogRevenue = $topAnimals->firstWhere('name', 'Dog');
            $catRevenue = $topAnimals->firstWhere('name', 'Cat');

            return $dogRevenue->total_revenue == 100 && $catRevenue->total_revenue == 50;
        });
});

it('returns admin user-management stats sourced from real user rows', function () {
    $this->makeAdopter(['account_status' => 'active']);
    $this->makeAdopter(['account_status' => 'suspended']);
    $this->makeAdopter(['account_status' => 'locked']);

    Livewire::test(Dashboard::class)
        ->assertViewHas('userStats', function ($stats) {
            return $stats->total_users >= 3
                && $stats->suspended_users >= 1
                && $stats->locked_users >= 1;
        });
});

it('returns the audit summary grouped by category with a computed success rate', function () {
    AuditLog::create([
        'category' => 'payment', 'action' => 'bill_paid', 'user_name' => 'A',
        'user_email' => 'a@example.com', 'status' => 'success', 'performed_at' => now(),
    ]);
    AuditLog::create([
        'category' => 'payment', 'action' => 'bill_paid', 'user_name' => 'B',
        'user_email' => 'b@example.com', 'status' => 'failure', 'performed_at' => now(),
    ]);

    Livewire::test(Dashboard::class)
        ->assertViewHas('auditSummary', function ($summary) {
            $payment = $summary->firstWhere('category', 'payment');

            return $payment->total_actions == 2
                && $payment->success_count == 1
                && $payment->failure_count == 1
                && $payment->success_rate == 50.0;
        });
});

it('falls back to zeroed booking metrics when the booking db is offline', function () {
    forceBookingOfflineForDashboard();

    Livewire::test(Dashboard::class)
        ->assertViewHas('totalBookings', 0)
        ->assertViewHas('bookingSuccessRate', 0)
        ->assertViewHas('bookingTypeBreakdown', fn ($b) => $b->isEmpty());
});

it('falls back to zeroed admin stats when the users db is offline', function () {
    forceUsersOfflineForDashboard();

    Livewire::test(Dashboard::class)
        ->assertViewHas('userStats', fn ($stats) => $stats->total_users === 0
            && $stats->active_users === 0)
        ->assertViewHas('auditSummary', fn ($summary) => $summary->isEmpty());
});

it('YearFilter dispatches yearChanged when selectedYear updates', function () {
    Livewire::test(YearFilter::class, ['years' => [2025, 2026], 'selectedYear' => 2026])
        ->assertSet('selectedYear', 2026)
        ->set('selectedYear', 2025)
        ->assertDispatched('yearChanged', year: 2025);
});

it('DatabaseWarningBanner exposes whatever dbDisconnected list it is mounted with', function () {
    Livewire::test(DatabaseWarningBanner::class, ['dbDisconnected' => ['booking' => ['module' => 'Booking & Adoption']]])
        ->assertSet('dbDisconnected', ['booking' => ['module' => 'Booking & Adoption']]);
});
