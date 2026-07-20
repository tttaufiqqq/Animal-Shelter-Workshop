@php
    use App\Services\Backup\BackupPresenter;
@endphp

@if($latest && !empty($latest['targets']))
<div class="bg-white shadow rounded-xl p-6 mb-6">
    <h3 class="text-base font-semibold text-gray-900 mb-4">What's in the last backup</h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        @foreach($latest['targets'] as $name => $meta)
            @php $friendly = BackupPresenter::targetLabel($name); @endphp
            <div class="border border-gray-100 bg-gray-50 rounded-lg p-4 text-center">
                <div class="text-3xl mb-2">{{ $friendly['icon'] }}</div>
                <p class="text-sm font-medium text-gray-900">{{ $friendly['label'] }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ BackupPresenter::friendlySize($meta['bytes'] ?? 0) }}</p>
            </div>
        @endforeach
    </div>
</div>
@endif
