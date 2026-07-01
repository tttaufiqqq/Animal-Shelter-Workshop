            <!-- Appointment Details -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-300 rounded-xl p-6 shadow-md">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center text-xl">
                    <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Appointment Details
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Date</p>
                        <p class="text-gray-800 font-bold text-lg">
                            {{ \Carbon\Carbon::parse($booking->appointment_date)->format('F d, Y') }}
                        </p>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Time</p>
                        <p class="text-gray-800 font-bold text-lg">
                            {{ \Carbon\Carbon::parse($booking->appointment_time)->format('h:i A') }}
                        </p>
                    </div>
                </div>
                <div class="mt-4 bg-white rounded-lg p-4 shadow-sm">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Booked On</p>
                    <p class="text-gray-800 font-medium">
                        {{ $booking->created_at->format('F d, Y') }}
                    </p>
                </div>
            </div>

            <!-- User Information -->
            @if($booking->user)
                <div class="bg-gradient-to-br from-green-50 to-green-100 border-2 border-green-300 rounded-xl p-6 shadow-md">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center text-xl">
                        <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Booker Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Name</p>
                            <p class="text-gray-800 font-medium text-lg">{{ $booking->user->name }}</p>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Email</p>
                            <p class="text-gray-800 font-medium">{{ $booking->user->email }}</p>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Phone Number</p>
                            <p class="text-gray-800 font-medium">{{ $booking->user->phoneNum ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Important Information -->
            @if(in_array(strtolower($booking->status), ['pending', 'confirmed']))
                <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-5">
                    <h3 class="font-bold text-gray-800 mb-3 flex items-center text-lg">
                        <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        Important Reminders
                    </h3>
                    <ul class="text-sm text-gray-700 space-y-2 list-disc list-inside">
                        <li>Please arrive 10 minutes before your scheduled appointment</li>
                        <li>Bring a valid government-issued ID</li>
                        <li>Be prepared to discuss your living situation and pet care experience</li>
                        <li>If you need to reschedule or cancel, please notify us at least 24 hours in advance</li>
                    </ul>
                </div>
            @endif
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 p-6 border-t border-gray-200 flex flex-wrap justify-end gap-3">
            <button type="button" onclick="closeModal('bookingModal-{{ $booking->id }}')" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition duration-300 shadow-md">
                Close
            </button>

            @if(in_array(strtolower($booking->status), ['pending', 'confirmed']))
                @if($animals->isNotEmpty())
                    <button type="button"
                            onclick="openAdoptionFeeModal({{ $booking->id }})"
                            id="confirmPayBtn-{{ $booking->id }}"
                            class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition duration-300 shadow-md flex items-center gap-2">
                        <span id="confirmPayBtnText-{{ $booking->id }}">Confirm & Pay</span>
                    </button>
                @endif

                <button type="button"
                        onclick="openCancelModal({{ $booking->id }})"
                        id="cancelBookingBtn-{{ $booking->id }}"
                        class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition duration-300 shadow-md flex items-center gap-2">
                    <span id="cancelBookingBtnText-{{ $booking->id }}">Cancel Booking</span>
                </button>
            @endif
        </div>
    </div>
</div>
