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
