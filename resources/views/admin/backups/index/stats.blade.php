@php
    use App\Services\Backup\BackupPresenter;

    $latestFailed = ($latest['status'] ?? 'failed') === 'failed';
    $targetCount = count($latest['targets'] ?? []);
    $totalSize = BackupPresenter::totalBytes($latest['targets'] ?? []);
    $issueCount = BackupPresenter::orphanIssueCount($latest['orphans'] ?? []);
    $historyCount = count($runs);
@endphp

@if($latest)
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    <x-admin.stat-card
        title="Databases Backed Up"
        :value="$latestFailed ? '—' : $targetCount"
        color="blue"
        :icon="'<span class=\'text-2xl\'>🗄️</span>'"
    />

    <x-admin.stat-card
        title="Total Backup Size"
        :value="$latestFailed ? '—' : BackupPresenter::friendlySize($totalSize)"
        color="purple"
        :icon="'<span class=\'text-2xl\'>💾</span>'"
    />

    <x-admin.stat-card
        title="Data Check"
        :value="$latestFailed ? 'Not checked' : ($issueCount > 0 ? $issueCount . ' to review' : 'All good')"
        :color="$latestFailed ? 'red' : ($issueCount > 0 ? 'orange' : 'green')"
        :icon="'<span class=\'text-2xl\'>' . ($latestFailed ? '❌' : ($issueCount > 0 ? '⚠️' : '✅')) . '</span>'"
    />

    <x-admin.stat-card
        title="Backups Kept"
        :value="$historyCount"
        color="indigo"
        :icon="'<span class=\'text-2xl\'>🕐</span>'"
    />
</div>
@endif
