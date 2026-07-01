<script>
    // Function to open the modal
    function openAdopterModal() {
        document.getElementById('adopterModal').classList.remove('hidden');
        document.getElementById('adopterModal').classList.add('flex');
    }

    // Function to close the modal
    function closeAdopterModal() {
        document.getElementById('adopterModal').classList.add('hidden');
        document.getElementById('adopterModal').classList.remove('flex');
    }

    // Handle form submission with AJAX (no page refresh)
    document.addEventListener('DOMContentLoaded', function() {
        // Keep modal open if there are validation errors on page load
        @if($errors->any())
            openAdopterModal();
            setTimeout(() => {
                const modalContent = document.querySelector('#adopterModal > div');
                if (modalContent) {
                    modalContent.scrollTop = 0;
                }
            }, 100);
        @endif

        const adopterForm = document.getElementById('adopterProfileForm');
        const messageContainer = document.getElementById('modalMessageContainer');

        if (adopterForm) {
            adopterForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                const saveBtn = document.getElementById('saveAdopterBtn');
                const cancelBtn = document.getElementById('cancelAdopterBtn');

                // Clear previous messages
                messageContainer.innerHTML = '';

                // Disable both buttons
                saveBtn.disabled = true;
                cancelBtn.disabled = true;

                // Show loading spinner
                saveBtn.innerHTML = `
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Saving Profile...</span>
                `;

                // Submit form via AJAX
                const formData = new FormData(adopterForm);

                fetch(adopterForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    // Store the response status
                    const isSuccess = response.ok;
                    return response.json().then(data => ({ isSuccess, data }));
                })
                .then(({ isSuccess, data }) => {
                    // Scroll to top of modal
                    const modalContent = document.querySelector('#adopterModal > div');
                    if (modalContent) {
                        modalContent.scrollTop = 0;
                    }

                    if (isSuccess && data.success) {
                        // Display success message
                        messageContainer.innerHTML = `
                            <div class="flex items-start gap-3 p-4 mb-4 bg-green-50 border border-green-200 rounded-xl shadow-sm">
                                <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <p class="font-semibold text-green-700">${data.message}</p>
                            </div>
                        `;
                    } else {
                        // Handle validation errors or general errors
                        let errorHtml = `
                            <div class="flex items-start gap-3 p-4 mb-4 bg-red-50 border border-red-200 rounded-xl shadow-sm">
                                <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <div class="flex-1">
                                    <p class="font-semibold text-red-700">${data.message || 'An error occurred. Please try again.'}</p>
                        `;

                        // Display validation errors if present
                        if (data.errors && Object.keys(data.errors).length > 0) {
                            errorHtml += '<ul class="mt-2 list-disc list-inside text-sm text-red-600">';
                            for (const [field, messages] of Object.entries(data.errors)) {
                                messages.forEach(message => {
                                    errorHtml += `<li>${message}</li>`;
                                });
                            }
                            errorHtml += '</ul>';
                        }

                        errorHtml += `
                                </div>
                            </div>
                        `;

                        messageContainer.innerHTML = errorHtml;
                    }

                    // Re-enable buttons and restore original text
                    saveBtn.disabled = false;
                    cancelBtn.disabled = false;
                    saveBtn.innerHTML = '<span>Save Profile</span>';
                })
                .catch(error => {
                    console.error('Error:', error);

                    // Scroll to top of modal
                    const modalContent = document.querySelector('#adopterModal > div');
                    if (modalContent) {
                        modalContent.scrollTop = 0;
                    }

                    // Display error message
                    messageContainer.innerHTML = `
                        <div class="flex items-start gap-3 p-4 mb-4 bg-red-50 border border-red-200 rounded-xl shadow-sm">
                            <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <p class="font-semibold text-red-700">An error occurred. Please try again.</p>
                        </div>
                    `;

                    // Re-enable buttons and restore original text
                    saveBtn.disabled = false;
                    cancelBtn.disabled = false;
                    saveBtn.innerHTML = '<span>Save Profile</span>';
                });
            });
        }
    });
</script>
