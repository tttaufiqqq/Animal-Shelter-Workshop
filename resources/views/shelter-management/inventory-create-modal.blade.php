<!-- Inventory Create Modal -->
<div id="inventoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60] p-4">
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
                <button onclick="closeInventoryModal()" class="text-white hover:text-gray-200 transition">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <form method="POST" action="{{ route('shelter-management.store-inventory') }}" class="p-6 space-y-4" id="inventoryForm">
            @csrf
            <input type="hidden" name="slotID" id="inventorySlotID">

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Item Name <span class="text-red-600">*</span>
                </label>
                <input type="text" name="item_name" id="inventoryItemName" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="e.g., Dog Food, Cat Litter" required>
            </div>

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Category <span class="text-red-600">*</span>
                </label>
                <select name="categoryID" id="inventoryCategoryID" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-white focus:border-blue-500 focus:ring focus:ring-blue-200 transition" required>
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
                    <input type="number" name="quantity" id="inventoryQuantity" min="0" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="0" required>
                </div>

                <div>
                    <label class="block text-gray-800 font-semibold mb-2">
                        Weight (kg) <span class="text-gray-500 text-sm">(Optional)</span>
                    </label>
                    <input type="number" name="weight" id="inventoryWeight" min="0" step="0.01" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="0.00">
                </div>
            </div>

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Brand <span class="text-gray-500 text-sm">(Optional)</span>
                </label>
                <input type="text" name="brand" id="inventoryBrand" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-blue-500 focus:ring focus:ring-blue-200 transition" placeholder="e.g., Royal Canin, Pedigree">
            </div>

            <div>
                <label class="block text-gray-800 font-semibold mb-2">
                    Status <span class="text-red-600">*</span>
                </label>
                <select name="status" id="inventoryStatus" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-white focus:border-blue-500 focus:ring focus:ring-blue-200 transition" required>
                    <option value="available">Available</option>
                    <option value="low">Low Stock</option>
                    <option value="out">Out of Stock</option>
                </select>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeInventoryModal()" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition duration-300">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-cyan-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-cyan-700 transition duration-300">
                    <i class="fas fa-save mr-2"></i>Add Inventory
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openInventoryModal(slotId, slotName) {
        document.getElementById('inventoryModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Set slot ID
        document.getElementById('inventorySlotID').value = slotId;
        
        // Display slot info
        document.getElementById('inventorySlotInfo').textContent = `Adding to: ${slotName}`;
        
        // Reset form
        document.getElementById('inventoryForm').reset();
        document.getElementById('inventorySlotID').value = slotId;
    }

    function closeInventoryModal() {
        document.getElementById('inventoryModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    document.getElementById('inventoryModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeInventoryModal();
        }
    });
</script>