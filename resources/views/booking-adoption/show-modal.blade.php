{{-- bookings/partials/booking-modal.blade.php --}}
{{-- Booking Details Modal with animal selection + Adoption Fee Modal with selected animals only --}}

@php
    $animals = $booking->animals ?? collect();
    $allFeeBreakdowns = [];
    $totalFee = 0;

    // Species-based fee structure (same as controller)
    $speciesBaseFees = [
        'dog' => 20,
        'cat' => 10,
    ];
    $medicalRate = 10;
    $vaccinationRate = 20;

    if ($animals->isNotEmpty()) {
        foreach ($animals as $animal) {
            $species = strtolower($animal->species);
            $baseFee = $speciesBaseFees[$species] ?? 100; // default RM 100

            $medicalCount = $animal->medicals ? $animal->medicals->count() : 0;
            $medicalFee = $medicalCount * $medicalRate;

            $vaccinationCount = $animal->vaccinations ? $animal->vaccinations->count() : 0;
            $vaccinationFee = $vaccinationCount * $vaccinationRate;

            $animalTotal = $baseFee + $medicalFee + $vaccinationFee;

            $allFeeBreakdowns[$animal->id] = [
                'base_fee' => $baseFee,
                'medical_rate' => $medicalRate,
                'medical_count' => $medicalCount,
                'medical_fee' => $medicalFee,
                'vaccination_rate' => $vaccinationRate,
                'vaccination_count' => $vaccinationCount,
                'vaccination_fee' => $vaccinationFee,
                'total_fee' => $animalTotal,
            ];

            $totalFee += $animalTotal;
        }
    }
@endphp

    <!-- Booking Details Modal -->
<div id="bookingModal-{{ $booking->id }}" class="modal-backdrop hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-[1400px] w-full max-h-[95vh] overflow-y-auto" onclick="event.stopPropagation()">

        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-6 sticky top-0 z-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📋</span>
                    <div>
                        <h2 class="text-2xl font-bold">Booking Details</h2>
                        <p class="text-purple-100 text-sm">Booking #{{ $booking->id }}</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('bookingModal-{{ $booking->id }}')" class="text-white hover:text-gray-200 transition">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-6">

            <!-- Status Badge -->
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">Booking Status</h3>
                @php
                    $statusBadgeColors = [
                        'pending' => 'bg-yellow-100 text-yellow-700',
                        'confirmed' => 'bg-blue-100 text-blue-700',
                        'completed' => 'bg-green-100 text-green-700',
                        'cancelled' => 'bg-red-100 text-red-700',
                    ];
                    $statusKey = strtolower($booking->status);
                @endphp
                <span class="px-4 py-2 rounded-full text-sm font-semibold {{ $statusBadgeColors[$statusKey] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ $booking->status }}
                </span>
            </div>

            <!-- Animals Section with Checkboxes -->
            @if($animals->isNotEmpty())
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 border-2 border-purple-300 rounded-xl p-6 shadow-md">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center text-xl">
                        <svg class="w-6 h-6 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Animals in this Booking
                        @if(in_array(strtolower($booking->status), ['pending', 'confirmed']))
                            <span class="ml-2 text-sm font-normal text-purple-600">(Select animals to adopt)</span>
                        @endif
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($animals as $index => $animal)
                            <label class="cursor-pointer group">
                                <div class="bg-white rounded-xl p-4 shadow-md border-2 border-transparent group-hover:border-purple-500 transition relative
                                    @if(in_array(strtolower($booking->status), ['pending', 'confirmed'])) has-[:checked]:border-purple-500 has-[:checked]:ring-2 has-[:checked]:ring-purple-300 @endif">

                                    {{-- Checkbox for selection (only for pending/confirmed) --}}
                                    @if(in_array(strtolower($booking->status), ['pending', 'confirmed']))
                                        <input type="checkbox"
                                               id="selectAnimal-{{ $booking->id }}-{{ $animal->id }}"
                                               class="animal-select-{{ $booking->id }} absolute top-3 right-3 w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                                               data-animal-id="{{ $animal->id }}"
                                               data-animal-name="{{ $animal->name }}"
                                               data-animal-species="{{ $animal->species }}"
                                               data-fee="{{ $allFeeBreakdowns[$animal->id]['total_fee'] }}"
                                               data-base-fee="{{ $allFeeBreakdowns[$animal->id]['base_fee'] }}"
                                               data-medical-fee="{{ $allFeeBreakdowns[$animal->id]['medical_fee'] }}"
                                               data-vaccination-fee="{{ $allFeeBreakdowns[$animal->id]['vaccination_fee'] }}"
                                               data-medical-count="{{ $allFeeBreakdowns[$animal->id]['medical_count'] }}"
                                               data-vaccination-count="{{ $allFeeBreakdowns[$animal->id]['vaccination_count'] }}"
                                            {{ $index === 0 ? 'checked' : '' }}>
                                    @endif

                                    @if($animal->images && $animal->images->count() > 0)
                                        <img src="{{ $animal->images->first()->url }}"
                                             alt="{{ $animal->name }}"
                                             class="w-full h-40 object-cover rounded-lg mb-3">
                                    @else
                                        <div class="w-full h-40 bg-gradient-to-br from-purple-300 to-purple-400 rounded-lg flex items-center justify-center mb-3">
                                            <span class="text-5xl">
                                                @if(strtolower($animal->species) == 'dog') 🐕
                                                @elseif(strtolower($animal->species) == 'cat') 🐈
                                                @else 🐾
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                    <div class="text-gray-800 font-semibold">{{ $animal->name }}</div>
                                    <div class="text-gray-600 text-sm">{{ $animal->species }} • {{ $animal->age }} • {{ $animal->gender }}</div>

                                    @if(in_array(strtolower($booking->status), ['pending', 'confirmed']))
                                        <div class="mt-2 text-sm font-semibold text-purple-700">
                                            Fee: RM {{ number_format($allFeeBreakdowns[$animal->id]['total_fee'], 2) }}
                                        </div>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>

                    {{-- Selection Summary --}}
                    @if(in_array(strtolower($booking->status), ['pending', 'confirmed']))
                        <div class="mt-4 p-3 bg-white rounded-lg border border-purple-200">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">
                                    Selected: <span id="selectedCount-{{ $booking->id }}" class="font-bold text-purple-700">1</span> animal(s)
                                </span>
                                <span class="text-gray-700">
                                    Estimated Fee: <span id="estimatedFee-{{ $booking->id }}" class="font-bold text-green-600">RM {{ number_format($allFeeBreakdowns[$animals->first()->id]['total_fee'] ?? 0, 2) }}</span>
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="bg-gray-100 rounded-xl p-6 text-center">
                    <p class="text-gray-500">No animals associated with this booking.</p>
                </div>
            @endif

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

