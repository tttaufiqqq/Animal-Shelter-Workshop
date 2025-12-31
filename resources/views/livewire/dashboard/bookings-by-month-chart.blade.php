<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-800">Bookings by Month</h2>
    </div>
    <div class="p-6">
        <div class="h-96">
            <canvas id="bookingsByMonthChart"></canvas>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                initializeBookingsByMonthChart();
            });

            document.addEventListener('livewire:navigated', function() {
                initializeBookingsByMonthChart();
            });

            Livewire.hook('morph.updated', () => {
                initializeBookingsByMonthChart();
            });

            let bookingsByMonthChart;

            function initializeBookingsByMonthChart() {
                if (bookingsByMonthChart) {
                    bookingsByMonthChart.destroy();
                }

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
                        plugins: [ChartDataLabels],
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
                                        return value;
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
            }
        </script>
    @endpush
</div>
