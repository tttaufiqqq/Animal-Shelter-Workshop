<script>
    // Initialize map
    function initializeMap() {
        if (mapInitialized || typeof L === 'undefined') return;

        try {
            // Default to Kuala Lumpur
            map = L.map('map', {
                zoomControl: true,
                attributionControl: true,
                scrollWheelZoom: true,
                doubleClickZoom: true
            }).setView([3.1390, 101.6869], 13);

            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '© OpenStreetMap contributors © CARTO',
                maxZoom: 19
            }).addTo(map);

            // Click to pin location - ALSO GETS CITY/STATE
            map.on('click', async function(e) {
                showToast('Getting address for clicked location...', 'info');
                await updateLocationOnMap(e.latlng.lat, e.latlng.lng, 50);
                await getAddressDetailsWithFallback(e.latlng.lat, e.latlng.lng);
            });

            mapInitialized = true;

        } catch (error) {
            console.error('Map initialization failed:', error);
            showToast('Map failed to load', 'error');
        }
    }

    // Enable manual adjustment mode
    function enableManualAdjustment() {
        if (!marker) return;

        showToast('Drag the pin to adjust location', 'info');
        document.getElementById('adjustLocationSection').classList.add('hidden');

        marker.getElement().classList.add('animate-bounce');
        setTimeout(() => {
            marker.getElement().classList.remove('animate-bounce');
        }, 1000);
    }
</script>
