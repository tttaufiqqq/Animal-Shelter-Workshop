@php
$breadcrumbs = [
    ['label' => 'Audit Log', 'url' => route('admin.audit.index'), 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
    ['label' => 'Authentication', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>']
];
@endphp

<x-admin-layout title="Authentication Audit Logs" :breadcrumbs="$breadcrumbs">

    {{-- Page Header --}}
    @include('admin.audit.components.header')

    {{-- Filters --}}
    @include('admin.audit.components.filters')

    {{-- Logs Table --}}
    @include('admin.audit.components.logs-table', ['logs' => $logs, 'suspiciousUsers' => $suspiciousUsers])

    {{-- User Management Modal --}}
    @include('admin.audit.components.user-management-modal')

    {{-- Modal Scripts --}}
    @include('admin.audit.components.modal-scripts')

</x-admin-layout>
