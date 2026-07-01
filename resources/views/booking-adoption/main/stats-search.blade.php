    @if (session('success'))
        <div class="flex items-start gap-3 p-4 mb-6 bg-green-50 border border-green-200 rounded-xl shadow-sm">
            <i class="fas fa-check-circle text-green-600 text-xl flex-shrink-0"></i>
            <p class="font-semibold text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-start gap-3 p-4 mb-6 bg-red-50 border border-red-200 rounded-xl shadow-sm">
            <i class="fas fa-times-circle text-red-600 text-xl flex-shrink-0"></i>
            <p class="font-semibold text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Stats Cards as Filter Buttons -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-10">
        <!-- Total Bookings Card -->
        <a href="{{ route('bookings.index') }}"
           class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 cursor-pointer {{ !request('status') ? 'ring-4 ring-purple-500' : '' }}">
            <div class="flex justify-center mb-4">
                <i class="fas fa-calendar-alt text-purple-600" style="font-size: 4rem;"></i>
            </div>
            <p class="text-4xl font-bold text-purple-700 mb-2">{{ $totalBookings }}</p>
            <p class="text-gray-600">Total Bookings</p>
            @if(!request('status'))
                <div class="mt-2 text-xs text-purple-600 font-semibold">● Active</div>
            @endif
        </a>

        <!-- Pending Card -->
        <a href="{{ route('bookings.index', ['status' => 'Pending']) }}"
           class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 cursor-pointer {{ request('status') == 'Pending' ? 'ring-4 ring-yellow-500' : '' }}">
            <div class="flex justify-center mb-4">
                <i class="fas fa-clock text-yellow-600" style="font-size: 4rem;"></i>
            </div>
            <p class="text-4xl font-bold text-yellow-600 mb-2">{{ $statusCounts['Pending'] ?? 0 }}</p>
            <p class="text-gray-600">Pending</p>
            @if(request('status') == 'Pending')
                <div class="mt-2 text-xs text-yellow-600 font-semibold">● Active</div>
            @endif
        </a>

        <!-- Confirmed Card -->
        <a href="{{ route('bookings.index', ['status' => 'Confirmed']) }}"
           class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 cursor-pointer {{ request('status') == 'Confirmed' ? 'ring-4 ring-blue-500' : '' }}">
            <div class="flex justify-center mb-4">
                <i class="fas fa-check-circle text-blue-600" style="font-size: 4rem;"></i>
            </div>
            <p class="text-4xl font-bold text-blue-600 mb-2">{{ $statusCounts['Confirmed'] ?? 0 }}</p>
            <p class="text-gray-600">Confirmed</p>
            @if(request('status') == 'Confirmed')
                <div class="mt-2 text-xs text-blue-600 font-semibold">● Active</div>
            @endif
        </a>

        <!-- Completed Card -->
        <a href="{{ route('bookings.index', ['status' => 'Completed']) }}"
           class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 cursor-pointer {{ request('status') == 'Completed' ? 'ring-4 ring-green-500' : '' }}">
            <div class="flex justify-center mb-4">
                <i class="fas fa-check-circle text-green-600" style="font-size: 4rem;"></i>
            </div>
            <p class="text-4xl font-bold text-green-600 mb-2">{{ $statusCounts['Completed'] ?? 0 }}</p>
            <p class="text-gray-600">Completed</p>
            @if(request('status') == 'Completed')
                <div class="mt-2 text-xs text-green-600 font-semibold">● Active</div>
            @endif
        </a>

        <!-- Cancelled Card -->
        <a href="{{ route('bookings.index', ['status' => 'Cancelled']) }}"
           class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 cursor-pointer {{ request('status') == 'Cancelled' ? 'ring-4 ring-red-500' : '' }}">
            <div class="flex justify-center mb-4">
                <i class="fas fa-times-circle text-red-600" style="font-size: 4rem;"></i>
            </div>
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
                <i class="fas fa-search text-purple-600"></i>
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
                    <i class="fas fa-search"></i>
                    Search
                </button>
                <a href="{{ route('bookings.index') }}"
                   class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold transition duration-300 flex items-center gap-2">
                    <i class="fas fa-times"></i>
                    Clear
                </a>
            </div>

            @if(request()->hasAny(['search', 'date_from', 'date_to']))
                <div class="mt-3 text-sm text-purple-600 font-medium">
                    <span class="inline-flex items-center gap-1">
                        <i class="fas fa-info-circle"></i>
                        Active filters applied
                    </span>
                </div>
            @endif
        </form>
    </div>
