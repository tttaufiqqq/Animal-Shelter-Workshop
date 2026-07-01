<script>
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
</script>
