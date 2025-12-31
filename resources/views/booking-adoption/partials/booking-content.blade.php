{{-- Success/Error Messages --}}
@if (session('success'))
    <div class="flex items-start gap-3 p-4 mb-6 bg-green-100 border-2 border-green-400 rounded-lg shadow-sm">
        <svg class="w-5 h-5 text-green-700 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
        <p class="font-semibold text-green-800">{{ session('success') }}</p>
    </div>
@endif

@if (session('error'))
    <div class="flex items-start gap-3 p-4 mb-6 bg-red-100 border-2 border-red-400 rounded-lg shadow-sm">
        <svg class="w-5 h-5 text-red-700 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <p class="font-semibold text-red-800">{{ session('error') }}</p>
    </div>
@endif

{{-- Stats Cards as Filter Buttons --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
    <!-- Total Bookings Card -->
    <a href="{{ route('bookings.index-admin') }}"
       class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ !request('status') ? 'ring-2 ring-purple-500' : '' }}">
        <div class="flex justify-center mb-2">
            <svg class="w-8 h-8 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <p class="text-xl font-bold text-purple-700 mb-0.5">{{ $totalBookings }}</p>
        <p class="text-gray-600 text-xs">Total</p>
    </a>

    <!-- Pending Card -->
    <a href="{{ route('bookings.index-admin', ['status' => 'Pending']) }}"
       class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ request('status') == 'Pending' ? 'ring-2 ring-yellow-500' : '' }}">
        <div class="flex justify-center mb-2">
            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-xl font-bold text-yellow-600 mb-0.5">{{ $statusCounts['Pending'] ?? 0 }}</p>
        <p class="text-gray-600 text-xs">Pending</p>
    </a>

    <!-- Confirmed Card -->
    <a href="{{ route('bookings.index-admin', ['status' => 'Confirmed']) }}"
       class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ request('status') == 'Confirmed' ? 'ring-2 ring-blue-500' : '' }}">
        <div class="flex justify-center mb-2">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-xl font-bold text-blue-600 mb-0.5">{{ $statusCounts['Confirmed'] ?? 0 }}</p>
        <p class="text-gray-600 text-xs">Confirmed</p>
    </a>

    <!-- Completed Card -->
    <a href="{{ route('bookings.index-admin', ['status' => 'Completed']) }}"
       class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ request('status') == 'Completed' ? 'ring-2 ring-green-500' : '' }}">
        <div class="flex justify-center mb-2">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-xl font-bold text-green-600 mb-0.5">{{ $statusCounts['Completed'] ?? 0 }}</p>
        <p class="text-gray-600 text-xs">Completed</p>
    </a>

    <!-- Cancelled Card -->
    <a href="{{ route('bookings.index-admin', ['status' => 'Cancelled']) }}"
       class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ request('status') == 'Cancelled' ? 'ring-2 ring-red-500' : '' }}">
        <div class="flex justify-center mb-2">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-xl font-bold text-red-600 mb-0.5">{{ $statusCounts['Cancelled'] ?? 0 }}</p>
        <p class="text-gray-600 text-xs">Cancelled</p>
    </a>
</div>

{{-- Search and Filter Form --}}
<div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 mb-6">
    <form method="GET" action="{{ route('bookings.index-admin') }}" class="space-y-4">
        <!-- Keep current status filter -->
        @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif

        <div class="flex items-center gap-2 mb-4">
            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <h3 class="text-sm font-semibold text-gray-900">Search & Filter Bookings</h3>
            @if(request()->hasAny(['user_search', 'booking_id', 'date_from', 'date_to']))
                <span class="ml-auto flex items-center gap-1 px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    {{ collect(request()->only(['user_search', 'booking_id', 'date_from', 'date_to']))->filter()->count() }} filter(s) active
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- User Search (Name or Email) -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">
                    User Name or Email
                </label>
                <div class="relative">
                    <input type="text"
                           name="user_search"
                           value="{{ request('user_search') }}"
                           placeholder="Search by user name or email..."
                           class="w-full px-3 py-2 pl-9 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    From Date
                </label>
                <input type="date"
                       name="date_from"
                       value="{{ request('date_from') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    To Date
                </label>
                <input type="date"
                       name="date_to"
                       value="{{ request('date_to') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-semibold transition duration-300 flex items-center gap-1.5 shadow-md">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Search
            </button>
            <a href="{{ route('bookings.index-admin') }}"
               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-semibold transition duration-300 flex items-center gap-1.5 shadow-md">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Clear Filters
            </a>
        </div>
    </form>
