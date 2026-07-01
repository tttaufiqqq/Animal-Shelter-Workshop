{{-- Leaflet JS --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let modalMapInstance = null;

    // Map modal functions
    function showMapModal(lat, lng, address) {
        document.getElementById('mapModalAddress').textContent = address;
        document.getElementById('mapModal').classList.remove('hidden');

        setTimeout(() => {
            if (modalMapInstance) {
                modalMapInstance.remove();
            }

            modalMapInstance = L.map('modalMap').setView([lat, lng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(modalMapInstance);

            L.marker([lat, lng]).addTo(modalMapInstance);
        }, 100);
    }

    function closeMapModal() {
        document.getElementById('mapModal').classList.add('hidden');
        if (modalMapInstance) {
            modalMapInstance.remove();
            modalMapInstance = null;
        }
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMapModal();
        }
    });

    // Close connectivity banner
    function closeConnectivityBanner() {
        document.getElementById('connectivityBanner').style.display = 'none';
    }
</script>
