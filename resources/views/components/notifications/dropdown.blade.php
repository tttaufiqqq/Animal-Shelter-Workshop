@php
use App\Models\AuditLog;

// Get recent payment audit logs (last 24 hours)
$paymentNotifications = AuditLog::where('category', 'payment')
    ->where('performed_at', '>=', now()->subDay())
    ->orderBy('performed_at', 'desc')
    ->limit(5)
    ->get();

// Get "seen" notification IDs from session
$seenNotifications = session('seen_payment_notifications', []);

// Calculate unread count
$unreadCount = $paymentNotifications->filter(function($log) use ($seenNotifications) {
    return !in_array($log->id, $seenNotifications);
})->count();

// Helper function to get priority based on action
$getPriority = function($action) {
    return match($action) {
        'payment_failed' => 'high',
        'payment_refunded' => 'high',
        'payment_initiated' => 'low',
        'payment_completed', 'payment_cancelled' => 'normal',
        default => 'normal',
    };
};

// Helper function to get notification title
$getTitle = function($action) {
    return match($action) {
        'payment_completed' => 'Payment Received',
        'payment_failed' => 'Payment Failed',
        'payment_initiated' => 'Payment Initiated',
        'payment_refunded' => 'Payment Refunded',
        'payment_cancelled' => 'Payment Cancelled',
        default => 'Payment Event',
    };
};

// Helper function to get notification message
$getMessage = function($log) {
    $amount = $log->metadata['amount'] ?? 0;
    $animalNames = implode(', ', $log->metadata['animal_names'] ?? []);
    $bookingId = $log->entity_id;
    $formattedAmount = 'RM ' . number_format($amount, 2);

    return "Payment of {$formattedAmount} for booking #{$bookingId}" . ($animalNames ? " ({$animalNames})" : '');
};
@endphp

<div class="relative" x-data="{ notificationOpen: false }" @click.away="notificationOpen = false">
    <button @click="notificationOpen = !notificationOpen"
            class="relative text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500 rounded-lg p-2 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <!-- Notification Badge -->
        @if($unreadCount > 0)
            <span class="absolute top-1 right-1 flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown Menu -->
    <div x-show="notificationOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-96 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden max-h-96 overflow-y-auto z-50">

        <!-- Header -->
        <div class="px-4 py-3 bg-gradient-to-r from-purple-50 to-purple-100 border-b border-purple-200 flex justify-between items-center sticky top-0">
            <h3 class="text-sm font-semibold text-purple-900 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                Payment Notifications
            </h3>
            @if($unreadCount > 0)
                <form action="{{ route('notifications.mark-all-seen') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs text-purple-600 hover:text-purple-800 font-medium transition">
                        Mark all as seen
                    </button>
                </form>
            @endif
        </div>

        <!-- Notifications List -->
        <div class="divide-y divide-gray-100">
            @forelse($paymentNotifications as $notification)
                @php
                    $isUnread = !in_array($notification->id, $seenNotifications);
                    $priority = $getPriority($notification->action);
                    $title = $getTitle($notification->action);
                    $message = $getMessage($notification);
                    $url = route('admin.audit.payments') . '?booking_id=' . $notification->entity_id;
                @endphp

                <a href="{{ $url }}"
                   @if($isUnread)
                       onclick="event.preventDefault(); document.getElementById('mark-seen-{{ $notification->id }}').submit();"
                   @endif
                   class="block px-4 py-3 hover:bg-purple-50 transition {{ $isUnread ? 'bg-blue-50' : '' }}">
                    <div class="flex gap-3">
                        <!-- Priority Icon -->
                        <div class="flex-shrink-0 mt-1">
                            @if($priority === 'high')
                                <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 {{ $isUnread ? 'font-bold' : '' }}">
                                {{ $title }}
                            </p>
                            <p class="text-sm text-gray-700 {{ $isUnread ? 'font-medium' : '' }}">
                                {{ $message }}
                            </p>
                            <div class="flex items-center gap-2 mt-1">
                                <p class="text-xs text-gray-500">
                                    {{ $notification->performed_at->diffForHumans() }}
                                </p>
                                @if($notification->metadata['amount'] ?? false)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800">
                                        RM {{ number_format($notification->metadata['amount'], 2) }}
                                    </span>
                                @endif
                                @if($notification->status === 'failure')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800">
                                        Failed
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Unread Indicator -->
                        @if($isUnread)
                            <div class="flex-shrink-0 mt-2">
                                <span class="w-2 h-2 bg-blue-500 rounded-full inline-block"></span>
                            </div>
                        @endif
                    </div>

                    <!-- Hidden form for marking as seen -->
                    @if($isUnread)
                        <form id="mark-seen-{{ $notification->id }}" action="{{ route('notifications.mark-seen', $notification->id) }}" method="POST" class="hidden">
                            @csrf
                        </form>
                    @endif
                </a>
            @empty
                <div class="px-4 py-8 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p class="text-sm font-medium">No recent payments</p>
                    <p class="text-xs mt-1">Payment notifications from the last 24 hours will appear here</p>
                </div>
            @endforelse
        </div>

        <!-- View All Link -->
        @if($paymentNotifications->count() > 0)
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 sticky bottom-0">
                <a href="{{ route('admin.audit.payments') }}" class="text-sm text-purple-600 hover:text-purple-800 font-medium transition flex items-center justify-center gap-1">
                    View all payment audit logs
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        @endif
    </div>
</div>
