    <script>
        let clinicMap;
        let clinicMarker;

        // ============================================
        // Toast Notification System
        // ============================================
        function showToast(message, type = 'info', duration = 4000) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');

            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500'
            };

            const icons = {
                success: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
                error: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
                warning: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
                info: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            };

            toast.className = `${colors[type]} text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 transform translate-x-full transition-transform duration-300 max-w-sm`;
            toast.innerHTML = `
                <span class="flex-shrink-0">${icons[type]}</span>
                <span class="flex-1 text-sm font-medium">${message}</span>
                <button onclick="this.parentElement.remove()" class="flex-shrink-0 hover:opacity-75">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            `;

            container.appendChild(toast);

            // Animate in
            setTimeout(() => toast.classList.remove('translate-x-full'), 10);

            // Auto remove
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        // ============================================
        // Confirmation Modal System
        // ============================================
        let pendingDeleteAction = null;

        function openConfirmModal(type, id, name) {
            const modal = document.getElementById('confirmDeleteModal');
            const content = document.getElementById('confirmDeleteModalContent');
            const title = document.getElementById('confirmDeleteTitle');
            const message = document.getElementById('confirmDeleteMessage');

            // Set content based on type
            if (type === 'clinic') {
                title.textContent = 'Delete Clinic?';
                message.innerHTML = `Are you sure you want to delete <strong class="text-gray-900">${name}</strong>? This action cannot be undone and may affect associated veterinarians.`;
            } else if (type === 'vet') {
                title.textContent = 'Delete Veterinarian?';
                message.innerHTML = `Are you sure you want to delete <strong class="text-gray-900">${name}</strong>? This action cannot be undone.`;
            }

            // Store the pending action
            pendingDeleteAction = { type, id };

            // Reset button state
            resetConfirmButton();

            // Show modal with animation
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeConfirmModal() {
            const modal = document.getElementById('confirmDeleteModal');
            const content = document.getElementById('confirmDeleteModalContent');

            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
                pendingDeleteAction = null;
            }, 200);
        }

        function resetConfirmButton() {
            const btn = document.getElementById('confirmDeleteBtn');
            const btnText = document.getElementById('confirmDeleteBtnText');
            const spinner = document.getElementById('confirmDeleteSpinner');
            const cancelBtn = document.getElementById('confirmCancelBtn');

            btn.disabled = false;
            btnText.textContent = 'Delete';
            spinner.classList.add('hidden');
            cancelBtn.disabled = false;
        }

        function executeDelete() {
            if (!pendingDeleteAction) return;

            const btn = document.getElementById('confirmDeleteBtn');
            const btnText = document.getElementById('confirmDeleteBtnText');
            const spinner = document.getElementById('confirmDeleteSpinner');
            const cancelBtn = document.getElementById('confirmCancelBtn');

            // Show loading state
            btn.disabled = true;
            cancelBtn.disabled = true;
            btnText.textContent = 'Deleting...';
            spinner.classList.remove('hidden');

            // Create and submit the form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = pendingDeleteAction.type === 'clinic'
                ? `/clinics/${pendingDeleteAction.id}`
                : `/vets/${pendingDeleteAction.id}`;

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

        // Close modal on backdrop click
        document.getElementById('confirmDeleteModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeConfirmModal();
            }
        });

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('confirmDeleteModal');
                if (modal && !modal.classList.contains('hidden')) {
                    closeConfirmModal();
                }
            }
        });

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

        // ============================================
        // Edit Clinic Map Functions
        // ============================================
        let editClinicMap;
        let editClinicMarker;

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

            if (lat && lng) {
                setEditClinicLocation(lat, lng);
            }

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

            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    if (data.display_name) {
                        document.getElementById('edit_clinicAddress').value = data.display_name;
                    }
                });
        }

        // ============================================
        // Edit Clinic Function
        // ============================================
        function editClinic(clinicId) {
            // Show loading on the edit button
            const editBtn = event.currentTarget;
            const originalContent = editBtn.innerHTML;
            editBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            editBtn.disabled = true;

            fetch(`/clinics/${clinicId}/edit`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_clinic_name').value = data.name || '';
                    document.getElementById('edit_clinicAddress').value = data.address || '';
                    document.getElementById('edit_phone').value = data.contactNum || '';
                    document.getElementById('edit_clinicLatitude').value = data.latitude || '';
                    document.getElementById('edit_clinicLongitude').value = data.longitude || '';
                    document.getElementById('editClinicForm').action = `/clinics/${clinicId}`;

                    document.getElementById('editClinicModal').classList.remove('hidden');

                    setTimeout(() => {
                        initEditClinicMap(parseFloat(data.latitude), parseFloat(data.longitude));
                    }, 100);
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Failed to load clinic data. Please try again.', 'error');
                })
                .finally(() => {
                    editBtn.innerHTML = originalContent;
                    editBtn.disabled = false;
                });
        }

        function closeEditClinicModal() {
            document.getElementById('editClinicModal').classList.add('hidden');
            document.getElementById('editClinicForm').reset();

            if (editClinicMarker && editClinicMap) {
                editClinicMap.removeLayer(editClinicMarker);
                editClinicMarker = null;
            }
        }

        document.getElementById('editClinicModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditClinicModal();
            }
        });

        // ============================================
        // Delete Functions (using confirmation modal)
        // ============================================
        function deleteClinic(clinicId) {
            // Find clinic name from the table
            const row = event.currentTarget.closest('tr');
            const clinicName = row?.querySelector('.font-semibold')?.textContent || 'this clinic';
            openConfirmModal('clinic', clinicId, clinicName);
        }

        function deleteVet(vetId) {
            // Find vet name from the table
            const row = event.currentTarget.closest('tr');
            const vetName = row?.querySelector('.font-semibold')?.textContent || 'this veterinarian';
            openConfirmModal('vet', vetId, vetName);
        }

        // ============================================
        // Edit Vet Function
        // ============================================
        function editVet(vetId) {
            const editBtn = event.currentTarget;
            const originalContent = editBtn.innerHTML;
            editBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            editBtn.disabled = true;

            const modal = document.getElementById('editVetModal');
            if (!modal) {
                showToast('Error: Modal not found. Please refresh the page.', 'error');
                editBtn.innerHTML = originalContent;
                editBtn.disabled = false;
                return;
            }

            fetch(`/vets/${vetId}/edit`, {
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
                    const nameInput = document.getElementById('edit_vet_name');
                    const specializationInput = document.getElementById('edit_vet_specialization');
                    const licenseInput = document.getElementById('edit_vet_license_no');
                    const clinicSelect = document.getElementById('edit_vet_clinicID');
                    const contactInput = document.getElementById('edit_vet_contactNum');
                    const emailInput = document.getElementById('edit_vet_email');

                    if (!nameInput || !specializationInput || !licenseInput || !clinicSelect || !contactInput || !emailInput) {
                        showToast('Error: Form fields not found. Please refresh the page.', 'error');
                        return;
                    }

                    nameInput.value = data.name || '';
                    specializationInput.value = data.specialization || '';
                    licenseInput.value = data.license_no || '';
                    clinicSelect.value = data.clinicID || '';
                    contactInput.value = data.contactNum || '';
                    emailInput.value = data.email || '';

                    document.getElementById('editVetForm').action = `/vets/${vetId}`;
                    modal.classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error details:', error);
                    showToast('Failed to load veterinarian data: ' + error.message, 'error');
                })
                .finally(() => {
                    editBtn.innerHTML = originalContent;
                    editBtn.disabled = false;
                });
        }

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
