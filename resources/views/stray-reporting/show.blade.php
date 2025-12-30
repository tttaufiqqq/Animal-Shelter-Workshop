@php
$breadcrumbs = [
    ['label' => 'Stray Reports', 'url' => route('reports.index'), 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
    ['label' => 'Report #' . $report->id]
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
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-2xl font-bold text-gray-900">Report #{{ $report->id }}</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                        @if($report->report_status === 'Pending') bg-yellow-100 text-yellow-800
                        @elseif($report->report_status === 'Assigned') bg-blue-100 text-blue-800
                        @elseif($report->report_status === 'In Progress') bg-purple-100 text-purple-800
                        @elseif($report->report_status === 'Completed') bg-green-100 text-green-800
                        @elseif($report->report_status === 'Rejected') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ $report->report_status }}
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ $report->created_at->format('M j, Y \a\t g:i A') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $report->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('reports.index') }}"
               class="inline-flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-50 text-sm font-medium shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Reports
            </a>
        </div>

        {{-- Main Content --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Report Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Report Information Card -->
            <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="bg-purple-600 p-2 rounded">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">Report Details</h2>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Reporter Information -->
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <label class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Reported By
                        </label>
                        <div class="flex items-center gap-4 bg-gray-50 p-4 rounded">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-full bg-purple-600 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">{{ $report->user->name ?? 'Unknown' }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-sm text-gray-600">{{ $report->user->email ?? 'No email available' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Information -->
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <label class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Location Details
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-3 rounded">
                                <p class="text-xs font-medium text-gray-500 mb-1">Address</p>
                                <p class="text-sm text-gray-900">{{ $report->address }}</p>
                            </div>

                            <div class="bg-gray-50 p-3 rounded">
                                <p class="text-xs font-medium text-gray-500 mb-1">City</p>
                                <p class="text-sm text-gray-900">{{ $report->city }}</p>
                            </div>

                            <div class="bg-gray-50 p-3 rounded">
                                <p class="text-xs font-medium text-gray-500 mb-1">State</p>
                                <p class="text-sm text-gray-900">{{ $report->state }}</p>
                            </div>

                            <div class="bg-gray-50 p-3 rounded">
                                <p class="text-xs font-medium text-gray-500 mb-1">Coordinates</p>
                                <p class="text-sm font-mono text-gray-900">{{ number_format($report->latitude, 6) }}, {{ number_format($report->longitude, 6) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <label class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                            </svg>
                            Description
                        </label>
                        <div class="bg-gray-50 p-4 rounded">
                            @if($report->description)
                                <p class="text-sm text-gray-900 leading-relaxed whitespace-pre-wrap">{{ $report->description }}</p>
                            @else
                                <p class="text-sm text-gray-400 italic">No description provided</p>
                            @endif
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div>
                        <label class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Timeline
                        </label>
                        <div class="bg-gray-50 p-4 rounded">
                            <p class="text-xs font-medium text-gray-500">Submitted</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $report->created_at->format('M j, Y \a\t g:i A') }}</p>
                            <p class="text-xs text-gray-600 mt-1">{{ $report->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Section -->
            <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="bg-green-600 p-2 rounded">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">Location Map</h2>
                        </div>
                        <a href="https://www.google.com/maps?q={{ $report->latitude }},{{ $report->longitude }}" target="_blank"
                           class="inline-flex items-center gap-2 text-xs font-medium text-purple-600 hover:text-purple-700 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Open in Google Maps
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <div id="map" class="h-96 rounded border border-gray-200 bg-gray-100"></div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Images Section -->
            <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="bg-pink-600 p-2 rounded">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">Images</h2>
                        </div>
                        @if($report->images && $report->images->count() > 0)
                            <span class="bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1 rounded-full">
                                {{ $report->images->count() }} {{ Str::plural('Photo', $report->images->count()) }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="p-6">
                    <div class="relative w-full h-64 bg-gray-100 rounded border border-gray-200 overflow-hidden">
                        <!-- Main Image Display -->
                        <div id="imageSwiperContent" class="w-full h-full flex items-center justify-center">
                            @if($report->images && $report->images->count() > 0)
                                <img src="{{ $report->images->first()->url }}"
                                    alt="Report Image 1"
                                    class="max-w-full max-h-full object-contain cursor-pointer"
                                    onclick="openImageModal(this.src)">
                            @else
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-sm mt-2">No images available</span>
                                </div>
                            @endif
                        </div>

                        <!-- Navigation Arrows -->
                        @if($report->images && $report->images->count() > 1)
                            <button id="prevImageBtn" class="absolute left-2 top-1/2 -translate-y-1/2 bg-white hover:bg-gray-50 text-gray-700 rounded-full w-8 h-8 flex items-center justify-center shadow">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button id="nextImageBtn" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white hover:bg-gray-50 text-gray-700 rounded-full w-8 h-8 flex items-center justify-center shadow">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>

                            <!-- Image Counter -->
                            <div id="imageCounter" class="absolute bottom-2 right-2 bg-purple-600 text-white px-2 py-1 rounded text-xs font-medium">
                                <span id="currentImageIndex">1</span> / <span id="totalImages">{{ $report->images->count() }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Thumbnail Strip -->
                    @if($report->images && $report->images->count() > 1)
                        <div class="mt-4">
                            <p class="text-xs font-medium text-gray-500 mb-2">Gallery</p>
                            <div class="overflow-x-auto">
                                <div class="flex gap-2">
                                    @foreach($report->images as $index => $image)
                                        <div onclick="goToImage({{ $index }})"
                                            class="flex-shrink-0 w-16 h-16 cursor-pointer rounded overflow-hidden border-2 {{ $index == 0 ? 'border-purple-500' : 'border-gray-200 hover:border-purple-400' }}"
                                            id="thumbnail-{{ $index }}">
                                            <img src="{{ $image->url }}"
                                                alt="Thumbnail {{ $loop->iteration }}"
                                                class="w-full h-full object-cover">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions Card -->
            <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="bg-blue-600 p-2 rounded">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">Admin Actions</h2>
                    </div>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Current Assignment Status -->
                    @if($report->rescue && $report->rescue->caretaker)
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-blue-900">Currently Assigned</p>
                                    <p class="text-sm text-gray-900 mt-1">{{ $report->rescue->caretaker->name }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-yellow-900">Not Yet Assigned</p>
                                    <p class="text-xs text-yellow-700 mt-1">Please assign a caretaker to handle this report</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Assign to Caretaker Form -->
                    <form action="{{ route('reports.assign-caretaker', $report->id) }}" method="POST" class="space-y-3" id="assignCaretakerForm">
                        @csrf
                        @method('PATCH')

                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ $report->rescue && $report->rescue->caretaker ? 'Reassign Caretaker' : 'Assign Caretaker' }}
                        </label>

                        <select name="caretaker_id" required id="caretakerSelect"
                                data-current-caretaker="{{ $report->rescue && $report->rescue->caretaker ? $report->rescue->caretakerID : '' }}"
                                class="w-full px-3 py-2 text-sm border @error('caretaker_id') border-red-500 @else border-gray-300 @enderror rounded focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white">
                            <option value="">Select caretaker...</option>
                            @foreach($caretakers as $caretaker)
                                <option value="{{ $caretaker->id }}"
                                        data-name="{{ $caretaker->name }}"
                                        {{ old('caretaker_id') == $caretaker->id || ($report->rescue && $report->rescue->caretakerID == $caretaker->id) ? 'selected' : '' }}>
                                    {{ $caretaker->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('caretaker_id')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        <button type="button" id="assignBtn" onclick="showAssignmentConfirmation()"
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded text-sm font-medium shadow flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span id="assignBtnText">{{ $report->rescue && $report->rescue->caretaker ? 'Update Assignment' : 'Assign Caretaker' }}</span>
                        </button>
                    </form>

                    <!-- Danger Zone -->
                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">Danger Zone</p>
                        <button type="button" onclick="openDeleteModal()"
                                class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm font-medium shadow flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Delete Report</span>
                        </button>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>

    {{-- Image Modal --}}
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md hidden z-[70] flex items-center justify-center p-4" onclick="closeImageModal()">
        <div class="max-w-6xl max-h-full relative" onclick="event.stopPropagation()">
            <button onclick="closeImageModal()" class="absolute -top-10 right-0 text-white hover:text-gray-300 transition-colors">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <img id="modalImage" src="" alt="Enlarged view" class="max-w-full max-h-screen rounded-2xl shadow-2xl">
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div id="deleteConfirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-[70] p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-red-500 to-red-600 text-white p-6 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <h2 class="text-lg font-bold">Delete Report</h2>
                            <p class="text-red-100 text-sm">Report #{{ $report->id }}</p>
                        </div>
                    </div>
                    <button onclick="closeDeleteModal()" class="text-white hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-6">
                <p class="text-gray-700 mb-4 text-sm font-medium">Are you sure you want to delete this report?</p>
                <div class="bg-red-50 border-l-4 border-red-500 p-3 rounded-lg">
                    <p class="text-xs font-semibold text-red-800 mb-1">Warning: This action cannot be undone!</p>
                    <p class="text-xs text-red-700">All information will be permanently deleted:</p>
                    <ul class="mt-2 text-xs text-red-700 list-disc list-inside space-y-1">
                        <li>Report details and location</li>
                        <li>All uploaded images</li>
                        <li>Assignment history</li>
                    </ul>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 p-4 border-t border-gray-200 flex gap-3 rounded-b-2xl">
                <button type="button"
                        onclick="closeDeleteModal()"
                        class="flex-1 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors text-sm">
                    Cancel
                </button>

                <form action="{{ route('reports.destroy', $report->id) }}" method="POST" class="flex-1" id="deleteReportForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            id="confirmDeleteBtn"
                            class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors text-sm flex items-center gap-2 justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span id="confirmDeleteBtnText">Delete</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Same Caretaker Error Modal --}}
    <div id="sameCaretakerErrorModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-[70] p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white p-6 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <h2 class="text-lg font-bold">Already Assigned</h2>
                            <p class="text-yellow-100 text-sm">Report #{{ $report->id }}</p>
                        </div>
                    </div>
                    <button onclick="closeSameCaretakerErrorModal()" class="text-white hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-6">
                <p class="text-gray-700 mb-4 text-sm font-medium">This report is already assigned to <strong id="currentCaretakerNameError"></strong>.</p>
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-yellow-900 mb-1">Cannot Reassign</p>
                            <p class="text-xs text-yellow-800 mt-2">Please select a different caretaker if you want to update the assignment.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 p-4 border-t border-gray-200 flex justify-end rounded-b-2xl">
                <button type="button"
                        onclick="closeSameCaretakerErrorModal()"
                        class="px-6 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors text-sm">
                    OK
                </button>
            </div>
        </div>
    </div>

    {{-- Assignment Confirmation Modal --}}
    <div id="assignmentConfirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-[70] p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-6 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <h2 class="text-lg font-bold">Confirm Assignment</h2>
                            <p class="text-purple-100 text-sm">Report #{{ $report->id }}</p>
                        </div>
                    </div>
                    <button onclick="closeAssignmentModal()" class="text-white hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-6">
                <p class="text-gray-700 mb-4 text-sm font-medium" id="assignmentConfirmMessage"></p>
                <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-purple-900 mb-1">Assignment Details</p>
                            <div class="space-y-2 mt-2">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-600">Report ID:</span>
                                    <span class="font-semibold text-gray-900">#{{ $report->id }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-600">Location:</span>
                                    <span class="font-semibold text-gray-900 text-right">{{ $report->city }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-600">New Caretaker:</span>
                                    <span class="font-semibold text-gray-900" id="newCaretakerName"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 p-4 border-t border-gray-200 flex gap-3 rounded-b-2xl">
                <button type="button"
                        onclick="closeAssignmentModal()"
                        class="flex-1 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors text-sm">
                    Cancel
                </button>

                <button type="button"
                        onclick="confirmAssignment()"
                        id="confirmAssignmentBtn"
                        class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors text-sm flex items-center gap-2 justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span id="confirmAssignmentBtnText">Confirm Assignment</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
// Initialize the map
const map = L.map('map').setView([{{ $report->latitude }}, {{ $report->longitude }}], 15);

// Add OpenStreetMap tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

// Add a marker for the report location
const marker = L.marker([{{ $report->latitude }}, {{ $report->longitude }}]).addTo(map);

// Add popup with location info
marker.bindPopup(`
    <div class="p-2">
        <strong class="text-sm">Report Location</strong><br>
        <span class="text-xs">{{ $report->address }}</span><br>
        <span class="text-xs">{{ $report->city }}, {{ $report->state }}</span>
    </div>
`).openPopup();

// Add a circle to highlight the area
const circle = L.circle([{{ $report->latitude }}, {{ $report->longitude }}], {
    color: '#9333ea',
    fillColor: '#9333ea',
    fillOpacity: 0.15,
    radius: 100
}).addTo(map);

// Disable/Enable Map Interactions
function disableMapInteractions() {
    // Close any open popups
    map.closePopup();

    // Disable all interactions
    map.dragging.disable();
    map.touchZoom.disable();
    map.doubleClickZoom.disable();
    map.scrollWheelZoom.disable();
    map.boxZoom.disable();
    map.keyboard.disable();
    if (map.tap) map.tap.disable();

    // Make map completely non-interactive
    document.getElementById('map').style.pointerEvents = 'none';
    document.getElementById('map').style.zIndex = '1';

    // Disable marker interactions
    marker.closePopup();
    marker.off('click');
}

function enableMapInteractions() {
    // Re-enable all interactions
    map.dragging.enable();
    map.touchZoom.enable();
    map.doubleClickZoom.enable();
    map.scrollWheelZoom.enable();
    map.boxZoom.enable();
    map.keyboard.enable();
    if (map.tap) map.tap.enable();

    // Restore interactivity
    document.getElementById('map').style.pointerEvents = 'auto';
    document.getElementById('map').style.zIndex = '';

    // Re-enable marker click
    marker.on('click', function() {
        this.openPopup();
    });
}

// Image Modal Functions
function openImageModal(imageSrc) {
    const modal = document.getElementById('imageModal');
    document.getElementById('modalImage').src = imageSrc;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    disableMapInteractions();
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
    enableMapInteractions();
}

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('imageModal').classList.contains('hidden')) {
        closeImageModal();
    }
});

// Image Swiper Data
let currentImages = [
    @if($report->images && $report->images->count() > 0)
        @foreach($report->images as $image)
            { path: "{{ $image->url }}" },
        @endforeach
    @endif
];
let currentImageIndex = 0;

// Display current image
function displayCurrentImage() {
    const content = document.getElementById('imageSwiperContent');

    if (currentImages.length === 0) return;

    // Update main image
    content.innerHTML = `<img src="${currentImages[currentImageIndex].path}"
                              class="max-w-full max-h-full object-contain cursor-pointer"
                              onclick="openImageModal(this.src)">`;

    // Update counter
    if (document.getElementById('currentImageIndex')) {
        document.getElementById('currentImageIndex').textContent = currentImageIndex + 1;
    }

    // Update thumbnails
    currentImages.forEach((_, index) => {
        const thumb = document.getElementById(`thumbnail-${index}`);
        if (thumb) {
            thumb.className = `flex-shrink-0 w-16 h-16 cursor-pointer rounded overflow-hidden border-2 ${
                index === currentImageIndex ? 'border-purple-500' : 'border-gray-200 hover:border-purple-400'
            }`;
        }
    });
}

// Go to image by index
function goToImage(index) {
    if (index >= 0 && index < currentImages.length) {
        currentImageIndex = index;
        displayCurrentImage();
    }
}

// Navigation buttons
function nextImage() {
    currentImageIndex = (currentImageIndex + 1) % currentImages.length;
    displayCurrentImage();
}

function prevImage() {
    currentImageIndex = (currentImageIndex - 1 + currentImages.length) % currentImages.length;
    displayCurrentImage();
}

// Event listeners
@if($report->images && $report->images->count() > 1)
    document.getElementById('prevImageBtn').addEventListener('click', prevImage);
    document.getElementById('nextImageBtn').addEventListener('click', nextImage);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') prevImage();
        if (e.key === 'ArrowRight') nextImage();
    });
@endif

// Initialize on page load
displayCurrentImage();

// ==================== ASSIGNMENT CONFIRMATION MODAL ====================

// Show same caretaker error modal
function showSameCaretakerError(caretakerName) {
    document.getElementById('currentCaretakerNameError').textContent = caretakerName;
    const modal = document.getElementById('sameCaretakerErrorModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    disableMapInteractions();
}

// Close same caretaker error modal
function closeSameCaretakerErrorModal() {
    const modal = document.getElementById('sameCaretakerErrorModal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
    enableMapInteractions();
}

// Show assignment confirmation modal
function showAssignmentConfirmation() {
    const caretakerSelect = document.getElementById('caretakerSelect');
    const selectedOption = caretakerSelect.options[caretakerSelect.selectedIndex];
    const selectedCaretakerId = caretakerSelect.value;
    const currentCaretakerId = caretakerSelect.dataset.currentCaretaker;
    const selectedCaretakerName = selectedOption.dataset.name || selectedOption.text;

    // Validate that a caretaker is selected
    if (!selectedCaretakerId) {
        alert('Please select a caretaker before submitting.');
        return;
    }

    // Check if trying to reassign to the same caretaker
    if (currentCaretakerId && selectedCaretakerId === currentCaretakerId) {
        showSameCaretakerError(selectedCaretakerName);
        return;
    }

    // Update modal content
    const confirmMessage = document.getElementById('assignmentConfirmMessage');
    const newCaretakerName = document.getElementById('newCaretakerName');

    if (currentCaretakerId) {
        confirmMessage.textContent = 'Are you sure you want to reassign this report to a new caretaker?';
    } else {
        confirmMessage.textContent = 'Are you sure you want to assign this report to the selected caretaker?';
    }

    newCaretakerName.textContent = selectedCaretakerName;

    // Show modal
    const modal = document.getElementById('assignmentConfirmModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    disableMapInteractions();
}

// Close assignment modal
function closeAssignmentModal() {
    const modal = document.getElementById('assignmentConfirmModal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
    enableMapInteractions();
}

// Confirm assignment and submit form
function confirmAssignment() {
    const confirmBtn = document.getElementById('confirmAssignmentBtn');
    const confirmBtnText = document.getElementById('confirmAssignmentBtnText');

    // Disable button
    confirmBtn.disabled = true;

    // Add spinner
    confirmBtn.innerHTML = `
        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Assigning...</span>
    `;

    // Submit the form
    document.getElementById('assignCaretakerForm').submit();
}

// Close modal when clicking outside
document.getElementById('sameCaretakerErrorModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeSameCaretakerErrorModal();
    }
});

document.getElementById('assignmentConfirmModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeAssignmentModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('sameCaretakerErrorModal')?.classList.contains('hidden')) {
            closeSameCaretakerErrorModal();
        } else if (!document.getElementById('assignmentConfirmModal')?.classList.contains('hidden')) {
            closeAssignmentModal();
        }
    }
});

// ==================== FORM SUBMISSION LOADING STATES ====================

// Delete Modal Functions
function openDeleteModal() {
    const modal = document.getElementById('deleteConfirmModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    disableMapInteractions();
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteConfirmModal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
    enableMapInteractions();
}

// Delete Report Form submission with loading state
document.getElementById('deleteReportForm').addEventListener('submit', function(e) {
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    const cancelBtn = document.getElementById('cancelDeleteBtn');
    const deleteIcon = document.getElementById('deleteIcon');
    const deleteText = document.getElementById('confirmDeleteBtnText');

    // Disable buttons
    deleteBtn.disabled = true;
    cancelBtn.disabled = true;

    // Replace content with spinner
    deleteBtn.innerHTML = `
        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Deleting...</span>
    `;

    // Allow form to submit
    return true;
});

// Close modal when clicking outside
document.getElementById('deleteConfirmModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('deleteConfirmModal')?.classList.contains('hidden')) {
        closeDeleteModal();
    }
});
        </script>
    @endpush
</x-admin-layout>
