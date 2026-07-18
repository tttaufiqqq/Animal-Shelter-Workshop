{{-- index Orchestrator --}}
@php
$breadcrumbs = [
    ['label' => 'Audit Log', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>']
];
@endphp

<x-admin-layout title="Audit Trail Dashboard" :breadcrumbs="$breadcrumbs">
    @include('admin.audit.index.header')
    @include('admin.audit.index.overview')
    @include('admin.audit.index.categories')
    @include('admin.audit.index.recent-activity')
</x-admin-layout>
