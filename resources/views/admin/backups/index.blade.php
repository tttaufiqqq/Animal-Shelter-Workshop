{{-- admin/backups/index Orchestrator --}}
@php
$breadcrumbs = [
    ['label' => 'Backups', 'icon' => '<svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>']
];

$latestStatus = $latest['status'] ?? 'failed';
@endphp

<x-admin-layout title="Database Backups" :breadcrumbs="$breadcrumbs">
    @include('admin.backups.index.status-banner')
    @include('admin.backups.index.stats')
    @include('admin.backups.index.included')
    @include('admin.backups.index.history')
</x-admin-layout>
