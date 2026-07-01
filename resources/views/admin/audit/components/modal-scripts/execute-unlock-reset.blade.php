
        function unlockUser() {
            if (!confirm('Are you sure you want to unlock this user account?')) return;

            // Show a temporary message that unlock is in progress
            showMessage('Unlocking user account...', 'success');

            fetch(`/admin/users/${currentUserId}/unlock`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
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
                    // Show success message
                    showMessage(data.message || 'User unlocked successfully!', 'success');
                    // Refresh modal content to show updated status
                    refreshModalContent();
                } else {
                    throw new Error(data.error || 'Failed to unlock user');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage(error.message || 'An error occurred while unlocking the user', 'error');
            });
        }

        function executePasswordReset() {
            const password = document.getElementById('newPassword').value.trim();
            const passwordConfirmation = document.getElementById('confirmPassword').value.trim();

            // Show loading spinner and hide buttons
            document.getElementById('resetPasswordLoading').classList.remove('hidden');
            document.getElementById('resetPasswordButtons').classList.add('hidden');
            hideMessage(); // Clear any previous messages

            fetch(`/admin/users/${currentUserId}/force-password-reset`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    password: password,
                    password_confirmation: passwordConfirmation
                })
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading spinner
                document.getElementById('resetPasswordLoading').classList.add('hidden');

                if (data.success) {
                    // Hide the form
                    document.getElementById('formContainer').innerHTML = '';
                    // Show success message
                    showMessage(data.message, 'success');
                    // Refresh modal content to show updated activity
                    refreshModalContent();
                } else {
                    // Show error message and restore buttons for retry
                    showMessage(data.error || 'Failed to reset password', 'error');
                    document.getElementById('resetPasswordButtons').classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Hide loading spinner on error
                document.getElementById('resetPasswordLoading').classList.add('hidden');
                document.getElementById('resetPasswordButtons').classList.remove('hidden');
                showMessage('An error occurred while resetting the password', 'error');
            });
        }

        // Refresh modal content after action (keeps modal open)
        function refreshModalContent() {
            if (!currentUserId) return;

            // Show a subtle loading indicator with smooth transition
            const activityContent = document.getElementById('userActivityContent');
            activityContent.style.transition = 'opacity 0.3s ease';
            activityContent.style.opacity = '0.5';
            activityContent.style.pointerEvents = 'none';

            // Re-fetch user activity data
            fetch(`/admin/users/${currentUserId}/activity`)
                .then(response => response.json())
                .then(data => {
                    currentUserData = data;
                    renderUserActivity(data);

                    // Smooth fade-in after content update
                    setTimeout(() => {
                        activityContent.style.opacity = '1';
                        activityContent.style.pointerEvents = 'auto';
                    }, 100);
                })
                .catch(error => {
                    console.error('Error refreshing content:', error);
                    showMessage('Failed to refresh user data', 'error');
                    // Restore visibility even on error
                    activityContent.style.opacity = '1';
                    activityContent.style.pointerEvents = 'auto';
                });
        }
    </script>
