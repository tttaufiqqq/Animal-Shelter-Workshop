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

    // Check if all steps are already completed (adoption complete)
    const allStepsCompleted = modal.dataset.allStepsCompleted === 'true';

    if (allStepsCompleted) {
        // Trigger celebration animations for all completed steps
        setTimeout(() => {
            const indicators = modal.querySelectorAll('.step-indicator');
            indicators.forEach((indicator, index) => {
                const circle = indicator.querySelector('.step-circle');
                const checkmark = indicator.querySelector('.step-checkmark-icon');

                if (circle && checkmark) {
                    // Stagger animations for each step
                    setTimeout(() => {
                        circle.classList.add('animate-checkmarkPop', 'animate-circleSuccessPulse');
                        checkmark.classList.add('animate-checkmarkPop', 'animate-checkmarkGlow');

                        const checkmarkPath = checkmark.querySelector('.checkmark-path');
                        if (checkmarkPath) {
                            checkmarkPath.classList.add('animate-checkmarkDraw');
                        }

                        // Trigger confetti burst
                        setTimeout(() => {
                            createConfettiBurst(circle);
                        }, 200);

                        // Cleanup after animation
                        setTimeout(() => {
                            circle.classList.remove('animate-checkmarkPop', 'animate-circleSuccessPulse');
                            checkmark.classList.remove('animate-checkmarkPop', 'animate-checkmarkGlow');
                            if (checkmarkPath) {
                                checkmarkPath.classList.remove('animate-checkmarkDraw');
                            }
                        }, 1500);
                    }, index * 200); // Stagger by 200ms each
                }
            });
        }, 100);
    }

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

    // Update step content visibility with fade animation
    for (let i = 1; i <= 3; i++) {
        const stepContent = document.getElementById(`step${i}-${bookingId}`);
        if (stepContent) {
            if (i === currentStep) {
                // Fade in the active step
                stepContent.classList.remove('hidden');
                stepContent.classList.remove('animate-fadeOut');
                stepContent.classList.add('animate-fadeInSlide');

                // Reset scroll position
                setTimeout(() => {
                    stepContent.scrollTop = 0;
                }, 50);
            } else {
                // Fade out inactive steps
                stepContent.classList.add('animate-fadeOut');
                setTimeout(() => {
                    stepContent.classList.add('hidden');
                    stepContent.classList.remove('animate-fadeOut', 'animate-fadeInSlide');
                }, 300);
            }
        }
    }

    // Skip step indicator manipulation if all steps are completed (adoption complete)
    if (allStepsCompleted) {
        // Just handle content visibility and return - don't touch the step circles
        return;
    }

    // Animate connecting lines based on progress
    const progressLine1 = document.getElementById(`progress-line-1-${bookingId}`);
    const progressLine2 = document.getElementById(`progress-line-2-${bookingId}`);

    if (currentStep >= 2 && progressLine1) {
        progressLine1.style.transform = 'scaleY(1)';
        progressLine1.classList.add('animate-lineFillDown');
        setTimeout(() => progressLine1.classList.remove('animate-lineFillDown'), 500);
    } else if (progressLine1) {
        progressLine1.style.transform = 'scaleY(0)';
    }

    if (currentStep >= 3 && progressLine2) {
        // Delay second line animation slightly for cascading effect
        setTimeout(() => {
            progressLine2.style.transform = 'scaleY(1)';
            progressLine2.classList.add('animate-lineFillDown');
            setTimeout(() => progressLine2.classList.remove('animate-lineFillDown'), 500);
        }, 200);
    } else if (progressLine2) {
        progressLine2.style.transform = 'scaleY(0)';
    }

    // Update step indicators with animations
    const indicators = document.querySelectorAll(`#bookingModal-${bookingId} .step-indicator`);
    indicators.forEach((indicator) => {
        const step = parseInt(indicator.dataset.step);
        const circleEl = indicator.querySelector('.step-circle');
        const numberIcon = indicator.querySelector('.step-number-icon');
        const checkmarkIcon = indicator.querySelector('.step-checkmark-icon');
        const titleEl = indicator.querySelector('h4');
        const subtitleEl = indicator.querySelector('p');

        // Add transition classes for smooth animations
        if (!circleEl.style.transition) {
            circleEl.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
        }
        if (titleEl && !titleEl.style.transition) {
            titleEl.style.transition = 'all 0.3s ease-in-out';
        }
        if (subtitleEl && !subtitleEl.style.transition) {
            subtitleEl.style.transition = 'all 0.3s ease-in-out';
        }

        if (step < currentStep) {
            // Completed step - green checkmark with enhanced animations
            indicator.classList.remove('opacity-60');
            indicator.classList.add('opacity-100', 'animate-stepComplete');
            circleEl.classList.remove('border-purple-300', 'border-purple-600', 'bg-white', 'bg-purple-600', 'shadow-md');
            circleEl.classList.add('border-green-500', 'bg-green-500', 'shadow-lg', 'animate-checkmarkPop', 'animate-circleSuccessPulse');

            if (numberIcon) {
                numberIcon.classList.add('animate-numberFadeOut');
                setTimeout(() => {
                    numberIcon.classList.add('hidden');
                }, 300);
            }
            if (checkmarkIcon) {
                checkmarkIcon.classList.remove('hidden');
                checkmarkIcon.classList.add('animate-checkmarkPop', 'animate-checkmarkGlow', 'animate-successSparkle');

                // Add drawing animation to the checkmark path
                const checkmarkPath = checkmarkIcon.querySelector('.checkmark-path');
                if (checkmarkPath) {
                    checkmarkPath.classList.add('animate-checkmarkDraw');
                }

                // Trigger confetti burst effect
                setTimeout(() => {
                    createConfettiBurst(circleEl);
                }, 200);
            }
            if (titleEl) {
                titleEl.classList.remove('text-purple-900', 'text-gray-700');
                titleEl.classList.add('text-green-700', 'font-bold', 'animate-textSlideIn');
            }
            if (subtitleEl) {
                subtitleEl.classList.remove('text-purple-600', 'text-gray-500');
                subtitleEl.classList.add('text-green-600', 'animate-textSlideIn');
            }
        } else if (step === currentStep) {
            // Active step - purple filled with pulse animation
            indicator.classList.remove('opacity-60');
            indicator.classList.add('opacity-100', 'animate-stepActive');
            circleEl.classList.remove('border-purple-300', 'border-green-500', 'bg-white', 'bg-green-500');
            circleEl.classList.add('border-purple-600', 'bg-purple-600', 'shadow-md', 'animate-pulseSlow');

            if (numberIcon) {
                numberIcon.classList.remove('hidden', 'text-purple-400', 'animate-fadeOut');
                numberIcon.classList.add('text-white', 'font-bold', 'animate-fadeIn');
            }
            if (checkmarkIcon) {
                checkmarkIcon.classList.add('hidden');
            }
            if (titleEl) {
                titleEl.classList.remove('text-gray-700', 'text-green-700', 'font-semibold');
                titleEl.classList.add('text-purple-900', 'font-bold', 'animate-textSlideIn');
            }
            if (subtitleEl) {
                subtitleEl.classList.remove('text-gray-500', 'text-green-600');
                subtitleEl.classList.add('text-purple-600', 'animate-textSlideIn');
            }
        } else {
            // Upcoming step - purple outline with subtle fade
            indicator.classList.remove('opacity-100', 'animate-stepComplete', 'animate-stepActive');
            indicator.classList.add('opacity-60', 'animate-fadeIn');
            circleEl.classList.remove('border-purple-600', 'border-green-500', 'bg-purple-600', 'bg-green-500', 'shadow-md', 'animate-pulseSlow', 'animate-checkmarkPop');
            circleEl.classList.add('border-purple-300', 'bg-white');

            if (numberIcon) {
                numberIcon.classList.remove('hidden', 'text-white', 'font-bold', 'animate-fadeIn');
                numberIcon.classList.add('text-purple-400', 'font-bold');
            }
            if (checkmarkIcon) {
                checkmarkIcon.classList.add('hidden');
            }
            if (titleEl) {
                titleEl.classList.remove('text-purple-900', 'text-green-700', 'font-bold', 'animate-textSlideIn');
                titleEl.classList.add('text-gray-700', 'font-semibold');
            }
            if (subtitleEl) {
                subtitleEl.classList.remove('text-purple-600', 'text-green-600', 'animate-textSlideIn');
                subtitleEl.classList.add('text-gray-500');
            }
        }

        // Remove animation classes after animation completes
        setTimeout(() => {
            indicator.classList.remove('animate-stepComplete', 'animate-stepActive', 'animate-fadeIn');
            circleEl.classList.remove('animate-pulseSlow', 'animate-checkmarkPop', 'animate-circleSuccessPulse');
            if (numberIcon) numberIcon.classList.remove('animate-fadeOut', 'animate-fadeIn', 'animate-numberFadeOut');
            if (checkmarkIcon) {
                checkmarkIcon.classList.remove('animate-checkmarkPop', 'animate-checkmarkGlow', 'animate-successSparkle');
                const checkmarkPath = checkmarkIcon.querySelector('.checkmark-path');
                if (checkmarkPath) {
                    checkmarkPath.classList.remove('animate-checkmarkDraw');
                }
            }
            if (titleEl) titleEl.classList.remove('animate-textSlideIn');
            if (subtitleEl) subtitleEl.classList.remove('animate-textSlideIn');
        }, 1500);
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
 * Create confetti burst effect at element position
 */
function createConfettiBurst(element) {
    const rect = element.getBoundingClientRect();
    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;

    // Create 4 confetti particles
    for (let i = 1; i <= 4; i++) {
        const particle = document.createElement('div');
        particle.className = `confetti-particle confetti-${i}`;
        particle.style.left = `${centerX}px`;
        particle.style.top = `${centerY}px`;

        document.body.appendChild(particle);

        // Remove particle after animation
        setTimeout(() => {
            particle.remove();
        }, 700);
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

// Enhanced Animations for Multi-Step Modal
const style = document.createElement('style');
style.textContent = `
    /* Slide In Alert */
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

    /* Fade In with Slide Up - For Step Content */
    @keyframes fadeInSlide {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fadeInSlide {
        animation: fadeInSlide 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Fade Out with Slide Down */
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            transform: translateY(-10px);
        }
    }

    .animate-fadeOut {
        animation: fadeOut 0.3s ease-out forwards;
    }

    /* Fade In Simple */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .animate-fadeIn {
        animation: fadeIn 0.3s ease-in-out;
    }

    /* Checkmark Pop - Enhanced Bounce Effect with Rotation */
    @keyframes checkmarkPop {
        0% {
            transform: scale(0) rotate(-180deg);
            opacity: 0;
        }
        50% {
            transform: scale(1.2) rotate(10deg);
        }
        65% {
            transform: scale(0.9) rotate(-5deg);
        }
        80% {
            transform: scale(1.05) rotate(2deg);
        }
        100% {
            transform: scale(1) rotate(0deg);
            opacity: 1;
        }
    }

    .animate-checkmarkPop {
        animation: checkmarkPop 0.7s cubic-bezier(0.68, -0.55, 0.27, 1.55);
    }

    /* Checkmark Draw Animation - SVG Path Drawing */
    @keyframes checkmarkDraw {
        0% {
            stroke-dashoffset: 100;
            opacity: 0;
        }
        20% {
            opacity: 1;
        }
        100% {
            stroke-dashoffset: 0;
            opacity: 1;
        }
    }

    .animate-checkmarkDraw {
        stroke-dasharray: 100;
        stroke-dashoffset: 100;
        animation: checkmarkDraw 0.6s ease-out forwards;
        animation-delay: 0.2s;
    }

    /* Checkmark Glow Effect */
    @keyframes checkmarkGlow {
        0%, 100% {
            filter: drop-shadow(0 0 2px rgba(34, 197, 94, 0.4));
        }
        50% {
            filter: drop-shadow(0 0 8px rgba(34, 197, 94, 0.8)) drop-shadow(0 0 12px rgba(34, 197, 94, 0.6));
        }
    }

    .animate-checkmarkGlow {
        animation: checkmarkGlow 1.5s ease-in-out;
    }

    /* Circle Success Pulse - For completed step background */
    @keyframes circleSuccessPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
        }
        50% {
            box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
        }
    }

    .animate-circleSuccessPulse {
        animation: circleSuccessPulse 0.8s ease-out;
    }

    /* Success Sparkle - For extra delight */
    @keyframes successSparkle {
        0% {
            transform: scale(1) rotate(0deg);
            opacity: 1;
        }
        25% {
            transform: scale(1.1) rotate(90deg);
            opacity: 0.8;
        }
        50% {
            transform: scale(1) rotate(180deg);
            opacity: 1;
        }
        75% {
            transform: scale(1.1) rotate(270deg);
            opacity: 0.8;
        }
        100% {
            transform: scale(1) rotate(360deg);
            opacity: 1;
        }
    }

    .animate-successSparkle {
        animation: successSparkle 0.6s ease-in-out;
    }

    /* Number Fade Out with Scale */
    @keyframes numberFadeOut {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        100% {
            transform: scale(0.5);
            opacity: 0;
        }
    }

    .animate-numberFadeOut {
        animation: numberFadeOut 0.3s ease-out forwards;
    }

    /* Connecting Line Fill - Grows from top to bottom */
    @keyframes lineFillDown {
        from {
            transform: scaleY(0);
            transform-origin: top;
        }
        to {
            transform: scaleY(1);
            transform-origin: top;
        }
    }

    .animate-lineFillDown {
        animation: lineFillDown 0.5s ease-out forwards;
    }

    /* Confetti Particle Burst Effect */
    @keyframes confettiBurst1 {
        0% {
            transform: translate(0, 0) scale(1);
            opacity: 1;
        }
        100% {
            transform: translate(-15px, -20px) scale(0);
            opacity: 0;
        }
    }

    @keyframes confettiBurst2 {
        0% {
            transform: translate(0, 0) scale(1);
            opacity: 1;
        }
        100% {
            transform: translate(15px, -20px) scale(0);
            opacity: 0;
        }
    }

    @keyframes confettiBurst3 {
        0% {
            transform: translate(0, 0) scale(1);
            opacity: 1;
        }
        100% {
            transform: translate(-20px, 5px) scale(0);
            opacity: 0;
        }
    }

    @keyframes confettiBurst4 {
        0% {
            transform: translate(0, 0) scale(1);
            opacity: 1;
        }
        100% {
            transform: translate(20px, 5px) scale(0);
            opacity: 0;
        }
    }

    /* Confetti particle styling */
    .confetti-particle {
        position: absolute;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        pointer-events: none;
        z-index: 100;
    }

    .confetti-1 { animation: confettiBurst1 0.6s ease-out forwards; background: #22c55e; }
    .confetti-2 { animation: confettiBurst2 0.6s ease-out forwards; background: #10b981; }
    .confetti-3 { animation: confettiBurst3 0.7s ease-out forwards; background: #34d399; }
    .confetti-4 { animation: confettiBurst4 0.7s ease-out forwards; background: #6ee7b7; }

    /* Pulse Slow - For Active Step */
    @keyframes pulseSlow {
        0%, 100% {
            opacity: 1;
            box-shadow: 0 4px 6px rgba(147, 51, 234, 0.3);
        }
        50% {
            opacity: 0.9;
            box-shadow: 0 4px 12px rgba(147, 51, 234, 0.5), 0 0 20px rgba(147, 51, 234, 0.3);
        }
    }

    .animate-pulseSlow {
        animation: pulseSlow 2s ease-in-out infinite;
    }

    /* Step Complete - Scale In */
    @keyframes stepComplete {
        from {
            transform: translateX(-5px);
            opacity: 0.8;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .animate-stepComplete {
        animation: stepComplete 0.4s ease-out;
    }

    /* Step Active - Slide In From Left */
    @keyframes stepActive {
        from {
            transform: translateX(-8px);
            opacity: 0.7;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .animate-stepActive {
        animation: stepActive 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Text Slide In - For Step Titles */
    @keyframes textSlideIn {
        from {
            transform: translateX(-5px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .animate-textSlideIn {
        animation: textSlideIn 0.35s ease-out;
    }

    /* Add smooth transition to all step elements */
    .step-indicator {
        transition: all 0.3s ease-in-out;
    }

    .step-circle {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .step-indicator h4,
    .step-indicator p {
        transition: all 0.3s ease-in-out;
    }
`;
document.head.appendChild(style);