<!-- Adoption Fee Modal (shows only selected animals) -->
@if(in_array(strtolower($booking->status), ['pending', 'confirmed']) && $animals->isNotEmpty())
    <div id="adoptionFeeModal-{{ $booking->id }}" class="modal-backdrop hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">

            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-6 sticky top-0 z-10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl">💰</span>
                        <div>
                            <h2 class="text-2xl font-bold">Adoption Fee Breakdown</h2>
                            <p class="text-green-100 text-sm">Booking #{{ $booking->id }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeModal('adoptionFeeModal-{{ $booking->id }}')" class="text-white hover:text-gray-200 transition">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form action="{{ route('bookings.confirm', $booking->id) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="p-6 space-y-6">

                    <!-- Selected Animals (Read-only display) -->
                    <div class="bg-purple-50 border-l-4 border-purple-600 rounded-lg p-5">
                        <h3 class="font-bold text-gray-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Selected Animals for Adoption
                        </h3>

                        {{-- Container for dynamically populated animals --}}
                        <div id="selectedAnimalsList-{{ $booking->id }}" class="space-y-3">
                            {{-- Will be populated by JavaScript --}}
                        </div>

                        <p id="noAnimalsSelected-{{ $booking->id }}" class="text-red-600 text-sm hidden">
                            Please select at least one animal from the booking details.
                        </p>
                    </div>

                    <!-- Grand Total -->
                    <div class="flex justify-between items-center py-4 bg-green-50 rounded-lg px-4">
                        <p class="text-xl font-bold text-gray-800">Total Adoption Fee</p>
                        <span id="grandTotal-{{ $booking->id }}" class="text-3xl font-bold text-green-600">RM 0.00</span>
                    </div>

                    <!-- Terms -->
                    <div class="flex items-start">
                        <input type="checkbox"
                               id="agree_terms_{{ $booking->id }}"
                               name="agree_terms"
                               class="mt-1 mr-3 h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                               required>
                        <label for="agree_terms_{{ $booking->id }}" class="text-sm text-gray-700">
                            I understand and agree to pay the adoption fee for the selected animals. <span class="text-red-600">*</span>
                        </label>
                    </div>

                    {{-- Hidden inputs for selected animal IDs --}}
                    <div id="hiddenAnimalInputs-{{ $booking->id }}"></div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 p-6 border-t border-gray-200">
                    <div class="flex flex-wrap justify-end gap-3">
                        <button type="button"
                                onclick="closeModal('adoptionFeeModal-{{ $booking->id }}')"
                                class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition duration-300">
                            Back
                        </button>
                        <button type="submit"
                                id="submitBtn-{{ $booking->id }}"
                                class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white font-semibold rounded-lg hover:from-green-700 hover:to-green-800 transition duration-300 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 justify-center">
                            <span id="submitBtnText-{{ $booking->id }}">Complete Adoptions</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div id="cancelConfirmModal-{{ $booking->id }}" class="modal-backdrop hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60] p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
            <!-- Header -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-6 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">⚠️</span>
                    <div>
                        <h2 class="text-xl font-bold">Cancel Booking</h2>
                        <p class="text-red-100 text-sm">Booking #{{ $booking->id }}</p>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="p-6">
                <p class="text-gray-700 mb-4">Are you sure you want to cancel this booking?</p>
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-4">
                    <p class="text-sm text-yellow-800">
                        <strong>⚠️ Warning:</strong> This action cannot be undone. Your appointment will be cancelled and the time slot will be made available to others.
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 p-6 border-t border-gray-200 flex gap-3 rounded-b-2xl">
                <button type="button"
                        onclick="closeCancelModal({{ $booking->id }})"
                        id="cancelModalNoBtn-{{ $booking->id }}"
                        class="flex-1 px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition duration-300">
                    No, Keep Booking
                </button>

                <form action="{{ route('bookings.cancel', $booking->id) }}" method="POST" class="flex-1" id="cancelForm-{{ $booking->id }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            id="confirmCancelBtn-{{ $booking->id }}"
                            class="w-full px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition duration-300 flex items-center gap-2 justify-center">
                        <span id="confirmCancelBtnText-{{ $booking->id }}">Yes, Cancel Booking</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
