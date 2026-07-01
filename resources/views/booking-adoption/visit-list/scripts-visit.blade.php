<script>
    // Enable / Disable Confirm Button
    window.updateConfirmButton = function() {
        const appointmentDate = document.getElementById('appointmentDate');
        const appointmentTime = document.getElementById('appointmentTime');
        const termsCheckbox = document.getElementById('termsCheckbox') || document.querySelector('input[name="terms"]');
        const confirmBtn = document.getElementById('confirmBookingBtn');

        // Safety check - ensure all elements exist
        if (!appointmentDate || !appointmentTime || !termsCheckbox || !confirmBtn) {
            return;
        }

        const isValid = appointmentDate.value.trim() !== '' &&
            appointmentTime.value.trim() !== '' &&
            termsCheckbox.checked;

        // Force remove/add disabled attribute
        if (isValid) {
            confirmBtn.disabled = false;
            confirmBtn.removeAttribute('disabled');
        } else {
            confirmBtn.disabled = true;
            confirmBtn.setAttribute('disabled', 'disabled');
        }

        // Remove ALL existing inline styles and classes first
        confirmBtn.removeAttribute('style');
        confirmBtn.className = '';

        if (isValid) {
            // ENABLED STATE - Force with !important inline styles + classes
            confirmBtn.style.setProperty('background', 'linear-gradient(to right, #9333ea, #7e22ce)', 'important');
            confirmBtn.style.setProperty('color', '#ffffff', 'important');
            confirmBtn.style.setProperty('cursor', 'pointer', 'important');
            confirmBtn.style.setProperty('opacity', '1', 'important');
            confirmBtn.className = 'flex-1 px-6 py-4 text-white font-bold rounded-xl shadow-lg transition-all duration-200 flex items-center justify-center gap-2 hover:shadow-xl transform hover:scale-105';
        } else {
            // DISABLED STATE - Force with !important inline styles + classes
            confirmBtn.style.setProperty('background-color', '#d1d5db', 'important');
            confirmBtn.style.setProperty('color', '#6b7280', 'important');
            confirmBtn.style.setProperty('cursor', 'not-allowed', 'important');
            confirmBtn.style.setProperty('opacity', '0.6', 'important');
            confirmBtn.className = 'flex-1 px-6 py-4 font-bold rounded-xl shadow-lg transition-all duration-200 flex items-center justify-center gap-2';
        }
    }

    // Handle visit booking submission
    function handleVisitSubmit(event) {
        const submitBtn = document.getElementById('confirmBookingBtn');
        const btnText = document.getElementById('visitBtnText');
        const btnLoading = document.getElementById('visitBtnLoading');

        // Show loading state
        submitBtn.disabled = true;
        btnText.classList.add('hidden');
        btnLoading.classList.remove('hidden');

        // Form will submit normally, button stays disabled
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        const appointmentDate = document.getElementById('appointmentDate');
        const appointmentTime = document.getElementById('appointmentTime');
        const termsCheckbox = document.getElementById('termsCheckbox') || document.querySelector('input[name="terms"]');

        if(appointmentDate){
            appointmentDate.addEventListener('input', window.updateConfirmButton);
            appointmentDate.addEventListener('change', window.updateConfirmButton);
        }

        if(appointmentTime){
            appointmentTime.addEventListener('change', window.updateConfirmButton);
        }

        if(termsCheckbox){
            termsCheckbox.addEventListener('change', window.updateConfirmButton);
            termsCheckbox.addEventListener('click', function() {
                setTimeout(() => window.updateConfirmButton(), 0);
            });
            termsCheckbox.addEventListener('input', window.updateConfirmButton);
        }

        // Initial check
        window.updateConfirmButton();
    });

    // Close modal on click outside
    document.addEventListener('click', function(e){
        const modal = document.getElementById('visitModal');
        if(!modal.classList.contains('hidden') && e.target === modal){
            window.closeVisitModal();
        }

        // Also handle remove confirmation modal
        const removeModal = document.getElementById('removeConfirmModal');
        if(!removeModal.classList.contains('hidden') && e.target === removeModal){
            window.closeRemoveConfirmModal();
        }
    });

    // Close modal on Escape
    document.addEventListener('keydown', function(e){
        if(e.key === "Escape"){
            const removeModal = document.getElementById('removeConfirmModal');
            if(!removeModal.classList.contains('hidden')){
                window.closeRemoveConfirmModal();
            } else {
                window.closeVisitModal();
            }
        }
    });

    // Auto-open modal if session flag
    @if (session('open_visit_modal'))
    document.addEventListener("DOMContentLoaded", function () {
        window.openVisitModal();
    });
    @endif
</script>
