
        // Action Execution Functions
        function executeSuspend() {
            const reason = document.getElementById('suspendReason').value.trim();
            if (!reason) {
                showMessage('Please enter a suspension reason', 'error');
                return;
            }

            // Show loading state
            const confirmBtn = event.target;
            const originalBtnContent = confirmBtn.innerHTML;
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = `
                <svg class="animate-spin h-5 w-5 inline mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Suspending...
            `;

            fetch(`/admin/users/${currentUserId}/suspend`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ reason })
            })
            .then(response => {
                // Handle non-200 responses
                if (!response.ok) {
                    return response.text().then(text => {
                        try {
                            const data = JSON.parse(text);
                            throw new Error(data.error || data.message || 'Server error');
                        } catch (e) {
                            throw new Error(`Server returned ${response.status}: ${text.substring(0, 100)}`);
                        }
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Hide the form
                    document.getElementById('formContainer').innerHTML = '';
                    // Show success message
                    showMessage(data.message || 'User suspended successfully!', 'success');
                    // Refresh modal content to show updated status
                    refreshModalContent();
                } else {
                    throw new Error(data.error || 'Failed to suspend user');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage(error.message || 'An error occurred while suspending the user', 'error');
                // Restore button state
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalBtnContent;
            });
        }

        function executeLock() {
            const duration = document.getElementById('lockDuration').value;
            const reason = document.getElementById('lockReason').value.trim();

            if (!reason) {
                showMessage('Please enter a lock reason', 'error');
                return;
            }

            let customDuration = null;
            if (duration === 'custom') {
                customDuration = document.getElementById('customDuration').value;
                if (!customDuration || customDuration < 1 || customDuration > 168) {
                    showMessage('Please enter a valid custom duration (1-168 hours)', 'error');
                    return;
                }
            }

            // Show loading state
            const confirmBtn = event.target;
            const originalBtnContent = confirmBtn.innerHTML;
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = `
                <svg class="animate-spin h-5 w-5 inline mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Locking...
            `;

            fetch(`/admin/users/${currentUserId}/lock`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ duration, custom_duration: customDuration, reason })
            })
            .then(response => {
                // Handle non-200 responses
                if (!response.ok) {
                    return response.text().then(text => {
                        try {
                            const data = JSON.parse(text);
                            throw new Error(data.error || data.message || 'Server error');
                        } catch (e) {
                            throw new Error(`Server returned ${response.status}: ${text.substring(0, 100)}`);
                        }
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Hide the form
                    document.getElementById('formContainer').innerHTML = '';
                    // Show success message
                    showMessage(data.message || 'User locked successfully!', 'success');
                    // Refresh modal content to show updated status
                    refreshModalContent();
                } else {
                    throw new Error(data.error || 'Failed to lock user');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage(error.message || 'An error occurred while locking the user', 'error');
                // Restore button state
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalBtnContent;
            });
        }
