<script>
    // ==================== CATEGORY MODAL FUNCTIONS ====================
    function openCategoryModal() {
        document.getElementById('categoryModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryModalTitle').textContent = 'Add New Category';
        document.getElementById('categorySubmitButtonText').textContent = 'Add Category';
        document.getElementById('categoryFormMethod').value = 'POST';
        document.getElementById('categoryId').value = '';
        document.getElementById('categoryForm').action = '{{ route($routePrefix . ".store-category") }}';
    }

    function closeCategoryModal() {
        document.getElementById('categoryModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function editCategory(categoryId) {
        showLoading('Loading category details...');
        document.getElementById('categoryModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        document.getElementById('categoryModalTitle').textContent = 'Edit Category';
        document.getElementById('categorySubmitButtonText').textContent = 'Update Category';
        document.getElementById('categoryFormMethod').value = 'PUT';
        document.getElementById('categoryId').value = categoryId;
        document.getElementById('categoryForm').action = `${routeBaseUrl}/categories/${categoryId}`;

        fetch(`${routeBaseUrl}/categories/${categoryId}/edit`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('categoryMain').value = data.main;
                document.getElementById('categorySub').value = data.sub;
                hideLoading();
            })
            .catch(error => {
                console.error('Error fetching category data:', error);
                hideLoading();
                showToast('Failed to load category data. Please try again.', 'error');
                closeCategoryModal();
            });
    }

    function deleteCategory(categoryId) {
        showConfirmation(
            'Delete Category',
            'Are you sure you want to delete this category? This action cannot be undone.',
            function() {
                showLoading('Deleting category...');
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `${routeBaseUrl}/categories/${categoryId}`;

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

    // ==================== MODAL EVENT LISTENERS ====================
    document.getElementById('sectionModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeSectionModal();
    });

    document.getElementById('categoryModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeCategoryModal();
    });

    document.getElementById('slotModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeSlotModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSectionModal();
            closeCategoryModal();
            closeSlotModal();
        }
    });
</script>
