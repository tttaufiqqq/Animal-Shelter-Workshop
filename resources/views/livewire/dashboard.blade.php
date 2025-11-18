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
                <select wire:model.live="selectedYear" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    @foreach($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Key Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-gray-500 text-sm font-medium">Total Bookings</p>
                    <div class="bg-blue-100 rounded-full p-2">
                        <span class="text-xl">📅</span>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-800">{{ $totalBookings }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-gray-500 text-sm font-medium">Successful Bookings</p>
                    <div class="bg-green-100 rounded-full p-2">
                        <span class="text-xl">✅</span>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-800">{{ $successfulBookings }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-gray-500 text-sm font-medium">Cancelled Bookings</p>
                    <div class="bg-red-100 rounded-full p-2">
                        <span class="text-xl">❌</span>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-800">{{ $cancelledBookings }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-gray-500 text-sm font-medium">Success Rate</p>
                    <div class="bg-purple-100 rounded-full p-2">
                        <span class="text-xl">📊</span>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-800">{{ $bookingSuccessRate }}%</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-gray-500 text-sm font-medium">Repeat Customer Rate</p>
                    <div class="bg-yellow-100 rounded-full p-2">
                        <span class="text-xl">🔄</span>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-800">{{ $repeatCustomerRate }}%</p>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Revenue by Species -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800">Revenue by Species</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($topAnimals as $animal)
                        <div>
                            <div class="flex justify-between text-sm mb-2">
                                <span class="font-semibold text-gray-800">{{ $animal->name }}</span>
                                <span class="text-gray-600">{{ number_format($animal->percentage, 2) }}% (RM {{ number_format($animal->total_revenue, 2) }})</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-purple-600 h-3 rounded-full transition-all duration-500" style="width: {{ $animal->percentage }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Booking Type Breakdown -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800">Booking Type Breakdown</h2>
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
                    <h2 class="text-xl font-bold text-gray-800">Booking Volume vs Average Value (MoM)</h2>
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
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                labels: {
                                    font: {
                                        size: 14
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        size: 12
                                    }
                                }
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
                        datasets: [{
                            label: 'Volume',
                            data: volumeVsValueData.map(item => item.volume),
                            backgroundColor: '#7C3AED',
                            yAxisID: 'y',
                        }, {
                            label: 'Avg Value',
                            data: volumeVsValueData.map(item => item.avg_value),
                            type: 'line',
                            borderColor: '#EF4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            borderWidth: 3,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            yAxisID: 'y1',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: {
                                    font: {
                                        size: 14
                                    }
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
                                    font: {
                                        size: 14,
                                        weight: 'bold'
                                    }
                                },
                                ticks: {
                                    font: {
                                        size: 12
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
                                    font: {
                                        size: 14,
                                        weight: 'bold'
                                    }
                                },
                                grid: {
                                    drawOnChartArea: false,
                                },
                                ticks: {
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        size: 12
                                    }
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