/**
 * Delete Handler Module
 * Handles delete confirmation modal and form logic
 */

class DeleteHandler {
    constructor(mapHandler) {
        this.mapHandler = mapHandler;
        this.init();
    }

    init() {
        this.attachEventListeners();
    }

    openModal() {
        const modal = document.getElementById('deleteConfirmModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (this.mapHandler) {
            this.mapHandler.disable();
        }
    }

    closeModal() {
        const modal = document.getElementById('deleteConfirmModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        if (this.mapHandler) {
            this.mapHandler.enable();
        }
    }

    attachEventListeners() {
        // Delete form submission with loading state
        const form = document.getElementById('deleteReportForm');
        if (form) {
            form.addEventListener('submit', () => {
                const deleteBtn = document.getElementById('confirmDeleteBtn');

                // Disable button
                deleteBtn.disabled = true;

                // Replace content with spinner
                deleteBtn.innerHTML = `
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Deleting...</span>
                `;

                return true;
            });
        }

        // Close modal when clicking outside
        const modal = document.getElementById('deleteConfirmModal');
        modal?.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !document.getElementById('deleteConfirmModal')?.classList.contains('hidden')) {
                this.closeModal();
            }
        });
    }
}

// Global functions for onclick attributes
function openDeleteModal() {
    if (window.deleteHandler) {
        window.deleteHandler.openModal();
    }
}

function closeDeleteModal() {
    if (window.deleteHandler) {
        window.deleteHandler.closeModal();
    }
}

// Export for use in global scope
window.DeleteHandler = DeleteHandler;
