{{-- booking-adoption/partials/adoption-fee-modal.blade.php --}}
{{-- Adoption Fee Modal - Shows only selected animals (read-only) --}}

@if(in_array(strtolower($booking->status), ['pending', 'confirmed']) && $animals->isNotEmpty())
    <div id="adoptionFeeModal-{{ $booking->id }}" class="modal-backdrop hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60] p-4">
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
                            {{-- Will be populated by JavaScript from booking details modal --}}
                        </div>

                        <p id="noAnimalsSelected-{{ $booking->id }}" class="text-red-600 text-sm mt-3 hidden">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            Please select at least one animal from the booking details.
                        </p>
                    </div>

                    <!-- Grand Total -->
                    <div class="flex justify-between items-center py-4 bg-green-50 rounded-lg px-4">
                        <p class="text-xl font-bold text-gray-800">Total Adoption Fee</p>
                        <span id="grandTotal-{{ $booking->id }}" class="text-3xl font-bold text-green-600">RM 0.00</span>
                    </div>

                    <!-- Fee Breakdown Info -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-semibold text-blue-800 mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Fee Information
                        </h4>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li><strong>Base Fee:</strong> RM 50.00 per animal (covers shelter care & processing)</li>
                            <li><strong>Medical Fee:</strong> Covers any treatments the animal received</li>
                            <li><strong>Vaccination Fee:</strong> Covers all vaccinations administered</li>
                        </ul>
                    </div>

                    <!-- Terms -->
                    <div class="flex items-start">
                        <input type="checkbox"
                               id="agree_terms_{{ $booking->id }}"
                               name="agree_terms"
                               class="mt-1 mr-3 h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                               required>
                        <label for="agree_terms_{{ $booking->id }}" class="text-sm text-gray-700">
                            I understand and agree to pay the adoption fee for the selected animals. I also agree to provide a loving home and proper care for the adopted animal(s). <span class="text-red-600">*</span>
                        </label>
                    </div>

                    {{-- Hidden inputs for selected animal IDs (populated by JavaScript) --}}
                    <div id="hiddenAnimalInputs-{{ $booking->id }}"></div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 p-6 border-t border-gray-200">
                    <div class="flex flex-wrap justify-between gap-3">
                        {{-- Back button to return to booking details --}}
                        <button type="button"
                                onclick="backToBookingDetails({{ $booking->id }})"
                                class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition duration-300 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Selection
                        </button>

                        <div class="flex gap-3">
                            <button type="button"
                                    onclick="closeModal('adoptionFeeModal-{{ $booking->id }}')"
                                    class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition duration-300">
                                Cancel
                            </button>
                            <button type="submit"
                                    id="submitBtn-{{ $booking->id }}"
                                    class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white font-semibold rounded-lg hover:from-green-700 hover:to-green-800 transition duration-300 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Complete Adoption
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif
