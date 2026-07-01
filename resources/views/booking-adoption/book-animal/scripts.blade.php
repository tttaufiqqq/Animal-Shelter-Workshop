<script>
    // Book Adoption Form Loading State
    document.addEventListener('DOMContentLoaded', function() {
        const bookAdoptionForm = document.getElementById('bookAdoptionForm');
        if (bookAdoptionForm) {
            bookAdoptionForm.addEventListener('submit', function(e) {
                const submitBtn = document.getElementById('bookAdoptionSubmitBtn');
                const submitText = document.getElementById('bookAdoptionSubmitText');
                const submitIcon = document.getElementById('bookAdoptionSubmitIcon');
                const submitSpinner = document.getElementById('bookAdoptionSubmitSpinner');
                const cancelBtn = document.getElementById('bookAdoptionCancelBtn');

                // Disable button and show loading state
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                submitBtn.classList.remove('hover:from-purple-700', 'hover:to-purple-800');

                // Hide icon and text, show spinner
                submitIcon.classList.add('hidden');
                submitText.textContent = 'Booking...';
                submitSpinner.classList.remove('hidden');

                // Disable cancel button
                cancelBtn.disabled = true;
                cancelBtn.classList.add('opacity-50', 'cursor-not-allowed');

                // Disable all form inputs
                const inputs = bookAdoptionForm.querySelectorAll('input, select, textarea, button[type="button"]');
                inputs.forEach(input => input.disabled = true);
            });
        }
    });
</script>

<script>
    // Open Book Adoption Modal
    function openBookAdoptionModal(animalId, animalName, animalSpecies) {
        // Set animal information
        document.getElementById('book_animalID').value = animalId;
        document.getElementById('display_animal_name').textContent = animalName;
        document.getElementById('display_animal_species').textContent = animalSpecies;

        // Show modal
        document.getElementById('bookAdoptionModal').classList.remove('hidden');
    }

    // Close Book Adoption Modal
    function closeBookAdoptionModal() {
        document.getElementById('bookAdoptionModal').classList.add('hidden');
        document.getElementById('bookAdoptionForm').reset();
    }

    // Close modal when clicking outside
    document.getElementById('bookAdoptionModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeBookAdoptionModal();
        }
    });

    // Close with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBookAdoptionModal();
        }
    });

    // Store booked slots
    const bookedSlots = @json($bookedSlots ?? []);

    // Function to check if a datetime is booked
    function isSlotBooked(selectedDateTime) {
        return bookedSlots.some(slot => slot.datetime === selectedDateTime);
    }

    // Validate appointment time on form submission
    document.getElementById('bookAdoptionForm')?.addEventListener('submit', function(e) {
        const appointmentInput = document.getElementById('appointment_date');
        const selectedDateTime = appointmentInput.value;

        if (isSlotBooked(selectedDateTime)) {
            e.preventDefault();
            alert('This time slot is already booked. Please select a different date and time.');
            appointmentInput.focus();
            return false;
        }
    });

    // Add real-time validation
    document.addEventListener('DOMContentLoaded', function() {
        const appointmentInput = document.getElementById('appointment_date');
        const warningDiv = document.getElementById('booking-warning');
        const submitButton = document.querySelector('#bookAdoptionForm button[type="submit"]');

        if (appointmentInput) {
            appointmentInput.addEventListener('change', function() {
                const selectedDateTime = this.value;

                if (isSlotBooked(selectedDateTime)) {
                    // Show warning
                    warningDiv.classList.remove('hidden');
                    // Disable submit button
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                    // Highlight input as error
                    this.classList.add('border-red-500', 'bg-red-50');
                } else {
                    // Hide warning
                    warningDiv.classList.add('hidden');
                    // Enable submit button
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                    // Remove error styling
                    this.classList.remove('border-red-500', 'bg-red-50');
                }
            });
        }
    });
</script>
