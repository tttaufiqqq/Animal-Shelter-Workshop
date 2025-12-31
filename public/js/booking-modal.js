/**
 * Booking Modal Multi-Step Flow
 * Handles navigation, animal selection, and fee calculation
 */

// Current step tracking
const currentSteps = {};

/**
 * Show loading overlay
 */
function showLoading(title = 'Processing...', message = 'Please wait while we process your request') {
    const overlay = document.getElementById('loadingOverlay');
    const titleEl = document.getElementById('loadingTitle');
    const messageEl = document.getElementById('loadingMessage');

    if (overlay) {
        if (titleEl) titleEl.textContent = title;
        if (messageEl) messageEl.textContent = message;

        overlay.classList.remove('hidden');
        overlay.classList.add('flex');

        // Add show class after a brief delay for animation
        setTimeout(() => {
            overlay.classList.add('show');
        }, 10);
    }
}

/**
 * Hide loading overlay
 */
function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');

    if (overlay) {
        overlay.classList.remove('show');

        // Remove hidden class after animation
        setTimeout(() => {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }, 300);
    }
}

/**
 * Open booking modal and initialize to step 1
 */
function openBookingModal(bookingId) {
    const modal = document.getElementById(`bookingModal-${bookingId}`);
    if (!modal) return;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';

    // Initialize to step 1
    currentSteps[bookingId] = 1;
    updateStepDisplay(bookingId);

    // Initialize selection summary
    updateSelectionSummary(bookingId);
}

/**
 * Close booking modal
 */
function closeBookingModal(bookingId) {
    const modal = document.getElementById(`bookingModal-${bookingId}`);
    if (!modal) return;

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = 'auto';

    // Reset to step 1
    currentSteps[bookingId] = 1;
    updateStepDisplay(bookingId);
}

/**
 * Go to next step
 */
function nextStep(bookingId) {
    const currentStep = currentSteps[bookingId] || 1;

    // Validation before moving to next step
    if (currentStep === 2) {
        // Check if at least one animal is selected
        const checkboxes = document.querySelectorAll(`.animal-select-${bookingId}:checked`);
        if (checkboxes.length === 0) {
            showAlert('error', 'Please select at least one animal to adopt.');
            return;
        }

        // Show loading while preparing step 3
        showLoading('Calculating Fees...', 'Preparing your adoption summary');

        // Populate step 3 with selected animals
        setTimeout(() => {
            populateStep3(bookingId);

            // Move to next step (max 3)
            if (currentStep < 3) {
                currentSteps[bookingId] = currentStep + 1;
                updateStepDisplay(bookingId);
            }

            hideLoading();
        }, 500);
    } else {
        // Move to next step (max 3)
        if (currentStep < 3) {
            currentSteps[bookingId] = currentStep + 1;
            updateStepDisplay(bookingId);
        }
    }
}

/**
 * Go to previous step
 */
function previousStep(bookingId) {
    const currentStep = currentSteps[bookingId] || 1;

    // Move to previous step (min 1)
    if (currentStep > 1) {
        currentSteps[bookingId] = currentStep - 1;
        updateStepDisplay(bookingId);
    }
}

/**
 * Update step display (show/hide content, update indicators)
 */
