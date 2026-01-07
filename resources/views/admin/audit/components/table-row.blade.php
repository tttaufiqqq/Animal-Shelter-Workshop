{{-- Table Row Component
     Props: $log, $suspiciousUsers
--}}
@php
    $isSuspicious = isset($suspiciousUsers[$log->user_email]);
    $highSeverity = $isSuspicious && collect($suspiciousUsers[$log->user_email])->contains('severity', 'high');
    $rowClass = $highSeverity ? 'bg-red-50/50 border-l-4 border-red-500' :
                ($isSuspicious ? 'bg-orange-50/50 border-l-4 border-orange-500' :
                ($log->status === 'failure' ? 'bg-red-50/30' : ''));
@endphp

<tr class="hover:bg-gray-50 transition-colors {{ $rowClass }}">
    <td class="px-4 py-3 whitespace-nowrap">
        <div class="text-xs font-medium text-gray-900">{{ $log->performed_at->format('H:i:s') }}</div>
        <div class="text-xs text-gray-500">{{ $log->performed_at->format('d M') }}</div>
    </td>
    <td class="px-4 py-3">
        <div class="flex items-center gap-2">
            <div class="flex-shrink-0 h-7 w-7 bg-indigo-100 rounded-full flex items-center justify-center">
                <svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-xs font-medium text-gray-900 truncate">{{ $log->user_name ?? 'Unknown' }}</div>
                <div class="text-xs text-gray-500 truncate">{{ $log->user_email }}</div>
            </div>
        </div>
    </td>
    <td class="px-4 py-3 whitespace-nowrap">
        @php
            $roleName = $log->user?->roles->first()?->name ?? 'N/A';
            $roleColors = [
                'admin' => 'bg-purple-100 text-purple-800',
                'caretaker' => 'bg-blue-100 text-blue-800',
                'user' => 'bg-gray-100 text-gray-800',
            ];
            $roleColor = $roleColors[$roleName] ?? 'bg-gray-100 text-gray-600';
        @endphp
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $roleColor }}">
            {{ ucfirst($roleName) }}
        </span>
    </td>
    <td class="px-4 py-3 whitespace-nowrap">
        @if($log->action === 'password_reset_by_admin')
            @php
                $targetUser = $log->metadata['target_user_name'] ?? 'Unknown User';
            @endphp
            <div class="inline-flex flex-col">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                    🔑 <span class="ml-1">Password Reset</span>
                </span>
                <span class="text-xs text-gray-600 mt-1">for {{ $targetUser }}</span>
            </div>
        @else
            @php
                $actionLabels = [
                    'login_success' => 'Login Success',
                    'login_failed' => 'Login Failed',
                    'logout' => 'Logout',
                    'user_suspended' => 'User Suspended',
                    'user_locked' => 'User Locked',
                    'user_unlocked' => 'User Unlocked',
                ];
                $actionLabel = $actionLabels[$log->action] ?? ucwords(str_replace('_', ' ', $log->action));
            @endphp
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                {{ $log->action === 'login_success' ? 'bg-green-100 text-green-800' : '' }}
                {{ $log->action === 'login_failed' ? 'bg-red-100 text-red-800' : '' }}
                {{ $log->action === 'logout' ? 'bg-gray-100 text-gray-800' : '' }}"
                title="{{ $log->error_message ?? '' }}">
                @if($log->action === 'login_success')
                    ✓
                @elseif($log->action === 'login_failed')
                    ✗
                @else
                    →
                @endif
                <span class="ml-1">{{ $actionLabel }}</span>
            </span>
        @endif
    </td>
    <td class="px-4 py-3 whitespace-nowrap">
        <div class="text-xs font-mono text-gray-900">{{ $log->ip_address }}</div>
    </td>
    <td class="px-4 py-3 whitespace-nowrap text-center" title="{{ $log->user_agent }}">
        @php
            $ua = strtolower($log->user_agent);
            $browser = 'Desktop';
            if (str_contains($ua, 'chrome')) $browser = '🌐 Chrome';
            elseif (str_contains($ua, 'firefox')) $browser = '🦊 Firefox';
            elseif (str_contains($ua, 'safari')) $browser = '🧭 Safari';
            elseif (str_contains($ua, 'edge')) $browser = '🌊 Edge';
            elseif (str_contains($ua, 'mobile')) $browser = '📱 Mobile';
        @endphp
        <span class="text-xs">{{ $browser }}</span>
    </td>
    <td class="px-4 py-3 whitespace-nowrap">
        @if($log->status === 'success')
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                ✓
            </span>
        @else
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                ✗
            </span>
        @endif
    </td>
    <td class="px-4 py-3 whitespace-nowrap">
        @if($log->user_email)
            @php
                $user = \App\Models\User::where('email', $log->user_email)->first();
                $isAdminUser = $user && $user->hasRole('admin');
            @endphp
            @if($user)
                @if($isAdminUser)
                    {{-- Disabled button for admin users --}}
                    <div class="relative group">
                        <button disabled
                                class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold rounded-lg w-full bg-gray-300 text-gray-500 cursor-not-allowed opacity-60">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Admin
                        </button>
                        {{-- Tooltip --}}
                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                            Admin accounts cannot be managed
                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1 border-4 border-transparent border-t-gray-900"></div>
                        </div>
                    </div>
                @else
                    {{-- Active button for non-admin users --}}
                    <button onclick="openUserManagementModal({{ $user->id }}, '{{ $user->email }}')"
                            class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold rounded-lg transition-all duration-200 w-full
                            {{ $isSuspicious ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-indigo-600 hover:bg-indigo-700 text-white' }}">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                        </svg>
                        Manage
                    </button>
                @endif
                @if($isSuspicious)
                    <span class="block mt-1 px-1 py-0.5 bg-red-100 text-red-800 text-xs font-semibold rounded text-center">
                        ⚠️
                    </span>
                @endif
            @endif
        @endif
    </td>
</tr>
