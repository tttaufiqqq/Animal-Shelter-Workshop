@php
$breadcrumbs = [
    ['label' => 'Audit Log', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>']
];
@endphp

<x-admin-layout title="Audit Trail Dashboard" :breadcrumbs="$breadcrumbs">

    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Audit Trail Dashboard</h1>
        <p class="text-gray-600 mt-1">Monitor system activity and security events across all modules</p>
        <p class="text-sm text-gray-500 mt-2 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Last updated: {{ now()->format('d M Y, H:i') }}
        </p>
    </div>
