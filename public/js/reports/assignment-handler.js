/**
 * Assignment Handler Module
 * Handles caretaker assignment modals and form logic
 */

class AssignmentHandler {
    constructor(mapHandler) {
        this.mapHandler = mapHandler;
        this.init();
    }

    init() {
        this.attachEventListeners();
    }

    showNoCaretakerSelectedModal() {
        const modal = document.getElementById('noCaretakerSelectedModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (this.mapHandler) {
            this.mapHandler.disable();
        }
    }

    closeNoCaretakerSelectedModal() {
        const modal = document.getElementById('noCaretakerSelectedModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        if (this.mapHandler) {
            this.mapHandler.enable();
        }
    }

    showSameCaretakerError(caretakerName) {
        document.getElementById('currentCaretakerNameError').textContent = caretakerName;
        const modal = document.getElementById('sameCaretakerErrorModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (this.mapHandler) {
            this.mapHandler.disable();
        }
    }

    closeSameCaretakerErrorModal() {
        const modal = document.getElementById('sameCaretakerErrorModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        if (this.mapHandler) {
            this.mapHandler.enable();
        }
    }

    showAssignmentConfirmation() {
        const caretakerSelect = document.getElementById('caretakerSelect');
        const selectedOption = caretakerSelect.options[caretakerSelect.selectedIndex];
        const selectedCaretakerId = caretakerSelect.value;
        const currentCaretakerId = caretakerSelect.dataset.currentCaretaker;
        const selectedCaretakerName = selectedOption.dataset.name || selectedOption.text;

        // Validate that a caretaker is selected
        if (!selectedCaretakerId) {
            this.showNoCaretakerSelectedModal();
            return;
        }

        // Check if trying to reassign to the same caretaker
        if (currentCaretakerId && selectedCaretakerId === currentCaretakerId) {
            this.showSameCaretakerError(selectedCaretakerName);
            return;
        }

        // Update modal content
        const confirmMessage = document.getElementById('assignmentConfirmMessage');
        const newCaretakerName = document.getElementById('newCaretakerName');

        if (currentCaretakerId) {
            confirmMessage.textContent = 'Are you sure you want to reassign this report to a new caretaker?';
        } else {
            confirmMessage.textContent = 'Are you sure you want to assign this report to the selected caretaker?';
        }

        newCaretakerName.textContent = selectedCaretakerName;

        // Show modal
        const modal = document.getElementById('assignmentConfirmModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (this.mapHandler) {
            this.mapHandler.disable();
        }
    }

    closeAssignmentModal() {
        const modal = document.getElementById('assignmentConfirmModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        if (this.mapHandler) {
            this.mapHandler.enable();
        }
    }

    confirmAssignment() {
        const confirmBtn = document.getElementById('confirmAssignmentBtn');

        // Disable button
        confirmBtn.disabled = true;

        // Add spinner
        confirmBtn.innerHTML = `
            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Assigning...</span>
        `;

        // Submit the form
        document.getElementById('assignCaretakerForm').submit();
    }

    attachEventListeners() {
        // Close modals when clicking outside
        const modals = [
            { id: 'sameCaretakerErrorModal', closeFunc: () => this.closeSameCaretakerErrorModal() },
            { id: 'assignmentConfirmModal', closeFunc: () => this.closeAssignmentModal() }
        ];

        modals.forEach(({ id, closeFunc }) => {
            const modal = document.getElementById(id);
            modal?.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeFunc();
                }
            });
        });

        // Close modals with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (!document.getElementById('sameCaretakerErrorModal')?.classList.contains('hidden')) {
                    this.closeSameCaretakerErrorModal();
                } else if (!document.getElementById('assignmentConfirmModal')?.classList.contains('hidden')) {
                    this.closeAssignmentModal();
                }
            }
        });
    }
}

// Global functions for onclick attributes
function showAssignmentConfirmation() {
    if (window.assignmentHandler) {
        window.assignmentHandler.showAssignmentConfirmation();
    }
}

function closeAssignmentModal() {
    if (window.assignmentHandler) {
        window.assignmentHandler.closeAssignmentModal();
    }
}

function confirmAssignment() {
    if (window.assignmentHandler) {
        window.assignmentHandler.confirmAssignment();
    }
}

function closeSameCaretakerErrorModal() {
    if (window.assignmentHandler) {
        window.assignmentHandler.closeSameCaretakerErrorModal();
    }
}

function closeNoCaretakerSelectedModal() {
    if (window.assignmentHandler) {
        window.assignmentHandler.closeNoCaretakerSelectedModal();
    }
}

// Export for use in global scope
window.AssignmentHandler = AssignmentHandler;
