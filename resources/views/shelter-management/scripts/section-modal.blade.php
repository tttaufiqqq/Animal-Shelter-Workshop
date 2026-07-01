<script>
    // ==================== SECTION MODAL FUNCTIONS ====================
    function openSectionModal() {
        document.getElementById('sectionModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        document.getElementById('sectionForm').reset();
        document.getElementById('sectionModalTitle').textContent = 'Add New Section';
        document.getElementById('sectionSubmitButtonText').textContent = 'Add Section';
        document.getElementById('sectionFormMethod').value = 'POST';
        document.getElementById('sectionId').value = '';
        document.getElementById('sectionForm').action = '{{ route($routePrefix . ".store-section") }}';
    }

    function closeSectionModal() {
        document.getElementById('sectionModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function editSection(sectionId) {
        showLoading('Loading section details...');
        document.getElementById('sectionModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        document.getElementById('sectionModalTitle').textContent = 'Edit Section';
        document.getElementById('sectionSubmitButtonText').textContent = 'Update Section';
        document.getElementById('sectionFormMethod').value = 'PUT';
        document.getElementById('sectionId').value = sectionId;
        document.getElementById('sectionForm').action = `${routeBaseUrl}/sections/${sectionId}`;

        fetch(`${routeBaseUrl}/sections/${sectionId}/edit`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('sectionName').value = data.name;
                document.getElementById('sectionDescription').value = data.description;
                hideLoading();
            })
            .catch(error => {
                console.error('Error fetching section data:', error);
                hideLoading();
                showToast('Failed to load section data. Please try again.', 'error');
                closeSectionModal();
            });
    }

    function deleteSection(sectionId) {
        showConfirmation(
            'Delete Section',
            'Are you sure you want to delete this section? This action cannot be undone.',
            function() {
                showLoading('Deleting section...');
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `${routeBaseUrl}/sections/${sectionId}`;

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken.content;
                    form.appendChild(csrfInput);
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
</script>
