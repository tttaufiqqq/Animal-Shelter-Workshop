<script>
    // ============================================
    // Form Submission with Loading States
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        // Search button click
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

        // Edit clinic address search
        document.getElementById('editClinicSearchBtn')?.addEventListener('click', function() {
            const address = document.getElementById('editClinicAddressSearch').value;
            const searchBtn = this;

            if (!address) {
                showToast('Please enter an address to search', 'warning');
                return;
            }

            searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Searching...';
            searchBtn.disabled = true;

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const result = data[0];
                        setEditClinicLocation(parseFloat(result.lat), parseFloat(result.lon));
                        showToast('Location found!', 'success');
                    } else {
                        showToast('Address not found. Please try a different search term.', 'warning');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error searching for address. Please try again.', 'error');
                })
                .finally(() => {
                    searchBtn.innerHTML = '<i class="fas fa-search mr-1"></i>Search';
                    searchBtn.disabled = false;
                });
        });

        // Clinic form validation and loading state
        const clinicForm = document.querySelector('#clinicModal form');
        if (clinicForm) {
            clinicForm.addEventListener('submit', function(e) {
                const latitude = document.getElementById('clinicLatitude').value;
                const longitude = document.getElementById('clinicLongitude').value;

                if (!latitude || !longitude) {
                    e.preventDefault();
                    document.getElementById('clinicMapError').classList.remove('hidden');
                    document.getElementById('clinicMap').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    showToast('Please select a location on the map before submitting.', 'warning');
                    return false;
                }

                // Show loading state on submit button
                const submitBtn = clinicForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding Clinic...';
                    submitBtn.disabled = true;
                }
            });
        }

        // Vet form loading state
        const vetForm = document.querySelector('#vetModal form');
        if (vetForm) {
            vetForm.addEventListener('submit', function(e) {
                const submitBtn = vetForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding Veterinarian...';
                    submitBtn.disabled = true;
                }
            });
        }

        // Edit Clinic form loading state
        const editClinicForm = document.getElementById('editClinicForm');
        if (editClinicForm) {
            editClinicForm.addEventListener('submit', function(e) {
                const submitBtn = editClinicForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving Changes...';
                    submitBtn.disabled = true;
                }
            });
        }

        // Edit Vet form loading state
        const editVetForm = document.getElementById('editVetForm');
        if (editVetForm) {
            editVetForm.addEventListener('submit', function(e) {
                const submitBtn = editVetForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving Changes...';
                    submitBtn.disabled = true;
                }
            });
        }
    });
</script>
