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