function updateStepDisplay(bookingId) {
    const currentStep = currentSteps[bookingId] || 1;
    const modal = document.getElementById(`bookingModal-${bookingId}`);

    // Check if this booking has all steps completed (adoption complete)
    const allStepsCompleted = modal && modal.dataset.allStepsCompleted === 'true';

    // Update step content visibility
    for (let i = 1; i <= 3; i++) {
        const stepContent = document.getElementById(`step${i}-${bookingId}`);
        if (stepContent) {
            if (i === currentStep) {
                stepContent.classList.remove('hidden');
            } else {
                stepContent.classList.add('hidden');
            }
        }
    }

    // Skip step indicator manipulation if all steps are completed (adoption complete)
    if (allStepsCompleted) {
        // Just handle content visibility and return - don't touch the step circles
        return;
    }

    // Update step indicators
    const indicators = document.querySelectorAll(`#bookingModal-${bookingId} .step-indicator`);
    indicators.forEach((indicator) => {
        const step = parseInt(indicator.dataset.step);
        const circleEl = indicator.querySelector('.step-circle');
        const numberIcon = indicator.querySelector('.step-number-icon');
        const checkmarkIcon = indicator.querySelector('.step-checkmark-icon');
        const titleEl = indicator.querySelector('h4');
        const subtitleEl = indicator.querySelector('p');

        if (step < currentStep) {
            // Completed step - green checkmark
            indicator.classList.remove('opacity-60');
            indicator.classList.add('opacity-100');
            circleEl.classList.remove('border-purple-300', 'border-purple-600', 'bg-white', 'bg-purple-600', 'shadow-md');
            circleEl.classList.add('border-green-500', 'bg-green-500', 'shadow-md');
            if (numberIcon) numberIcon.classList.add('hidden');
            if (checkmarkIcon) checkmarkIcon.classList.remove('hidden');
            if (titleEl) {
                titleEl.classList.remove('text-purple-900', 'text-gray-700');
                titleEl.classList.add('text-green-700', 'font-bold');
            }
            if (subtitleEl) {
                subtitleEl.classList.remove('text-purple-600', 'text-gray-500');
                subtitleEl.classList.add('text-green-600');
            }
        } else if (step === currentStep) {
            // Active step - purple filled
            indicator.classList.remove('opacity-60');
            indicator.classList.add('opacity-100');
            circleEl.classList.remove('border-purple-300', 'border-green-500', 'bg-white', 'bg-green-500');
            circleEl.classList.add('border-purple-600', 'bg-purple-600', 'shadow-md');
            if (numberIcon) {
                numberIcon.classList.remove('hidden', 'text-purple-400');
                numberIcon.classList.add('text-white', 'font-bold');
            }
            if (checkmarkIcon) checkmarkIcon.classList.add('hidden');
            if (titleEl) {
                titleEl.classList.remove('text-gray-700', 'text-green-700', 'font-semibold');
                titleEl.classList.add('text-purple-900', 'font-bold');
            }
            if (subtitleEl) {
                subtitleEl.classList.remove('text-gray-500', 'text-green-600');
                subtitleEl.classList.add('text-purple-600');
            }
        } else {
            // Upcoming step - purple outline
            indicator.classList.remove('opacity-100');
            indicator.classList.add('opacity-60');
            circleEl.classList.remove('border-purple-600', 'border-green-500', 'bg-purple-600', 'bg-green-500', 'shadow-md');
            circleEl.classList.add('border-purple-300', 'bg-white');
            if (numberIcon) {
                numberIcon.classList.remove('hidden', 'text-white', 'font-bold');
                numberIcon.classList.add('text-purple-400', 'font-bold');
            }
            if (checkmarkIcon) checkmarkIcon.classList.add('hidden');
            if (titleEl) {
                titleEl.classList.remove('text-purple-900', 'text-green-700', 'font-bold');
                titleEl.classList.add('text-gray-700', 'font-semibold');
            }
            if (subtitleEl) {
                subtitleEl.classList.remove('text-purple-600', 'text-green-600');
                subtitleEl.classList.add('text-gray-500');
            }
        }
    });

    // Update progress bar (only if not a completed adoption)
    if (!allStepsCompleted) {
        const progressBar = document.getElementById(`progress-bar-${bookingId}`);
        const progressText = document.getElementById(`progress-text-${bookingId}`);
        const progress = Math.round((currentStep / 3) * 100);

        if (progressBar) {
            progressBar.style.width = `${progress}%`;
        }
        if (progressText) {
            progressText.textContent = `${progress}%`;
        }

        // Update title and subtitle
        const titles = {
            1: { title: 'Booking Details', subtitle: 'Review your booking information' },
            2: { title: 'Select Animals', subtitle: 'Choose animals you want to adopt' },
            3: { title: 'Confirm & Pay', subtitle: 'Review and complete your adoption' }
        };

        const titleEl = document.getElementById(`step-title-${bookingId}`);
        const subtitleEl = document.getElementById(`step-subtitle-${bookingId}`);

        if (titleEl && titles[currentStep]) {
            titleEl.textContent = titles[currentStep].title;
        }
        if (subtitleEl && titles[currentStep]) {
            subtitleEl.textContent = titles[currentStep].subtitle;
        }
    }

    // Update navigation buttons (only if not a completed adoption)
    if (!allStepsCompleted) {
        const prevBtn = document.getElementById(`prev-btn-${bookingId}`);
        const nextBtn = document.getElementById(`next-btn-${bookingId}`);

        if (prevBtn) {
            if (currentStep === 1) {
                prevBtn.classList.add('hidden');
            } else {
                prevBtn.classList.remove('hidden');
            }
        }

        if (nextBtn) {
            if (currentStep === 3) {
                nextBtn.classList.add('hidden');
            } else {
                nextBtn.classList.remove('hidden');
            }
        }
    }
}

