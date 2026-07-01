<script>
    let currentInventoryId = null;
    let currentInventoryData = null;

    function openInventoryDetailModal(inventoryId) {
        currentInventoryId = inventoryId;
        document.getElementById('inventoryDetailModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        document.getElementById('inventoryDetailLoading').classList.remove('hidden');
        document.getElementById('inventoryDetailContent').classList.add('hidden');

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
        document.getElementById('inventoryDetailLoading').classList.add('hidden');
        document.getElementById('inventoryDetailContent').classList.remove('hidden');

        document.getElementById('inventoryDetailTitle').textContent = data.item_name;
        document.getElementById('inventoryDetailSubtitle').textContent = `${data.category_main}${data.category_sub ? ' - ' + data.category_sub : ''}`;

        document.getElementById('detailItemName').textContent = data.item_name;
        document.getElementById('detailCategory').textContent = `${data.category_main}${data.category_sub ? ' - ' + data.category_sub : ''}`;
        document.getElementById('detailQuantity').textContent = data.quantity || 0;
        document.getElementById('detailWeight').textContent = data.weight ? `${data.weight} kg` : 'N/A';
        document.getElementById('detailBrand').textContent = data.brand || 'N/A';
        document.getElementById('detailSlotLocation').textContent = data.slot_name ? `${data.slot_name} (${data.slot_section})` : 'N/A';

        const statusEl = document.getElementById('detailStatus');
        const statusColors = {
            'available': 'text-green-600',
            'low': 'text-orange-600',
            'out': 'text-red-600'
        };
        statusEl.textContent = data.status ? data.status.charAt(0).toUpperCase() + data.status.slice(1) : 'N/A';
        statusEl.className = `font-bold ${statusColors[data.status] || 'text-gray-600'}`;

        if (data.animal) {
            displayCompatibilityAnalysis(data.animal, data);
        } else {
            document.getElementById('compatibilitySection').classList.add('hidden');
            document.getElementById('noAnimalCompatSection').classList.remove('hidden');
        }

        document.getElementById('updateInventoryForm').action = `/shelter-management/inventory/${data.id}`;
    }
</script>
