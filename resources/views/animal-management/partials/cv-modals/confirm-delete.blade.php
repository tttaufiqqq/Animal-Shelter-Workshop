{{-- Modals for Clinics & Vets Management --}}

{{-- Confirmation Modal for Delete Actions --}}
<div id="confirmDeleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-[60] p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all duration-300 scale-95 opacity-0" id="confirmDeleteModalContent">
        <div class="p-6">
            {{-- Icon --}}
            <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            {{-- Title & Message --}}
            <h3 id="confirmDeleteTitle" class="text-xl font-bold text-gray-900 text-center mb-2">Delete Item?</h3>
            <p id="confirmDeleteMessage" class="text-gray-600 text-center mb-6">This action cannot be undone. Are you sure you want to proceed?</p>

            {{-- Buttons --}}
            <div class="flex gap-3">
                <button type="button" onclick="closeConfirmModal()" id="confirmCancelBtn" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition duration-200">
                    Cancel
                </button>
                <button type="button" onclick="executeDelete()" id="confirmDeleteBtn" class="flex-1 px-4 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white font-semibold rounded-xl hover:from-red-600 hover:to-red-700 transition duration-200 flex items-center justify-center gap-2">
                    <span id="confirmDeleteBtnText">Delete</span>
                    <svg id="confirmDeleteSpinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Toast Notification Container --}}
<div id="toastContainer" class="fixed top-4 right-4 z-[70] flex flex-col gap-2"></div>
