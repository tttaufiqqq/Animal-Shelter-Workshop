<script>
    function viewAnimalDetails(animalId) {
        if (typeof openAnimalDetailModal === 'function') {
            openAnimalDetailModal(animalId);
        } else {
            console.error('openAnimalDetailModal function not found');
            window.location.href = `/animal-management/${animalId}`;
        }
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

        const animalsHtml = animals.map(animal => {
            const speciesEmoji = animal.species?.toLowerCase() === 'dog' ? '🐕' :
                animal.species?.toLowerCase() === 'cat' ? '🐱' : '🐾';

            const statusColors = {
                'not adopted': 'bg-green-100 text-green-700',
                'adopted': 'bg-blue-100 text-blue-700',
                'reserved': 'bg-yellow-100 text-yellow-700'
            };

            const statusKey = (animal.adoption_status || '').toLowerCase();
            const statusColor = statusColors[statusKey] || 'bg-gray-100 text-gray-700';

            return `
            <div onclick="viewAnimalDetails(${animal.id})"
                 class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200 hover:shadow-lg hover:border-green-400 cursor-pointer">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <div class="bg-green-600 text-white rounded-full w-12 h-12 flex items-center justify-center text-2xl">
                            ${speciesEmoji}
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg">${animal.name}</h4>
                            <p class="text-sm text-gray-600">
                                ${animal.species ? animal.species.charAt(0).toUpperCase() + animal.species.slice(1) : 'Unknown'}
                            </p>
                        </div>
                    </div>
                    <span class="px-2 py-1 rounded-full text-xs font-medium ${statusColor}">
                        ${animal.adoption_status || 'Unknown'}
                    </span>
                </div>
                <div class="mt-3 grid grid-cols-3 gap-2 text-sm text-gray-600">
                    <div>
                        <i class="fas fa-calendar text-green-600 mr-1"></i>
                        <span>${animal.age || 'N/A'}</span>
                    </div>
                    <div>
                        <i class="fas fa-venus-mars text-green-600 mr-1"></i>
                        <span>${animal.gender || 'N/A'}</span>
                    </div>
                    <div>
                        <i class="fas fa-heart text-green-600 mr-1"></i>
                        <span>${animal.vaccinations_count || 0} vaccines</span>
                    </div>
                </div>
                ${animal.health_details ? `
                    <div class="mt-3 p-2 bg-white rounded-lg">
                        <p class="text-xs text-gray-600 line-clamp-2">${animal.health_details}</p>
                    </div>
                ` : ''}
                <div class="mt-3 text-center">
                    <span class="text-xs text-green-600 font-semibold">
                        <i class="fas fa-info-circle mr-1"></i>Click to view details
                    </span>
                </div>
            </div>
        `;
        }).join('');

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
            let status = (item.status || 'unknown').toLowerCase();
            let statusColor =
                status === 'available' ? 'text-green-600 bg-green-50' :
                    status === 'low' ? 'text-orange-600 bg-orange-50' :
                        'text-red-600 bg-red-50';

            return `
            <div onclick="viewInventoryDetails(${item.id})"
                 class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-4 border border-blue-200 hover:shadow-lg hover:border-blue-400 cursor-pointer">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <div class="bg-blue-600 text-white rounded-full w-12 h-12 flex items-center justify-center">
                            <i class="fas fa-box text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg">${item.name}</h4>
                            <p class="text-sm text-gray-600">
                                ${item.category ? item.category.main || 'Uncategorized' : 'Uncategorized'}
                                ${item.category && item.category.sub ? ' • ' + item.category.sub : ''}
                            </p>
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
                        <i class="fas fa-edit mr-1"></i> Click to view details
                    </span>
                </div>
            </div>
        `;
        }).join('');

        container.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-2 gap-4">${inventoriesHtml}</div>`;
    }
</script>
