@php
    use App\Services\Backup\BackupPresenter;

    $meta = BackupPresenter::statusMeta($latestStatus);
    $bannerColors = [
        'green' => 'bg-green-50 border-green-500',
        'yellow' => 'bg-yellow-50 border-yellow-500',
        'red' => 'bg-red-50 border-red-500',
    ];
    $headingColors = [
        'green' => 'text-green-800',
        'yellow' => 'text-yellow-800',
        'red' => 'text-red-800',
    ];
@endphp

<div class="bg-white shadow rounded-xl p-6 mb-6 border-l-4 {{ $bannerColors[$meta['color']] }}">
    <div class="flex items-start gap-4">
        <span class="text-4xl leading-none">{{ $meta['icon'] }}</span>
        <div class="flex-1">
            <h2 class="text-xl font-bold {{ $headingColors[$meta['color']] }}">{{ $meta['heading'] }}</h2>

            @if($latest)
                <p class="text-sm text-gray-600 mt-1">
                    Last checked: <span class="font-medium">{{ BackupPresenter::friendlyDate($latest['finished_at'] ?? $latest['run_id'] ?? null) }}</span>
                    @if(isset($latest['duration_seconds']))
                        &middot; Took {{ BackupPresenter::friendlyDuration($latest['duration_seconds']) }}
                    @endif
                </p>

                @if(!empty($latest['message']))
                    <p class="mt-3 text-sm text-gray-700 bg-white/60 rounded-lg p-3">{{ $latest['message'] }}</p>
                @endif

                @if($latestStatus === 'degraded' && !empty($latest['orphans']))
                    <div class="mt-4 bg-white/60 rounded-lg p-4">
                        <p class="text-sm font-semibold text-yellow-800 mb-2">A few records don't fully match up — nothing is lost, but worth a look:</p>
                        <ul class="text-sm text-gray-700 space-y-1">
                            @foreach($latest['orphans'] as $label => $count)
                                @if($count)
                                    <li class="flex items-start gap-2">
                                        <span class="text-yellow-600">•</span>
                                        <span>{{ BackupPresenter::orphanLabel($label) }} — <strong>{{ $count }}</strong> {{ Str::plural('record', $count) }} to check</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif
            @else
                <p class="text-sm text-gray-600 mt-1">No backup has run yet.</p>
            @endif
        </div>
    </div>
</div>