</div>

@if($bookings->isEmpty())
    <div class="bg-white rounded-lg shadow-lg p-12 text-center">
        <div class="text-6xl mb-4">📅</div>
        <h3 class="text-2xl font-bold text-gray-800 mb-2">No Bookings Found</h3>
        <p class="text-gray-600 mb-6">There are no bookings matching your criteria</p>
    </div>
@else
    {{-- Table View --}}
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-purple-500 to-purple-600">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Booking ID
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        User
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Appointment
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Animals
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Adoption
                    </th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach($bookings as $booking)
                    @php
                        $statusKey = strtolower($booking->status);
                        $badgeColors = [
                            'pending' => 'bg-yellow-200 text-yellow-900 border-yellow-400',
                            'confirmed' => 'bg-blue-200 text-blue-900 border-blue-400',
                            'completed' => 'bg-green-200 text-green-900 border-green-500',
                            'cancelled' => 'bg-red-200 text-red-900 border-red-400',
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">📅</span>
                                <span class="text-sm font-bold text-purple-700">#{{ $booking->id }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($booking->user)
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $booking->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $booking->user->email }}</div>
                                </div>
                            @else
                                <span class="text-sm text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badgeColors[$statusKey] ?? 'bg-gray-100 text-gray-800 border-gray-300' }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 font-medium">
                                {{ \Carbon\Carbon::parse($booking->appointment_date)->format('M d, Y') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($booking->appointment_time)->format('h:i A') }}
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            @if($booking->animals->isNotEmpty())
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-xs font-bold">
                                        {{ $booking->animals->count() }}
                                    </span>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($booking->animals->take(2) as $animal)
                                            <span class="bg-purple-50 text-purple-700 px-2 py-1 rounded text-xs font-medium border border-purple-200">
                                                {{ $animal->name }}
                                            </span>
                                        @endforeach
                                        @if($booking->animals->count() > 2)
                                            <span class="bg-gray-50 text-gray-700 px-2 py-1 rounded text-xs font-medium border border-gray-200">
                                                +{{ $booking->animals->count() - 2 }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <span class="text-sm text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($booking->adoptions->isNotEmpty())
                                <button onclick="openAdoptionModal({{ $booking->id }})"
                                        class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold border border-green-200 hover:bg-green-200 transition-colors"
                                        title="View Adoption Records">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Yes
                                </button>
                            @else
                                <span class="text-sm text-gray-400">No adoption</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-center">
                            <button type="button"
                                    onclick="openBookingDetailModal({{ $booking->id }})"
                                    class="inline-flex items-center gap-1 bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition duration-200 shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($bookings->hasPages())
        <div class="mt-6">
            {{ $bookings->links() }}
        </div>
    @endif
@endif

{{-- Store booking data in JavaScript for detail modal --}}
<script>
    window.bookingsData = {!! json_encode($bookings->map(function($booking) {
        return [
            'id' => $booking->id,
            'status' => $booking->status,
            'appointment_date' => $booking->appointment_date,
            'appointment_time' => $booking->appointment_time,
            'created_at' => $booking->created_at,
            'user' => $booking->user ? [
                'name' => $booking->user->name,
                'email' => $booking->user->email,
                'phoneNum' => $booking->user->phoneNum ?? 'N/A'
            ] : null,
            'animals' => $booking->animals->map(function($animal) {
                return [
                    'id' => $animal->id,
                    'name' => $animal->name,
                    'species' => $animal->species,
                    'age' => $animal->age,
                    'gender' => $animal->gender,
                    'image_url' => $animal->images && $animal->images->count() > 0 ? $animal->images->first()->url : null
                ];
            })->toArray(),
            'adoptions' => $booking->adoptions->map(function($adoption) {
                return [
                    'id' => $adoption->id,
                    'fee' => $adoption->fee,
                    'remarks' => $adoption->remarks ?? 'No remarks',
                    'created_at' => $adoption->created_at->format('F d, Y'),
                    'transaction' => $adoption->transaction ? [
                        'amount' => $adoption->transaction->amount,
                        'status' => $adoption->transaction->status,
                        'bill_code' => $adoption->transaction->bill_code ?? null,
                        'reference_no' => $adoption->transaction->reference_no ?? null
                    ] : null
                ];
            })->toArray()
        ];
    })->toArray()) !!};
</script>
