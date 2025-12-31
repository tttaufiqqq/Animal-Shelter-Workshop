@props(['report'])

{{-- Report Information Card --}}
<div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
        <div class="flex items-center gap-3">
            <div class="bg-purple-600 p-2 rounded">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-gray-900">Report Details</h2>
        </div>
    </div>

    <div class="p-6">
        <!-- Reporter Information -->
        <div class="mb-6 pb-6 border-b border-gray-200">
            <label class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Reported By
            </label>
            <div class="flex items-center gap-4 bg-gray-50 p-4 rounded">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full bg-purple-600 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-900">{{ $report->user->name ?? 'Unknown' }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm text-gray-600">{{ $report->user->email ?? 'No email available' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Information -->
        <div class="mb-6 pb-6 border-b border-gray-200">
            <label class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Location Details
            </label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 p-3 rounded">
                    <p class="text-xs font-medium text-gray-500 mb-1">Address</p>
                    <p class="text-sm text-gray-900">{{ $report->address }}</p>
                </div>

                <div class="bg-gray-50 p-3 rounded">
                    <p class="text-xs font-medium text-gray-500 mb-1">City</p>
                    <p class="text-sm text-gray-900">{{ $report->city }}</p>
                </div>

                <div class="bg-gray-50 p-3 rounded">
                    <p class="text-xs font-medium text-gray-500 mb-1">State</p>
                    <p class="text-sm text-gray-900">{{ $report->state }}</p>
                </div>

                <div class="bg-gray-50 p-3 rounded">
                    <p class="text-xs font-medium text-gray-500 mb-1">Coordinates</p>
                    <p class="text-sm font-mono text-gray-900">{{ number_format($report->latitude, 6) }}, {{ number_format($report->longitude, 6) }}</p>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="mb-6 pb-6 border-b border-gray-200">
            <label class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
                Description
            </label>
            <div class="bg-gray-50 p-4 rounded">
                @if($report->description)
                    <p class="text-sm text-gray-900 leading-relaxed whitespace-pre-wrap">{{ $report->description }}</p>
                @else
                    <p class="text-sm text-gray-400 italic">No description provided</p>
                @endif
            </div>
        </div>

        <!-- Timeline -->
        <div>
            <label class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Timeline
            </label>
            <div class="bg-gray-50 p-4 rounded">
                <p class="text-xs font-medium text-gray-500">Submitted</p>
                <p class="text-sm font-semibold text-gray-900 mt-1">{{ $report->created_at->format('M j, Y \a\t g:i A') }}</p>
                <p class="text-xs text-gray-600 mt-1">{{ $report->created_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>
</div>