/**
 * Update selection summary in step 2
 */
function updateSelectionSummary(bookingId) {
    const checkboxes = document.querySelectorAll(`.animal-select-${bookingId}:checked`);
    let total = 0;
    let count = 0;

    checkboxes.forEach((cb) => {
        total += parseFloat(cb.dataset.fee) || 0;
        count++;
    });

    const countEl = document.getElementById(`selectedCount-${bookingId}`);
    const feeEl = document.getElementById(`estimatedFee-${bookingId}`);

    if (countEl) countEl.textContent = count;
    if (feeEl) feeEl.textContent = 'RM ' + total.toFixed(2);
}

/**
 * Populate step 3 with selected animals
 */
function populateStep3(bookingId) {
    const checkboxes = document.querySelectorAll(`.animal-select-${bookingId}:checked`);

    // Get containers
    const listContainer = document.getElementById(`selectedAnimalsList-${bookingId}`);
    const feeBreakdownContainer = document.getElementById(`feeBreakdownList-${bookingId}`);
    const hiddenInputsContainer = document.getElementById(`hiddenAnimalInputs-${bookingId}`);
    const grandTotalEl = document.getElementById(`grandTotal-${bookingId}`);
    const noAnimalsMsg = document.getElementById(`noAnimalsSelected-${bookingId}`);
    const submitBtn = document.getElementById(`submitBtn-${bookingId}`);
    const termsCheckbox = document.getElementById(`agree_terms_${bookingId}`);

    // Clear previous content
    if (listContainer) listContainer.innerHTML = '';
    if (feeBreakdownContainer) feeBreakdownContainer.innerHTML = '';
    if (hiddenInputsContainer) hiddenInputsContainer.innerHTML = '';

    // Reset terms checkbox to force re-confirmation
    if (termsCheckbox) termsCheckbox.checked = false;

    if (checkboxes.length === 0) {
        if (noAnimalsMsg) noAnimalsMsg.classList.remove('hidden');
        if (submitBtn) submitBtn.disabled = true;
        if (grandTotalEl) grandTotalEl.textContent = 'RM 0.00';
        return;
    }

    if (noAnimalsMsg) noAnimalsMsg.classList.add('hidden');

    // Keep button disabled - will be enabled only when terms checkbox is checked
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        submitBtn.classList.remove('hover:bg-purple-700');
    }

    let grandTotal = 0;

    checkboxes.forEach((cb) => {
        const animalId = cb.dataset.animalId;
        const animalName = cb.dataset.animalName;
        const animalSpecies = cb.dataset.animalSpecies;
        const baseFee = parseFloat(cb.dataset.baseFee) || 0;
        const medicalFee = parseFloat(cb.dataset.medicalFee) || 0;
        const medicalCount = parseInt(cb.dataset.medicalCount) || 0;
        const vaccinationFee = parseFloat(cb.dataset.vaccinationFee) || 0;
        const vaccinationCount = parseInt(cb.dataset.vaccinationCount) || 0;
        const totalFee = parseFloat(cb.dataset.fee) || 0;

        grandTotal += totalFee;

        // Create animal card for selected animals list
        if (listContainer) {
            const animalCard = document.createElement('div');
            animalCard.className = 'flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-gray-200';
            animalCard.innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">${animalName}</p>
                        <p class="text-sm text-gray-600">${animalSpecies}</p>
                    </div>
                </div>
                <span class="font-semibold text-gray-900">RM ${totalFee.toFixed(2)}</span>
            `;
            listContainer.appendChild(animalCard);
        }

        // Create fee breakdown item
        if (feeBreakdownContainer) {
            const breakdownItem = document.createElement('div');
            breakdownItem.className = 'pb-3 border-b border-gray-100 last:border-0 last:pb-0';
            breakdownItem.innerHTML = `
                <div class="flex justify-between items-center mb-2">
                    <span class="font-semibold text-gray-900">${animalName}</span>
                    <span class="font-semibold text-gray-900">RM ${totalFee.toFixed(2)}</span>
                </div>
                <div class="space-y-1 text-xs text-gray-600">
                    <div class="flex justify-between">
                        <span>Base</span>
                        <span>RM ${baseFee.toFixed(2)}</span>
                    </div>
                    ${medicalFee > 0 ? `
                    <div class="flex justify-between">
                        <span>Medical (${medicalCount})</span>
                        <span>RM ${medicalFee.toFixed(2)}</span>
                    </div>` : ''}
                    ${vaccinationFee > 0 ? `
                    <div class="flex justify-between">
                        <span>Vaccines (${vaccinationCount})</span>
                        <span>RM ${vaccinationFee.toFixed(2)}</span>
                    </div>` : ''}
                </div>
            `;
            feeBreakdownContainer.appendChild(breakdownItem);
        }

        // Create hidden input for animal ID
        if (hiddenInputsContainer) {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'animal_ids[]';
            hiddenInput.value = animalId;
            hiddenInputsContainer.appendChild(hiddenInput);
        }
    });

    // Add hidden input for total fee
    if (hiddenInputsContainer) {
        const totalFeeInput = document.createElement('input');
        totalFeeInput.type = 'hidden';
        totalFeeInput.name = 'total_fee';
        totalFeeInput.value = grandTotal.toFixed(2);
        hiddenInputsContainer.appendChild(totalFeeInput);
    }

    // Update grand total
    if (grandTotalEl) {
        grandTotalEl.textContent = 'RM ' + grandTotal.toFixed(2);
    }
}

/**
 * Open cancel confirmation modal
 */
function openCancelModal(bookingId) {
    const modal = document.getElementById(`cancelConfirmModal-${bookingId}`);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

/**
 * Close cancel confirmation modal
 */
function closeCancelModal(bookingId) {
    const modal = document.getElementById(`cancelConfirmModal-${bookingId}`);
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');

        // Check if any other modals are still open
        const remainingModals = document.querySelectorAll('.modal-backdrop:not(.hidden)');
        // Keep body scroll locked if booking modal is still open
        if (remainingModals.length === 0) {
            document.body.style.overflow = 'auto';
        }
    }
}

/**
 * Show alert message
 */
function showAlert(type, message) {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `fixed top-4 right-4 z-[70] max-w-md p-4 rounded-lg shadow-xl animate-slideIn ${
        type === 'error' ? 'bg-red-100 border-l-4 border-red-500' : 'bg-green-100 border-l-4 border-green-500'
    }`;

    alert.innerHTML = `
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 flex-shrink-0 ${type === 'error' ? 'text-red-600' : 'text-green-600'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${type === 'error'
                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>'
                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>'
                }
            </svg>
            <div class="flex-1">
                <p class="font-semibold ${type === 'error' ? 'text-red-700' : 'text-green-700'}">${message}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="${type === 'error' ? 'text-red-400 hover:text-red-600' : 'text-green-400 hover:text-green-600'}">
                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    `;

    document.body.appendChild(alert);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Initialize event listeners when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Add change listeners to all animal selection checkboxes
    document.querySelectorAll('[class*="animal-select-"]').forEach((cb) => {
        cb.addEventListener('change', function() {
            // Extract booking ID from class name
            const classes = this.className.split(' ');
            const selectClass = classes.find(c => c.startsWith('animal-select-'));
            if (selectClass) {
                const bookingId = selectClass.replace('animal-select-', '');
                updateSelectionSummary(bookingId);
            }
        });
    });

    // Handle cancel booking form submission
    document.querySelectorAll('[id^="cancelForm-"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const bookingId = this.id.replace('cancelForm-', '');
            const submitBtn = document.getElementById(`confirmCancelBtn-${bookingId}`);
            const noBtn = document.getElementById(`cancelModalNoBtn-${bookingId}`);

            // Show global loading overlay
            showLoading('Cancelling Booking...', 'Please wait while we cancel your booking');

            // Disable buttons
            if (submitBtn) submitBtn.disabled = true;
            if (noBtn) noBtn.disabled = true;

            // Show loading spinner on button
            if (submitBtn) {
                submitBtn.innerHTML = `
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Cancelling...</span>
                `;
            }

            // Allow form to submit
            return true;
        });
    });

    // Handle adoption confirmation form submission
    document.querySelectorAll('[id^="confirmForm-"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const bookingId = this.id.replace('confirmForm-', '');
            const submitBtn = document.getElementById(`submitBtn-${bookingId}`);

            // Show global loading overlay
            showLoading('Preparing Payment...', 'Redirecting you to our secure payment gateway');

            // Disable button
            if (submitBtn) {
                submitBtn.disabled = true;

                // Show loading spinner on button
                submitBtn.innerHTML = `
                    <svg class="animate-spin h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Processing...</span>
                `;
            }

            // Allow form to submit
            return true;
        });
    });

    // Close modal when clicking outside (only top-most modal)
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-backdrop')) {
            // Get all visible modals
            const visibleModals = Array.from(document.querySelectorAll('.modal-backdrop:not(.hidden)'));

            if (visibleModals.length > 0) {
                // Find the modal with highest z-index (top-most)
                const topModal = visibleModals.reduce((top, current) => {
                    const topZ = parseInt(window.getComputedStyle(top).zIndex) || 0;
                    const currentZ = parseInt(window.getComputedStyle(current).zIndex) || 0;
                    return currentZ > topZ ? current : top;
                });

                // Only close if clicked modal is the top-most one
                if (e.target === topModal) {
                    topModal.classList.add('hidden');
                    topModal.classList.remove('flex');

                    // Only restore scroll if no modals are open
                    const remainingModals = document.querySelectorAll('.modal-backdrop:not(.hidden)');
                    if (remainingModals.length === 0) {
                        document.body.style.overflow = 'auto';
                    }
                }
            }
        }
    });

    // Close modal with Escape key (only top-most modal)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const visibleModals = Array.from(document.querySelectorAll('.modal-backdrop:not(.hidden)'));

            if (visibleModals.length > 0) {
                // Find the modal with highest z-index (top-most)
                const topModal = visibleModals.reduce((top, current) => {
                    const topZ = parseInt(window.getComputedStyle(top).zIndex) || 0;
                    const currentZ = parseInt(window.getComputedStyle(current).zIndex) || 0;
                    return currentZ > topZ ? current : top;
                });

                // Close only the top-most modal
                topModal.classList.add('hidden');
                topModal.classList.remove('flex');

                // Only restore scroll if no modals are open
                const remainingModals = document.querySelectorAll('.modal-backdrop:not(.hidden)');
                if (remainingModals.length === 0) {
                    document.body.style.overflow = 'auto';
                }
            }
        }
    });
});

/**
 * Toggle payment button based on terms checkbox
 */
function togglePaymentButton(bookingId) {
    const checkbox = document.getElementById(`agree_terms_${bookingId}`);
    const submitBtn = document.getElementById(`submitBtn-${bookingId}`);

    if (checkbox && submitBtn) {
        if (checkbox.checked) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            submitBtn.classList.add('hover:bg-purple-700');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            submitBtn.classList.remove('hover:bg-purple-700');
        }
    }
}

// Animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .animate-slideIn {
        animation: slideIn 0.3s ease-out;
    }
`;
document.head.appendChild(style);
