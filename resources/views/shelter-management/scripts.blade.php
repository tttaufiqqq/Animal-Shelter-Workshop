<script>
    // ==================== ROUTE CONFIGURATION ====================
    const routePrefix = '{{ $routePrefix ?? "shelter-management" }}';
    const routeBaseUrl = routePrefix === 'admin.shelter-management' ? '/admin/shelter-management' : '/shelter-management';

    // ==================== LOADING INDICATOR UTILITIES ====================
    function showLoading(message = 'Processing...') {
        const loadingOverlay = document.getElementById('globalLoadingOverlay');
        const loadingMessage = document.getElementById('globalLoadingMessage');
        if (loadingOverlay && loadingMessage) {
            loadingMessage.textContent = message;
            loadingOverlay.classList.remove('hidden');
        }
    }

    function hideLoading() {
        const loadingOverlay = document.getElementById('globalLoadingOverlay');
        if (loadingOverlay) {
            loadingOverlay.classList.add('hidden');
        }
    }

    function setButtonLoading(button, isLoading, originalText = '') {
        if (!button) return;

        if (isLoading) {
            button.disabled = true;
            button.dataset.originalHtml = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            button.classList.add('opacity-75', 'cursor-not-allowed');
        } else {
            button.disabled = false;
            button.innerHTML = button.dataset.originalHtml || originalText;
            button.classList.remove('opacity-75', 'cursor-not-allowed');
        }
    }

    // ==================== CONFIRMATION MODAL UTILITIES ====================
    let confirmationCallback = null;

    function showConfirmation(title, message, onConfirm) {
        const modal = document.getElementById('confirmationModal');
        const titleEl = document.getElementById('confirmationModalTitle');
        const messageEl = document.getElementById('confirmationModalMessage');

        titleEl.textContent = title;
        messageEl.textContent = message;
        confirmationCallback = onConfirm;

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeConfirmationModal() {
        const modal = document.getElementById('confirmationModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        confirmationCallback = null;
    }

    function confirmAction() {
        if (confirmationCallback && typeof confirmationCallback === 'function') {
            confirmationCallback();
        }
        closeConfirmationModal();
    }

    // ==================== TOAST NOTIFICATION UTILITIES ====================
    let toastTimeout = null;
    let toastProgressInterval = null;

    function showToast(message, type = 'info', title = '', duration = 4000) {
        const toast = document.getElementById('toastNotification');
        const toastIcon = document.getElementById('toastIcon');
        const toastTitle = document.getElementById('toastTitle');
        const toastMessage = document.getElementById('toastMessage');
        const toastProgress = document.getElementById('toastProgress');

        // Clear existing timers
        if (toastTimeout) clearTimeout(toastTimeout);
        if (toastProgressInterval) clearInterval(toastProgressInterval);

        // Configure based on type
        const configs = {
            error: {
                borderColor: 'border-red-500',
                iconColor: 'text-red-500',
                progressColor: 'bg-red-500',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
                title: title || 'Error'
            },
            success: {
                borderColor: 'border-green-500',
                iconColor: 'text-green-500',
                progressColor: 'bg-green-500',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
                title: title || 'Success'
            },
            warning: {
                borderColor: 'border-yellow-500',
                iconColor: 'text-yellow-500',
                progressColor: 'bg-yellow-500',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>',
                title: title || 'Warning'
            },
            info: {
                borderColor: 'border-blue-500',
                iconColor: 'text-blue-500',
                progressColor: 'bg-blue-500',
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
                title: title || 'Info'
            }
        };

        const config = configs[type] || configs.info;

        // Update styles
        toast.classList.remove('border-red-500', 'border-green-500', 'border-yellow-500', 'border-blue-500');
        toast.classList.add(config.borderColor);

        toastIcon.classList.remove('text-red-500', 'text-green-500', 'text-yellow-500', 'text-blue-500');
        toastIcon.classList.add(config.iconColor);
        toastIcon.querySelector('svg').innerHTML = config.icon;

        toastProgress.classList.remove('bg-red-500', 'bg-green-500', 'bg-yellow-500', 'bg-blue-500');
        toastProgress.classList.add(config.progressColor);

        // Update content
        toastTitle.textContent = config.title;
        toastMessage.textContent = message;

        // Show toast with animation
        toast.classList.remove('hidden', 'toast-exit');
        toast.classList.add('toast-enter');

        // Progress bar animation
        toastProgress.style.width = '100%';
        let progress = 100;
        const step = 100 / (duration / 100);

        toastProgressInterval = setInterval(() => {
            progress -= step;
            toastProgress.style.width = progress + '%';
            if (progress <= 0) {
                clearInterval(toastProgressInterval);
            }
        }, 100);

        // Auto hide
        toastTimeout = setTimeout(() => {
            closeToast();
        }, duration);
    }

    function closeToast() {
        const toast = document.getElementById('toastNotification');
        toast.classList.remove('toast-enter');
        toast.classList.add('toast-exit');

        setTimeout(() => {
            toast.classList.add('hidden');
            toast.classList.remove('toast-exit');
        }, 300);

        if (toastTimeout) clearTimeout(toastTimeout);
        if (toastProgressInterval) clearInterval(toastProgressInterval);
    }

    // ==================== VIEW SWITCHING FUNCTIONS ====================
    let currentView = 'slots'; // Default view

    function switchView(view) {
        currentView = view;

        // Update tabs
        document.querySelectorAll('.view-tab').forEach(tab => {
            tab.classList.remove('bg-gradient-to-r', 'from-purple-500', 'to-purple-600', 'text-white', 'shadow-md');
            tab.classList.add('text-gray-600', 'hover:bg-gray-100');
        });

        // Highlight active tab
        const activeTab = document.getElementById(view + 'Tab');
        activeTab.classList.remove('text-gray-600', 'hover:bg-gray-100');
        activeTab.classList.add('bg-gradient-to-r', 'from-purple-500', 'to-purple-600', 'text-white', 'shadow-md');

        // Hide all content sections
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.add('hidden');
        });

        // Hide all stats sections
        document.querySelectorAll('.stats-section').forEach(section => {
            section.classList.add('hidden');
        });

        // Hide all filter sections
        document.querySelectorAll('.search-filter-section').forEach(section => {
            section.classList.add('hidden');
        });

        // Hide all action buttons
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.classList.add('hidden');
        });

        // Show active content, stats, filters, and button
        document.getElementById(view + 'Content').classList.remove('hidden');
        document.getElementById(view + 'Stats').classList.remove('hidden');
        document.getElementById(view + 'Filters').classList.remove('hidden');

        // Determine correct button ID (handle 'categories' -> 'Category' special case)
        let buttonId;
        if (view === 'categories') {
            buttonId = 'addCategoryBtn';
        } else {
            buttonId = 'add' + view.charAt(0).toUpperCase() + view.slice(1, -1) + 'Btn';
        }
        document.getElementById(buttonId).classList.remove('hidden');

        // Clear filters when switching views
        clearFilters();
    }

    // ==================== FILTER FUNCTIONS ====================
    function filterSlots() {
        const searchTerm = document.getElementById('searchSlotsInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
        const sectionFilter = document.getElementById('sectionFilter').value;

        const rows = document.querySelectorAll('.slot-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const slotName = row.getAttribute('data-slot-name') || '';
            const slotStatus = row.getAttribute('data-slot-status') || '';
            const slotSection = row.getAttribute('data-slot-section') || '';

            const matchesSearch = slotName.includes(searchTerm);
            const matchesStatus = !statusFilter || slotStatus === statusFilter;
            const matchesSection = !sectionFilter || slotSection === sectionFilter;

            if (matchesSearch && matchesStatus && matchesSection) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        updateResultsCount('slots', visibleCount, rows.length);
    }

    function filterSections() {
        const searchTerm = document.getElementById('searchSectionsInput').value.toLowerCase();
        const rows = document.querySelectorAll('.section-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const sectionName = row.getAttribute('data-section-name') || '';
            const matchesSearch = sectionName.includes(searchTerm);

            if (matchesSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        updateResultsCount('sections', visibleCount, rows.length);
    }

    function filterCategories() {
        const searchTerm = document.getElementById('searchCategoriesInput').value.toLowerCase();
        const rows = document.querySelectorAll('.category-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const categoryMain = row.getAttribute('data-category-main') || '';
            const categorySub = row.getAttribute('data-category-sub') || '';
            const matchesSearch = categoryMain.includes(searchTerm) || categorySub.includes(searchTerm);

            if (matchesSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        updateResultsCount('categories', visibleCount, rows.length);
    }

    function clearFilters() {
        // Clear all search inputs
        const searchInputs = ['searchSlotsInput', 'searchSectionsInput', 'searchCategoriesInput'];
        searchInputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) input.value = '';
        });

        // Clear slot-specific filters
        const statusFilter = document.getElementById('statusFilter');
        const sectionFilter = document.getElementById('sectionFilter');
        if (statusFilter) statusFilter.value = '';
        if (sectionFilter) sectionFilter.value = '';

        // Trigger appropriate filter
        if (currentView === 'slots') filterSlots();
        else if (currentView === 'sections') filterSections();
        else if (currentView === 'categories') filterCategories();
    }

    function updateResultsCount(view, visible, total) {
        const resultsDiv = document.getElementById(view + 'ResultsCount');
        if (!resultsDiv) return;

        const viewLabels = {
            'slots': 'slot',
            'sections': 'section',
            'categories': 'categor'
        };
        const label = viewLabels[view];
        const pluralLabel = label + (label.endsWith('r') ? 'ies' : 's');

        if (visible === total) {
            resultsDiv.innerHTML = `<i class="fas fa-check-circle text-green-600 mr-1"></i>Showing all <strong>${total}</strong> ${total === 1 ? label : pluralLabel}`;
        } else {
            resultsDiv.innerHTML = `<i class="fas fa-filter text-purple-600 mr-1"></i>Showing <strong>${visible}</strong> of <strong>${total}</strong> ${pluralLabel}`;
        }
    }

    // Initialize results counts on page load
    document.addEventListener('DOMContentLoaded', function() {
        const slotsCount = document.querySelectorAll('.slot-row').length;
        const sectionsCount = document.querySelectorAll('.section-row').length;
        const categoriesCount = document.querySelectorAll('.category-row').length;

        updateResultsCount('slots', slotsCount, slotsCount);
        updateResultsCount('sections', sectionsCount, sectionsCount);
        updateResultsCount('categories', categoriesCount, categoriesCount);
    });

    // Section Modal Functions
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

    // ==================== SLOT MODAL FUNCTIONS ====================
    function openSlotModal() {
        document.getElementById('slotModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Reset form for adding new slot
        document.getElementById('slotForm').reset();
        document.getElementById('slotModalTitle').textContent = 'Add New Slot';
        document.getElementById('slotSubmitButtonText').textContent = 'Add Slot';
        document.getElementById('slotFormMethod').value = 'POST';
        document.getElementById('slotId').value = '';

        // Reset form action to store route
        document.getElementById('slotForm').action = '{{ route($routePrefix . ".store-slot") }}';

        // Hide status field for add mode
        document.getElementById('slotStatusField').classList.add('hidden');
        document.getElementById('slotStatus').removeAttribute('required');
    }

    function editSlot(slotId) {
        showLoading('Loading slot details...');
        // Open modal in edit mode
        document.getElementById('slotModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        document.getElementById('slotModalTitle').textContent = 'Edit Slot';
        document.getElementById('slotSubmitButtonText').textContent = 'Update Slot';
        document.getElementById('slotFormMethod').value = 'PUT';
        document.getElementById('slotId').value = slotId;

        // Show status field for edit mode
        document.getElementById('slotStatusField').classList.remove('hidden');
        document.getElementById('slotStatus').setAttribute('required', 'required');

        // Update form action - changed to match new route
        document.getElementById('slotForm').action = `${routeBaseUrl}/slots/${slotId}`;

        // Fetch slot data via AJAX - changed to match new route
        fetch(`${routeBaseUrl}/slots/${slotId}/edit`)
            .then(response => response.json())
            .then(data => {
                // Populate form fields
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
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `${routeBaseUrl}/slots/${slotId}`;

                // Add CSRF token - Get fresh token from meta tag
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken.getAttribute('content'); // Use getAttribute
                    form.appendChild(csrfInput);
                } else {
                    // Fallback: try to get from any existing form
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

                // Add DELETE method
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

    // Category Modal Functions
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

    // Close modals when clicking outside
    document.getElementById('sectionModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeSectionModal();
    });

    document.getElementById('categoryModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeCategoryModal();
    });

    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSectionModal();
            closeCategoryModal();
            closeSlotModal();
        }
    });

    // Close modal when clicking outside
    document.getElementById('slotModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeSlotModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSlotModal();
        }
    });

    function closeSlotModal() {
        document.getElementById('slotModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // ==================== FORM SUBMISSION LOADING STATES ====================

    // Section Form Submission
    document.getElementById('sectionForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('sectionSubmitBtn');
        const submitIcon = document.getElementById('sectionSubmitIcon');
        const submitText = document.getElementById('sectionSubmitButtonText');
        const cancelBtn = document.getElementById('sectionCancelBtn');

        // Show global loading overlay
        const isUpdate = document.getElementById('sectionFormMethod').value === 'PUT';
        showLoading(isUpdate ? 'Updating section...' : 'Creating section...');

        // Disable buttons
        submitBtn.disabled = true;
        cancelBtn.disabled = true;

        // Change icon to spinner
        submitIcon.className = 'fas fa-spinner fa-spin';

        // Update text based on mode (Add or Update)
        submitText.textContent = isUpdate ? 'Updating...' : 'Adding...';

        // Allow form to submit
        return true;
    });

    // Slot Form Submission
    document.getElementById('slotForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('slotSubmitBtn');
        const submitIcon = document.getElementById('slotSubmitIcon');
        const submitText = document.getElementById('slotSubmitButtonText');
        const cancelBtn = document.getElementById('slotCancelBtn');

        // Show global loading overlay
        const isUpdate = document.getElementById('slotFormMethod').value === 'PUT';
        showLoading(isUpdate ? 'Updating slot...' : 'Creating slot...');

        // Disable buttons
        submitBtn.disabled = true;
        cancelBtn.disabled = true;

        // Change icon to spinner
        submitIcon.className = 'fas fa-spinner fa-spin';

        // Update text based on mode (Add or Update)
        submitText.textContent = isUpdate ? 'Updating...' : 'Adding...';

        // Allow form to submit
        return true;
    });

    // Category Form Submission
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('categorySubmitBtn');
        const submitIcon = document.getElementById('categorySubmitIcon');
        const submitText = document.getElementById('categorySubmitButtonText');
        const cancelBtn = document.getElementById('categoryCancelBtn');

        // Show global loading overlay
        const isUpdate = document.getElementById('categoryFormMethod').value === 'PUT';
        showLoading(isUpdate ? 'Updating category...' : 'Creating category...');

        // Disable buttons
        submitBtn.disabled = true;
        cancelBtn.disabled = true;

        // Change icon to spinner
        submitIcon.className = 'fas fa-spinner fa-spin';

        // Update text based on mode (Add or Update)
        submitText.textContent = isUpdate ? 'Updating...' : 'Adding...';

        // Allow form to submit
        return true;
    });

    // Inventory Form Submission
    document.getElementById('inventoryForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('inventorySubmitBtn');

        showLoading('Adding inventory item...');
        setButtonLoading(submitBtn, true);

        // Allow form to submit
        return true;
    });

    // Inventory Update Form Submission (if exists)
    const updateInventoryForm = document.getElementById('updateInventoryForm');
    if (updateInventoryForm) {
        updateInventoryForm.addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('updateInventorySubmitBtn');

            showLoading('Updating inventory item...');
            setButtonLoading(submitBtn, true);

            // Allow form to submit
            return true;
        });
    }
</script>

<style>
    /* Spinner animation for FontAwesome */
    .fa-spin {
        animation: fa-spin 1s infinite linear;
    }

    @keyframes fa-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
