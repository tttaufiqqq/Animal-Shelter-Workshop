<script>
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
</script>
