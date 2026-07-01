<!-- Hidden form for removing animals -->
<form id="removeAnimalForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Remove Confirmation Modal -->
<div id="removeConfirmModal" class="fixed inset-0 hidden bg-black/50 backdrop-blur-sm z-[10000] flex items-center justify-center p-4 transition-opacity duration-300">
    <div id="removeConfirmContent" class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all duration-300 opacity-0 scale-95">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-red-500 to-red-600 p-6 rounded-t-2xl">
            <div class="flex items-center gap-3 text-white">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold">Remove Animal</h3>
                    <p class="text-red-100 text-sm">Are you sure about this?</p>
                </div>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <p class="text-gray-700 text-base mb-2">
                You are about to remove <strong id="removeAnimalName" class="text-gray-900"></strong> from your visit list.
            </p>
            <p class="text-gray-600 text-sm">
                This action cannot be undone. You can always add the animal back to your list later.
            </p>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 pb-6 flex gap-3">
            <button type="button"
                    id="removeModalCancelBtn"
                    onclick="closeRemoveConfirmModal()"
                    class="flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-all duration-200 border border-gray-200">
                <i class="fas fa-times mr-2"></i>Cancel
            </button>
            <button type="button"
                    id="removeModalConfirmBtn"
                    onclick="confirmRemoveAnimal()"
                    class="flex-1 px-4 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl">
                <i class="fas fa-trash-alt mr-2" id="removeIcon"></i>
                <span id="removeText">Remove</span>
                <svg class="animate-spin h-5 w-5 text-white hidden inline-block ml-2" id="removeSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </div>
    </div>
</div>
