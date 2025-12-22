<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Stray Animal Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
@include('navbar')

<!-- Limited Connectivity Warning Banner -->
@if(isset($dbDisconnected) && count($dbDisconnected) > 0)
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 shadow-sm">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="ml-3 flex-1">
            <h3 class="text-sm font-semibold text-yellow-800">Limited Connectivity</h3>
            <p class="text-sm text-yellow-700 mt-1">{{ count($dbDisconnected) }} database(s) currently unavailable. You may experience limited functionality or missing data.</p>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($dbDisconnected as $connection => $info)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    {{ $info['module'] }}
                </span>
                @endforeach
            </div>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto flex-shrink-0 text-yellow-400 hover:text-yellow-600">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
        </button>
    </div>
</div>
@endif

<div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-5xl font-bold mb-4">My Bookings</h1>
                <p class="text-xl text-purple-100">View and manage your appointment bookings for adoptions</p>
            </div>
            @unless($bookings->isEmpty())
                <div class="mt-6 md:mt-0">
                    <a href="{{ route('animal:main') }}" class="inline-flex items-center gap-2 bg-white text-purple-700 px-8 py-3 rounded-lg font-semibold hover:bg-purple-50 transition duration-300 shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>New Booking</span>
                    </a>
                </div>
            @endunless
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

    @if (session('error'))
        <div class="bg-red-50 border-l-4 border-red-600 text-red-700 p-4 rounded-lg mb-8 shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <p class="font-semibold">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Stats Cards as Filter Buttons -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-10">
        <!-- Total Bookings Card -->
        <!-- Total Bookings Card -->
        <a href="{{ route('bookings.index') }}"
           class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 cursor-pointer {{ !request('status') ? 'ring-4 ring-purple-500' : '' }}">
            <div class="text-5xl mb-4">📅</div>
            <p class="text-4xl font-bold text-purple-700 mb-2">{{ $totalBookings }}</p>
            <p class="text-gray-600">Total Bookings</p>
            @if(!request('status'))
                <div class="mt-2 text-xs text-purple-600 font-semibold">● Active</div>
            @endif
        </a>

        <!-- Pending Card -->
        <a href="{{ route('bookings.index', ['status' => 'Pending']) }}"
           class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 cursor-pointer {{ request('status') == 'Pending' ? 'ring-4 ring-yellow-500' : '' }}">
            <div class="text-5xl mb-4">⏳</div>
            <p class="text-4xl font-bold text-yellow-600 mb-2">{{ $statusCounts['Pending'] ?? 0 }}</p>
            <p class="text-gray-600">Pending</p>
            @if(request('status') == 'Pending')
                <div class="mt-2 text-xs text-yellow-600 font-semibold">● Active</div>
            @endif
        </a>

        <!-- Confirmed Card -->
        <a href="{{ route('bookings.index', ['status' => 'Confirmed']) }}"
           class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 cursor-pointer {{ request('status') == 'Confirmed' ? 'ring-4 ring-blue-500' : '' }}">
            <div class="text-5xl mb-4">✅</div>
            <p class="text-4xl font-bold text-blue-600 mb-2">{{ $statusCounts['Confirmed'] ?? 0 }}</p>
            <p class="text-gray-600">Confirmed</p>
            @if(request('status') == 'Confirmed')
                <div class="mt-2 text-xs text-blue-600 font-semibold">● Active</div>
            @endif
        </a>

        <!-- Completed Card -->
        <a href="{{ route('bookings.index', ['status' => 'Completed']) }}"
           class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 cursor-pointer {{ request('status') == 'Completed' ? 'ring-4 ring-green-500' : '' }}">
            <div class="text-5xl mb-4">🎉</div>
            <p class="text-4xl font-bold text-green-600 mb-2">{{ $statusCounts['Completed'] ?? 0 }}</p>
            <p class="text-gray-600">Completed</p>
            @if(request('status') == 'Completed')
                <div class="mt-2 text-xs text-green-600 font-semibold">● Active</div>
            @endif
        </a>

        <!-- Cancelled Card -->
        <a href="{{ route('bookings.index', ['status' => 'Cancelled']) }}"
           class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 cursor-pointer {{ request('status') == 'Cancelled' ? 'ring-4 ring-red-500' : '' }}">
            <div class="text-5xl mb-4">❌</div>
            <p class="text-4xl font-bold text-red-600 mb-2">{{ $statusCounts['Cancelled'] ?? 0 }}</p>
            <p class="text-gray-600">Cancelled</p>
            @if(request('status') == 'Cancelled')
                <div class="mt-2 text-xs text-red-600 font-semibold">● Active</div>
            @endif
        </a>
    </div>

    <!-- Search and Filter Form -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <form method="GET" action="{{ route('bookings.index') }}" class="space-y-4">
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- General Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Search
                    </label>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Booking ID, Date, Remarks..."
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
                        class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold transition duration-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Search
                </button>
                <a href="{{ route('bookings.index') }}"
                   class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold transition duration-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Clear
                </a>
            </div>

            @if(request()->hasAny(['search', 'date_from', 'date_to']))
                <div class="mt-3 text-sm text-purple-600 font-medium">
                    <span class="inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        Active filters applied
                    </span>
                </div>
            @endif
        </form>
    </div>

    @if($bookings->isEmpty())
        <div class="bg-white rounded-lg shadow-lg p-12 text-center">
            <div class="mb-6">
                <svg class="w-32 h-32 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h3 class="text-3xl font-bold text-gray-700 mb-3">No Bookings Yet</h3>
            <p class="text-gray-500 text-lg mb-8">You haven't made any bookings. Start by creating your first appointment!</p>
            <a href="{{ route('animal:main') }}" class="inline-flex items-center gap-2 bg-purple-700 hover:bg-purple-800 text-white px-8 py-3 rounded-lg font-semibold transition duration-300 shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Create First Booking</span>
            </a>
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
                                    <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Confirmed
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="openModal('bookingModal-{{ $booking->id }}')"
                                        class="text-purple-600 hover:text-purple-900 transition duration-150"
                                        title="View Details">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modals for all bookings (outside table structure) -->
        @foreach($bookings as $booking)
            @include('booking-adoption.show-modal', ['booking' => $booking])
        @endforeach
    @endif

    @if($bookings->hasPages())
        <div class="mt-8 flex justify-center">
            {{ $bookings->links() }}
        </div>
    @endif

    <div class="mt-16 bg-gradient-to-r from-purple-700 to-purple-900 rounded-lg p-12 text-center text-white">
        <h2 class="text-3xl font-bold mb-4">Need Help with Your Booking?</h2>
        <p class="text-xl mb-6">Contact us if you have any questions about your appointments or need to make changes.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('contact') }}" class="bg-white text-purple-700 px-8 py-3 rounded-lg font-semibold hover:bg-purple-50 transition duration-300 inline-block">
                Contact Support
            </a>
        </div>
    </div>
