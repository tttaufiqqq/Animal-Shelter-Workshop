<?php

namespace App\Livewire\Concerns\Notifications;

use App\Models\Transaction;
use App\Models\Adoption;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait LoadsNotifications
{
    public function loadNotifications()
    {
        $this->databaseErrors = [];
        $this->danishDbOnline = true;
        $this->notifications = collect([]);

        $since = Carbon::now()->subDays(7);

        if (!app(\App\Services\DatabaseConnectionChecker::class)->isConnected('booking')) {
            $this->danishDbOnline = false;
            $this->databaseErrors['booking'] = 'Notifications unavailable - booking database is offline';
            Log::warning('Booking database offline in Notifications component');

            $this->notifications = collect([[
                'id' => 'system_error_danish',
                'type' => 'system',
                'title' => 'Database Offline',
                'message' => 'Notification system is temporarily unavailable. The booking & adoption database is currently offline.',
                'icon' => 'warning',
                'color' => 'red',
                'time' => Carbon::now(),
                'read' => false,
                'url' => null,
            ]]);

            $this->unreadCount = 0;
            return;
        }

        $transactions = collect([]);
        $adoptions = collect([]);
        $bookings = collect([]);

        try {
            $transactions = Transaction::with('user')
                ->where('created_at', '>=', $since)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($t) => $this->mapTransactionNotification($t));
        } catch (\Exception $e) {
            Log::error('Failed to load transactions in Notifications: ' . $e->getMessage());
            $this->databaseErrors['transactions'] = 'Unable to load payment notifications';
        }

        try {
            $adoptions = Adoption::with(['transaction.user', 'animal', 'booking'])
                ->where('created_at', '>=', $since)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($a) => $this->mapAdoptionNotification($a));
        } catch (\Exception $e) {
            Log::error('Failed to load adoptions in Notifications: ' . $e->getMessage());
            $this->databaseErrors['adoptions'] = 'Unable to load adoption notifications';
        }

        try {
            $bookings = Booking::with(['user', 'animalBookings'])
                ->where('created_at', '>=', $since)
                ->whereIn('status', ['Pending', 'Confirmed'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($b) => $this->mapBookingNotification($b));
        } catch (\Exception $e) {
            Log::error('Failed to load bookings in Notifications: ' . $e->getMessage());
            $this->databaseErrors['bookings'] = 'Unable to load booking notifications';
        }

        $this->notifications = collect([])
            ->merge($transactions)
            ->merge($adoptions)
            ->merge($bookings)
            ->sortByDesc('time')
            ->take(20);

        if (!empty($this->databaseErrors) && $this->danishDbOnline) {
            $errorMessage = 'Some notifications may be incomplete: ' . implode(', ', $this->databaseErrors);

            $this->notifications->prepend([
                'id' => 'system_warning_partial',
                'type' => 'system',
                'title' => 'Partial Database Connectivity',
                'message' => $errorMessage,
                'icon' => 'warning',
                'color' => 'yellow',
                'time' => Carbon::now(),
                'read' => $this->isNotificationRead('system_warning_partial'),
                'url' => null,
            ]);
        }

        $this->unreadCount = $this->notifications->where('read', false)->count();
    }
}
