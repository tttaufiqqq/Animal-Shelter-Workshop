<!-- Page Header -->
<div class="bg-purple-600 shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-semibold text-white">Rescue #{{ $rescue->id }}</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold shadow-sm
                        @if($rescue->status === 'Scheduled') bg-amber-200 text-amber-900 border border-amber-300
                        @elseif($rescue->status === 'In Progress') bg-sky-200 text-sky-900 border border-sky-300
                        @elseif($rescue->status === 'Success') bg-emerald-200 text-emerald-900 border border-emerald-300
                        @elseif($rescue->status === 'Failed') bg-rose-200 text-rose-900 border border-rose-300
                        @else bg-gray-200 text-gray-800 border border-gray-300 @endif">
                        {{ $rescue->status }}
                    </span>
                </div>
                <p class="text-purple-100 text-sm mt-1">Assigned on {{ $rescue->created_at->format('M j, Y') }}</p>
            </div>
            <a href="{{ route('rescues.index') }}"
               class="inline-flex items-center gap-2 bg-white text-purple-700 px-4 py-2 rounded-lg hover:bg-purple-50 transition-colors text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Rescues
            </a>
        </div>
    </div>
</div>
