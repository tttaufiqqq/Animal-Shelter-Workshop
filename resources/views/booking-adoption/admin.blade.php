
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Bookings - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
@include('navbar')

<!-- Limited Connectivity Warning Banner -->
@if(isset($dbDisconnected) && count($dbDisconnected) > 0)
<div id="connectivityBanner" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 shadow-sm">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="ml-3 flex-1">
            <h3 class="text-sm font-semibold text-yellow-800">Limited Connectivity</h3>
            <p class="text-sm text-yellow-700 mt-1">{{ count($dbDisconnected) }} database(s) currently unavailable. Some features may not work properly.</p>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($dbDisconnected as $connection => $info)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    {{ $info['module'] }}
                </span>
                @endforeach
            </div>
        </div>
        <button onclick="closeConnectivityBanner()" class="flex-shrink-0 ml-4 text-yellow-400 hover:text-yellow-600 transition-colors">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>

<script>
    function closeConnectivityBanner() {
        document.getElementById('connectivityBanner').style.display = 'none';
    }
</script>
@endif

<div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-5xl font-bold mb-4">All Bookings</h1>
                <p class="text-xl text-purple-100">Admin view of all appointment bookings</p>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-600 text-green-700 p-4 rounded-lg mb-8 shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <p class="font-semibold">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Stats Cards as Filter Buttons -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <!-- Total Bookings Card -->
        <a href="{{ route('bookings.index-admin') }}"
           class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 cursor-pointer {{ !request('status') ? 'ring-2 ring-purple-500' : '' }}">
            <div class="text-3xl mb-2">📅</div>
            <p class="text-2xl font-bold text-purple-700 mb-1">{{ $totalBookings }}</p>
            <p class="text-gray-600 text-sm">Total</p>
        </a>

        <!-- Pending Card -->
        <a href="{{ route('bookings.index-admin', ['status' => 'Pending']) }}"
           class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 cursor-pointer {{ request('status') == 'Pending' ? 'ring-2 ring-yellow-500' : '' }}">
            <div class="text-3xl mb-2">⏳</div>
            <p class="text-2xl font-bold text-yellow-600 mb-1">{{ $statusCounts['Pending'] ?? 0 }}</p>
            <p class="text-gray-600 text-sm">Pending</p>
        </a>

        <!-- Confirmed Card -->
        <a href="{{ route('bookings.index-admin', ['status' => 'Confirmed']) }}"
           class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 cursor-pointer {{ request('status') == 'Confirmed' ? 'ring-2 ring-blue-500' : '' }}">
            <div class="text-3xl mb-2">✅</div>
            <p class="text-2xl font-bold text-blue-600 mb-1">{{ $statusCounts['Confirmed'] ?? 0 }}</p>
            <p class="text-gray-600 text-sm">Confirmed</p>
        </a>

        <!-- Completed Card -->
        <a href="{{ route('bookings.index-admin', ['status' => 'Completed']) }}"
           class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 cursor-pointer {{ request('status') == 'Completed' ? 'ring-2 ring-green-500' : '' }}">
            <div class="text-3xl mb-2">🎉</div>
            <p class="text-2xl font-bold text-green-600 mb-1">{{ $statusCounts['Completed'] ?? 0 }}</p>
            <p class="text-gray-600 text-sm">Completed</p>
        </a>

        <!-- Cancelled Card -->
        <a href="{{ route('bookings.index-admin', ['status' => 'Cancelled']) }}"
           class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 cursor-pointer {{ request('status') == 'Cancelled' ? 'ring-2 ring-red-500' : '' }}">
            <div class="text-3xl mb-2">❌</div>
            <p class="text-2xl font-bold text-red-600 mb-1">{{ $statusCounts['Cancelled'] ?? 0 }}</p>
            <p class="text-gray-600 text-sm">Cancelled</p>
        </a>
    </div>

    <!-- Search and Filter Form -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <form method="GET" action="{{ route('bookings.index-admin') }}" class="space-y-4">
            <!-- Keep current status filter -->
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900">Search & Filter Bookings</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- User Search (Name or Email) -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        User Name or Email
                    </label>
                    <div class="relative">
                        <input type="text"
                               name="user_search"
                               value="{{ request('user_search') }}"
                               placeholder="Search by user name or email..."
                               class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Booking ID -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Booking ID
                    </label>
                    <input type="number"
                           name="booking_id"
                           value="{{ request('booking_id') }}"
                           placeholder="e.g. 123"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        From Date
                    </label>
                    <input type="date"
                           name="date_from"
                           value="{{ request('date_from') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        To Date
                    </label>
                    <input type="date"
                           name="date_to"
                           value="{{ request('date_to') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold transition duration-300 flex items-center gap-2 shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Search
                </button>
                <a href="{{ route('bookings.index-admin') }}"
                   class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold transition duration-300 flex items-center gap-2 shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Clear Filters
                </a>

                @if(request()->hasAny(['user_search', 'booking_id', 'date_from', 'date_to']))
                    <div class="ml-auto flex items-center text-sm text-purple-600 font-medium">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <span>{{ collect(request()->only(['user_search', 'booking_id', 'date_from', 'date_to']))->filter()->count() }} filter(s) active</span>
                    </div>
                @endif
            </div>
        </form>
    </div>

    @if($bookings->isEmpty())
        <div class="bg-white rounded-lg shadow-lg p-12 text-center">
            <div class="mb-6">
                <svg class="w-32 h-32 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h3 class="text-3xl font-bold text-gray-700 mb-3">No Bookings Found</h3>
            <p class="text-gray-500 text-lg">There are no bookings in the system yet.</p>
        </div>
    @else
        <!-- Table View -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Booking ID
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            User
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Appointment Date
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Time
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Animals
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Adoption
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($bookings as $booking)
                        @php
                            $statusKey = strtolower($booking->status);
                            $badgeColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                'confirmed' => 'bg-blue-100 text-blue-800 border-blue-300',
                                'completed' => 'bg-green-100 text-green-800 border-green-300',
                                'cancelled' => 'bg-red-100 text-red-800 border-red-300',
                            ];
                            $statusEmojis = [
                                'pending' => '⏳',
                                'confirmed' => '✅',
                                'completed' => '🎉',
                                'cancelled' => '❌',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">#{{ $booking->id }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($booking->user)
                                    <div class="flex items-center">
                                        <div class="bg-green-100 rounded-full p-2 mr-3">
                                            <svg class="w-4 h-4 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $booking->user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $booking->user->email }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-500">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex items-center gap-1 text-xs leading-5 font-semibold rounded-full border {{ $badgeColors[$statusKey] ?? 'bg-gray-100 text-gray-800' }}">
                                        <span>{{ $statusEmojis[$statusKey] ?? '📅' }}</span>
                                        {{ ucfirst($booking->status) }}
                                    </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-purple-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700">
                                            {{ \Carbon\Carbon::parse($booking->appointment_date)->format('M d, Y') }}
                                        </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-purple-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700">
                                            {{ \Carbon\Carbon::parse($booking->appointment_time)->format('h:i A') }}
                                        </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($booking->animals->isNotEmpty())
                                    <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-xs font-bold">
                                                {{ $booking->animals->count() }}
                                            </span>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($booking->animals->take(2) as $animal)
                                                <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded-full text-xs font-medium">
                                                        {{ $animal->name }}
                                                    </span>
                                            @endforeach
                                            @if($booking->animals->count() > 2)
                                                <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-full text-xs font-medium">
                                                        +{{ $booking->animals->count() - 2 }}
                                                    </span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">No animals</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($booking->adoptions->isNotEmpty())
                                    <button onclick="openAdoptionModal({{ $booking->id }})"
                                            class="inline-flex items-center px-3 py-1 bg-green-100 hover:bg-green-200 text-green-800 rounded-full text-xs font-semibold transition"
                                            title="View Adoption Records">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Available
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="openBookingModal({{ $booking->id }})"
                                        class="text-purple-600 hover:text-purple-900 transition duration-150"
                                        title="View Details">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>

                        <!-- Booking Details Modal -->
                        <div id="bookingModal-{{ $booking->id }}" class="modal-backdrop hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                            <div class="bg-white rounded-2xl shadow-2xl max-w-7xl w-full max-h-[100vh] overflow-y-auto">
                                <!-- Modal Header -->
                                <div class="bg-gradient-to-r from-purple-600 to-purple-700 p-6 text-white sticky top-0 z-10">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h2 class="text-2xl font-bold">Booking Details #{{ $booking->id }}</h2>
                                            <p class="text-purple-100 text-sm">Admin View</p>
                                        </div>
                                        <button onclick="closeModal('bookingModal-{{ $booking->id }}')" class="text-white hover:text-gray-200">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Modal Body -->
                                <div class="p-6 space-y-6">
                                    <!-- Status Badge -->
                                    <div class="flex justify-between items-center">
                                        <h3 class="text-lg font-bold text-gray-800">Booking Status</h3>
                                        <span class="px-4 py-2 rounded-full text-sm font-semibold {{ str_replace(['bg-', 'text-', 'border-'], ['bg-', 'text-white ', ''], $badgeColors[$statusKey] ?? 'bg-gray-500') }}">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                    </div>

                                    <!-- User Information -->
                                    @if($booking->user)
                                        <div class="bg-gradient-to-br from-green-50 to-green-100 border-2 border-green-300 rounded-xl p-6">
                                            <h3 class="font-bold text-gray-800 mb-4 text-xl">User Information</h3>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Name</p>
                                                    <p class="text-gray-800 font-medium">{{ $booking->user->name }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Email</p>
                                                    <p class="text-gray-800 font-medium">{{ $booking->user->email }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Phone</p>
                                                    <p class="text-gray-800 font-medium">{{ $booking->user->phoneNum ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Animals Section -->
                                    @if($booking->animals->isNotEmpty())
                                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 border-2 border-purple-300 rounded-xl p-6">
                                            <h3 class="font-bold text-gray-800 mb-4 text-xl">Animals in Booking ({{ $booking->animals->count() }})</h3>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                @foreach($booking->animals as $animal)
                                                    <div class="bg-white rounded-xl p-4 shadow-md">
                                                        @if($animal->images && $animal->images->count() > 0)
                                                            <img src="{{ asset('storage/' . $animal->images->first()->image_path) }}"
                                                                 alt="{{ $animal->name }}"
                                                                 class="w-full h-32 object-cover rounded-lg mb-3">
                                                        @endif
                                                        <h4 class="font-bold text-gray-800 mb-2">{{ $animal->name }}</h4>
                                                        <p class="text-sm text-gray-600">{{ $animal->species }} • {{ $animal->age }} • {{ $animal->gender }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Appointment Details -->
                                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-300 rounded-xl p-6">
                                        <h3 class="font-bold text-gray-800 mb-4 text-xl">Appointment Details</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Date</p>
                                                <p class="text-gray-800 font-bold text-lg">{{ \Carbon\Carbon::parse($booking->appointment_date)->format('F d, Y') }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Time</p>
                                                <p class="text-gray-800 font-bold text-lg">{{ \Carbon\Carbon::parse($booking->appointment_time)->format('h:i A') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    @if($booking->adoptions->isNotEmpty())
                                        <button onclick="openAdoptionModal({{ $booking->id }})"
                                                class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold transition">
                                            View Adoption Records
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Adoption Records Modal -->
                        @if($booking->adoptions->isNotEmpty())
                            <div id="adoptionModal-{{ $booking->id }}" class="modal-backdrop hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60] p-4">
                                <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                                    <!-- Modal Header -->
                                    <div class="bg-gradient-to-r from-green-600 to-green-700 p-6 text-white sticky top-0 z-10">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h2 class="text-2xl font-bold">Adoption Records</h2>
                                                <p class="text-green-100 text-sm">Booking #{{ $booking->id }}</p>
                                            </div>
                                            <button onclick="closeAdoptionModal({{ $booking->id }})" class="text-white hover:text-gray-200">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Modal Body -->
                                    <div class="p-6 space-y-6">
                                        @foreach($booking->adoptions as $adoption)
                                            <div class="bg-green-50 border-2 border-green-300 rounded-xl p-6">
                                                <div class="flex items-center justify-between mb-4">
                                                    <h3 class="text-xl font-bold text-gray-800">Adoption #{{ $adoption->id }}</h3>
                                                    <span class="px-3 py-1 bg-green-600 text-white rounded-full text-sm font-semibold">Completed</span>
                                                </div>

                                                <div class="grid grid-cols-2 gap-4 mb-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500 font-medium">Adoption Fee</p>
                                                        <p class="text-2xl font-bold text-green-600">RM {{ number_format($adoption->fee, 2) }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm text-gray-500 font-medium">Date</p>
                                                        <p class="text-lg font-semibold text-gray-800">{{ $adoption->created_at->format('F d, Y') }}</p>
                                                    </div>
                                                </div>

                                                <div class="bg-white rounded-lg p-4 mb-4">
                                                    <p class="text-sm text-gray-500 font-medium mb-1">Remarks</p>
                                                    <p class="text-gray-800">{{ $adoption->remarks ?? 'No remarks' }}</p>
                                                </div>

                                                @if($adoption->transaction)
                                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                                        <h4 class="font-semibold text-gray-800 mb-2">Payment Information</h4>
                                                        <div class="grid grid-cols-2 gap-2 text-sm">
                                                            <div>
                                                                <span class="text-gray-600">Amount:</span>
                                                                <span class="font-medium text-gray-800">RM {{ number_format($adoption->transaction->amount, 2) }}</span>
                                                            </div>
                                                            <div>
                                                                <span class="text-gray-600">Status:</span>
                                                                <span class="font-medium text-green-600">{{ $adoption->transaction->status }}</span>
                                                            </div>
                                                            @if($adoption->transaction->bill_code)
                                                                <div>
                                                                    <span class="text-gray-600">Bill Code:</span>
                                                                    <span class="font-medium text-gray-800">{{ $adoption->transaction->bill_code }}</span>
                                                                </div>
                                                            @endif
                                                            @if($adoption->transaction->reference_no)
                                                                <div>
                                                                    <span class="text-gray-600">Reference:</span>
                                                                    <span class="font-medium text-gray-800">{{ $adoption->transaction->reference_no }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach

                                        <button onclick="closeAdoptionModal({{ $booking->id }})"
                                                class="w-full bg-gray-600 hover:bg-gray-700 text-white py-3 rounded-lg font-semibold transition">
                                            Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($bookings->hasPages())
        <div class="mt-8 flex justify-center">
            {{ $bookings->links() }}
        </div>
    @endif
</div>

<script>
    function openBookingModal(bookingId) {
        document.getElementById('bookingModal-' + bookingId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function openAdoptionModal(bookingId) {
        document.getElementById('adoptionModal-' + bookingId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeAdoptionModal(bookingId) {
        document.getElementById('adoptionModal-' + bookingId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-backdrop')) {
            e.target.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop:not(.hidden)').forEach(modal => {
                modal.classList.add('hidden');
            });
            document.body.style.overflow = 'auto';
        }
    });
</script>
</body>
</html>
