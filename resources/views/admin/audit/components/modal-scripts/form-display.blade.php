
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
                                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                    </div>
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
