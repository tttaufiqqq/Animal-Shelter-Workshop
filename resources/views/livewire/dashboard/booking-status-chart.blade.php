<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-800">Booking Status Breakdown</h2>
    </div>
    <div class="p-6">
        <div class="h-80 flex justify-center items-center">
            <canvas id="bookingTypeChart"></canvas>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                initializeBookingTypeChart();
            });

            document.addEventListener('livewire:navigated', function() {
                initializeBookingTypeChart();
            });

            Livewire.hook('morph.updated', () => {
                initializeBookingTypeChart();
            });

            let bookingTypeChart;

            function initializeBookingTypeChart() {
                if (bookingTypeChart) {
                    bookingTypeChart.destroy();
                }

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
            }
        </script>
    @endpush
</div>
