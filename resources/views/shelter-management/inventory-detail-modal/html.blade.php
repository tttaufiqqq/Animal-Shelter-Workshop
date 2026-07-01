<!-- Inventory Detail Modal -->
<div id="inventoryDetailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-[70] p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-500 to-cyan-600 text-white p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold flex items-center">
                        <i class="fas fa-box mr-2"></i>
                        <span id="inventoryDetailTitle">Inventory Details</span>
                    </h2>
                    <p class="text-blue-100 mt-1" id="inventoryDetailSubtitle"></p>
                </div>
                <button onclick="closeInventoryDetailModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div id="inventoryDetailLoading" class="p-12 text-center">
            <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
            <p class="text-gray-600">Loading inventory details...</p>
        </div>

        <!-- Detail Content -->
        <div id="inventoryDetailContent" class="hidden">
            <!-- Animal Compatibility Section -->
            <div id="compatibilitySection" class="hidden mx-6 mt-6 bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-5 border-2 border-green-300">
                <div class="flex items-start gap-4">
                    <div class="bg-green-600 text-white rounded-full p-3">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-800 mb-2 flex items-center gap-2">
                            <i class="fas fa-paw text-green-600"></i>
                            Animal Compatibility Analysis
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                            <div class="bg-white rounded-lg p-3 border border-green-100">
                                <p class="text-xs text-gray-600 mb-1">Animal in Slot</p>
                                <p class="font-bold text-gray-800" id="compatAnimalName"></p>
                            </div>
                            <div class="bg-white rounded-lg p-3 border border-green-100">
                                <p class="text-xs text-gray-600 mb-1">Health Status</p>
                                <p class="font-bold" id="compatHealthStatus"></p>
                            </div>
                        </div>
                        <div id="compatibilityStatus" class="mt-3"></div>
                    </div>
                </div>
            </div>

            <!-- No Animal Warning -->
            <div id="noAnimalCompatSection" class="hidden mx-6 mt-6 bg-gray-50 rounded-xl p-4 border border-gray-200">
                <p class="text-gray-600 flex items-center gap-2">
                    <i class="fas fa-info-circle"></i>
                    <span>No animal assigned to this slot. Inventory is available for general use.</span>
                </p>
            </div>

            <!-- Inventory Information -->
            <div class="p-6 space-y-4">
                <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-6 border border-blue-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        Item Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white rounded-lg p-4">
                            <p class="text-gray-600 text-sm mb-1">Item Name</p>
                            <p class="font-bold text-gray-800" id="detailItemName"></p>
                        </div>
                        <div class="bg-white rounded-lg p-4">
                            <p class="text-gray-600 text-sm mb-1">Category</p>
                            <p class="font-bold text-gray-800" id="detailCategory"></p>
                        </div>
                        <div class="bg-white rounded-lg p-4">
                            <p class="text-gray-600 text-sm mb-1">Quantity</p>
                            <p class="font-bold text-blue-600 text-xl" id="detailQuantity"></p>
                        </div>
                        <div class="bg-white rounded-lg p-4">
                            <p class="text-gray-600 text-sm mb-1">Weight</p>
                            <p class="font-bold text-gray-800" id="detailWeight"></p>
                        </div>
                        <div class="bg-white rounded-lg p-4">
                            <p class="text-gray-600 text-sm mb-1">Brand</p>
                            <p class="font-bold text-gray-800" id="detailBrand"></p>
                        </div>
                        <div class="bg-white rounded-lg p-4">
                            <p class="text-gray-600 text-sm mb-1">Status</p>
                            <p class="font-bold" id="detailStatus"></p>
                        </div>
                        <div class="bg-white rounded-lg p-4">
                            <p class="text-gray-600 text-sm mb-1">Located In</p>
                            <p class="font-bold text-gray-800" id="detailSlotLocation"></p>
                        </div>
                    </div>
                </div>

                <!-- Edit Form (Hidden by default) -->
                <div id="inventoryEditForm" class="hidden bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-edit text-blue-600 mr-2"></i>
                        Edit Inventory
                    </h3>
                    <form method="POST" action="" id="updateInventoryForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="inventory_id" id="editInventoryId">

                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Item Name</label>
                                <input type="text" name="item_name" id="editItemName" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Category</label>
                                <select name="categoryID" id="editCategoryID" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-white focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">
                                            {{ $category->main }}{{ $category->sub ? ' - ' . $category->sub : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Quantity</label>
                                    <input type="number" name="quantity" id="editQuantity" min="0" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                                </div>
                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Weight (kg)</label>
                                    <input type="number" name="weight" id="editWeight" min="0" step="0.01" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Brand</label>
                                <input type="text" name="brand" id="editBrand" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                            </div>

                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Status</label>
                                <select name="status" id="editStatus" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-white focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                                    <option value="available">Available</option>
                                    <option value="low">Low Stock</option>
                                    <option value="out">Out of Stock</option>
                                </select>
                            </div>

                            <div class="flex justify-end gap-3 pt-4">
                                <button type="button" onclick="cancelInventoryEdit()" id="updateInventoryCancelBtn" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300">
                                    Cancel
                                </button>
                                <button type="submit" id="updateInventorySubmitBtn" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-cyan-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-cyan-700 flex items-center gap-2">
                                    <i class="fas fa-save" id="updateInventoryIcon"></i>
                                    <span id="updateInventoryText">Update Inventory</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 p-6 border-t flex justify-between">
                <button onclick="deleteInventoryItem()" id="deleteInventoryBtn" class="px-6 py-3 bg-red-500 text-white font-semibold rounded-lg hover:bg-red-600">
                    <i class="fas fa-trash mr-2"></i>Delete
                </button>
                <div class="flex gap-3">
                    <button onclick="closeInventoryDetailModal()" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300">
                        Close
                    </button>
                    <button onclick="toggleInventoryEdit()" id="editInventoryBtn" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-cyan-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-cyan-700">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
