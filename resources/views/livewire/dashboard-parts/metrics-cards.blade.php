        {{-- Page Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Booking Analytics Dashboard</h1>
            <p class="text-sm text-gray-600 mt-1">Overview of booking performance and trends</p>
        </div>

        {{-- Key Metrics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <!-- Total Bookings -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 p-6 border border-blue-200 group hover:scale-105">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-blue-700 text-sm font-semibold uppercase tracking-wide">Total Bookings</p>
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-2.5 shadow-md group-hover:shadow-lg transition-shadow">
                        <i class="fas fa-calendar-alt text-white text-lg"></i>
                    </div>
                </div>
                <p class="text-4xl font-bold text-blue-900 mb-1">{{ $totalBookings }}</p>
                <p class="text-xs text-blue-600 font-medium">All time bookings</p>
            </div>

            <!-- Successful Bookings -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 p-6 border border-green-200 group hover:scale-105">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-green-700 text-sm font-semibold uppercase tracking-wide">Successful</p>
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-2.5 shadow-md group-hover:shadow-lg transition-shadow">
                        <i class="fas fa-check-circle text-white text-lg"></i>
                    </div>
                </div>
                <p class="text-4xl font-bold text-green-900 mb-1">{{ $successfulBookings }}</p>
                <p class="text-xs text-green-600 font-medium">Completed adoptions</p>
            </div>

            <!-- Cancelled Bookings -->
            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 p-6 border border-red-200 group hover:scale-105">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-red-700 text-sm font-semibold uppercase tracking-wide">Cancelled</p>
                    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg p-2.5 shadow-md group-hover:shadow-lg transition-shadow">
                        <i class="fas fa-times-circle text-white text-lg"></i>
                    </div>
                </div>
                <p class="text-4xl font-bold text-red-900 mb-1">{{ $cancelledBookings }}</p>
                <p class="text-xs text-red-600 font-medium">Cancelled visits</p>
            </div>

            <!-- Success Rate -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 p-6 border border-purple-200 group hover:scale-105">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-purple-700 text-sm font-semibold uppercase tracking-wide">Success Rate</p>
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-2.5 shadow-md group-hover:shadow-lg transition-shadow">
                        <i class="fas fa-chart-line text-white text-lg"></i>
                    </div>
                </div>
                <p class="text-4xl font-bold text-purple-900 mb-1">{{ $bookingSuccessRate }}%</p>
                <p class="text-xs text-purple-600 font-medium">Completion rate</p>
            </div>

            <!-- Repeat Customer Rate -->
            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 p-6 border border-amber-200 group hover:scale-105">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-amber-700 text-sm font-semibold uppercase tracking-wide">Repeat Rate</p>
                    <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg p-2.5 shadow-md group-hover:shadow-lg transition-shadow">
                        <i class="fas fa-redo-alt text-white text-lg"></i>
                    </div>
                </div>
                <p class="text-4xl font-bold text-amber-900 mb-1">{{ $repeatCustomerRate }}%</p>
                <p class="text-xs text-amber-600 font-medium">Returning customers</p>
            </div>
        </div>
