<div>
    {{-- Database Warning Banner --}}
    @if(isset($dbDisconnected) && count($dbDisconnected) > 0)
        <div class="mb-4 flex items-center gap-2 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded-lg">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="flex-1">
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
            <button onclick="this.parentElement.remove()" class="flex-shrink-0 text-yellow-400 hover:text-yellow-600 transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    @endif

    <!-- Dashboard Content -->
    <div class="space-y-6">
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

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
            <script>
                let bookingTypeChart, bookingsByMonthChart, volumeVsValueChart;

                // Store chart data - initially from PHP, then updated via Livewire events
                let chartData = {
                    bookingTypeBreakdown: @json($bookingTypeBreakdown),
                    bookingsByMonth: @json($bookingsByMonth),
                    volumeVsValue: @json($volumeVsValue)
                };

                function initializeCharts() {
                    // Destroy existing charts to prevent memory leaks
                    if (bookingTypeChart) bookingTypeChart.destroy();
                    if (bookingsByMonthChart) bookingsByMonthChart.destroy();
                    if (volumeVsValueChart) volumeVsValueChart.destroy();

                    // Booking Type Pie Chart
                    const bookingTypeData = chartData.bookingTypeBreakdown;
                    const bookingTypeCtx = document.getElementById('bookingTypeChart');

                    if (bookingTypeCtx && bookingTypeData.length > 0) {
                        bookingTypeChart = new Chart(bookingTypeCtx, {
                            type: 'doughnut',
                            data: {
                                labels: bookingTypeData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1)),
                                datasets: [{
                                    data: bookingTypeData.map(item => item.count),
                                    backgroundColor: ['#7C3AED', '#10B981', '#EF4444', '#F59E0B', '#3B82F6'],
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        position: 'right',
                                        labels: {
                                            padding: 15,
                                            font: {
                                                size: 14
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // Bookings by Month Line Chart
                    const bookingsByMonthData = chartData.bookingsByMonth;
                    const bookingsByMonthCtx = document.getElementById('bookingsByMonthChart');

                    if (bookingsByMonthCtx && bookingsByMonthData.length > 0) {
                        bookingsByMonthChart = new Chart(bookingsByMonthCtx, {
                            type: 'line',
                            data: {
                                labels: bookingsByMonthData.map(item => item.month_name),
                                datasets: [{
                                    label: 'Bookings',
                                    data: bookingsByMonthData.map(item => item.count),
                                    borderColor: '#7C3AED',
                                    backgroundColor: 'rgba(124, 58, 237, 0.1)',
                                    tension: 0.4,
                                    fill: true,
                                    borderWidth: 3,
                                    pointRadius: 5,
                                    pointHoverRadius: 7
                                }]
                            },
                            plugins: [ChartDataLabels],   // ← ENABLE plugin
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        color: '#333',
                                        font: {
                                            weight: 'bold',
                                            size: 14
                                        },
                                        formatter: function(value) {
                                            return value; // show exact number
                                        }
                                    },
                                    legend: {
                                        display: true,
                                        labels: {
                                            font: { size: 14 }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: { font: { size: 12 } }
                                    },
                                    x: {
                                        ticks: { font: { size: 12 } }
                                    }
                                }
                            }
                        });

                    }

                    // Volume vs Average Value Chart
                    const volumeVsValueData = chartData.volumeVsValue;
                    const volumeVsValueCtx = document.getElementById('volumeVsValueChart');

                    if (volumeVsValueCtx && volumeVsValueData.length > 0) {
                        volumeVsValueChart = new Chart(volumeVsValueCtx, {
                            type: 'bar',
                            data: {
                                labels: volumeVsValueData.map(item => item.month_name),
                                datasets: [
                                    {
                                        label: 'Volume',
                                        data: volumeVsValueData.map(item => item.volume),
                                        backgroundColor: 'rgba(124, 58, 237, 0.5)', // 50% transparent purple
                                        borderColor: '#7C3AED',
                                        borderWidth: 1,
                                        yAxisID: 'y',
                                    },
                                    {
                                        label: 'Avg Value',
                                        data: volumeVsValueData.map(item => item.avg_value),
                                        type: 'line',
                                        borderColor: '#EF4444',
                                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                        borderWidth: 3,
                                        pointRadius: 5,
                                        pointHoverRadius: 7,
                                        yAxisID: 'y1',
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                let value = context.parsed.y;

                                                if (context.dataset.label === 'Avg Value') {
                                                    return 'Avg Value: RM ' + Number(value).toFixed(2);
                                                }

                                                if (context.dataset.label === 'Volume') {
                                                    return 'Volume: ' + value + ' Adoptions';
                                                }

                                                return context.dataset.label + ': ' + value;
                                            }
                                        }
                                    },
                                    legend: {
                                        labels: {
                                            font: { size: 14 }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        type: 'linear',
                                        display: true,
                                        position: 'left',
                                        title: {
                                            display: true,
                                            text: 'Volume',
                                            font: { size: 14, weight: 'bold' }
                                        },
                                        ticks: {
                                            font: { size: 12 },
                                            callback: function(value) {
                                                return value + ' Adoptions';
                                            }
                                        }
                                    },
                                    y1: {
                                        type: 'linear',
                                        display: true,
                                        position: 'right',
                                        title: {
                                            display: true,
                                            text: 'Average Value',
                                            font: { size: 14, weight: 'bold' }
                                        },
                                        grid: { drawOnChartArea: false },
                                        ticks: {
                                            font: { size: 12 },
                                            callback: function(value) {
                                                return 'RM ' + Number(value).toFixed(2);
                                            }
                                        }
                                    },
                                    x: {
                                        ticks: {
                                            font: { size: 12 }
                                        }
                                    }
                                }
                            }
                        });
                    }
                }

                // Initialize on page load
                document.addEventListener('DOMContentLoaded', initializeCharts);

                // Re-initialize when Livewire navigates
                document.addEventListener('livewire:navigated', initializeCharts);
            </script>
        @endpush
    </div>
</div>
