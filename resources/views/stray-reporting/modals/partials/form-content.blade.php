<!-- Right Column: Form Content (9 columns) -->
<div class="col-span-9" id="successModalBody">
    <!-- Validation Error Alert (Shared across all steps) -->
    <div id="validationAlert" class="hidden mb-6 flex items-start gap-3 p-4 bg-red-50 border-2 border-red-300 rounded-xl shadow-sm">
        <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-red-800 mb-2">Error</p>
            <p id="validationMessage" class="text-sm text-red-700 whitespace-pre-wrap break-words"></p>
        </div>
        <button onclick="hideValidationAlert()" class="text-red-600 hover:text-red-800 transition flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    @include('stray-reporting.modals.steps.step1-count')
    @include('stray-reporting.modals.steps.step2-animal-form')
    @include('stray-reporting.modals.steps.step3-confirmation')
</div>
