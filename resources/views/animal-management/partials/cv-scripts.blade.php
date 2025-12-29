    <script>
        let clinicMap;
        let clinicMarker;

        // Open modal function
        function openModal(type) {
            if (type === 'clinic') {
                document.getElementById('clinicModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                // Initialize map after modal is visible
                setTimeout(() => {
                    initClinicMap();
                }, 100);
            } else if (type === 'vet') {
                document.getElementById('vetModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        // Close modal function - FIXED
        function closeModal(type) {
            if (type === 'clinic') {
                document.getElementById('clinicModal').classList.add('hidden');
                document.body.style.overflow = 'auto';
                // Reset map
                if (clinicMap) {
                    clinicMap.remove();
                    clinicMap = null;
                    clinicMarker = null;
                }
                // Reset form fields
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

        // Initialize map when modal opens
        function initClinicMap() {
            if (!clinicMap) {
                // Default center to Malaysia (Seremban, Negeri Sembilan)
                clinicMap = L.map('clinicMap').setView([2.7258, 101.9424], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(clinicMap);

                // Fix map display issue
                setTimeout(() => {
                    clinicMap.invalidateSize();
                }, 200);

                // Click on map to pin location
                clinicMap.on('click', function (e) {
                    const { lat, lng } = e.latlng;
                    updateClinicLocation(lat, lng);

                    // Reverse geocode to get address when clicking on map
                    reverseGeocodeClinic(lat, lng);
                });
            }
        }

        // Function to update marker and form fields
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

            // Hide map error when location is selected
            document.getElementById('clinicMapError').classList.add('hidden');
        }

        // Reverse geocode function
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

        // Search address functionality
        function searchClinicAddress() {
            const query = document.getElementById('clinicAddressSearch').value.trim();
            const searchBtn = document.getElementById('clinicSearchBtn');

            if (!query) {
                alert('Please enter an address to search');
                return;
            }

            // Show loading state
            searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Searching...';
            searchBtn.disabled = true;

            // Try first search with Malaysia country code
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&countrycodes=my`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const result = data[0];
                        const lat = parseFloat(result.lat);
                        const lng = parseFloat(result.lon);
                        const address = result.display_name;

                        updateClinicLocation(lat, lng, address);
                    } else {
                        // If no results with country code, try without it but add "Malaysia" to query
                        return fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ', Malaysia')}&limit=5`)
                            .then(response => response.json())
                            .then(data => {
                                if (data && data.length > 0) {
                                    const result = data[0];
                                    const lat = parseFloat(result.lat);
                                    const lng = parseFloat(result.lon);
                                    const address = result.display_name;

                                    updateClinicLocation(lat, lng, address);
                                } else {
                                    alert('Address not found. Please try:\n\n1. A more specific address (include area/city)\n2. Click directly on the map instead\n3. Use a well-known landmark nearby');
                                }
                            });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error searching address. Please try again or click on the map to select location manually.');
                })
                .finally(() => {
                    searchBtn.innerHTML = '<i class="fas fa-search mr-1"></i>Search';
                    searchBtn.disabled = false;
                });
        }

        // Add event listeners when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Search on button click
            const searchBtn = document.getElementById('clinicSearchBtn');
            if (searchBtn) {
                searchBtn.addEventListener('click', searchClinicAddress);
            }

            // Search on Enter key
            const addressSearch = document.getElementById('clinicAddressSearch');
            if (addressSearch) {
                addressSearch.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        searchClinicAddress();
                    }
                });
            }

            // Form validation
            const clinicForm = document.querySelector('#clinicModal form');
            if (clinicForm) {
                clinicForm.addEventListener('submit', function(e) {
                    const latitude = document.getElementById('clinicLatitude').value;
                    const longitude = document.getElementById('clinicLongitude').value;

                    if (!latitude || !longitude) {
                        e.preventDefault();
                        document.getElementById('clinicMapError').classList.remove('hidden');
                        document.getElementById('clinicMap').scrollIntoView({ behavior: 'smooth', block: 'center' });

                        alert('Please select a location on the map before submitting the form.');
                        return false;
                    }
                });
            }
        });
        let editClinicMap;
        let editClinicMarker;
        let editClinicGeocoder;

        // Initialize edit map when modal opens
        function initEditClinicMap(lat, lng) {
            if (!editClinicMap) {
                editClinicMap = L.map('editClinicMap').setView([lat || 2.7297, lng || 101.9381], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(editClinicMap);

                editClinicMap.on('click', function(e) {
                    setEditClinicLocation(e.latlng.lat, e.latlng.lng);
                });
            } else {
                editClinicMap.setView([lat || 2.7297, lng || 101.9381], 13);
            }

            // Set initial marker if coordinates exist
            if (lat && lng) {
                setEditClinicLocation(lat, lng);
            }

            // Fix map display issue
            setTimeout(() => {
                editClinicMap.invalidateSize();
            }, 100);
        }

        function setEditClinicLocation(lat, lng) {
            if (editClinicMarker) {
                editClinicMap.removeLayer(editClinicMarker);
            }

            editClinicMarker = L.marker([lat, lng]).addTo(editClinicMap);
            editClinicMap.setView([lat, lng], 15);

            document.getElementById('edit_clinicLatitude').value = lat.toFixed(6);
            document.getElementById('edit_clinicLongitude').value = lng.toFixed(6);

            // Reverse geocode to get address
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    if (data.display_name) {
                        document.getElementById('edit_clinicAddress').value = data.display_name;
                    }
                });
        }

        // Search address for edit modal
        document.getElementById('editClinicSearchBtn')?.addEventListener('click', function() {
            const address = document.getElementById('editClinicAddressSearch').value;
            if (!address) return;

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const result = data[0];
                        setEditClinicLocation(parseFloat(result.lat), parseFloat(result.lon));
                    } else {
                        alert('Address not found. Please try a different search term.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error searching for address');
                });
        });

        // Edit Clinic Function
        function editClinic(clinicId) {
            fetch(`/clinics/${clinicId}/edit`)
                .then(response => response.json())
                .then(data => {
                    // Populate form fields
                    document.getElementById('edit_clinic_name').value = data.name || '';
                    document.getElementById('edit_clinicAddress').value = data.address || '';
                    document.getElementById('edit_phone').value = data.contactNum || '';
                    document.getElementById('edit_clinicLatitude').value = data.latitude || '';
                    document.getElementById('edit_clinicLongitude').value = data.longitude || '';

                    // Update form action
                    document.getElementById('editClinicForm').action = `/clinics/${clinicId}`;

                    // Show modal
                    document.getElementById('editClinicModal').classList.remove('hidden');

                    // Initialize map with clinic location
                    setTimeout(() => {
                        initEditClinicMap(parseFloat(data.latitude), parseFloat(data.longitude));
                    }, 100);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load clinic data');
                });
        }

        // Close Edit Modal
        function closeEditClinicModal() {
            document.getElementById('editClinicModal').classList.add('hidden');
            document.getElementById('editClinicForm').reset();

            // Clear marker
            if (editClinicMarker && editClinicMap) {
                editClinicMap.removeLayer(editClinicMarker);
                editClinicMarker = null;
            }
        }

        // Close modal when clicking outside
        document.getElementById('editClinicModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditClinicModal();
            }
        });

        // Delete Clinic Function
        function deleteClinic(clinicId) {
            if (confirm('Are you sure you want to delete this clinic? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/clinics/${clinicId}`;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';

                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        }
        function editVet(vetId) {
            console.log('Edit vet clicked:', vetId); // Debug

            const modal = document.getElementById('editVetModal');
            if (!modal) {
                console.error('Modal not found!');
                return;
            }

            modal.classList.remove('hidden');

            const url = `/vets/${vetId}/edit`;

            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Vet data received:', data); // Debug

                    // Check if elements exist before setting values
                    const nameInput = document.getElementById('edit_vet_name');
                    const specializationInput = document.getElementById('edit_vet_specialization');
                    const licenseInput = document.getElementById('edit_vet_license_no');
                    const clinicSelect = document.getElementById('edit_vet_clinicID');
                    const contactInput = document.getElementById('edit_vet_contactNum');
                    const emailInput = document.getElementById('edit_vet_email');

                    if (!nameInput || !specializationInput || !licenseInput || !clinicSelect || !contactInput || !emailInput) {
                        console.error('One or more form fields not found!');
                        console.log('Name:', nameInput);
                        console.log('Specialization:', specializationInput);
                        console.log('License:', licenseInput);
                        console.log('Clinic:', clinicSelect);
                        console.log('Contact:', contactInput);
                        console.log('Email:', emailInput);
                        alert('Error: Form fields not found. Please refresh the page.');
                        return;
                    }

                    // Populate form fields
                    nameInput.value = data.name || '';
                    specializationInput.value = data.specialization || '';
                    licenseInput.value = data.license_no || '';
                    clinicSelect.value = data.clinicID || '';
                    contactInput.value = data.contactNum || '';
                    emailInput.value = data.email || '';

                    // Update form action
                    document.getElementById('editVetForm').action = `/vets/${vetId}`;
                })
                .catch(error => {
                    console.error('Error details:', error);
                    alert('Failed to load veterinarian data: ' + error.message);
                    closeEditVetModal();
                });
        }

        // Close Edit Vet Modal
        function closeEditVetModal() {
            const modal = document.getElementById('editVetModal');
            if (modal) {
                modal.classList.add('hidden');
            }
            const form = document.getElementById('editVetForm');
            if (form) {
                form.reset();
            }
        }

        // Close modal when clicking outside
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('editVetModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeEditVetModal();
                    }
                });
            }
        });

        // Delete Vet Function
        function deleteVet(vetId) {
            if (confirm('Are you sure you want to delete this veterinarian? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/vets/${vetId}`;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';

                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        }
</script>
