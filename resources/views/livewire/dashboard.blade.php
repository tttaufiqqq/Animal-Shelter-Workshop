<div>

    <!-- Dashboard Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Booking Analytics Dashboard</h1>
            <p class="text-gray-600 mt-2">Overview of booking performance and trends</p>
        </div>

        <!-- Filters -->
        <div class="mb-6 flex gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                <select wire:model.live="selectedYear" class="w-80 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    @foreach($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Key Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
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

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Revenue by Species -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold text-gray-800">Revenue by Adopted Species</h2>
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

        <!-- Line Charts -->
        <div class="grid grid-cols-1 gap-6 mb-6">
            <!-- Bookings by Month -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800">Bookings by Month</h2>
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
                    <h2 class="text-xl font-bold text-gray-800">Adoption Volume vs Average Value (MoM)</h2>
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

                function initializeCharts() {
                    // Destroy existing charts to prevent memory leaks
                    if (bookingTypeChart) bookingTypeChart.destroy();
                    if (bookingsByMonthChart) bookingsByMonthChart.destroy();
                    if (volumeVsValueChart) volumeVsValueChart.destroy();

                    // Booking Type Pie Chart
                    const bookingTypeData = @json($bookingTypeBreakdown);
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
                    const bookingsByMonthData = @json($bookingsByMonth);
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
                    const volumeVsValueData = @json($volumeVsValue);
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

                // Re-initialize when Livewire updates (like when year changes)
                document.addEventListener('livewire:navigated', initializeCharts);

                // For Livewire v3
                Livewire.hook('morph.updated', () => {
                    initializeCharts();
                });
            </script>
        @endpush
    </div>
</div>
