<script>
    // ==================== SLOT MODAL FUNCTIONS ====================
    function openSlotModal() {
        document.getElementById('slotModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        document.getElementById('slotForm').reset();
        document.getElementById('slotModalTitle').textContent = 'Add New Slot';
        document.getElementById('slotSubmitButtonText').textContent = 'Add Slot';
        document.getElementById('slotFormMethod').value = 'POST';
        document.getElementById('slotId').value = '';
        document.getElementById('slotForm').action = '{{ route($routePrefix . ".store-slot") }}';

        document.getElementById('slotStatusField').classList.add('hidden');
        document.getElementById('slotStatus').removeAttribute('required');
    }

    function editSlot(slotId) {
        showLoading('Loading slot details...');
        document.getElementById('slotModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        document.getElementById('slotModalTitle').textContent = 'Edit Slot';
        document.getElementById('slotSubmitButtonText').textContent = 'Update Slot';
        document.getElementById('slotFormMethod').value = 'PUT';
        document.getElementById('slotId').value = slotId;

        document.getElementById('slotStatusField').classList.remove('hidden');
        document.getElementById('slotStatus').setAttribute('required', 'required');
        document.getElementById('slotForm').action = `${routeBaseUrl}/slots/${slotId}`;

        fetch(`${routeBaseUrl}/slots/${slotId}/edit`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('slotName').value = data.name;
                document.getElementById('slotSection').value = data.sectionID;
                document.getElementById('slotCapacity').value = data.capacity;
                document.getElementById('slotStatus').value = data.status;
                hideLoading();
            })
            .catch(error => {
                console.error('Error fetching slot data:', error);
                hideLoading();
                showToast('Failed to load slot data. Please try again.', 'error');
                closeSlotModal();
            });
    }

    function deleteSlot(slotId) {
        showConfirmation(
            'Delete Slot',
            'Are you sure you want to delete this slot? This action cannot be undone.',
            function() {
                showLoading('Deleting slot...');
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `${routeBaseUrl}/slots/${slotId}`;

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken.getAttribute('content');
                    form.appendChild(csrfInput);
                } else {
                    const existingToken = document.querySelector('input[name="_token"]');
                    if (existingToken) {
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = existingToken.value;
                        form.appendChild(csrfInput);
                    } else {
                        showToast('Security token not found. Please refresh the page and try again.', 'error');
                        return;
                    }
                }

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            }
        );
    }

    function closeSlotModal() {
        document.getElementById('slotModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
</script>
