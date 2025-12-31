{{-- Step 1: Booking Details --}}
<div class="space-y-5">

    {{-- Status Badge --}}
    <div class="flex justify-between items-center bg-white rounded-xl p-5 border border-gray-200">
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Status</p>
            <h3 class="text-lg font-semibold text-gray-900">{{ $booking->status }}</h3>
        </div>
        @php
            $statusColors = [
                'pending' => 'bg-yellow-100 text-yellow-700',
                'confirmed' => 'bg-blue-100 text-blue-700',
                'completed' => 'bg-green-100 text-green-700',
                'cancelled' => 'bg-red-100 text-red-700',
            ];
            $statusKey = strtolower($booking->status);
        @endphp
        <div class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $statusColors[$statusKey] ?? 'bg-gray-100 text-gray-700' }}">
            {{ $booking->status }}
        </div>
    </div>

    {{-- Appointment Details --}}
    <div class="bg-white rounded-xl p-5 border border-gray-200">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-4">Appointment</p>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600 mb-1">Date</p>
                <p class="text-base font-semibold text-gray-900">
                    {{ \Carbon\Carbon::parse($booking->appointment_date)->format('M d, Y') }}
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600 mb-1">Time</p>
                <p class="text-base font-semibold text-gray-900">
                    {{ \Carbon\Carbon::parse($booking->appointment_time)->format('g:i A') }}
                </p>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-sm text-gray-600 mb-1">Booked On</p>
            <p class="text-base font-semibold text-gray-900">
                {{ $booking->created_at->format('M d, Y') }}
            </p>
        </div>
    </div>

    {{-- Animals in Booking --}}
    @if($booking->animals && $booking->animals->isNotEmpty())
        <div class="bg-white rounded-xl p-5 border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Animals</p>
                <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-md text-xs font-medium">
                    {{ $booking->animals->count() }} {{ $booking->animals->count() === 1 ? 'Animal' : 'Animals' }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($booking->animals as $animal)
                    <div class="flex gap-3 p-3 rounded-lg border border-gray-100 hover:border-purple-200 hover:bg-purple-50 transition-colors">
                        @if($animal->images && $animal->images->count() > 0)
                            <img src="{{ $animal->images->first()->url }}"
                                 alt="{{ $animal->name }}"
                                 class="w-16 h-16 object-cover rounded-lg flex-shrink-0">
                        @else
                            <div class="w-16 h-16 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <span class="text-2xl">
                                    @if(strtolower($animal->species) == 'dog') 🐕
                                    @elseif(strtolower($animal->species) == 'cat') 🐈
                                    @else 🐾
                                    @endif
                                </span>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 truncate">{{ $animal->name }}</p>
                            <p class="text-sm text-gray-600 mt-0.5">{{ $animal->species }} • {{ $animal->age }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- User Information --}}
    @if($booking->user)
        <div class="bg-white rounded-xl p-5 border border-gray-200">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-4">Contact Information</p>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Name</p>
                    <p class="text-base font-semibold text-gray-900">{{ $booking->user->name }}</p>
                </div>
                <div class="pt-3 border-t border-gray-100">
                    <p class="text-sm text-gray-600 mb-1">Email</p>
                    <p class="text-base font-semibold text-gray-900">{{ $booking->user->email }}</p>
                </div>
                @if($booking->user->phoneNum)
                <div class="pt-3 border-t border-gray-100">
                    <p class="text-sm text-gray-600 mb-1">Phone</p>
                    <p class="text-base font-semibold text-gray-900">{{ $booking->user->phoneNum }}</p>
                </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Important Information --}}
    @if(in_array(strtolower($booking->status), ['pending', 'confirmed']))
        <div class="bg-amber-50 rounded-xl p-4 border border-amber-200">
            <p class="text-xs font-medium text-amber-900 uppercase tracking-wide mb-3">Important Reminders</p>
            <ul class="space-y-2 text-sm text-gray-700">
                <li class="flex gap-2">
                    <span class="text-amber-600 mt-0.5">•</span>
                    <span>Arrive 10 minutes before your appointment</span>
                </li>
                <li class="flex gap-2">
                    <span class="text-amber-600 mt-0.5">•</span>
                    <span>Bring a valid government-issued ID</span>
                </li>
                <li class="flex gap-2">
                    <span class="text-amber-600 mt-0.5">•</span>
                    <span>Be prepared to discuss your living situation</span>
                </li>
                <li class="flex gap-2">
                    <span class="text-amber-600 mt-0.5">•</span>
                    <span>Cancel or reschedule 24 hours in advance if needed</span>
                </li>
            </ul>
        </div>
    @endif

    {{-- Action Buttons --}}
    @if(in_array(strtolower($booking->status), ['pending', 'confirmed']))
        <div class="flex gap-3 justify-end pt-4">
            <button type="button"
                    onclick="openCancelModal({{ $booking->id }})"
                    class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition duration-300 shadow-md flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Cancel Booking
            </button>
        </div>
    @endif
</div>
