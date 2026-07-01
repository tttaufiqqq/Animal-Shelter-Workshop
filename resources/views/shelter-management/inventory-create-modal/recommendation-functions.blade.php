<script>
    function openInventoryModal(slotId, slotName, animalData = null) {
        document.getElementById('inventoryModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        document.getElementById('inventorySlotID').value = slotId;
        document.getElementById('inventorySlotInfo').textContent = `Adding to: ${slotName}`;

        document.getElementById('inventoryForm').reset();
        document.getElementById('inventorySlotID').value = slotId;

        if (animalData && animalData.name) {
            displayAnimalSuggestions(animalData);
        } else {
            document.getElementById('animalInfoSection').classList.add('hidden');
            document.getElementById('noAnimalSection').classList.remove('hidden');
        }
    }

    function displayAnimalSuggestions(animal) {
        document.getElementById('animalInfoSection').classList.remove('hidden');
        document.getElementById('noAnimalSection').classList.add('hidden');

        document.getElementById('animalName').textContent = animal.name;
        document.getElementById('animalSpecies').textContent = animal.species || 'Unknown';

        const healthEl = document.getElementById('animalHealth');
        const healthColors = {
            'Healthy': 'text-green-600',
            'Sick': 'text-red-600',
            'Recovering': 'text-orange-600',
            'Critical': 'text-red-700'
        };
        healthEl.textContent = animal.health_status || 'Unknown';
        healthEl.className = `font-bold ${healthColors[animal.health_status] || 'text-gray-600'}`;

        document.getElementById('animalAge').textContent = animal.age_category || 'Adult';

        const recommendations = getRecommendations(animal.species, animal.health_status, animal.age_category);
        displayRecommendations(recommendations);
    }

    function getRecommendations(species, healthStatus, ageCategory) {
        const normalizedSpecies = species || 'Other';
        const normalizedHealth = healthStatus || 'Healthy';
        const normalizedAge = ageCategory || 'Adult';

        const speciesRecs = inventoryRecommendations[normalizedSpecies] || inventoryRecommendations['Other'];
        const healthRecs = speciesRecs[normalizedHealth] || speciesRecs['Healthy'];
        return healthRecs[normalizedAge] || healthRecs['Adult'] || [];
    }

    function displayRecommendations(recommendations) {
        const container = document.getElementById('recommendedItems');
        container.innerHTML = '';

        if (recommendations.length === 0) {
            container.innerHTML = '<p class="text-sm text-gray-600">No specific recommendations available.</p>';
            return;
        }

        recommendations.forEach((rec, index) => {
            const priorityBadge = rec.priority === 'high'
                ? '<span class="ml-2 px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded-full">CRITICAL</span>'
                : rec.priority === 'medium'
                ? '<span class="ml-2 px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-bold rounded-full">IMPORTANT</span>'
                : '';

            const item = document.createElement('div');
            item.className = 'bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg p-3 border border-blue-200 hover:border-blue-400 cursor-pointer';
            item.innerHTML = `
                <div class="flex items-start gap-3">
                    <div class="bg-blue-600 text-white rounded-lg p-2">
                        <i class="fas fa-${rec.icon} text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-gray-800 text-sm flex items-center">
                            ${rec.type}
                            ${priorityBadge}
                        </p>
                        <p class="text-xs text-gray-600 mt-1">
                            <span class="font-semibold">Category:</span> ${rec.category}
                        </p>
                        <p class="text-xs text-blue-700 mt-1">
                            <span class="font-semibold">Suggested Brands:</span> ${rec.brands.join(', ')}
                        </p>
                    </div>
                    <button type="button" onclick="applyRecommendation('${rec.type}', '${rec.brands[0]}')"
                            class="px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700">
                        Apply
                    </button>
                </div>
            `;
            container.appendChild(item);
        });
    }

    function applyRecommendation(itemType, suggestedBrand) {
        document.getElementById('inventoryItemName').value = itemType;
        document.getElementById('inventoryBrand').value = suggestedBrand;

        document.getElementById('inventoryItemName').scrollIntoView({ behavior: 'smooth', block: 'center' });

        document.getElementById('inventoryItemName').classList.add('ring-2', 'ring-blue-500');
        setTimeout(() => {
            document.getElementById('inventoryItemName').classList.remove('ring-2', 'ring-blue-500');
        }, 2000);
    }

    function closeInventoryModal() {
        document.getElementById('inventoryModal').classList.add('hidden');
        document.body.style.overflow = 'auto';

        document.getElementById('animalInfoSection').classList.add('hidden');
        document.getElementById('noAnimalSection').classList.add('hidden');
    }

    document.getElementById('inventoryModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeInventoryModal();
        }
    });

    document.getElementById('inventoryForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('inventorySubmitBtn');
        const submitIcon = document.getElementById('inventorySubmitIcon');
        const submitText = document.getElementById('inventorySubmitText');
        const cancelBtn = document.getElementById('inventoryCancelBtn');

        submitBtn.disabled = true;
        cancelBtn.disabled = true;
        submitIcon.className = 'fas fa-spinner fa-spin';
        submitText.textContent = 'Adding...';

        return true;
    });
</script>

<style>
    .fa-spin {
        animation: fa-spin 1s infinite linear;
    }

    @keyframes fa-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