</div>

<!-- Payment Status Modal -->
@if(session('show_payment_modal') && session('payment_status'))
    @php
        $payment = session('payment_status');
        $isSuccess = $payment['status_id'] == 1;
    @endphp
    <div id="paymentStatusModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center modal-backdrop">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="bg-gradient-to-r {{ $isSuccess ? 'from-green-600 to-green-700' : 'from-red-600 to-red-700' }} text-white p-8 rounded-t-2xl">
                <div class="flex items-center justify-center mb-4">
                    @if($isSuccess)
                        <div class="bg-white rounded-full p-4">
                            <svg class="w-16 h-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    @else
                        <div class="bg-white rounded-full p-4">
                            <svg class="w-16 h-16 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                <h2 class="text-3xl font-bold text-center">
                    {{ $isSuccess ? 'Payment Successful!' : 'Payment Failed' }}
                </h2>
                <p class="text-center text-white text-opacity-90 mt-2">
                    {{ $isSuccess ? 'Your adoption has been confirmed' : 'Payment could not be processed' }}
                </p>
            </div>

            <!-- Body -->
            <div class="p-8">
                @if($isSuccess)
                    <!-- Success Message -->
                    <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-lg mb-6">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-lg font-semibold text-green-800">Congratulations!</h3>
                                <p class="text-green-700 mt-1">
                                    You have successfully adopted <strong>{{ $payment['animal_names'] }}</strong>.
                                    Your booking has been completed and the animal(s) are now marked as adopted.
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Failure Message -->
                    <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg mb-6">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-red-500 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-lg font-semibold text-red-800">Payment Failed</h3>
                                <p class="text-red-700 mt-1">
                                    The payment for <strong>{{ $payment['animal_names'] }}</strong> could not be processed.
                                    Your booking remains confirmed. Please try again or contact support.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Payment Details -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Payment Details</h3>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Booking ID</p>
                            <p class="font-semibold text-gray-900">#{{ $payment['booking_id'] }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Amount</p>
                            <p class="font-semibold text-gray-900">RM {{ number_format($payment['amount'], 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Bill Code</p>
                            <p class="font-semibold text-gray-900">{{ $payment['billcode'] }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Reference Number</p>
                            <p class="font-semibold text-gray-900">{{ $payment['reference_no'] }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-600">Animal(s)</p>
                            <p class="font-semibold text-gray-900">{{ $payment['animal_names'] }} ({{ $payment['animal_count'] }} animal{{ $payment['animal_count'] > 1 ? 's' : '' }})</p>
                        </div>
                    </div>
                </div>

                @if($isSuccess)
                    <!-- Next Steps -->
                    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h4 class="font-semibold text-blue-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            What's Next?
                        </h4>
                        <ul class="space-y-2 text-sm text-blue-900">
                            <li class="flex items-start">
                                <svg class="w-4 h-4 mr-2 mt-0.5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>A confirmation email has been sent to your email address</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 mr-2 mt-0.5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>You can now pick up your adopted animal(s) from the shelter</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 mr-2 mt-0.5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Contact us if you have any questions or need assistance</span>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-8 py-6 rounded-b-2xl flex justify-center gap-4">
                <button onclick="closePaymentModal()"
                        class="px-8 py-3 bg-gradient-to-r {{ $isSuccess ? 'from-green-600 to-green-700 hover:from-green-700 hover:to-green-800' : 'from-red-600 to-red-700 hover:from-red-700 hover:to-red-800' }} text-white rounded-lg font-semibold transition duration-300 shadow-lg">
                    {{ $isSuccess ? 'Close & View Bookings' : 'Close' }}
                </button>
            </div>
        </div>
    </div>
@endif

<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close payment status modal
    function closePaymentModal() {
        const modal = document.getElementById('paymentStatusModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
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

    // Back to booking details - just close adoption fee modal
    // Booking details modal is still open underneath
    function backToBookingDetails(bookingId) {
        closeModal('adoptionFeeModal-' + bookingId);
    }

    // Update selection count and estimated fee in booking details modal
    function updateSelectionSummary(bookingId) {
        const checkboxes = document.querySelectorAll('.animal-select-' + bookingId + ':checked');
        let total = 0;
        let count = 0;

        checkboxes.forEach(function(cb) {
            total += parseFloat(cb.dataset.fee) || 0;
            count++;
        });

        const countEl = document.getElementById('selectedCount-' + bookingId);
        const feeEl = document.getElementById('estimatedFee-' + bookingId);

        if (countEl) countEl.innerText = count;
        if (feeEl) feeEl.innerText = 'RM ' + total.toFixed(2);
    }

    // Open adoption fee modal and populate with selected animals
    // Does NOT close the booking details modal
    function openAdoptionFeeModal(bookingId) {
        const checkboxes = document.querySelectorAll('.animal-select-' + bookingId + ':checked');

        // Check if at least one animal is selected
        if (checkboxes.length === 0) {
            alert('Please select at least one animal to adopt.');
            return;
        }

        // Get containers
        const listContainer = document.getElementById('selectedAnimalsList-' + bookingId);
        const hiddenInputsContainer = document.getElementById('hiddenAnimalInputs-' + bookingId);
        const grandTotalEl = document.getElementById('grandTotal-' + bookingId);
        const noAnimalsMsg = document.getElementById('noAnimalsSelected-' + bookingId);
        const submitBtn = document.getElementById('submitBtn-' + bookingId);

        // Clear previous content
        listContainer.innerHTML = '';
        hiddenInputsContainer.innerHTML = '';

        let grandTotal = 0;

        checkboxes.forEach(function(cb) {
            const animalId = cb.dataset.animalId;
            const animalName = cb.dataset.animalName;
            const animalSpecies = cb.dataset.animalSpecies;
            const baseFee = parseFloat(cb.dataset.baseFee) || 0;
            const medicalFee = parseFloat(cb.dataset.medicalFee ?? 0);
            const medicalCount = parseInt(cb.dataset.medicalCount ?? 0);
            const vaccinationFee = parseFloat(cb.dataset.vaccinationFee ?? 0);
            const vaccinationCount = parseInt(cb.dataset.vaccinationCount ?? 0);
            const totalFee = parseFloat(cb.dataset.fee) || 0;

            grandTotal += totalFee;

            // Create animal card (read-only) with detailed breakdown
            const animalCard = document.createElement('div');
            animalCard.className = 'flex items-center justify-between bg-white rounded-lg p-3 border border-gray-200';
            animalCard.innerHTML = `
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">${animalName} (${animalSpecies})</p>
                            <p class="text-sm text-gray-600">
                                Base: RM ${baseFee.toFixed(2)} |
                                Medical: RM ${medicalFee.toFixed(2)} (${medicalCount} record${medicalCount !== 1 ? 's' : ''}) |
                                Vaccination: RM ${vaccinationFee.toFixed(2)} (${vaccinationCount} shot${vaccinationCount !== 1 ? 's' : ''})
                            </p>
                        </div>
                    </div>
                    <span class="font-bold text-gray-800">RM ${totalFee.toFixed(2)}</span>
                `;
            listContainer.appendChild(animalCard);

            // Create hidden input for form submission
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'animal_ids[]';
            hiddenInput.value = animalId;
            hiddenInputsContainer.appendChild(hiddenInput);
        });

        // *** ADD THIS: Create hidden input for total fee ***
        const totalFeeInput = document.createElement('input');
        totalFeeInput.type = 'hidden';
        totalFeeInput.name = 'total_fee';
        totalFeeInput.value = grandTotal.toFixed(2);
        hiddenInputsContainer.appendChild(totalFeeInput);
        // *** END OF ADDITION ***

        // Update grand total
        grandTotalEl.innerText = 'RM ' + grandTotal.toFixed(2);

        // Show/hide no animals message and enable/disable submit
        if (checkboxes.length === 0) {
            noAnimalsMsg.classList.remove('hidden');
            submitBtn.disabled = true;
        } else {
            noAnimalsMsg.classList.add('hidden');
            submitBtn.disabled = false;
        }

        // Open adoption fee modal WITHOUT closing booking modal
        openModal('adoptionFeeModal-' + bookingId);
    }

    // Add event listeners for animal selection checkboxes
    document.addEventListener('DOMContentLoaded', function() {
        // Find all animal selection checkboxes and add change listeners
        document.querySelectorAll('[class*="animal-select-"]').forEach(function(cb) {
            cb.addEventListener('change', function() {
                // Extract booking ID from class name
                const classes = this.className.split(' ');
                const selectClass = classes.find(c => c.startsWith('animal-select-'));
                if (selectClass) {
                    const bookingId = selectClass.replace('animal-select-', '');
                    updateSelectionSummary(bookingId);
                }
            });
        });
    });
</script>
</body>
</html>
