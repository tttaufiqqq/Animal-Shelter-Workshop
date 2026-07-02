{{-- admin/caretakers/index Orchestrator --}}
@php
$breadcrumbs = [
    ['label' => 'Caretakers', 'icon' => '<svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>']
];
@endphp

<x-admin-layout title="Caretakers Management" :breadcrumbs="$breadcrumbs">
    @include('admin.caretakers.index.header-messages')
    @include('admin.caretakers.index.stats-table')
    @include('admin.caretakers.index.modal-scripts')
</x-admin-layout>
