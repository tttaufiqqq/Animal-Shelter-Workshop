{{-- all Orchestrator --}}
@php
$breadcrumbs = [
    ['label' => 'Audit Log', 'url' => route('admin.audit.index'), 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
    ['label' => 'All Logs', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>']
];

$categoryColors = [
    'authentication' => 'bg-indigo-100 text-indigo-800',
    'payment' => 'bg-green-100 text-green-800',
    'animal' => 'bg-purple-100 text-purple-800',
    'rescue' => 'bg-blue-100 text-blue-800',
    'user_management' => 'bg-cyan-100 text-cyan-800',
    'authorization' => 'bg-amber-100 text-amber-800',
    'shelter' => 'bg-teal-100 text-teal-800',
    'booking' => 'bg-pink-100 text-pink-800',
    'system' => 'bg-gray-100 text-gray-800',
];
@endphp

<x-admin-layout title="All Audit Logs" :breadcrumbs="$breadcrumbs">
    @include('admin.audit.all.header')
    @include('admin.audit.all.filters')
    @include('admin.audit.all.logs-table')
</x-admin-layout>
