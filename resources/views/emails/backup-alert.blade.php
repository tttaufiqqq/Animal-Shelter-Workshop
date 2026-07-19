<h2>{{ $degraded ? 'Backup completed with warnings' : 'Backup failed' }}</h2>

<p><strong>Run:</strong> {{ $manifest['run_id'] ?? 'n/a' }}</p>
<p><strong>Status:</strong> {{ $manifest['status'] ?? 'failed' }}</p>
<p><strong>Time:</strong> {{ $manifest['finished_at'] ?? 'n/a' }}</p>

@if(!empty($manifest['message']))
<p>{{ $manifest['message'] }}</p>
@endif

@if(!empty($manifest['orphans']))
<h3>Logical foreign key orphans</h3>
<ul>
@foreach($manifest['orphans'] as $label => $count)
    @if($count)
    <li>{{ $label }}: {{ $count }} orphan(s)</li>
    @endif
@endforeach
</ul>
<p>See docs/10-backups.md for what this means and how to investigate.</p>
@endif
