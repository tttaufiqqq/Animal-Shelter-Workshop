    <script>
        let currentUserId = null;
        let currentUserData = null;

        // Helper functions for displaying messages
        function showMessage(message, type = 'success') {
            const container = document.getElementById('messageContainer');
            const bgColor = type === 'success' ? 'bg-green-50 border-green-500' : 'bg-red-50 border-red-500';
            const textColor = type === 'success' ? 'text-green-800' : 'text-red-800';
            const iconColor = type === 'success' ? 'text-green-600' : 'text-red-600';
            const icon = type === 'success'
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';

            container.innerHTML = `
                <div class="${bgColor} border-l-4 rounded-lg p-4 mb-4 animate-fade-in">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 ${iconColor} mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            ${icon}
                        </svg>
                        <div class="flex-1">
                            <p class="${textColor} font-medium">${message}</p>
                        </div>
                        <button onclick="hideMessage()" class="${textColor} hover:opacity-75 ml-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            `;
            container.classList.remove('hidden');

            // Scroll to top of modal to ensure message is visible
            const modalContent = document.getElementById('modalContent');
            if (modalContent) {
                modalContent.scrollTop = 0;
            }

            // Auto-hide success messages after 8 seconds (longer to read)
            if (type === 'success') {
                setTimeout(() => hideMessage(), 8000);
            }
        }

        function hideMessage() {
            const container = document.getElementById('messageContainer');
            container.classList.add('hidden');
            container.innerHTML = '';
        }

        function openUserManagementModal(userId, userEmail) {
            currentUserId = userId;
            hideMessage(); // Clear any previous messages
            document.getElementById('modalUserEmail').textContent = userEmail;
            document.getElementById('userManagementModal').classList.remove('hidden');
            document.getElementById('loadingState').classList.remove('hidden');
            document.getElementById('userActivityContent').classList.add('hidden');

            // Prevent background scrolling
            document.body.style.overflow = 'hidden';

            // Fetch user activity
            fetch(`/admin/users/${userId}/activity`)
                .then(response => response.json())
                .then(data => {
                    currentUserData = data;
                    document.getElementById('loadingState').classList.add('hidden');
                    document.getElementById('userActivityContent').classList.remove('hidden');
                    renderUserActivity(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('loadingState').innerHTML = `
                        <div class="text-center text-red-600">
                            <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p>Failed to load user activity</p>
                        </div>
                    `;
                });
        }

        function closeUserManagementModal() {
            document.getElementById('userManagementModal').classList.add('hidden');
            hideMessage(); // Clear any messages when closing
            currentUserId = null;
            currentUserData = null;

            // Restore background scrolling
            document.body.style.overflow = '';
        }
