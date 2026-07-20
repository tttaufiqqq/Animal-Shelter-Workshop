@php
    use App\Services\Backup\BackupPresenter;
@endphp

<div class="bg-white shadow rounded-xl overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-base font-semibold text-gray-900">Backup history</h3>
        <p class="text-xs text-gray-500 mt-1">We automatically keep your last 7 daily backups, plus a few older weekly ones — click a row for the full technical details.</p>
    </div>

    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-500">When</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Result</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Size</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Data Check</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        @forelse($runs as $runId => $manifest)
            @php
                $isFailed = ($manifest['status'] ?? 'failed') === 'failed';
                $meta = BackupPresenter::statusMeta($manifest['status'] ?? 'failed');
                $issueCount = BackupPresenter::orphanIssueCount($manifest['orphans'] ?? []);
                $totalSize = BackupPresenter::totalBytes($manifest['targets'] ?? []);
            @endphp
            {{-- Each run gets its own <tbody> (valid HTML5, a table may have several) so
                 Alpine's x-data scope covers both the summary row and its detail row —
                 a plain sibling <tr> can't see a neighboring <tr>'s x-data. --}}
            <tbody x-data="{ open: false }" class="divide-y divide-gray-100">
                <tr>
                    <td class="px-4 py-3 cursor-pointer" @click="open = !open">{{ BackupPresenter::friendlyDate($runId) }}</td>
                    <td class="px-4 py-3 cursor-pointer" @click="open = !open">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold
                            {{ $meta['color'] === 'green' ? 'bg-green-100 text-green-800' : ($meta['color'] === 'yellow' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $meta['icon'] }} {{ $meta['badge'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 cursor-pointer" @click="open = !open">
                        {{ $isFailed ? '—' : BackupPresenter::friendlySize($totalSize) }}
                    </td>
                    <td class="px-4 py-3 cursor-pointer" @click="open = !open">
                        @if($isFailed)
                            <span class="text-gray-400">—</span>
                        @elseif($issueCount > 0)
                            <span class="text-orange-700">⚠️ {{ $issueCount }} to review</span>
                        @else
                            <span class="text-green-700">✅ All good</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <button @click="open = !open" class="text-gray-400 hover:text-gray-600" type="button">
                            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </td>
                </tr>
                <tr x-show="open" x-cloak>
                    <td colspan="5" class="px-4 py-4 bg-gray-50 text-xs text-gray-600">
                        <p class="font-mono text-gray-400 mb-2">Run ID: {{ $runId }}</p>

                        @if($isFailed)
                            <p class="text-red-700">This backup didn't finish, so there's nothing to check yet.</p>
                            @if(!empty($manifest['message']))
                                <p class="mt-1 text-gray-600">{{ $manifest['message'] }}</p>
                            @endif
                        @endif

                        @if(!empty($manifest['targets']))
                            <p class="font-semibold text-gray-700 mb-1">Databases in this backup:</p>
                            <ul class="mb-3 space-y-0.5">
                                @foreach($manifest['targets'] as $name => $targetMeta)
                                    @php $friendly = BackupPresenter::targetLabel($name); @endphp
                                    <li>{{ $friendly['icon'] }} {{ $friendly['label'] }} <span class="text-gray-400">({{ $name }})</span> — {{ BackupPresenter::friendlySize($targetMeta['bytes'] ?? 0) }}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if($issueCount > 0)
                            <p class="font-semibold text-gray-700 mb-1">Data check details:</p>
                            <ul class="space-y-0.5">
                                @foreach($manifest['orphans'] as $label => $count)
                                    @if($count)
                                        <li>{{ BackupPresenter::orphanLabel($label) }} — {{ $count }} <span class="text-gray-400">({{ $label }})</span></li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                    </td>
                </tr>
            </tbody>
        @empty
            <tbody>
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">No backup runs recorded yet.</td>
                </tr>
            </tbody>
        @endforelse
    </table>
</div>
