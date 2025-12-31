@php
$breadcrumbs = [
    ['label' => 'Stray Reports', 'url' => route('reports.index'), 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
    ['label' => 'Report #' . $report->id, 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>']
];
@endphp

<x-admin-layout title="Report #{{ $report->id }}" :breadcrumbs="$breadcrumbs">
    {{-- Additional Styles --}}
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <style>
            /* Custom scrollbar */
            ::-webkit-scrollbar {
                width: 8px;
                height: 8px;
            }

            ::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 10px;
            }

            ::-webkit-scrollbar-thumb {
                background: #9333ea;
                border-radius: 10px;
            }

            ::-webkit-scrollbar-thumb:hover {
                background: #7e22ce;
            }

            /* Spinner animation */
            .animate-spin {
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        </style>
    @endpush

    {{-- Page Content --}}
    <div class="space-y-4">
        {{-- Page Header Component --}}
        <x-report-header :report="$report" />

        {{-- Main Content --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Report Details -->
            <div class="lg:col-span-2 space-y-6">
                {{-- Report Details Card Component --}}
                <x-report-details-card :report="$report" />

                {{-- Map Component --}}
                <x-report-map :report="$report" />
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                {{-- Images Gallery Component --}}
                <x-report-images :report="$report" />

                {{-- Actions Card Component --}}
                <x-report-actions :report="$report" :caretakers="$caretakers" />
            </div>
        </div>
    </div>

    {{-- Modals --}}
    <x-modals.image-modal />
    <x-modals.delete-modal :report="$report" />
    <x-modals.assignment-modals :report="$report" />

    {{-- Scripts --}}
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="{{ asset('js/reports/map-handler.js') }}"></script>
        <script src="{{ asset('js/reports/image-gallery.js') }}"></script>
        <script src="{{ asset('js/reports/assignment-handler.js') }}"></script>
        <script src="{{ asset('js/reports/delete-handler.js') }}"></script>
        <script src="{{ asset('js/reports/init.js') }}"></script>
    @endpush
</x-admin-layout>
