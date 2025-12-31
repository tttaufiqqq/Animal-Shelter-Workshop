{{-- Step 3: Confirm & Pay --}}
<form action="{{ route('bookings.confirm', $booking->id) }}" method="POST" id="confirmForm-{{ $booking->id }}">
    @csrf
    @method('PATCH')

    <div class="space-y-5">

        {{-- Selected Animals Summary --}}
        <div class="bg-white rounded-xl p-5 border border-gray-200">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-4">Selected Animals</p>

            {{-- Container for dynamically populated animals --}}
            <div id="selectedAnimalsList-{{ $booking->id }}" class="space-y-2">
                {{-- Will be populated by JavaScript --}}
            </div>

            <p id="noAnimalsSelected-{{ $booking->id }}" class="text-red-600 text-sm mt-3 hidden bg-red-50 p-3 rounded-lg border border-red-200">
                Please go back and select at least one animal.
            </p>
        </div>

        {{-- Fee Breakdown --}}
        <div class="bg-white rounded-xl p-5 border border-gray-200">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-4">Fee Breakdown</p>

            <div id="feeBreakdownList-{{ $booking->id }}" class="space-y-3 mb-4">
                {{-- Will be populated by JavaScript --}}
            </div>

            <div class="pt-4 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold text-gray-900">Total</span>
                    <span id="grandTotal-{{ $booking->id }}" class="text-2xl font-bold text-purple-600">RM 0.00</span>
                </div>
            </div>
        </div>

        {{-- Payment Information --}}
        <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
            <p class="text-xs font-medium text-blue-900 uppercase tracking-wide mb-3">Payment Details</p>
            <ul class="space-y-2 text-sm text-gray-700">
                <li class="flex gap-2">
                    <span class="text-blue-600 mt-0.5">•</span>
                    <span>Secure payment via ToyyibPay gateway</span>
                </li>
                <li class="flex gap-2">
                    <span class="text-blue-600 mt-0.5">•</span>
                    <span>Accepts FPX, Credit/Debit cards</span>
                </li>
                <li class="flex gap-2">
                    <span class="text-blue-600 mt-0.5">•</span>
                    <span>Animals ready for pickup after payment</span>
                </li>
            </ul>
        </div>

        {{-- Terms and Conditions --}}
        <div class="bg-white rounded-xl p-5 border border-gray-200">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Terms & Conditions</p>

            <div class="bg-gray-50 rounded-lg p-4 mb-4 max-h-40 overflow-y-auto text-sm text-gray-700 space-y-1.5">
                <p>• Must be 18+ years old</p>
                <p>• Fee covers medical care and vaccinations</p>
                <p>• Provide safe, permanent home</p>
                <p>• Non-refundable after payment</p>
                <p>• Follow-up visits may be conducted</p>
            </div>

            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox"
                       id="agree_terms_{{ $booking->id }}"
                       name="agree_terms"
                       onchange="togglePaymentButton({{ $booking->id }})"
                       class="mt-0.5 h-5 w-5 text-purple-600 border-gray-300 rounded focus:ring-2 focus:ring-purple-500"
                       required>
                <span class="text-sm text-gray-700">
                    I agree to the terms and conditions <span class="text-red-600">*</span>
                </span>
            </label>
        </div>

        {{-- Hidden inputs for selected animal IDs and total fee --}}
        <div id="hiddenAnimalInputs-{{ $booking->id }}"></div>

        {{-- Submit Button --}}
        <button type="submit"
                id="submitBtn-{{ $booking->id }}"
                disabled
                class="w-full px-6 py-4 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            <span id="submitBtnText-{{ $booking->id }}">Proceed to Payment</span>
        </button>

    </div>
</form>

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