@endif

<style>
    /* Loading spinner animation */
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .animate-spin {
        animation: spin 1s linear infinite;
    }
</style>

<script>
    // Open cancel confirmation modal
    function openCancelModal(bookingId) {
        document.getElementById('cancelConfirmModal-' + bookingId).classList.remove('hidden');
        document.getElementById('cancelConfirmModal-' + bookingId).classList.add('flex');
    }

    // Close cancel confirmation modal
    function closeCancelModal(bookingId) {
        document.getElementById('cancelConfirmModal-' + bookingId).classList.add('hidden');
        document.getElementById('cancelConfirmModal-' + bookingId).classList.remove('flex');
    }

    // Handle cancel booking form submission
    document.addEventListener('DOMContentLoaded', function() {
        // Handle all cancel forms
        document.querySelectorAll('[id^="cancelForm-"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                const bookingId = this.id.replace('cancelForm-', '');
                const submitBtn = document.getElementById('confirmCancelBtn-' + bookingId);
                const noBtn = document.getElementById('cancelModalNoBtn-' + bookingId);

                // Disable buttons
                submitBtn.disabled = true;
                noBtn.disabled = true;

                // Show loading spinner
                submitBtn.innerHTML = `
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Cancelling...</span>
                `;

                // Allow form to submit
                return true;
            });
        });

        // Handle all adoption fee forms (Complete Adoptions button)
        document.querySelectorAll('[id^="adoptionFeeModal-"]').forEach(modal => {
            const form = modal.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const bookingId = modal.id.replace('adoptionFeeModal-', '');
                    const submitBtn = document.getElementById('submitBtn-' + bookingId);

                    // Disable button
                    submitBtn.disabled = true;

                    // Show loading spinner
                    submitBtn.innerHTML = `
                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Processing...</span>
                    `;

                    // Allow form to submit
                    return true;
                });
            }
        });
    });
</script>
