{{-- admin/backups/index Orchestrator --}}
@php
$breadcrumbs = [
    ['label' => 'Backups', 'icon' => '<svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>']
];

$statusColors = [
    'ok' => 'bg-green-50 border-green-500 text-green-800',
    'degraded' => 'bg-yellow-50 border-yellow-500 text-yellow-800',
    'failed' => 'bg-red-50 border-red-500 text-red-800',
];

$latestStatus = $latest['status'] ?? 'failed';
@endphp

<x-admin-layout title="Database Backups" :breadcrumbs="$breadcrumbs">
    <div class="bg-white shadow rounded-lg p-6 mb-6 border-l-4 {{ $statusColors[$latestStatus] ?? $statusColors['failed'] }}">
        <h2 class="text-lg font-semibold mb-2">Last backup: {{ strtoupper($latestStatus) }}</h2>

        @if($latest)
            <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">Run</dt>
                    <dd class="font-medium">{{ $latest['run_id'] ?? 'n/a' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Finished</dt>
                    <dd class="font-medium">{{ $latest['finished_at'] ?? 'n/a' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Duration</dt>
                    <dd class="font-medium">{{ $latest['duration_seconds'] ?? 'n/a' }}s</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Targets</dt>
                    <dd class="font-medium">{{ count($latest['targets'] ?? []) }}</dd>
                </div>
            </dl>

            @if(!empty($latest['message']))
                <p class="mt-4 text-sm">{{ $latest['message'] }}</p>
            @endif

            @if(!empty($latest['orphans']))
                <div class="mt-4">
                    <p class="text-sm font-semibold mb-1">Logical foreign key orphans:</p>
                    <ul class="text-sm list-disc list-inside">
                        @foreach($latest['orphans'] as $label => $count)
                            @if($count)
                                <li>{{ $label }}: {{ $count }} orphan(s)</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif
        @else
            <p class="text-sm">No backup has run yet.</p>
        @endif
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Run</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Duration</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Targets</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Orphans</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($runs as $runId => $manifest)
                    @php
                        $orphanTotal = collect($manifest['orphans'] ?? [])->filter()->sum();
                    @endphp
                    <tr>
                        <td class="px-4 py-3">{{ $runId }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $statusColors[$manifest['status'] ?? 'failed'] ?? $statusColors['failed'] }}">
                                {{ strtoupper($manifest['status'] ?? 'failed') }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $manifest['duration_seconds'] ?? '—' }}s</td>
                        <td class="px-4 py-3">
                            @foreach(($manifest['targets'] ?? []) as $name => $meta)
                                <div>{{ $name }} — {{ number_format(($meta['bytes'] ?? 0) / 1024, 1) }} KB</div>
                            @endforeach
                        </td>
                        <td class="px-4 py-3">{{ $orphanTotal }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">No backup runs recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
