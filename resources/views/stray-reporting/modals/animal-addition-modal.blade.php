<!-- Success Modal (Multi-step: Animal Addition) -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-70 backdrop-blur-sm hidden z-[10000] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-700 text-white p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-white bg-opacity-20 p-3 rounded-xl backdrop-blur-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">Add Rescued Animals</h2>
                        <p class="text-purple-100 text-sm" id="successModalSubtitle">Register animals to the shelter system</p>
                    </div>
                </div>
                <button onclick="closeSuccessModal()" class="text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-lg transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Modal Body: 2 Columns (Left: Steps, Right: Content) -->
        <div class="grid grid-cols-12 gap-6 p-6 overflow-y-auto max-h-[calc(90vh-200px)]">
            @include('stray-reporting.modals.partials.step-indicator')
            @include('stray-reporting.modals.partials.form-content')
        </div>

        <!-- Modal Footer -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-t border-gray-200 flex justify-between items-center">
            <button type="button" onclick="prevStep()" id="backBtn"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-white hover:bg-gray-100 text-gray-700 font-bold rounded-xl transition-all shadow-sm hover:shadow-md border-2 border-gray-200 text-sm">
                <i class="fas fa-arrow-left"></i>
                Back
            </button>
            <button type="button" onclick="nextStep()" id="nextBtn"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 text-sm">
                Next
                <i class="fas fa-arrow-right"></i>
            </button>
            <button type="button" onclick="submitSuccessRescue()" id="submitSuccessBtn" class="hidden inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 text-sm">
                <i class="fas fa-check-circle"></i>
                Submit Rescue
            </button>
        </div>
    </div>
</div>
