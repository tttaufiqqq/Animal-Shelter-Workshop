@php
// Set route prefix for caretaker panel
$routePrefix = 'shelter-management';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Shelter Slots Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        /* Smooth line clamp */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

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
    </style>
</head>
<body class="bg-gray-50">
@include('navbar')

<div class="container mx-auto px-4 py-8">
    {{-- Database Warning Banner --}}
    @if(isset($dbDisconnected) && count($dbDisconnected) > 0)
        <div class="mb-4 flex items-center gap-2 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded-lg">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-yellow-800">Limited Connectivity</h3>
                <p class="text-sm text-yellow-700 mt-1">{{ count($dbDisconnected) }} database(s) currently unavailable. Some features may not work properly.</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach($dbDisconnected as $connection => $info)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            {{ $info['module'] }}
                        </span>
                    @endforeach
                </div>
            </div>
            <button onclick="this.parentElement.remove()" class="flex-shrink-0 text-yellow-400 hover:text-yellow-600 transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Shelter Management</h1>
        <p class="text-sm text-gray-600 mt-1">Manage sections, slots, categories, and inventory</p>
    </div>
    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="flex items-start gap-3 p-4 mb-6 bg-green-50 border-l-4 border-green-500 rounded">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-start gap-3 p-4 mb-6 bg-red-50 border border-red-200 rounded-xl shadow-sm">
            <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <p class="font-semibold text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    {{-- View Switcher Tabs --}}
    @include('shelter-management.view-tabs')

    {{-- Stats Overview Cards --}}
    @include('shelter-management.slots-stats')
    @include('shelter-management.sections-stats')
    @include('shelter-management.categories-stats')

    {{-- Search and Filter Sections --}}
    @include('shelter-management.slots-filters')
    @include('shelter-management.sections-filters')
    @include('shelter-management.categories-filters')

    {{-- Action Buttons --}}
    @include('shelter-management.action-buttons')

    {{-- Content Tables --}}
    @include('shelter-management.sections-table')
    @include('shelter-management.slots-table')
    @include('shelter-management.categories-table')
</div>

{{-- Modals --}}
@include('shelter-management.section-modal')
@include('shelter-management.slot-modal')
@include('shelter-management.category-modal')
@include('shelter-management.slot-detail-modal')
@include('shelter-management.inventory-create-modal', ['categories' => $categories])
@include('shelter-management.inventory-detail-modal', ['categories' => $categories])
@include('shelter-management.animal-detail-modal')

{{-- Loading Overlay --}}
@include('shelter-management.loading-overlay')

{{-- JavaScript --}}
@include('shelter-management.scripts')

</body>
</html>
