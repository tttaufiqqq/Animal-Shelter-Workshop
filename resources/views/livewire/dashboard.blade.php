<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Booking Analytics Dashboard</h1>
            <p class="text-gray-600 mt-1">Overview of booking performance and trends</p>
        </div>

        <!-- Year Filter -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
            <select wire:model.live="selectedYear" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                @foreach($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>

        <!-- Key Metrics Cards - Row 1 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-gray-500 text-sm font-medium">Total Bookings</p>
                    <div class="bg-blue-100 rounded-lg p-2">
                        <span class="text-lg">📅</span>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($totalBookings) }}</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-gray-500 text-sm font-medium">Successful Bookings</p>
                    <div class="bg-green-100 rounded-lg p-2">
                        <span class="text-lg">✅</span>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($successfulBookings) }}</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-gray-500 text-sm font-medium">Cancelled Bookings</p>
                    <div class="bg-red-100 rounded-lg p-2">
                        <span class="text-lg">❌</span>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($cancelledBookings) }}</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-gray-500 text-sm font-medium">Success Rate</p>
                    <div class="bg-purple-100 rounded-lg p-2">
                        <span class="text-lg">📊</span>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-800">{{ $bookingSuccessRate }}%</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-gray-500 text-sm font-medium">Repeat Customer Rate</p>
                    <div class="bg-yellow-100 rounded-lg p-2">
                        <span class="text-lg">🔄</span>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-800">{{ $repeatCustomerRate }}%</p>
            </div>
        </div>

        <!-- Financial Metrics Cards - Row 2 -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-xl shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-purple-100 text-sm font-medium">Total Revenue</p>
                    <div class="bg-purple-500 rounded-lg p-2">
                        <span class="text-lg">💰</span>
                    </div>
                </div>
                <p class="text-3xl font-bold text-white">RM {{ number_format($totalRevenue, 2) }}</p>
            </div>

            <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-xl shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-green-100 text-sm font-medium">Total Adoptions</p>
                    <div class="bg-green-500 rounded-lg p-2">
                        <span class="text-lg">🐾</span>
                    </div>
                </div>
                <p class="text-3xl font-bold text-white">{{ number_format($totalAdoptions) }}</p>
            </div>

            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-blue-100 text-sm font-medium">Avg Adoption Fee</p>
                    <div class="bg-blue-500 rounded-lg p-2">
                        <span class="text-lg">📈</span>
                    </div>
                </div>
                <p class="text-3xl font-bold text-white">RM {{ number_format($avgAdoptionFee, 2) }}</p>
            </div>
        </div>

        <!-- Revenue & Booking Status Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Revenue by Species -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-bold text-gray-800">Revenue by Species</h2>
                        <div class="text-right">
                            <p class="text-xs text-gray-500">Total Revenue</p>
                            <p class="text-xl font-bold text-purple-600">RM {{ number_format($topAnimals['totalRevenue'], 2) }}</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-5">
                        @forelse($topAnimals['animals'] as $animal)
                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="font-medium text-gray-700">{{ $animal->name }}</span>
                                    <span class="text-gray-500">{{ number_format($animal->percentage, 2) }}% (RM {{ number_format($animal->total_revenue, 2) }})</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="bg-purple-500 h-3 rounded-full transition-all duration-500" style="width: {{ $animal->percentage }}%"></div>
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

            <!-- Booking Status Breakdown -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-800">Booking Status Breakdown</h2>
                </div>
                <div class="p-6">
                    <div class="h-72 flex justify-center items-center">
                        <canvas id="bookingTypeChart" wire:ignore></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue by Month & Transaction Status -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Revenue by Month -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-800">Revenue by Month</h2>
                </div>
                <div class="p-6">
                    <div class="h-72">
                        <canvas id="revenueByMonthChart" wire:ignore></canvas>
                    </div>
                </div>
            </div>

            <!-- Transaction Status -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-800">Transaction Status</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($transactionStatus as $status)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-800">{{ $status->status }}</p>
                                    <p class="text-sm text-gray-500">{{ $status->count }} transactions</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-800">RM {{ number_format($status->total_amount, 2) }}</p>
                                    <p class="text-sm text-purple-600">{{ $status->percentage }}%</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <p>No transaction data available</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Bookings by Month -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-800">Bookings by Month</h2>
            </div>
            <div class="p-6">
                <div class="h-80">
                    <canvas id="bookingsByMonthChart" wire:ignore></canvas>
                </div>
            </div>
        </div>

        <!-- Volume vs Average Value -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-800">Adoption Volume vs Average Value (MoM)</h2>
            </div>
            <div class="p-6">
                <div class="h-80">
                    <canvas id="volumeVsValueChart" wire:ignore></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
        let bookingTypeChart, bookingsByMonthChart, volumeVsValueChart, revenueByMonthChart;

        function initializeCharts() {
            if (bookingTypeChart) bookingTypeChart.destroy();
            if (bookingsByMonthChart) bookingsByMonthChart.destroy();
            if (volumeVsValueChart) volumeVsValueChart.destroy();
            if (revenueByMonthChart) revenueByMonthChart.destroy();

            // Booking Status Donut Chart
            const bookingTypeData = @json($bookingTypeBreakdown);
            const bookingTypeCtx = document.getElementById('bookingTypeChart');

            if (bookingTypeCtx && bookingTypeData.length > 0) {
                bookingTypeChart = new Chart(bookingTypeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: bookingTypeData.map(item => item.status),
                        datasets: [{
                            data: bookingTypeData.map(item => item.count),
                            backgroundColor: ['#8B5CF6', '#10B981', '#EF4444', '#F59E0B', '#3B82F6'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '55%',
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    padding: 15,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    font: { size: 12 }
                                }
                            }
                        }
                    }
                });
            }

            // Revenue by Month Bar Chart
            const revenueByMonthData = @json($revenueByMonth);
            const revenueByMonthCtx = document.getElementById('revenueByMonthChart');

            if (revenueByMonthCtx && revenueByMonthData.length > 0) {
                revenueByMonthChart = new Chart(revenueByMonthCtx, {
                    type: 'bar',
                    data: {
                        labels: revenueByMonthData.map(item => item.month_name),
                        datasets: [{
                            label: 'Revenue (RM)',
                            data: revenueByMonthData.map(item => item.revenue),
                            backgroundColor: '#8B5CF6',
                            borderRadius: 6
                        }]
                    },
                    plugins: [ChartDataLabels],
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                color: '#374151',
                                font: { weight: 'bold', size: 10 },
                                formatter: (value) => 'RM ' + Number(value).toLocaleString()
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#F3F4F6' },
                                ticks: {
                                    font: { size: 11 },
                                    callback: (value) => 'RM ' + value.toLocaleString()
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 11 } }
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
                            borderColor: '#8B5CF6',
                            backgroundColor: 'rgba(139, 92, 246, 0.15)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 3,
                            pointRadius: 5,
                            pointBackgroundColor: '#8B5CF6',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointHoverRadius: 7
                        }]
                    },
                    plugins: [ChartDataLabels],
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                align: 'end',
                                labels: {
                                    usePointStyle: true,
                                    pointStyle: 'rect',
                                    font: { size: 12 }
                                }
                            },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                offset: 5,
                                color: '#374151',
                                font: { weight: 'bold', size: 12 },
                                formatter: (value) => value
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#F3F4F6' },
                                ticks: { font: { size: 12 }, color: '#6B7280' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 12 }, color: '#6B7280' }
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
                                backgroundColor: '#8B5CF6',
                                borderRadius: 4,
                                yAxisID: 'y',
                            },
                            {
                                label: 'Avg Value',
                                data: volumeVsValueData.map(item => item.avg_value),
                                type: 'line',
                                borderColor: '#EF4444',
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                pointRadius: 5,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: '#EF4444',
                                pointBorderWidth: 2,
                                pointHoverRadius: 7,
                                yAxisID: 'y1',
                                tension: 0.3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                align: 'center',
                                labels: {
                                    usePointStyle: true,
                                    pointStyle: 'rect',
                                    padding: 20,
                                    font: { size: 12 }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        if (context.dataset.label === 'Avg Value') {
                                            return 'Avg Value: RM ' + Number(context.parsed.y).toFixed(2);
                                        }
                                        return 'Volume: ' + context.parsed.y + ' Adoptions';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                position: 'left',
                                beginAtZero: true,
                                grid: { color: '#F3F4F6' },
                                title: {
                                    display: true,
                                    text: 'Volume',
                                    font: { size: 12, weight: 'bold' },
                                    color: '#374151'
                                },
                                ticks: {
                                    font: { size: 11 },
                                    color: '#6B7280',
                                    callback: (value) => value + ' Adoptions'
                                }
                            },
                            y1: {
                                type: 'linear',
                                position: 'right',
                                beginAtZero: true,
                                grid: { drawOnChartArea: false },
                                title: {
                                    display: true,
                                    text: 'Average Value',
                                    font: { size: 12, weight: 'bold' },
                                    color: '#374151'
                                },
                                ticks: {
                                    font: { size: 11 },
                                    color: '#6B7280',
                                    callback: (value) => 'RM ' + Number(value).toFixed(2)
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 12 }, color: '#6B7280' }
                            }
                        }
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', initializeCharts);
        document.addEventListener('livewire:navigated', initializeCharts);

        Livewire.hook('morph.updated', () => {
            setTimeout(initializeCharts, 100);
        });
    </script>
@endpush
