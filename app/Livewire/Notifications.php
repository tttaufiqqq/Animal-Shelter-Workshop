<?php

namespace App\Livewire;

use App\Models\Transaction;
use App\Models\Adoption;
use App\Models\Booking;
use Livewire\Component;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Notifications extends Component
{
    public bool $isOpen = false;
    public int $unreadCount = 0;
    public Collection $notifications;
    public array $databaseErrors = [];
    public bool $danishDbOnline = true;

    protected $listeners = ['notificationRead' => 'markAsRead'];

    public function mount()
    {
        try {
            // Initialize notifications collection to prevent errors
            $this->notifications = collect([]);
            $this->loadNotifications();
        } catch (\Exception $e) {
            // If mount fails completely, ensure component still renders with safe defaults
            Log::error('Notifications component mount failed: ' . $e->getMessage());
            $this->notifications = collect([]);
            $this->unreadCount = 0;
            $this->databaseErrors = ['system' => 'Notification system temporarily unavailable'];
            $this->danishDbOnline = false;
        }
    }

    public function loadNotifications()
    {
        // Reset errors and initialize notifications (prevents errors during render)
        $this->databaseErrors = [];
        $this->danishDbOnline = true;
        $this->notifications = collect([]); // Always start with empty collection

        // Get recent transactions, adoptions, and bookings (last 7 days)
        $since = Carbon::now()->subDays(7);

        // Initialize empty collections
        $transactions = collect([]);
        $adoptions = collect([]);
        $bookings = collect([]);

        // Check Danish database connection first (required for all notification types)
        try {
            DB::connection('danish')->getPdo();
        } catch (\Exception $e) {
            $this->danishDbOnline = false;
            $this->databaseErrors['danish'] = 'Notifications unavailable - Danish database is offline';
            Log::warning('Danish database offline in Notifications component: ' . $e->getMessage());

            // Add system notification about offline database
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

        // Get recent transactions with user info
        try {
            $transactions = Transaction::with('user')
                ->where('created_at', '>=', $since)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($transaction) {
                    // Safely get user name (may fail if Taufiq DB is offline)
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
                });
        } catch (\Exception $e) {
            Log::error('Failed to load transactions in Notifications: ' . $e->getMessage());
            $this->databaseErrors['transactions'] = 'Unable to load payment notifications';
        }

        // Get recent adoptions with animal and user info
        try {
            $adoptions = Adoption::with(['transaction.user', 'animal', 'booking'])
                ->where('created_at', '>=', $since)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($adoption) {
                    // Safely get user name (may fail if Taufiq DB is offline)
                    $userName = 'Unknown User';
                    try {
                        $userName = $adoption->transaction && $adoption->transaction->user
                            ? $adoption->transaction->user->name
                            : 'Unknown User';
                    } catch (\Exception $e) {
                        Log::warning('Failed to fetch user for adoption: ' . $e->getMessage());
                        $userName = 'User (DB Offline)';
                    }

                    // Safely get animal name (may fail if Shafiqah DB is offline)
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
                });
        } catch (\Exception $e) {
            Log::error('Failed to load adoptions in Notifications: ' . $e->getMessage());
            $this->databaseErrors['adoptions'] = 'Unable to load adoption notifications';
        }

        // Get recent bookings with user info
        try {
            $bookings = Booking::with(['user', 'animals'])
                ->where('created_at', '>=', $since)
                ->whereIn('status', ['Pending', 'Confirmed'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($booking) {
                    // Safely get user name (may fail if Taufiq DB is offline)
                    $userName = 'Unknown User';
                    try {
                        $userName = $booking->user ? $booking->user->name : 'Unknown User';
                    } catch (\Exception $e) {
                        Log::warning('Failed to fetch user for booking: ' . $e->getMessage());
                        $userName = 'User (DB Offline)';
                    }

                    // Safely get animal count (may fail if Shafiqah DB is offline)
                    $animalCount = 0;
                    try {
                        $animalCount = $booking->animals->count();
                    } catch (\Exception $e) {
                        Log::warning('Failed to fetch animals for booking: ' . $e->getMessage());
                        $animalCount = 0;
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
                });
        } catch (\Exception $e) {
            Log::error('Failed to load bookings in Notifications: ' . $e->getMessage());
            $this->databaseErrors['bookings'] = 'Unable to load booking notifications';
        }

        // Merge and sort all notifications by time
        $this->notifications = collect([])
            ->merge($transactions)
            ->merge($adoptions)
            ->merge($bookings)
            ->sortByDesc('time')
            ->take(20);

        // Add warning notification if there were any errors loading specific types
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

        // Count unread notifications
        $this->unreadCount = $this->notifications->where('read', false)->count();
    }

    public function toggleDropdown()
    {
        try {
            $this->isOpen = !$this->isOpen;

            if ($this->isOpen) {
                $this->loadNotifications();
            }
        } catch (\Exception $e) {
            Log::error('Failed to toggle notifications dropdown: ' . $e->getMessage());
            // Ensure dropdown can still open even if load fails
            $this->isOpen = !$this->isOpen;
        }
    }

    public function markAsRead($notificationId)
    {
        $readNotifications = session()->get('read_notifications', []);

        if (!in_array($notificationId, $readNotifications)) {
            $readNotifications[] = $notificationId;
            session()->put('read_notifications', $readNotifications);
        }

        $this->loadNotifications();
    }

    public function markAllAsRead()
    {
        $allIds = $this->notifications->pluck('id')->toArray();
        session()->put('read_notifications', array_merge(
            session()->get('read_notifications', []),
            $allIds
        ));

        $this->loadNotifications();
    }

    private function isNotificationRead($notificationId): bool
    {
        $readNotifications = session()->get('read_notifications', []);
        return in_array($notificationId, $readNotifications);
    }

    private function getStatusColor($status): string
    {
        return match(strtolower($status ?? '')) {
            'success', 'successful', 'completed' => 'green',
            'pending' => 'yellow',
            'failed', 'cancelled' => 'red',
            default => 'gray',
        };
    }

    public function render()
    {
        try {
            // Ensure notifications collection exists before rendering
            if (!isset($this->notifications)) {
                $this->notifications = collect([]);
            }
            return view('livewire.notifications');
        } catch (\Exception $e) {
            Log::error('Notifications component render failed: ' . $e->getMessage());
            // Fallback to safe defaults
            $this->notifications = collect([]);
            $this->unreadCount = 0;
            return view('livewire.notifications');
        }
    }
}
