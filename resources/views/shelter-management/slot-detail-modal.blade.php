<!-- Slot Detail Modal -->
<div id="slotDetailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
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
                <button onclick="closeSlotDetailModal()" class="text-white hover:text-gray-200 transition">
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
                        <div id="detailProgressBar" class="bg-gradient-to-r from-indigo-500 to-purple-500 h-4 rounded-full transition-all duration-500" style="width: 0%"></div>
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
                    <!-- Animals will be loaded here -->
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
                        <button onclick="openInventoryModalForSlot()" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-blue-700 transition duration-300 shadow-sm">
                            <i class="fas fa-plus mr-2"></i>Add Inventory
                        </button>
                    </div>
                </div>
                <div id="detailInventoriesContainer" class="p-4">
                    <!-- Inventories will be loaded here -->
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-spinner fa-spin text-3xl mb-2"></i>
                        <p>Loading inventory...</p>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

<script>
   let currentSlotId = null;

    function viewSlotDetails(slotId) {
        currentSlotId = slotId;
        document.getElementById('slotDetailModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Fetch slot details via AJAX
        fetch(`/shelter-management/slots/${slotId}/details`)
            .then(response => response.json())
            .then(data => {
                // Populate slot information
                document.getElementById('detailSlotName').textContent = data.name;
                document.getElementById('detailSlotSection').textContent = data.section;
                
                // Status with color
                const statusEl = document.getElementById('detailSlotStatus');
                const statusColors = {
                    'available': 'text-green-600',
                    'occupied': 'text-orange-600',
                    'maintenance': 'text-red-600'
                };
                statusEl.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
                statusEl.className = `font-bold text-lg ${statusColors[data.status]}`;
                
                // Capacity info
                document.getElementById('detailSlotCapacity').textContent = data.capacity;
                document.getElementById('detailSlotOccupancy').textContent = data.animals.length;
                
                // Progress bar
                const occupancyPercent = data.capacity > 0 ? Math.round((data.animals.length / data.capacity) * 100) : 0;
                document.getElementById('detailOccupancyPercent').textContent = occupancyPercent + '%';
                document.getElementById('detailProgressBar').style.width = occupancyPercent + '%';

                // Populate animals
                displayAnimals(data.animals);
                
                // Populate inventories
                displayInventories(data.inventories);
            })
            .catch(error => {
                console.error('Error fetching slot details:', error);
                alert('Failed to load slot details. Please try again.');
                closeSlotDetailModal();
            });
    }

    function displayAnimals(animals) {
        const container = document.getElementById('detailAnimalsContainer');
        document.getElementById('detailAnimalCount').textContent = animals.length;

        if (animals.length === 0) {
            container.innerHTML = `
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-paw text-4xl mb-2 opacity-50"></i>
                    <p>No animals in this slot</p>
                </div>
            `;
            return;
        }

        const animalsHtml = animals.map(animal => `
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200 hover:shadow-md transition duration-300">
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="bg-green-600 text-white rounded-full w-12 h-12 flex items-center justify-center text-xl">
                            ${animal.species === 'dog' ? '🐕' : animal.species === 'cat' ? '🐱' : '🐾'}
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg">${animal.name}</h4>
                            <p class="text-sm text-gray-600">
                                <span class="font-semibold">${animal.species.charAt(0).toUpperCase() + animal.species.slice(1)}</span>
                                ${animal.breed ? ' • ' + animal.breed : ''}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-3 grid grid-cols-3 gap-2 text-sm text-gray-600">
                    <div>
                        <i class="fas fa-calendar text-green-600 mr-1"></i>
                        <span>${animal.age || 'N/A'} ${animal.age ? '' : ''}</span>
                    </div>
                    <div>
                        <i class="fas fa-venus-mars text-green-600 mr-1"></i>
                        <span>${animal.gender || 'N/A'}</span>
                    </div>
                </div>
            </div>
        `).join('');

        container.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-2 gap-4">${animalsHtml}</div>`;
    }

    function displayInventories(inventories) {
        const container = document.getElementById('detailInventoriesContainer');
        document.getElementById('detailInventoryCount').textContent = inventories.length;

        if (inventories.length === 0) {
            container.innerHTML = `
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-box-open text-4xl mb-2 opacity-50"></i>
                    <p>No inventory items in this slot</p>
                </div>
            `;
            return;
        }

       const inventoriesHtml = inventories.map(item => {
         const categoryText = item.category ? `${item.category.main} - ${item.category.sub}` : 'Uncategorized';
         const quantityText = item.quantity || 0;
         const unitText = item.unit || 'units';
         
         return `
            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-4 border border-blue-200 hover:shadow-md transition duration-300">
                  <div class="flex items-start justify-between">
                     <div class="flex items-center space-x-3">
                        <div class="bg-blue-600 text-white rounded-full w-12 h-12 flex items-center justify-center">
                              <i class="fas fa-box text-xl"></i>
                        </div>
                        <div>
                              <h4 class="font-bold text-gray-800">${item.name}</h4>
                              <p class="text-sm text-gray-600">${categoryText}</p>
                        </div>
                     </div>
                     <div class="text-right">
                        <p class="text-2xl font-bold text-blue-600">${quantityText}</p>
                        <p class="text-xs text-gray-500">${unitText}</p>
                     </div>
                  </div>
                  ${item.description ? `
                     <p class="mt-3 text-sm text-gray-600 bg-white rounded p-2">
                        ${item.description}
                     </p>
                  ` : ''}
            </div>
         `;
      }).join('');


        container.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-2 gap-4">${inventoriesHtml}</div>`;
    }

    function closeSlotDetailModal() {
        document.getElementById('slotDetailModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        currentSlotId = null;
    }

    function editSlotFromDetail() {
        if (currentSlotId) {
            closeSlotDetailModal();
            // Call your existing editSlot function
            editSlot(currentSlotId);
        }
    }

    function openInventoryModalForSlot() {
        if (currentSlotId) {
            // Get slot info to pass to inventory modal
            const slotName = document.getElementById('detailSlotName').textContent;
            
            // Check if the inventory modal open function exists
            if (typeof openInventoryModal === 'function') {
                openInventoryModal(currentSlotId, slotName);
            } else {
                console.error('openInventoryModal function not found');
                alert('Inventory modal not available. Please make sure it is included in the page.');
            }
        }
    }

    // Close modal when clicking outside
    document.getElementById('slotDetailModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeSlotDetailModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !document.getElementById('slotDetailModal').classList.contains('hidden')) {
            closeSlotDetailModal();
        }
    });
</script>