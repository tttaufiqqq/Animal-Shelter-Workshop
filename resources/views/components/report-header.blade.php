@props(['report'])

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-3 mb-2">
            <h1 class="text-2xl font-bold text-gray-900">Report #{{ $report->id }}</h1>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                @if($report->report_status === 'Pending') bg-yellow-100 text-yellow-800
                @elseif($report->report_status === 'Assigned') bg-blue-100 text-blue-800
                @elseif($report->report_status === 'In Progress') bg-purple-100 text-purple-800
                @elseif($report->report_status === 'Completed') bg-green-100 text-green-800
                @elseif($report->report_status === 'Rejected') bg-red-100 text-red-800
                @else bg-gray-100 text-gray-800 @endif">
                {{ $report->report_status }}
            </span>
        </div>
        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>{{ $report->created_at->format('M j, Y \a\t g:i A') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ $report->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
    <a href="{{ route('reports.index') }}"
       class="inline-flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-50 text-sm font-medium shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Reports
    </a>
</div>
