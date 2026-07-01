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

                function initBookingTypeChart() {
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
                                            font: { size: 14 }
                                        }
                                    }
                                }
                            }
                        });
                    }
                }

                function initBookingsByMonthChart() {
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
                            plugins: [ChartDataLabels],
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        color: '#333',
                                        font: { weight: 'bold', size: 14 },
                                        formatter: function(value) { return value; }
                                    },
                                    legend: {
                                        display: true,
                                        labels: { font: { size: 14 } }
                                    }
                                },
                                scales: {
                                    y: { beginAtZero: true, ticks: { font: { size: 12 } } },
                                    x: { ticks: { font: { size: 12 } } }
                                }
                            }
                        });
                    }
                }
            </script>
        @endpush
