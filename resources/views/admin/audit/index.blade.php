<x-app-layout>
    <x-slot name="title">
        Audit Log - Dashboard
    </x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button onclick="history.back()"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-white hover:bg-gray-50 border border-gray-200 shadow-sm transition-all duration-200 hover:shadow-md group">
                    <svg class="w-5 h-5 text-gray-600 group-hover:text-gray-900 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </button>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Audit Trail Dashboard
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">Monitor system activity and security events across all modules</p>
                </div>
            </div>
            <div class="text-sm text-gray-500">
                <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Last updated: {{ now()->format('d M Y, H:i') }}
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Key Metrics Overview -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">System Overview</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                    <!-- Total Logs Card -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 mb-1">Total Audit Logs</p>
                                <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_logs']) }}</p>
                                <p class="text-xs text-gray-500 mt-2">All-time records</p>
                            </div>
                            <div class="bg-gray-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Activity Card -->
                    <div class="bg-white border border-blue-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 mb-1">Today's Activity</p>
                                <p class="text-3xl font-bold text-blue-600">{{ number_format($stats['today_logs']) }}</p>
                                <p class="text-xs text-blue-600 mt-2 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                    Current day logs
                                </p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Failed Actions Card -->
                    <div class="bg-white border border-red-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 mb-1">Failed Actions</p>
                                <p class="text-3xl font-bold text-red-600">{{ number_format($stats['failed_actions']) }}</p>
                                <p class="text-xs text-red-600 mt-2 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    Requires attention
                                </p>
                            </div>
                            <div class="bg-red-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Security Alert Card -->
                    <div class="bg-white border border-orange-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 mb-1">Failed Logins (24h)</p>
                                <p class="text-3xl font-bold text-orange-600">{{ number_format($failedLogins) }}</p>
                                <p class="text-xs text-orange-600 mt-2 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Security monitor
                                </p>
                            </div>
                            <div class="bg-orange-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Categories -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Audit Categories</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

                    <!-- Authentication Logs -->
                    <a href="{{ route('admin.audit.authentication') }}"
                       class="bg-white border border-gray-200 rounded-lg p-6 hover:border-indigo-300 hover:shadow-lg transition-all group">
                        <div class="flex items-start justify-between mb-4">
                            <div class="bg-indigo-100 p-3 rounded-lg group-hover:bg-indigo-200 transition-colors">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                            </div>
                            <span class="text-2xl font-bold text-indigo-600">{{ number_format($stats['authentication']) }}</span>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">Authentication</h4>
                        <p class="text-sm text-gray-600 mb-3">Login & logout tracking, session management</p>
                        <div class="flex items-center text-indigo-600 text-sm font-medium group-hover:translate-x-1 transition-transform">
                            View logs
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>

                    <!-- Payment & Adoption -->
                    <a href="{{ route('admin.audit.payments') }}"
                       class="bg-white border border-gray-200 rounded-lg p-6 hover:border-green-300 hover:shadow-lg transition-all group">
                        <div class="flex items-start justify-between mb-4">
                            <div class="bg-green-100 p-3 rounded-lg group-hover:bg-green-200 transition-colors">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <span class="text-2xl font-bold text-green-600">{{ number_format($stats['payment']) }}</span>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">Payment & Adoption</h4>
                        <p class="text-sm text-gray-600 mb-3">Transaction records, adoption payments</p>
                        <div class="flex items-center text-green-600 text-sm font-medium group-hover:translate-x-1 transition-transform">
                            View logs
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>

                    <!-- Animal Welfare -->
                    <a href="{{ route('admin.audit.animals') }}"
                       class="bg-white border border-gray-200 rounded-lg p-6 hover:border-purple-300 hover:shadow-lg transition-all group">
                        <div class="flex items-start justify-between mb-4">
                            <div class="bg-purple-100 p-3 rounded-lg group-hover:bg-purple-200 transition-colors">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </div>
                            <span class="text-2xl font-bold text-purple-600">{{ number_format($stats['animal']) }}</span>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">Animal Welfare</h4>
                        <p class="text-sm text-gray-600 mb-3">Medical records, vaccination tracking</p>
                        <div class="flex items-center text-purple-600 text-sm font-medium group-hover:translate-x-1 transition-transform">
                            View logs
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>

                    <!-- Rescue Operations -->
                    <a href="{{ route('admin.audit.rescues') }}"
                       class="bg-white border border-gray-200 rounded-lg p-6 hover:border-blue-300 hover:shadow-lg transition-all group">
                        <div class="flex items-start justify-between mb-4">
                            <div class="bg-blue-100 p-3 rounded-lg group-hover:bg-blue-200 transition-colors">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <span class="text-2xl font-bold text-blue-600">{{ number_format($stats['rescue']) }}</span>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">Rescue Operations</h4>
                        <p class="text-sm text-gray-600 mb-3">Caretaker assignments, rescue tracking</p>
                        <div class="flex items-center text-blue-600 text-sm font-medium group-hover:translate-x-1 transition-transform">
                            View logs
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                            <p class="text-sm text-gray-600 mt-1">Last 20 audit log entries</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500">Auto-refresh</span>
                            <div class="relative inline-block w-10 h-6 transition duration-200 ease-in-out bg-gray-300 rounded-full">
                                <span class="absolute left-0 inline-block w-5 h-5 transition duration-200 ease-in-out transform translate-x-0.5 translate-y-0.5 bg-white rounded-full shadow"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Time
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Category
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Action
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    User
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Details
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($recentLogs as $log)
                                <tr class="hover:bg-gray-50 transition-colors {{ $log->status === 'failure' ? 'bg-red-50/30' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $log->performed_at->format('H:i:s') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $log->performed_at->format('d M Y') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                            {{ $log->category === 'authentication' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                            {{ $log->category === 'payment' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $log->category === 'animal' ? 'bg-purple-100 text-purple-800' : '' }}
                                            {{ $log->category === 'rescue' ? 'bg-blue-100 text-blue-800' : '' }}">
                                            <svg class="w-2 h-2 mr-1.5 fill-current" viewBox="0 0 8 8">
                                                <circle cx="4" cy="4" r="3" />
                                            </svg>
                                            {{ ucfirst($log->category) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 font-medium">
                                            {{ str_replace('_', ' ', ucwords($log->action, '_')) }}
                                        </div>
                                        @if($log->entity_id)
                                            <div class="text-xs text-gray-500">ID: {{ $log->entity_id }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-gray-200 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $log->user_name ?? 'System' }}
                                                </div>
                                                @if($log->user_email)
                                                    <div class="text-xs text-gray-500">{{ $log->user_email }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                            {{ $log->status === 'success' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $log->status === 'failure' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $log->status === 'error' ? 'bg-orange-100 text-orange-800' : '' }}">
                                            @if($log->status === 'success')
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                            @elseif($log->status === 'failure' || $log->status === 'error')
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                </svg>
                                            @endif
                                            {{ ucfirst($log->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($log->metadata && isset($log->metadata['correlation_id']))
                                            <a href="{{ route('admin.audit.timeline', $log->metadata['correlation_id']) }}"
                                               class="text-indigo-600 hover:text-indigo-900 font-medium inline-flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Timeline
                                            </a>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-500">No audit logs found.</p>
                                        <p class="text-xs text-gray-400 mt-1">System activity will appear here</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer -->
                @if($recentLogs->isNotEmpty())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <span>Showing {{ $recentLogs->count() }} of {{ number_format($stats['total_logs']) }} total logs</span>
                        <a href="{{ route('admin.audit.authentication') }}" class="text-indigo-600 hover:text-indigo-900 font-medium inline-flex items-center">
                            View all audit logs
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
