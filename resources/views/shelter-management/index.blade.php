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

<!-- Limited Connectivity Warning Banner -->
@if(isset($dbDisconnected) && count($dbDisconnected) > 0)
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 shadow-sm">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="ml-3 flex-1">
            <h3 class="text-sm font-semibold text-yellow-800">Limited Connectivity</h3>
            <p class="text-sm text-yellow-700 mt-1">{{ count($dbDisconnected) }} database(s) currently unavailable. You may experience limited functionality or missing data.</p>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($dbDisconnected as $connection => $info)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    {{ $info['module'] }}
                </span>
                @endforeach
            </div>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto flex-shrink-0 text-yellow-400 hover:text-yellow-600">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
        </button>
    </div>
</div>
@endif

<div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white py-16">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-5xl font-bold mb-4">Shelter Management</h1>
                <p class="text-xl text-purple-100">Manage sections, slots, categories, and inventory for the shelter</p>
            </div>
        </div>
    </div>
</div>

<div class="px-4 sm:px-6 lg:px-8 py-8">
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
@include('shelter-management.section-detail-modal')
@include('shelter-management.category-detail-modal')
@include('shelter-management.inventory-create-modal', ['categories' => $categories])
@include('shelter-management.inventory-detail-modal', ['categories' => $categories])
@include('shelter-management.animal-detail-modal')

{{-- Loading Overlay --}}
@include('shelter-management.loading-overlay')

{{-- JavaScript --}}
@include('shelter-management.scripts')

</body>
</html>
