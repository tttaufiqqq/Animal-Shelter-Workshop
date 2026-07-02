
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