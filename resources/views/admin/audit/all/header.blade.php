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

    {{-- Page Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.audit.index') }}"
                   class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-white hover:bg-gray-50 border-2 border-gray-200 shadow-sm transition-all duration-200 hover:shadow-md hover:border-indigo-300 group"
                   title="Back to Audit Dashboard">
                    <svg class="w-5 h-5 text-gray-600 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <svg class="w-7 h-7 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        All Audit Logs
                    </h1>
                    <p class="text-gray-600 mt-1">Complete audit trail across all system categories</p>
                </div>
            </div>
            <a href="{{ route('admin.audit.export', ['category' => 'all']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export to CSV
            </a>
        </div>
    </div>
