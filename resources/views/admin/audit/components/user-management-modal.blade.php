{{-- User Management Modal Component --}}
<div id="userManagementModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-all duration-300" onclick="closeUserManagementModal()">
    <div class="bg-white rounded-lg shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-auto transform transition-all duration-300" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-4 border-b sticky top-0 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold">User Management</h3>
                    <p class="text-indigo-100 text-sm" id="modalUserEmail"></p>
                </div>
            </div>
            <button onclick="closeUserManagementModal()" class="text-white hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Message Container -->
        <div id="messageContainer" class="hidden mx-4 mt-4"></div>

        <!-- Modal Content -->
        <div id="modalContent" class="p-4">
            <!-- Loading State -->
            <div id="loadingState" class="flex items-center justify-center py-12">
                <div class="text-center">
                    <svg class="animate-spin h-12 w-12 text-indigo-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-gray-600">Loading user activity...</p>
                </div>
            </div>

            <!-- Content (populated via JavaScript) -->
            <div id="userActivityContent" class="hidden"></div>
        </div>
    </div>
</div>
