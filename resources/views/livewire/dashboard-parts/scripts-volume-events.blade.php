        @push('scripts')
            <script>
                function initVolumeVsValueChart() {
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
                                        backgroundColor: 'rgba(124, 58, 237, 0.5)',
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
                                    legend: { labels: { font: { size: 14 } } }
                                },
                                scales: {
                                    y: {
                                        type: 'linear',
                                        display: true,
                                        position: 'left',
                                        title: { display: true, text: 'Volume', font: { size: 14, weight: 'bold' } },
                                        ticks: {
                                            font: { size: 12 },
                                            callback: function(value) { return value + ' Adoptions'; }
                                        }
                                    },
                                    y1: {
                                        type: 'linear',
                                        display: true,
                                        position: 'right',
                                        title: { display: true, text: 'Average Value', font: { size: 14, weight: 'bold' } },
                                        grid: { drawOnChartArea: false },
                                        ticks: {
                                            font: { size: 12 },
                                            callback: function(value) { return 'RM ' + Number(value).toFixed(2); }
                                        }
                                    },
                                    x: { ticks: { font: { size: 12 } } }
                                }
                            }
                        });
                    }
                }

                function initializeCharts() {
                    // Destroy existing charts to prevent memory leaks
                    if (bookingTypeChart) bookingTypeChart.destroy();
                    if (bookingsByMonthChart) bookingsByMonthChart.destroy();
                    if (volumeVsValueChart) volumeVsValueChart.destroy();

                    initBookingTypeChart();
                    initBookingsByMonthChart();
                    initVolumeVsValueChart();
                }

                // Initialize on page load
                document.addEventListener('DOMContentLoaded', initializeCharts);

                // Re-initialize when Livewire navigates
                document.addEventListener('livewire:navigated', initializeCharts);
            </script>
        @endpush
