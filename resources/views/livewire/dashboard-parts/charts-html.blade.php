        {{-- Charts Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenue by Species -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold text-gray-800">Revenue by Adopted Species (All Time)</h2>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Total Revenue</p>
                            <p class="text-2xl font-bold text-purple-700">
                                RM {{ number_format($topAnimals->sum('total_revenue'), 2) }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($topAnimals as $animal)
                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="font-semibold text-gray-800">{{ $animal->name }}</span>
                                    <span class="text-gray-600">{{ number_format($animal->percentage, 2) }}% (RM {{ number_format($animal->total_revenue, 2) }})</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="bg-purple-600 h-3 rounded-full transition-all duration-500" style="width: {{ $animal->percentage }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <p>No revenue data available</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Booking Type Breakdown -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800">Booking Status Breakdown</h2>
                </div>
                <div class="p-6">
                    <div class="h-80 flex justify-center items-center">
                        <canvas id="bookingTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Line Charts --}}
        <div class="grid grid-cols-1 gap-6">
            <!-- Bookings by Month -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800">Bookings by Month (Last 12 Months)</h2>
                </div>
                <div class="p-6">
                    <div class="h-96">
                        <canvas id="bookingsByMonthChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Volume vs Average Value -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800">Adoption Volume vs Average Value (Last 6 Months)</h2>
                </div>
                <div class="p-6">
                    <div class="h-96">
                        <canvas id="volumeVsValueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
