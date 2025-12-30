@php
$breadcrumbs = [
    ['label' => 'Audit Log', 'url' => route('admin.audit.index')],
    ['label' => 'Timeline']
];
@endphp

<x-admin-layout title="Correlated Audit Timeline" :breadcrumbs="$breadcrumbs">

    <!-- Page Header (no export button for timeline) -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Correlated Audit Timeline</h1>
        <p class="text-gray-600 mt-1">View complete audit trail for correlated operations</p>
    </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                <h3 class="font-semibold text-lg mb-2">Correlation ID: {{ $correlationId }}</h3>
                <p>Total Operations: {{ $logs->count() }} | Duration: {{ $duration }} seconds | Status: {{ collect($statusCounts)->map(function($count, $status) { return ucfirst($status) . ': ' . $count; })->implode(', ') }}</p>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @foreach($logs as $log)
                        <div class="mb-4 pb-4 border-b last:border-b-0">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="font-bold">{{ $log->performed_at->format('H:i:s') }}</span>
                                    <span class="ml-2 px-2 py-1 rounded text-xs bg-gray-100">{{ strtoupper($log->source_database) }}</span>
                                    <span class="ml-2">{{ str_replace('_', ' ', ucwords($log->action, '_')) }}</span>
                                </div>
                                <span class="px-2 py-1 rounded-full text-xs {{ $log->status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </div>
                            @if($log->metadata)
                                <div class="mt-2 text-sm text-gray-600">
                                    {{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
</x-admin-layout>
