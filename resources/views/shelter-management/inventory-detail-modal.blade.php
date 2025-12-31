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

<script>
    let currentInventoryId = null;
    let currentInventoryData = null;

    function openInventoryDetailModal(inventoryId) {
        currentInventoryId = inventoryId;
        document.getElementById('inventoryDetailModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Show loading state
        document.getElementById('inventoryDetailLoading').classList.remove('hidden');
        document.getElementById('inventoryDetailContent').classList.add('hidden');

        // Fetch inventory details
        fetch(`/shelter-management/inventory/${inventoryId}/details`)
            .then(response => response.json())
            .then(data => {
                currentInventoryData = data;
                displayInventoryDetails(data);
            })
            .catch(error => {
                console.error('Error fetching inventory details:', error);
                alert('Failed to load inventory details. Please try again.');
                closeInventoryDetailModal();
            });
    }

    function displayInventoryDetails(data) {
        // Hide loading, show content
        document.getElementById('inventoryDetailLoading').classList.add('hidden');
        document.getElementById('inventoryDetailContent').classList.remove('hidden');

        // Populate header
        document.getElementById('inventoryDetailTitle').textContent = data.item_name;
        document.getElementById('inventoryDetailSubtitle').textContent = `${data.category_main}${data.category_sub ? ' - ' + data.category_sub : ''}`;

        // Populate details
        document.getElementById('detailItemName').textContent = data.item_name;
        document.getElementById('detailCategory').textContent = `${data.category_main}${data.category_sub ? ' - ' + data.category_sub : ''}`;
        document.getElementById('detailQuantity').textContent = data.quantity || 0;
        document.getElementById('detailWeight').textContent = data.weight ? `${data.weight} kg` : 'N/A';
        document.getElementById('detailBrand').textContent = data.brand || 'N/A';
        document.getElementById('detailSlotLocation').textContent = data.slot_name ? `${data.slot_name} (${data.slot_section})` : 'N/A';

        // Status with color
        const statusEl = document.getElementById('detailStatus');
        const statusColors = {
            'available': 'text-green-600',
            'low': 'text-orange-600',
            'out': 'text-red-600'
        };
        statusEl.textContent = data.status ? data.status.charAt(0).toUpperCase() + data.status.slice(1) : 'N/A';
        statusEl.className = `font-bold ${statusColors[data.status] || 'text-gray-600'}`;

        // Display animal compatibility analysis
        if (data.animal) {
            displayCompatibilityAnalysis(data.animal, data);
        } else {
            document.getElementById('compatibilitySection').classList.add('hidden');
            document.getElementById('noAnimalCompatSection').classList.remove('hidden');
        }

        // Update form action
        document.getElementById('updateInventoryForm').action = `/shelter-management/inventory/${data.id}`;
    }

    function displayCompatibilityAnalysis(animal, inventoryData) {
        document.getElementById('compatibilitySection').classList.remove('hidden');
        document.getElementById('noAnimalCompatSection').classList.add('hidden');

        // Populate animal info
        document.getElementById('compatAnimalName').textContent = `${animal.name} (${animal.species})`;

        const healthEl = document.getElementById('compatHealthStatus');
        const healthColors = {
            'Healthy': 'text-green-600',
            'Sick': 'text-red-600',
            'Recovering': 'text-orange-600',
            'Critical': 'text-red-700'
        };
        healthEl.textContent = animal.health_status || 'Unknown';
        healthEl.className = `font-bold ${healthColors[animal.health_status] || 'text-gray-600'}`;

        // Analyze compatibility
        const compatibility = analyzeCompatibility(animal, inventoryData);
        displayCompatibilityResult(compatibility);
    }

    function analyzeCompatibility(animal, inventory) {
        const itemName = inventory.item_name.toLowerCase();
        const brand = (inventory.brand || '').toLowerCase();
        const category = (inventory.category_main || '').toLowerCase();
        const species = animal.species || 'Unknown';
        const health = animal.health_status || 'Healthy';
        const age = animal.age_category || 'Adult';

        let status = 'suitable';
        let messages = [];

        // Define compatibility rules
        const rules = {
            // Species-specific restrictions
            'Cat': {
                unsuitable: ['dog food', 'dog treat', 'dog toy', 'canine', 'puppy'],
                warnings: ['xylitol', 'chocolate', 'onion', 'garlic']
            },
            'Dog': {
                unsuitable: ['cat food', 'cat litter', 'feline', 'kitten'],
                warnings: ['xylitol', 'chocolate', 'grapes', 'raisins', 'onion', 'garlic']
            }
        };

        // Health-based recommendations
        if (health === 'Sick' || health === 'Critical') {
            if (!itemName.includes('veterinary') && !itemName.includes('recovery') && !itemName.includes('medical') && category === 'food') {
                status = 'warning';
                messages.push('[!] Consider using veterinary-prescribed recovery diet for sick animals');
            }
            if (itemName.includes('recovery') || itemName.includes('veterinary') || brand.includes('veterinary')) {
                status = 'excellent';
                messages.push('[✓] Excellent choice! This is a specialized recovery product suitable for sick animals');
            }
        }

        // Age-based recommendations
        if (age === 'Kitten' || age === 'Puppy') {
            if (itemName.includes('adult') && !itemName.includes('kitten') && !itemName.includes('puppy')) {
                status = 'warning';
                messages.push('[!] This product is for adults. Consider using age-appropriate formula for young animals');
            }
            if (itemName.includes(age.toLowerCase())) {
                messages.push('[✓] Perfect! This product is specifically formulated for young animals');
            }
        }

        if (age === 'Senior') {
            if (itemName.includes('senior') || itemName.includes('7+')) {
                messages.push('[✓] Great! This product supports senior animal health needs');
            }
        }

        // Species compatibility check
        if (rules[species]) {
            // Check unsuitable items
            for (const unsuitable of rules[species].unsuitable) {
                if (itemName.includes(unsuitable) || brand.includes(unsuitable)) {
                    status = 'unsuitable';
                    messages.push(`[✗] UNSUITABLE: This product is not appropriate for ${species.toLowerCase()}s`);
                    break;
                }
            }

            // Check warnings
            for (const warning of rules[species].warnings) {
                if (itemName.includes(warning) || brand.includes(warning)) {
                    status = 'danger';
                    messages.push(`[⚠] DANGER: Contains ${warning} which is toxic to ${species.toLowerCase()}s`);
                    break;
                }
            }
        }

        // If no specific messages, add general compatibility
        if (messages.length === 0) {
            messages.push(`[✓] This item appears suitable for ${animal.name} (${species} - ${health})`);
        }

        return { status, messages };
    }

    function displayCompatibilityResult(compatibility) {
        const container = document.getElementById('compatibilityStatus');
        const compatSection = document.getElementById('compatibilitySection');

        const statusConfig = {
            'excellent': {
                bg: 'from-green-50 to-emerald-50',
                border: 'border-green-300',
                icon: 'check-circle',
                iconColor: 'text-green-600',
                containerBg: 'bg-green-600'
            },
            'suitable': {
                bg: 'from-green-50 to-emerald-50',
                border: 'border-green-300',
                icon: 'check-circle',
                iconColor: 'text-green-600',
                containerBg: 'bg-green-600'
            },
            'warning': {
                bg: 'from-orange-50 to-amber-50',
                border: 'border-orange-300',
                icon: 'exclamation-triangle',
                iconColor: 'text-orange-600',
                containerBg: 'bg-orange-600'
            },
            'unsuitable': {
                bg: 'from-red-50 to-pink-50',
                border: 'border-red-300',
                icon: 'times-circle',
                iconColor: 'text-red-600',
                containerBg: 'bg-red-600'
            },
            'danger': {
                bg: 'from-red-50 to-pink-50',
                border: 'border-red-400',
                icon: 'skull-crossbones',
                iconColor: 'text-red-700',
                containerBg: 'bg-red-700'
            }
        };

        const config = statusConfig[compatibility.status];

        // Update section background
        compatSection.className = `mx-6 mt-6 bg-gradient-to-br ${config.bg} rounded-xl p-5 border-2 ${config.border}`;

        // Update icon
        const iconContainer = compatSection.querySelector('.bg-green-600, .bg-orange-600, .bg-red-600, .bg-red-700');
        if (iconContainer) {
            iconContainer.className = `${config.containerBg} text-white rounded-full p-3`;
            const icon = iconContainer.querySelector('i');
            if (icon) {
                icon.className = `fas fa-${config.icon} text-xl`;
            }
        }

        // Display messages
        container.innerHTML = compatibility.messages.map(msg => {
            let bgColor = 'bg-white';
            let borderColor = 'border-gray-200';

            if (msg.includes('[✗]') || msg.includes('[⚠] DANGER')) {
                bgColor = 'bg-red-100';
                borderColor = 'border-red-300';
            } else if (msg.includes('[!]')) {
                bgColor = 'bg-orange-100';
                borderColor = 'border-orange-300';
            } else if (msg.includes('[✓]')) {
                bgColor = 'bg-green-100';
                borderColor = 'border-green-300';
            }

            return `
                <div class="${bgColor} rounded-lg p-3 border ${borderColor} mb-2">
                    <p class="text-sm font-semibold text-gray-800">${msg}</p>
                </div>
            `;
        }).join('');
    }

    function toggleInventoryEdit() {
        const form = document.getElementById('inventoryEditForm');
        const isHidden = form.classList.contains('hidden');

        if (isHidden) {
            // Show edit form and populate with current data
            form.classList.remove('hidden');
            document.getElementById('editInventoryId').value = currentInventoryData.id;
            document.getElementById('editItemName').value = currentInventoryData.item_name;
            document.getElementById('editCategoryID').value = currentInventoryData.categoryID;
            document.getElementById('editQuantity').value = currentInventoryData.quantity;
            document.getElementById('editWeight').value = currentInventoryData.weight || '';
            document.getElementById('editBrand').value = currentInventoryData.brand || '';
            document.getElementById('editStatus').value = currentInventoryData.status;

            // Change button text
            document.getElementById('editInventoryBtn').innerHTML = '<i class="fas fa-times mr-2"></i>Cancel Edit';
        } else {
            // Hide edit form
            form.classList.add('hidden');
            document.getElementById('editInventoryBtn').innerHTML = '<i class="fas fa-edit mr-2"></i>Edit';
        }
    }

    function cancelInventoryEdit() {
        document.getElementById('inventoryEditForm').classList.add('hidden');
        document.getElementById('editInventoryBtn').innerHTML = '<i class="fas fa-edit mr-2"></i>Edit';
    }

    function deleteInventoryItem() {
        if (confirm('Are you sure you want to delete this inventory item? This action cannot be undone.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/shelter-management/inventory/${currentInventoryId}`;

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken.content;
                form.appendChild(csrfInput);
            }

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }
    }

    function closeInventoryDetailModal() {
        document.getElementById('inventoryDetailModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        currentInventoryId = null;
        currentInventoryData = null;
        
        // Reset form visibility
        document.getElementById('inventoryEditForm').classList.add('hidden');
        document.getElementById('editInventoryBtn').innerHTML = '<i class="fas fa-edit mr-2"></i>Edit';
    }

    // Close modal when clicking outside
    document.getElementById('inventoryDetailModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeInventoryDetailModal();
        }
    });

    // Handle update inventory form submission with loading state
    document.getElementById('updateInventoryForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('updateInventorySubmitBtn');
        const submitIcon = document.getElementById('updateInventoryIcon');
        const submitText = document.getElementById('updateInventoryText');
        const cancelBtn = document.getElementById('updateInventoryCancelBtn');

        // Disable buttons
        submitBtn.disabled = true;
        cancelBtn.disabled = true;

        // Change icon to spinner
        submitIcon.className = 'fas fa-spinner fa-spin';
        submitText.textContent = 'Updating...';

        // Allow form to submit
        return true;
    });
</script>

<style>
    /* Spinner animation */
    .fa-spin {
        animation: fa-spin 1s infinite linear;
    }

    @keyframes fa-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>