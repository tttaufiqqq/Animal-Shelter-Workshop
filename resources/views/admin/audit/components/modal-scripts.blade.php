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

        function renderUserActivity(data) {
            const { user, stats, suspicious_patterns, recent_activity, can_manage } = data;

            let html = '';

            // Suspicious Activity Warnings
            if (suspicious_patterns && suspicious_patterns.length > 0) {
                html += `
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-red-800 mb-2">⚠️ Suspicious Activity Detected!</h3>
                                <ul class="space-y-1">`;

                suspicious_patterns.forEach(pattern => {
                    const severityColors = {
                        high: 'text-red-700 font-semibold',
                        medium: 'text-orange-700',
                        low: 'text-yellow-700'
                    };
                    html += `<li class="${severityColors[pattern.severity]}">${pattern.description}</li>`;
                });

                html += `
                                </ul>
                            </div>
                        </div>
                    </div>
                `;
            }

            // User Statistics
            html += `
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="text-sm text-blue-600 font-semibold">Total Logins</div>
                        <div class="text-2xl font-bold text-blue-900">${stats.total_logins}</div>
                    </div>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="text-sm text-red-600 font-semibold">Failed Logins</div>
                        <div class="text-2xl font-bold text-red-900">${stats.failed_logins}</div>
                    </div>
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <div class="text-sm text-orange-600 font-semibold">Recent Failures (24h)</div>
                        <div class="text-2xl font-bold text-orange-900">${stats.recent_failed_logins}</div>
                    </div>
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <div class="text-sm text-purple-600 font-semibold">Unique IPs</div>
                        <div class="text-2xl font-bold text-purple-900">${stats.unique_ips}</div>
                    </div>
                </div>
            `;

            // Account Status
            let statusBadge = '';
            if (user.account_status === 'suspended') {
                statusBadge = '<span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-semibold rounded-full">🚫 Suspended</span>';
            } else if (user.account_status === 'locked') {
                statusBadge = '<span class="px-3 py-1 bg-orange-100 text-orange-800 text-sm font-semibold rounded-full">🔒 Locked</span>';
            } else {
                statusBadge = '<span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded-full">✓ Active</span>';
            }

            html += `
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Account Status</h3>
                            ${statusBadge}
                        </div>
                    </div>
                </div>
            `;

            // Action Buttons or Restricted Message
            if (!can_manage) {
                html += `
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                        <div class="flex">
                            <svg class="w-5 h-5 text-yellow-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <h3 class="text-sm font-medium text-yellow-800">Management Restricted</h3>
                                <p class="mt-1 text-sm text-yellow-700">You cannot manage admin users or your own account.</p>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                // Action Buttons Container with Centered Layout
                html += `
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 border-2 border-gray-200 rounded-xl p-6 mb-6">
                        <h3 class="text-center text-lg font-bold text-gray-800 mb-4">
                            <svg class="w-6 h-6 inline-block mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                            Account Management Actions
                        </h3>
                        <div class="flex flex-wrap items-center justify-center gap-4">
                `;

                if (user.account_status === 'active') {
                    html += `
                        <button onclick="showSuspendForm()" class="flex items-center justify-center gap-2 px-6 py-3.5 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 min-w-[160px]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 15.636 5.636m12.728 12.728L5.636 5.636"></path>
                            </svg>
                            <span>Suspend</span>
                        </button>
                        <button onclick="showLockForm()" class="flex items-center justify-center gap-2 px-6 py-3.5 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 min-w-[160px]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <span>Lock</span>
                        </button>
                    `;
                } else {
                    html += `
                        <button onclick="unlockUser()" class="flex items-center justify-center gap-2 px-6 py-3.5 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 min-w-[160px]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                            </svg>
                            <span>Unlock</span>
                        </button>
                    `;
                }

                html += `
                        <button onclick="showResetPasswordForm()" class="flex items-center justify-center gap-2 px-6 py-3.5 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 min-w-[160px]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                            <span>Reset Password</span>
                        </button>
                        </div>
                        <p class="text-center text-xs text-gray-500 mt-4">Click an action above to manage this user account</p>
                    </div>

                    <!-- Form Container -->
                    <div id="formContainer" class="mb-6"></div>
                `;
            }

            // Recent Activity
            html += `
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Recent Activity (Last 20)</h3>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg overflow-hidden">
                        <div class="max-h-64 overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100 sticky top-0">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">Time</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">Action</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">IP</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
            `;

            recent_activity.forEach(log => {
                const statusColor = log.status === 'success' ? 'text-green-600' : 'text-red-600';

                // Format action labels properly
                const actionLabels = {
                    'login_success': 'Login Success',
                    'login_failed': 'Login Failed',
                    'logout': 'Logout',
                    'password_reset_by_admin': 'Password Reset by Admin',
                    'user_suspended': 'User Suspended',
                    'user_locked': 'User Locked',
                    'user_unlocked': 'User Unlocked',
                };
                const actionLabel = actionLabels[log.action] || log.action.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');

                html += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-sm">${new Date(log.performed_at).toLocaleString()}</td>
                        <td class="px-4 py-2 text-sm">${actionLabel}</td>
                        <td class="px-4 py-2 text-sm font-mono">${log.ip_address}</td>
                        <td class="px-4 py-2 text-sm ${statusColor} font-semibold">${log.status.charAt(0).toUpperCase() + log.status.slice(1)}</td>
                    </tr>
                `;
            });

            html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('userActivityContent').innerHTML = html;
        }

        // Form Display Functions
        function showSuspendForm() {
            hideMessage(); // Clear any previous messages
            const formHtml = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h4 class="text-lg font-semibold text-red-800 mb-4">Suspend User Account</h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Suspension Reason *</label>
                            <textarea id="suspendReason" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" placeholder="Enter reason for suspension..."></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button onclick="executeSuspend()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition">
                                Confirm Suspend
                            </button>
                            <button onclick="hideForm()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('formContainer').innerHTML = formHtml;
        }

        function showLockForm() {
            hideMessage(); // Clear any previous messages
            const formHtml = `
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <h4 class="text-lg font-semibold text-orange-800 mb-4">Lock User Account</h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lock Duration *</label>
                            <select id="lockDuration" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                <option value="1_hour">1 Hour</option>
                                <option value="24_hours">24 Hours</option>
                                <option value="7_days">7 Days</option>
                                <option value="custom">Custom (enter hours)</option>
                            </select>
                        </div>
                        <div id="customDurationField" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Custom Hours (1-168)</label>
                            <input type="number" id="customDuration" min="1" max="168" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" placeholder="Enter hours...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lock Reason *</label>
                            <textarea id="lockReason" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" placeholder="Enter reason for lock..."></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button onclick="executeLock()" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg transition">
                                Confirm Lock
                            </button>
                            <button onclick="hideForm()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('formContainer').innerHTML = formHtml;

            // Show/hide custom duration field based on selection
            document.getElementById('lockDuration').addEventListener('change', function() {
                const customField = document.getElementById('customDurationField');
                if (this.value === 'custom') {
                    customField.classList.remove('hidden');
                } else {
                    customField.classList.add('hidden');
                }
            });
        }

        function showResetPasswordForm() {
            hideMessage(); // Clear any previous messages
            const formHtml = `
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h4 class="text-lg font-semibold text-purple-800 mb-4">Reset User Password</h4>

                    <!-- Step 1: Enter Password -->
                    <div id="passwordInputStep" class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">New Password *</label>
                            <input type="password" id="newPassword" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" placeholder="Enter new password (min 8 characters)">
                            <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password *</label>
                            <input type="password" id="confirmPassword" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" placeholder="Confirm new password">
                        </div>
                        <div class="flex gap-3">
                            <button onclick="showPasswordResetConfirmation()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition">
                                Continue
                            </button>
                            <button onclick="hideForm()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
                                Cancel
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Confirmation -->
                    <div id="passwordConfirmationStep" class="hidden space-y-4">
                        <div class="bg-white border-2 border-purple-300 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h5 class="text-sm font-semibold text-gray-900 mb-1">Confirm Password Reset</h5>
                                    <p class="text-sm text-gray-600">
                                        You are about to reset the password for <strong class="text-purple-700">${currentUserData.user.email}</strong>.
                                    </p>
                                    <ul class="mt-2 text-xs text-gray-600 space-y-1">
                                        <li>• The user will be required to change their password on next login</li>
                                        <li>• This action will be logged in the audit trail</li>
                                        <li>• This action cannot be undone</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Loading Spinner (initially hidden) -->
                        <div id="resetPasswordLoading" class="hidden">
                            <div class="flex items-center justify-center gap-3 py-4">
                                <svg class="animate-spin h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-purple-700 font-medium">Resetting password...</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div id="resetPasswordButtons" class="flex gap-3">
                            <button onclick="executePasswordReset()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition">
                                Yes, Reset Password
                            </button>
                            <button onclick="showResetPasswordForm()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
                                Go Back
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('formContainer').innerHTML = formHtml;
        }

        function showPasswordResetConfirmation() {
            const password = document.getElementById('newPassword').value.trim();
            const passwordConfirmation = document.getElementById('confirmPassword').value.trim();

            // Validation
            if (!password) {
                showMessage('Please enter a new password', 'error');
                return;
            }

            if (password.length < 8) {
                showMessage('Password must be at least 8 characters long', 'error');
                return;
            }

            if (password !== passwordConfirmation) {
                showMessage('Passwords do not match', 'error');
                return;
            }

            // Hide step 1, show step 2
            document.getElementById('passwordInputStep').classList.add('hidden');
            document.getElementById('passwordConfirmationStep').classList.remove('hidden');
        }

        function hideForm() {
            document.getElementById('formContainer').innerHTML = '';
            hideMessage(); // Clear any error messages when hiding form
        }

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

