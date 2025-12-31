<!-- Section Detail Modal -->
<div id="sectionDetailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold flex items-center">
                        <i class="fas fa-layer-group mr-2"></i>
                        <span id="detailSectionName">Section Details</span>
                    </h2>
                    <p class="text-indigo-100 mt-1 text-sm">Complete section information</p>
                </div>
                <button onclick="closeSectionDetailModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-6">
            <!-- Section Information Card -->
            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-6 border border-indigo-200">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
                    Section Information
                </h3>
                <div class="space-y-4">
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-gray-600 text-sm mb-1">Description</p>
                        <p class="text-gray-800" id="detailSectionDescription"></p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <p class="text-gray-600 text-sm mb-1">Total Slots</p>
                            <p class="font-bold text-2xl text-indigo-600" id="detailSectionSlotCount">0</p>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <p class="text-gray-600 text-sm mb-1">Occupied Slots</p>
                            <p class="font-bold text-2xl text-orange-600" id="detailSectionOccupiedSlots">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slots Section -->
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 p-4 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-door-open text-purple-600 mr-2"></i>
                        Slots in this Section
                        <span id="detailSectionSlotBadge" class="ml-2 bg-purple-600 text-white px-3 py-1 rounded-full text-sm">0</span>
                    </h3>
                </div>
                <div id="detailSlotsContainer" class="p-4">
                    <!-- Slots will be loaded here -->
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-spinner fa-spin text-3xl mb-2"></i>
                        <p>Loading slots...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentSectionId = null;

    function viewSectionDetails(sectionId) {
        currentSectionId = sectionId;
        document.getElementById('sectionDetailModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        fetch(`/shelter-management/sections/${sectionId}/details`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Section data received:', data);

                // Section Info
                document.getElementById('detailSectionName').textContent = data.name;
                document.getElementById('detailSectionDescription').textContent = data.description || 'No description';
                document.getElementById('detailSectionSlotCount').textContent = data.slots.length;
                document.getElementById('detailSectionSlotBadge').textContent = data.slots.length;

                // Count occupied slots
                const occupiedSlots = data.slots.filter(slot => slot.status === 'occupied').length;
                document.getElementById('detailSectionOccupiedSlots').textContent = occupiedSlots;

                // Display slots
                displaySectionSlots(data.slots);
            })
            .catch(error => {
                console.error('Error fetching section details:', error);
                alert('Failed to load section details.');
                closeSectionDetailModal();
            });
    }

    function displaySectionSlots(slots) {
        const container = document.getElementById('detailSlotsContainer');

        if (slots.length === 0) {
            container.innerHTML = `
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-door-open text-4xl mb-2 opacity-50"></i>
                    <p>No slots in this section</p>
                </div>
            `;
            return;
        }

        const slotsHtml = slots.map(slot => {
            const statusColors = {
                available: 'bg-green-100 text-green-700',
                occupied: 'bg-orange-100 text-orange-700',
                maintenance: 'bg-red-100 text-red-700'
            };

            const statusKey = (slot.status || 'available').toLowerCase();
            const statusColor = statusColors[statusKey] || 'bg-gray-100 text-gray-700';

            return `
                <div onclick="viewSlotDetails(${slot.id})"
                     class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-lg p-4 border border-purple-200 hover:shadow-lg hover:border-purple-400 cursor-pointer transition-all">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="bg-purple-600 text-white rounded-full w-12 h-12 flex items-center justify-center">
                                <i class="fas fa-door-open text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-lg">${slot.name}</h4>
                                <p class="text-sm text-gray-600">Capacity: ${slot.capacity || 0}</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold ${statusColor}">
                            ${(slot.status || 'Available').charAt(0).toUpperCase() + (slot.status || 'Available').slice(1)}
                        </span>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                        <div class="bg-white rounded-lg p-2">
                            <p class="text-gray-600 text-xs">Animals</p>
                            <p class="font-semibold text-gray-800">${slot.animals_count || 0}</p>
                        </div>
                        <div class="bg-white rounded-lg p-2">
                            <p class="text-gray-600 text-xs">Inventory</p>
                            <p class="font-semibold text-gray-800">${slot.inventories_count || 0}</p>
                        </div>
                    </div>

                    <div class="mt-3 text-center">
                        <span class="text-xs text-purple-600 font-semibold">
                            <i class="fas fa-info-circle mr-1"></i>Click to view slot details
                        </span>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-2 gap-4">${slotsHtml}</div>`;
    }

    function closeSectionDetailModal() {
        document.getElementById('sectionDetailModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        currentSectionId = null;
    }
</script>
