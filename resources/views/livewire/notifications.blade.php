<div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
    <!-- Notification Button with Badge -->
    <x-tooltip.wrapper text="View notifications and<br>recent activity" position="bottom" size="sm">
        <button @click="dropdownOpen = !dropdownOpen; if(dropdownOpen) { $wire.loadNotifications().catch(() => {}) }"
                class="relative text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500 rounded-lg p-2 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>

            <!-- Notification Badge with Count -->
            @if($unreadCount > 0)
                <span class="absolute top-0.5 right-0.5 min-w-[18px] h-[18px] bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1 animate-pulse">
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </span>
            @endif
        </button>
    </x-tooltip.wrapper>

    <!-- Dropdown Menu -->
    <div x-show="dropdownOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-[-10px]"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-[-10px]"
         class="absolute right-0 mt-2 w-96 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden z-50">

        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <h3 class="text-white font-semibold">Notifications</h3>
                @if($unreadCount > 0)
                    <span class="bg-white/20 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ $unreadCount }}
                    </span>
                @endif
            </div>

            @if($unreadCount > 0)
                <button wire:click="markAllAsRead"
                        class="text-white/90 hover:text-white text-xs font-medium underline transition">
                    Mark all read
                </button>
            @endif
        </div>

        <!-- Notifications List -->
        <div class="max-h-[400px] overflow-y-auto">
            @forelse($notifications as $notification)
                <div wire:click="markAsRead('{{ $notification['id'] }}')"
                     class="px-4 py-3 hover:bg-purple-50 transition cursor-pointer border-b border-gray-100 {{ !$notification['read'] ? 'bg-purple-50/30' : '' }}">
                    <div class="flex gap-3">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-{{ $notification['color'] }}-100 flex items-center justify-center">
                                @if($notification['icon'] === 'currency')
                                    <svg class="w-5 h-5 text-{{ $notification['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @elseif($notification['icon'] === 'heart')
                                    <svg class="w-5 h-5 text-{{ $notification['color'] }}-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                    </svg>
                                @elseif($notification['icon'] === 'calendar')
                                    <svg class="w-5 h-5 text-{{ $notification['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                @elseif($notification['icon'] === 'warning')
                                    <svg class="w-5 h-5 text-{{ $notification['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                @endif
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                        {{ $notification['title'] }}
                                        @if(!$notification['read'])
                                            <span class="w-2 h-2 bg-purple-600 rounded-full"></span>
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-600 mt-0.5 line-clamp-2">
                                        {{ $notification['message'] }}
                                    </p>

                                    <!-- Status Badge -->
                                    @if(isset($notification['status']))
                                        <div class="mt-1.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                {{ $notification['color'] === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $notification['color'] === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $notification['color'] === 'red' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $notification['color'] === 'blue' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $notification['color'] === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}">
                                                {{ ucfirst($notification['status']) }}
                                            </span>

                                            @if(isset($notification['reference']))
                                                <span class="ml-2 text-xs text-gray-500">
                                                    Ref: {{ $notification['reference'] }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Time -->
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ \Carbon\Carbon::parse($notification['time'])->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="text-gray-500 text-sm font-medium">No new notifications</p>
                    <p class="text-gray-400 text-xs mt-1">You're all caught up!</p>
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        @if($notifications->isNotEmpty())
            <div class="bg-gray-50 px-4 py-2 border-t border-gray-200">
                <div class="flex items-center justify-between text-xs text-gray-600">
                    <span>Showing {{ $notifications->count() }} recent notifications</span>
                    <span class="text-purple-600 font-medium">Last 7 days</span>
                </div>
            </div>
        @endif
    </div>
</div>
