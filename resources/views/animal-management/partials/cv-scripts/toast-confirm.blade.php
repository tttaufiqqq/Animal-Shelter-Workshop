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
</script>
