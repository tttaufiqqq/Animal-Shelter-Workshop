@if($bookings->isEmpty())
    <div class="bg-white rounded-lg shadow-lg p-12 text-center">
        <div class="text-6xl mb-4">📅</div>
        <h3 class="text-2xl font-bold text-gray-800 mb-2">No Bookings Found</h3>
        <p class="text-gray-600 mb-6">There are no bookings matching your criteria</p>
    </div>
@else
    {{-- Table View --}}
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-purple-500 to-purple-600">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Booking ID
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        User
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Appointment
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Adoption
                    </th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach($bookings as $booking)
                    @php
                        $statusKey = strtolower($booking->status);
                        $badgeColors = [
                            'pending' => 'bg-yellow-200 text-yellow-900 border-yellow-400',
                            'confirmed' => 'bg-blue-200 text-blue-900 border-blue-400',
                            'completed' => 'bg-green-200 text-green-900 border-green-500',
                            'cancelled' => 'bg-red-200 text-red-900 border-red-400',
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">📅</span>
                                <span class="text-sm font-bold text-purple-700">#{{ $booking->id }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($booking->user)
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $booking->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $booking->user->email }}</div>
                                </div>
                            @else
                                <span class="text-sm text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badgeColors[$statusKey] ?? 'bg-gray-100 text-gray-800 border-gray-300' }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 font-medium">
                                {{ \Carbon\Carbon::parse($booking->appointment_date)->format('M d, Y') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($booking->appointment_time)->format('h:i A') }}
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($booking->adoptions->isNotEmpty())
                                <button onclick="openAdoptionModal({{ $booking->id }})"
                                        class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold border border-green-200 hover:bg-green-200 transition-colors"
                                        title="View Adoption Records">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Yes
                                </button>
                            @else
                                <span class="text-sm text-gray-400">No adoption</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-center">
                            <button type="button"
                                    onclick="openBookingDetailModal({{ $booking->id }})"
                                    class="inline-flex items-center gap-1 bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition duration-200 shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($bookings->hasPages())
        <div class="mt-6">
            {{ $bookings->links() }}
        </div>
    @endif
@endif

{{-- Store booking data in JavaScript for detail modal --}}
<script>
    window.bookingsData = {!! json_encode($bookings->map(function($booking) {
        return [
            'id' => $booking->id,
            'status' => $booking->status,
            'appointment_date' => $booking->appointment_date,
            'appointment_time' => $booking->appointment_time,
            'created_at' => $booking->created_at,
            'user' => $booking->user ? [
                'name' => $booking->user->name,
                'email' => $booking->user->email,
                'phoneNum' => $booking->user->phoneNum ?? 'N/A'
            ] : null,
            'animals' => $booking->animals->map(function($animal) {
                return [
                    'id' => $animal->id,
                    'name' => $animal->name,
                    'species' => $animal->species,
                    'age' => $animal->age,
                    'gender' => $animal->gender,
                    'image_url' => ($animal->relationLoaded('images') && $animal->images->count() > 0) ? $animal->images->first()->url : null
                ];
            })->toArray(),
            'adoptions' => $booking->adoptions->map(function($adoption) {
                return [
                    'id' => $adoption->id,
                    'fee' => $adoption->fee,
                    'remarks' => $adoption->remarks ?? 'No remarks',
                    'created_at' => $adoption->created_at->format('F d, Y'),
                    'transaction' => $adoption->transaction ? [
                        'amount' => $adoption->transaction->amount,
                        'status' => $adoption->transaction->status,
                        'bill_code' => $adoption->transaction->bill_code ?? null,
                        'reference_no' => $adoption->transaction->reference_no ?? null
                    ] : null
                ];
            })->toArray()
        ];
    })->toArray()) !!};
</script>
