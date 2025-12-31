<!-- Category Detail Modal -->
<div id="categoryDetailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-purple-500 to-pink-600 text-white p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold flex items-center">
                        <i class="fas fa-folder mr-2"></i>
                        <span id="detailCategoryMain">Category Details</span>
                    </h2>
                    <p class="text-purple-100 mt-1 flex items-center">
                        <i class="fas fa-tag mr-2"></i>
                        <span id="detailCategorySub"></span>
                    </p>
                </div>
                <button onclick="closeCategoryDetailModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-6">
            <!-- Category Information Card -->
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-200">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-purple-600 mr-2"></i>
                    Category Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-gray-600 text-sm mb-1">Main Category</p>
                        <p class="font-bold text-lg text-purple-600 flex items-center gap-2">
                            <i class="fas fa-folder"></i>
                            <span id="detailCategoryMainText"></span>
                        </p>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-gray-600 text-sm mb-1">Sub Category</p>
                        <p class="font-bold text-lg text-pink-600 flex items-center gap-2">
                            <i class="fas fa-tag"></i>
                            <span id="detailCategorySubText"></span>
                        </p>
                    </div>
                </div>
                <div class="mt-4 bg-white rounded-lg p-4 shadow-sm">
                    <p class="text-gray-600 text-sm mb-1">Total Inventory Items</p>
                    <p class="font-bold text-3xl text-blue-600" id="detailCategoryInventoryCount">0</p>
                </div>
            </div>

            <!-- Inventory Items Section -->
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="bg-gradient-to-r from-blue-50 to-cyan-50 p-4 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-box text-blue-600 mr-2"></i>
                        Inventory Items
                        <span id="detailCategoryInventoryBadge" class="ml-2 bg-blue-600 text-white px-3 py-1 rounded-full text-sm">0</span>
                    </h3>
                </div>
                <div id="detailCategoryInventoriesContainer" class="p-4">
                    <!-- Inventory items will be loaded here -->
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-spinner fa-spin text-3xl mb-2"></i>
                        <p>Loading inventory items...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentCategoryId = null;

    function viewCategoryDetails(categoryId) {
        currentCategoryId = categoryId;
        document.getElementById('categoryDetailModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        fetch(`/shelter-management/categories/${categoryId}/details`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Category data received:', data);

                // Category Info
                document.getElementById('detailCategoryMain').textContent = data.main;
                document.getElementById('detailCategorySub').textContent = data.sub;
                document.getElementById('detailCategoryMainText').textContent = data.main;
                document.getElementById('detailCategorySubText').textContent = data.sub;
                document.getElementById('detailCategoryInventoryCount').textContent = data.inventories.length;
                document.getElementById('detailCategoryInventoryBadge').textContent = data.inventories.length;

                // Display inventory items
                displayCategoryInventories(data.inventories);
            })
            .catch(error => {
                console.error('Error fetching category details:', error);
                alert('Failed to load category details.');
                closeCategoryDetailModal();
            });
    }

    function displayCategoryInventories(inventories) {
        const container = document.getElementById('detailCategoryInventoriesContainer');

        if (inventories.length === 0) {
            container.innerHTML = `
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-box-open text-4xl mb-2 opacity-50"></i>
                    <p>No inventory items in this category</p>
                </div>
            `;
            return;
        }

        const inventoriesHtml = inventories.map(item => {
            let status = (item.status || 'unknown').toLowerCase();
            let statusColor =
                status === 'available' ? 'text-green-600 bg-green-50' :
                status === 'low' ? 'text-orange-600 bg-orange-50' :
                'text-red-600 bg-red-50';

            return `
                <div onclick="viewInventoryDetails(${item.id})"
                     class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-4 border border-blue-200 hover:shadow-lg hover:border-blue-400 cursor-pointer transition-all">

                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="bg-blue-600 text-white rounded-full w-12 h-12 flex items-center justify-center">
                                <i class="fas fa-box text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-lg">${item.name}</h4>
                                ${item.slot ? `
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-door-open mr-1"></i>${item.slot.name}
                                    </p>
                                ` : '<p class="text-sm text-gray-500">No slot assigned</p>'}
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="text-2xl font-bold text-blue-600">${item.quantity || 0}</p>
                            <p class="text-xs text-gray-500">${item.unit || 'units'}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-sm">
                        ${item.brand ? `
                            <div class="bg-white rounded-lg p-2">
                                <p class="text-gray-600 text-xs">Brand</p>
                                <p class="font-semibold text-gray-800">${item.brand}</p>
                            </div>` : ''}

                        <div class="bg-white rounded-lg p-2">
                            <p class="text-gray-600 text-xs">Status</p>
                            <p class="font-semibold ${statusColor}">
                                ${status.charAt(0).toUpperCase() + status.slice(1)}
                            </p>
                        </div>
                    </div>

                    ${item.description ? `
                        <div class="mt-3 p-2 bg-white rounded-lg">
                            <p class="text-xs text-gray-600 line-clamp-2">${item.description}</p>
                        </div>
                    ` : ''}

                    <div class="mt-3 text-center">
                        <span class="text-xs text-blue-600 font-semibold">
                            <i class="fas fa-info-circle mr-1"></i>Click to view details
                        </span>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-2 gap-4">${inventoriesHtml}</div>`;
    }

    function closeCategoryDetailModal() {
        document.getElementById('categoryDetailModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        currentCategoryId = null;
    }
</script>
