<script>
    let currentSlotId = null;

    function viewSlotDetails(slotId) {
        currentSlotId = slotId;
        document.getElementById('slotDetailModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        fetch(`/shelter-management/slots/${slotId}/details`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Slot data received:', data);

                document.getElementById('detailSlotName').textContent = 'Slot ' + data.name;

                const sectionText = data.section
                    ? (data.section.name || data.section)
                    : 'Unknown Section';
                document.getElementById('detailSlotSection').textContent = sectionText;

                const statusEl = document.getElementById('detailSlotStatus');
                const statusColors = {
                    available: 'text-green-600',
                    occupied: 'text-orange-600',
                    maintenance: 'text-red-600'
                };

                let statusKey = data.status?.toLowerCase() || 'unknown';
                let readableStatus = statusKey.charAt(0).toUpperCase() + statusKey.slice(1);

                statusEl.textContent = readableStatus;
                statusEl.className = `font-bold text-lg ${statusColors[statusKey] || 'text-gray-600'}`;

                document.getElementById('detailSlotCapacity').textContent = data.capacity;
                document.getElementById('detailSlotOccupancy').textContent = data.animals.length;

                const occupancyPercent = data.capacity > 0
                    ? Math.round((data.animals.length / data.capacity) * 100)
                    : 0;

                document.getElementById('detailOccupancyPercent').textContent = occupancyPercent + '%';
                document.getElementById('detailProgressBar').style.width = occupancyPercent + '%';

                displayAnimals(data.animals);
                displayInventories(data.inventories);
            })
            .catch(error => {
                console.error('Error fetching slot details:', error);
                alert('Failed to load slot details.');
                closeSlotDetailModal();
            });
    }
</script>
