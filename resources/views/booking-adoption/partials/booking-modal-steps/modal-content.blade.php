{{-- Column 2: Main Content (9 cols) --}}
<div class="col-span-9 flex flex-col max-h-[calc(90vh-120px)]">
    {{-- Content Steps --}}
    <div class="flex-1 overflow-y-auto p-6">

    {{-- Step 1: Booking Details --}}
    <div class="step-content" data-step="1" id="step1-{{ $booking->id }}">
        @include('booking-adoption.partials.step1-details', ['booking' => $booking])
    </div>

    {{-- Step 2: Select Animals --}}
    <div class="step-content hidden" data-step="2" id="step2-{{ $booking->id }}">
        @include('booking-adoption.partials.step2-select', ['booking' => $booking, 'animals' => $animals, 'allFeeBreakdowns' => $allFeeBreakdowns])
    </div>

    {{-- Step 3: Confirm & Pay --}}
    <div class="step-content hidden" data-step="3" id="step3-{{ $booking->id }}">
        @include('booking-adoption.partials.step3-confirm', ['booking' => $booking])
    </div>

    </div>

    {{-- Footer Navigation - Always Visible --}}
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-t border-gray-200 flex items-center justify-between flex-shrink-0 sticky bottom-0">
        <button type="button"
                id="prev-btn-{{ $booking->id }}"
                onclick="previousStep({{ $booking->id }})"
                class="hidden inline-flex items-center gap-2 px-6 py-3 bg-white hover:bg-gray-100 text-gray-700 font-bold rounded-xl transition-all shadow-sm hover:shadow-md border-2 border-gray-200 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            <span>Back</span>
        </button>

        @if(in_array(strtolower($booking->status), ['pending', 'confirmed']))
            <button type="button"
                    id="next-btn-{{ $booking->id }}"
                    onclick="nextStep({{ $booking->id }})"
                    class="ml-auto inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 text-sm">
                <span>Next</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        @endif
    </div>
</div>
</div>
</div>
</div>

{{-- Cancel Confirmation Modal --}}
@if(in_array(strtolower($booking->status), ['pending', 'confirmed']))
    @include('booking-adoption.partials.cancel-modal', ['booking' => $booking])
@endif
