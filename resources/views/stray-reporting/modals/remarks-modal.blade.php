<!-- Remarks Modal (For Failed Status) -->
<div id="remarksModal" class="fixed inset-0 bg-black bg-opacity-70 backdrop-blur-sm hidden z-[10000] flex items-center justify-center p-4" onclick="closeRemarksModal()">
    <div class="bg-white rounded-lg shadow-xl max-w-lg w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div id="modalIcon" class="text-2xl"></div>
                    <h3 id="modalTitle" class="text-xl font-semibold text-gray-900"></h3>
                </div>
                <button onclick="closeRemarksModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Form -->
            <form id="remarksForm" onsubmit="submitStatusUpdate(event)">
                <div class="mb-6">
                    <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">
                        Remarks <span class="text-red-600">*</span>
                    </label>
                    <textarea id="remarks" name="remarks" rows="5" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none text-sm"
                              placeholder="Explain why the rescue could not be completed..."></textarea>
                    <p class="mt-2 text-xs text-gray-500">
                        Please provide a detailed explanation for this status update.
                    </p>
                </div>

                <!-- Modal Actions -->
                <div class="flex gap-3">
                    <button type="button" onclick="closeRemarksModal()" id="modalCancelBtn"
                            class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 rounded-lg transition-colors text-sm">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn"
                            class="flex-1 bg-red-600 hover:bg-red-700 font-medium py-2 rounded-lg transition-colors text-sm text-white flex items-center justify-center gap-2">
                        <span id="submitBtnText">Confirm</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
