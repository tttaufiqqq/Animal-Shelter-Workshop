<!-- Slot Detail Modal -->
<div id="slotDetailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-7xl w-full max-h-[100vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white p-6 sticky top-0 z-10">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold flex items-center">
                        <i class="fas fa-door-open mr-2"></i>
                        <span id="detailSlotName">Slot Details</span>
                    </h2>
                    <p class="text-indigo-100 mt-1">
                        <i class="fas fa-layer-group mr-2"></i>
                        <span id="detailSlotSection"></span>
                    </p>
                </div>
                <button onclick="closeSlotDetailModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-6">
            <!-- Slot Information Card -->
            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-6 border border-indigo-200">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
                    Slot Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-gray-600 text-sm mb-1">Status</p>
                        <p class="font-bold text-lg" id="detailSlotStatus"></p>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-gray-600 text-sm mb-1">Capacity</p>
                        <p class="font-bold text-lg text-indigo-600" id="detailSlotCapacity"></p>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-gray-600 text-sm mb-1">Current Occupancy</p>
                        <p class="font-bold text-lg text-orange-600" id="detailSlotOccupancy"></p>
                    </div>
                </div>
                <!-- Progress Bar -->
                <div class="mt-4">
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span>Occupancy Rate</span>
                        <span id="detailOccupancyPercent" class="font-semibold">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                        <div id="detailProgressBar" class="bg-gradient-to-r from-indigo-500 to-purple-500 h-4 rounded-full" style="width: 0%"></div>
                    </div>
                </div>
            </div>

            <!-- Animals Section -->
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-4 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-paw text-green-600 mr-2"></i>
                        Animals in this Slot
                        <span id="detailAnimalCount" class="ml-2 bg-green-600 text-white px-3 py-1 rounded-full text-sm">0</span>
                    </h3>
                </div>
                <div id="detailAnimalsContainer" class="p-4">
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-spinner fa-spin text-3xl mb-2"></i>
                        <p>Loading animals...</p>
                    </div>
                </div>
            </div>

            <!-- Inventories Section -->
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="bg-gradient-to-r from-blue-50 to-cyan-50 p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-box text-blue-600 mr-2"></i>
                            Inventory Items
                            <span id="detailInventoryCount" class="ml-2 bg-blue-600 text-white px-3 py-1 rounded-full text-sm">0</span>
                        </h3>
                        @role('admin|caretaker')
                        <button onclick="openInventoryModalForSlot()" id="addInventoryBtn" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-blue-700 shadow-sm flex items-center gap-2">
                            <i class="fas fa-plus" id="addInventoryIcon"></i>
                            <span id="addInventoryText">Add Inventory</span>
                        </button>
                        @endrole
                    </div>
                </div>
                <div id="detailInventoriesContainer" class="p-4">
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-spinner fa-spin text-3xl mb-2"></i>
                        <p>Loading inventory...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
