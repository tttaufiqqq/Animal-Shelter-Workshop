{{-- Confirmation Modal --}}
<div id="confirmationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
        <!-- Modal Header -->
        <div id="confirmationModalHeader" class="bg-gradient-to-r from-red-500 to-red-600 text-white p-6 rounded-t-2xl">
            <div class="flex items-center gap-3">
                <div class="bg-white bg-opacity-20 p-3 rounded-full backdrop-blur-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div>
                    <h3 id="confirmationModalTitle" class="text-xl font-bold">Confirm Action</h3>
                </div>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <p id="confirmationModalMessage" class="text-gray-700 text-base leading-relaxed">
                Are you sure you want to proceed with this action?
            </p>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex items-center justify-end gap-3">
            <button onclick="closeConfirmationModal()"
                    class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition duration-200">
                Cancel
            </button>
            <button id="confirmationModalConfirmBtn"
                    onclick="confirmAction()"
                    class="px-5 py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-lg transition duration-200 shadow-md hover:shadow-lg">
                <i class="fas fa-check mr-2"></i>Confirm
            </button>
        </div>
    </div>
</div>
