<!-- Inventory Create Modal -->
<div id="inventoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-[60] p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-500 to-cyan-600 text-white p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">
                        <i class="fas fa-box mr-2"></i>
                        Add Inventory Item
                    </h2>
                    <p class="text-blue-100 mt-1" id="inventorySlotInfo"></p>
                </div>
                <button onclick="closeInventoryModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <form method="POST" action="{{ route($routePrefix . '.store-inventory') }}" class="p-6 space-y-4" id="inventoryForm">
            @csrf
            <input type="hidden" name="slotID" id="inventorySlotID">

            <!-- Animal Information & Smart Suggestions -->
            <div id="animalInfoSection" class="hidden bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-5 border-2 border-purple-200">
                <div class="flex items-start gap-4">
                    <div class="bg-purple-600 text-white rounded-full p-3">
                        <i class="fas fa-paw text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-800 mb-2 flex items-center gap-2">
                            <i class="fas fa-brain text-purple-600"></i>
                            Smart Inventory Suggestions
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                            <div class="bg-white rounded-lg p-3 border border-purple-100">
                                <p class="text-xs text-gray-600 mb-1">Animal</p>
                                <p class="font-bold text-gray-800" id="animalName"></p>
                            </div>
                            <div class="bg-white rounded-lg p-3 border border-purple-100">
                                <p class="text-xs text-gray-600 mb-1">Species</p>
                                <p class="font-bold text-gray-800" id="animalSpecies"></p>
                            </div>
                            <div class="bg-white rounded-lg p-3 border border-purple-100">
                                <p class="text-xs text-gray-600 mb-1">Health Status</p>
                                <p class="font-bold" id="animalHealth"></p>
                            </div>
                            <div class="bg-white rounded-lg p-3 border border-purple-100">
                                <p class="text-xs text-gray-600 mb-1">Age</p>
                                <p class="font-bold text-gray-800" id="animalAge"></p>
                            </div>
                        </div>

                        <!-- Recommended Items -->
                        <div class="bg-white rounded-lg p-4 border-2 border-purple-300">
                            <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                                <i class="fas fa-lightbulb text-amber-500"></i>
                                Recommended for this Animal
                            </h4>
                            <div id="recommendedItems" class="space-y-2"></div>
                        </div>

                        <!-- Warning for Unsuitable Items -->
                        <div id="restrictedWarning" class="hidden mt-3 bg-red-50 border-l-4 border-red-500 p-3 rounded">
                            <p class="text-sm text-red-800 font-semibold flex items-center gap-2">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span id="restrictedMessage"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Animal Warning -->
            <div id="noAnimalSection" class="hidden bg-amber-50 rounded-xl p-4 border border-amber-200">
                <p class="text-amber-800 flex items-center gap-2">
                    <i class="fas fa-info-circle"></i>
                    <span>This slot has no animal assigned. Adding general inventory items.</span>
                </p>
            </div>

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Item Name <span class="text-red-600">*</span>
                </label>
                <input type="text" name="item_name" id="inventoryItemName" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200" placeholder="e.g., Dog Food, Cat Litter" required>
            </div>

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Category <span class="text-red-600">*</span>
                </label>
                <select name="categoryID" id="inventoryCategoryID" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-white focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">
                            {{ $category->main }}{{ $category->sub ? ' - ' . $category->sub : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">
                        Quantity <span class="text-red-600">*</span>
                    </label>
                    <input type="number" name="quantity" id="inventoryQuantity" min="0" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200" placeholder="0" required>
                </div>

                <div>
                    <label class="block text-gray-800 font-semibold mb-2">
                        Weight (kg) <span class="text-gray-500 text-sm">(Optional)</span>
                    </label>
                    <input type="number" name="weight" id="inventoryWeight" min="0" step="0.01" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200" placeholder="0.00">
                </div>
            </div>

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Brand <span class="text-gray-500 text-sm">(Optional)</span>
                </label>
                <input type="text" name="brand" id="inventoryBrand" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200" placeholder="e.g., Royal Canin, Pedigree">
            </div>

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Status <span class="text-red-600">*</span>
                </label>
                <select name="status" id="inventoryStatus" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-white focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                    <option value="available">Available</option>
                    <option value="low">Low Stock</option>
                    <option value="out">Out of Stock</option>
                </select>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeInventoryModal()" id="inventoryCancelBtn" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" id="inventorySubmitBtn" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-cyan-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-cyan-700 flex items-center gap-2">
                    <i class="fas fa-save" id="inventorySubmitIcon"></i>
                    <span id="inventorySubmitText">Add Inventory</span>
                </button>
            </div>
        </form>
    </div>
</div>
