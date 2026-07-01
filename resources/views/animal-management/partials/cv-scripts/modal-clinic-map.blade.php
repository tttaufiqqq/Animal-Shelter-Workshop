<script>
    // ============================================
    // Modal Functions
    // ============================================
    function openModal(type) {
        if (type === 'clinic') {
            document.getElementById('clinicModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(() => {
                initClinicMap();
            }, 100);
        } else if (type === 'vet') {
            document.getElementById('vetModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(type) {
        if (type === 'clinic') {
            document.getElementById('clinicModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            if (clinicMap) {
                clinicMap.remove();
                clinicMap = null;
                clinicMarker = null;
            }
            document.getElementById('clinicLatitude').value = '';
            document.getElementById('clinicLongitude').value = '';
            document.getElementById('clinicAddress').value = '';
            document.getElementById('clinicAddressSearch').value = '';
            document.getElementById('clinicMapError').classList.add('hidden');
        } else if (type === 'vet') {
            document.getElementById('vetModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    // Close modal when clicking outside
    document.getElementById('clinicModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal('clinic');
        }
    });

    document.getElementById('vetModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal('vet');
        }
    });

    // ============================================
    // Clinic Map Functions
    // ============================================
    function initClinicMap() {
        if (!clinicMap) {
            clinicMap = L.map('clinicMap').setView([2.7258, 101.9424], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(clinicMap);

            setTimeout(() => {
                clinicMap.invalidateSize();
            }, 200);

            clinicMap.on('click', function (e) {
                const { lat, lng } = e.latlng;
                updateClinicLocation(lat, lng);
                reverseGeocodeClinic(lat, lng);
            });
        }
    }

    function updateClinicLocation(lat, lng, address = '') {
        if (clinicMarker) {
            clinicMarker.setLatLng([lat, lng]);
        } else {
            clinicMarker = L.marker([lat, lng]).addTo(clinicMap);
        }

        document.getElementById('clinicLatitude').value = lat.toFixed(6);
        document.getElementById('clinicLongitude').value = lng.toFixed(6);

        if (address) {
            document.getElementById('clinicAddress').value = address;
        }

        clinicMap.setView([lat, lng], 15);
        document.getElementById('clinicMapError').classList.add('hidden');
    }

    function reverseGeocodeClinic(lat, lng) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`)
            .then(response => response.json())
            .then(data => {
                if (data && data.display_name) {
                    document.getElementById('clinicAddress').value = data.display_name;
                }
            })
            .catch(error => {
                console.error('Reverse geocoding error:', error);
            });
    }

    function searchClinicAddress() {
        const query = document.getElementById('clinicAddressSearch').value.trim();
        const searchBtn = document.getElementById('clinicSearchBtn');

        if (!query) {
            showToast('Please enter an address to search', 'warning');
            return;
        }

        // Show loading state
        searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Searching...';
        searchBtn.disabled = true;

        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&countrycodes=my`)
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    const result = data[0];
                    const lat = parseFloat(result.lat);
                    const lng = parseFloat(result.lon);
                    const address = result.display_name;
                    updateClinicLocation(lat, lng, address);
                    showToast('Location found!', 'success');
                } else {
                    return fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ', Malaysia')}&limit=5`)
                        .then(response => response.json())
                        .then(data => {
                            if (data && data.length > 0) {
                                const result = data[0];
                                const lat = parseFloat(result.lat);
                                const lng = parseFloat(result.lon);
                                const address = result.display_name;
                                updateClinicLocation(lat, lng, address);
                                showToast('Location found!', 'success');
                            } else {
                                showToast('Address not found. Try a more specific address or click on the map.', 'warning', 5000);
                            }
                        });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error searching address. Please try again.', 'error');
            })
            .finally(() => {
                searchBtn.innerHTML = '<i class="fas fa-search mr-1"></i>Search';
                searchBtn.disabled = false;
            });
    }
</script>
