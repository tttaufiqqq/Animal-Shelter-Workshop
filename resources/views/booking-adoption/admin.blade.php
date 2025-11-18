<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Stray Animal Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    @include('navbar')

    <div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-5xl font-bold mb-4">All Bookings</h1>
                    <p class="text-xl text-purple-100">View and manage appointment bookings</p>
                </div>
                {{-- FIX: Close the @unless block and the div properly --}}
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-600 text-green-700 p-4 rounded-lg mb-8 shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="font-semibold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border-l-4 border-red-600 text-red-700 p-4 rounded-lg mb-8 shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="font-semibold">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-10">
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="text-5xl mb-4">📅</div>
                <p class="text-4xl font-bold text-purple-700 mb-2">{{ $bookings->count() }}</p>
                <p class="text-gray-600">Total Bookings</p>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="text-5xl mb-4">⏳</div>
                <p class="text-4xl font-bold text-yellow-600 mb-2">{{ $bookings->where('status', 'Pending')->count() }}</p>
                <p class="text-gray-600">Pending</p>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="text-5xl mb-4">✅</div>
                <p class="text-4xl font-bold text-blue-600 mb-2">{{ $bookings->where('status', 'Confirmed')->count() }}</p>
                <p class="text-gray-600">Confirmed</p>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="text-5xl mb-4">🎉</div>
                <p class="text-4xl font-bold text-green-600 mb-2">{{ $bookings->where('status', 'Completed')->count() }}</p>
                <p class="text-gray-600">Completed</p>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="text-5xl mb-4">❌</div>
                <p class="text-4xl font-bold text-red-600 mb-2">{{ $bookings->whereIn('status', ['Cancelled', 'cancelled'])->count() }}</p>
                <p class="text-gray-600">Cancelled</p>
            </div>
        </div>

        @if($bookings->isEmpty())
            <div class="bg-white rounded-lg shadow-lg p-12 text-center">
                <div class="mb-6">
                    <svg class="w-32 h-32 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-700 mb-3">No Bookings Yet</h3>
                <p class="text-gray-500 text-lg mb-8">You haven't made any bookings. Start by creating your first appointment!</p>
                <a href="{{ route('bookings.create') }}" class="inline-flex items-center gap-2 bg-purple-700 hover:bg-purple-800 text-white px-8 py-3 rounded-lg font-semibold transition duration-300 shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Create First Booking</span>
                </a>
            </div>
        @else
            <h2 class="text-3xl font-bold text-gray-800 mb-6">All Appointments</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($bookings as $booking)
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-2xl transition duration-300">
                        
                        <div class="relative">
                            @php
                                 $statusColors = [
                                    'pending'   => 'from-yellow-300 to-yellow-400',
                                    'confirmed' => 'from-blue-300 to-blue-400',
                                    'completed' => 'from-green-300 to-green-400',
                                    'cancelled' => 'from-red-300 to-red-400',
                                 ];
                                 $statusEmojis = [
                                    'pending'   => '⏳',
                                    'confirmed' => '✅',
                                    'completed' => '🎉',
                                    'cancelled' => '❌',
                                 ];

                                 // 1. Normalize the status to lowercase for the lookup
                                 $statusKey = strtolower($booking->status);

                                 // 2. Use the normalized key for safe retrieval with a default fallback (??)
                                 $statusColor = $statusColors[$statusKey] ?? 'from-gray-300 to-gray-400';
                                 $statusEmoji = $statusEmojis[$statusKey] ?? '📅';
                              @endphp
                            <div class="h-32 bg-gradient-to-br {{ $statusColor }} flex items-center justify-center">
                                <span class="text-7xl">{{ $statusEmoji }}</span>
                            </div>
                            @php
                                 $badgeColors = [
                                    'pending'   => 'bg-yellow-500',
                                    'confirmed' => 'bg-blue-500',
                                    'completed' => 'bg-green-500',
                                    'cancelled' => 'bg-red-500',
                                 ];

                                 // 1. Normalize the status to lowercase for the lookup key
                                 $statusKey = strtolower($booking->status);

                                 // 2. Safely retrieve the badge color class using the normalized key
                                 //    The ?? operator provides a default 'bg-gray-500' if the status is unrecognized.
                                 $badgeColor = $badgeColors[$statusKey] ?? 'bg-gray-500';

                                 // (Optional) Prepare the display status
                                 $displayStatus = ucwords($booking->status);
                              @endphp
                            <div class="absolute top-4 right-4 {{ $badgeColor }} text-white px-3 py-1 rounded-full text-sm font-semibold">
                                {{ ucfirst($booking->status) }}
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-2xl font-bold text-gray-800">Booking #{{ $booking->id }}</h3>
                            </div>

                            <div class="space-y-3 mb-4">
                                <div class="flex items-start">
                                    <div class="bg-purple-100 rounded-full p-2 mr-3 flex-shrink-0">
                                        <svg class="w-5 h-5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 font-medium">Appointment Date</p>
                                        <p class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($booking->appointment_date)->format('F d, Y') }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="bg-purple-100 rounded-full p-2 mr-3 flex-shrink-0">
                                        <svg class="w-5 h-5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 font-medium">Time</p>
                                        {{-- FIX: Corrected variable name from booking_time to appointment_time --}}
                                        <p class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($booking->appointment_time)->format('h:i A') }}</p>
                                    </div>
                                </div>

                                {{-- FIX: Changed $booking->animals->isNotEmpty() to check singular animal relationship $booking->animal --}}
                                @if($booking->animal)
                                    <div class="flex items-start">
                                        <div class="bg-purple-100 rounded-full p-2 mr-3 flex-shrink-0">
                                            <svg class="w-5 h-5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500 font-medium">Animal</p>
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                {{-- FIX: Accessing singular animal directly --}}
                                                <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded-full text-xs font-medium">
                                                    {{ $booking->animal->name ?? 'Animal #' . $booking->animal->id }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($booking->adoption)
                                    <div class="mt-3 p-3 bg-green-50 rounded-lg border border-green-200">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="text-sm font-semibold text-green-800">Adoption Confirmed</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="flex gap-2 mt-6">
                                <button onclick="viewBookingDetails({{ $booking->id }})" 
                                        class="flex-1 text-center bg-purple-700 hover:bg-purple-800 text-white py-3 rounded-lg font-semibold transition duration-300">
                                    View Details
                              </button>
                            </div>

                            <!-- Add this at the bottom of your bookings list page -->
                            <div id="bookingDetailsModalContainer"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-16 bg-gradient-to-r from-purple-700 to-purple-900 rounded-lg p-12 text-center text-white">
            <h2 class="text-3xl font-bold mb-4">Need Help with Your Booking?</h2>
            <p class="text-xl mb-6">Contact us if you have any questions about your appointments or need to make changes.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{route('contact')}}" class="bg-white text-purple-700 px-8 py-3 rounded-lg font-semibold hover:bg-purple-50 transition duration-300 inline-block">
                    Contact Support
                </a>
            </div>
        </div>
    </div>
    <script>
            function viewBookingDetails(bookingId) {
            const container = document.getElementById('bookingDetailsModalContainer');
            container.innerHTML = '<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"><div class="bg-white rounded-lg p-6"><i class="fas fa-spinner fa-spin mr-2"></i>Loading...</div></div>';
            
            fetch(`/bookings/${bookingId}/modal/admin`, {
                method: 'GET',
                headers: {
                    'Accept': 'text/html',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                container.innerHTML = html;
                
                // Attach event listeners after modal is loaded
                const modal = document.getElementById('bookingDetailsModal');
                if (modal) {
                    // Click outside to close
                    modal.addEventListener('click', function(e) {
                        if (e.target === this) {
                            container.innerHTML = '';
                        }
                    });
                }
                
                // Escape key to close
                const escapeHandler = function(e) {
                    if (e.key === 'Escape' && document.getElementById('bookingDetailsModal')) {
                        container.innerHTML = '';
                        document.removeEventListener('keydown', escapeHandler);
                    }
                };
                document.addEventListener('keydown', escapeHandler);
            })
            .catch(error => {
                console.error('Error loading booking details:', error);
                alert('Failed to load booking details: ' + error.message);
                container.innerHTML = '';
            });
        }

        // Global function for close button
        function closeBookingDetailsModal() {
            const container = document.getElementById('bookingDetailsModalContainer');
            if (container) {
                container.innerHTML = '';
            }
        }
</script>
</body>
</html>