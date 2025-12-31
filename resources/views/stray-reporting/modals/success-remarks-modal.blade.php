<!-- Success Remarks Modal (Step 0: Before Animal Addition) -->
<div id="successRemarksModal" class="fixed inset-0 bg-black bg-opacity-70 backdrop-blur-sm hidden z-[10000] flex items-center justify-center p-4" onclick="closeSuccessRemarksModal()">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-600 via-emerald-600 to-green-700 text-white p-6">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-3">
                    <div class="bg-white bg-opacity-20 p-3 rounded-xl backdrop-blur-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">Rescue Successful!</h2>
                        <p class="text-green-100 text-sm">Provide rescue operation details</p>
                    </div>
                </div>
                <button onclick="closeSuccessRemarksModal()" class="text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-lg transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="p-6">
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-bold text-green-900 mb-1">Great job on completing the rescue!</p>
                        <p class="text-sm text-green-800">Before adding the rescued animals to the system, please describe how the rescue operation was completed.</p>
                    </div>
                </div>
            </div>

            <form onsubmit="event.preventDefault(); proceedToAnimalAddition();">
                <div class="mb-6">
                    <label class="flex items-center gap-2 text-gray-800 font-semibold mb-2">
                        <i class="fas fa-clipboard-list text-green-600"></i>
                        Rescue Operation Remarks <span class="text-red-600">*</span>
                    </label>
                    <textarea id="successRescueRemarks" rows="6" required
                              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 resize-none transition"
                              placeholder="Describe the rescue operation details:&#10;• How the rescue was completed&#10;• Condition and location where animals were found&#10;• Any challenges faced during the operation&#10;• Special notes or observations"></textarea>
                    <div class="mt-2 flex items-start gap-2 bg-green-50 p-3 rounded-lg border border-green-200">
                        <i class="fas fa-info-circle text-green-600 mt-0.5"></i>
                        <p class="text-xs text-gray-700">
                            Provide comprehensive details about the rescue operation. This information will be saved with all rescued animals.
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button type="button" onclick="closeSuccessRemarksModal()"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-xl transition-all shadow-sm hover:shadow-md text-sm">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 text-sm">
                        Next: Add Animals
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
