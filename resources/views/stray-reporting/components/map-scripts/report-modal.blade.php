<script>
    // Open report modal
    function openReportModal() {
        const modal = document.getElementById('reportModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Check online status
        if (!navigator.onLine) {
            document.getElementById('offlineWarning').classList.remove('hidden');
        }

        // Initialize map
        setTimeout(() => {
            if (!mapInitialized) {
                initializeMap();
            } else if (map) {
                map.invalidateSize();
            }

            checkGeolocationSupport();
        }, 100);
    }

    // Close report modal
    function closeReportModal() {
        const modal = document.getElementById('reportModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';

        stopLocationTracking();

        // Reset form
        document.getElementById('reportForm').reset();
        document.getElementById('imagePreview').innerHTML = '';
        document.getElementById('gpsStatus').innerHTML = '';
        document.getElementById('gpsInstructions').classList.add('hidden');
        document.getElementById('accuracyTips').classList.add('hidden');
        document.getElementById('adjustLocationSection').classList.add('hidden');

        document.getElementById('gpsBtn').innerHTML = `
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        <span id="gpsBtnText">Use My Current Location</span>
        `;
        document.getElementById('gpsBtn').disabled = false;
    }
</script>
