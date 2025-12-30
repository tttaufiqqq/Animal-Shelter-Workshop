@php
$breadcrumbs = [
    ['label' => 'Stray Reports', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>']
];
@endphp

<x-admin-layout title="Stray Animal Reports" :breadcrumbs="$breadcrumbs">
    {{-- Additional Styles --}}
    @push('styles')
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
    @endpush

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

    {{-- Page Content --}}
    <div class="space-y-4">
        {{-- Page Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Stray Animal Reports</h1>
            <p class="text-sm text-gray-600 mt-1">
                <span class="inline-flex items-center gap-1">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Live updates enabled - New reports appear automatically
                </span>
            </p>
        </div>

        {{-- Livewire Component for Real-time Reports --}}
        @livewire('reports-table')
    </div>

    {{-- Images Modal --}}
    <div id="imagesModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" onclick="closeImagesModal()">
        <div class="bg-white rounded shadow-lg max-w-4xl w-full max-h-[90vh] overflow-auto" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center p-4 border-b sticky top-0 bg-white">
                <h3 class="text-lg font-semibold text-gray-900">Report Images</h3>
                <button onclick="closeImagesModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="imagesContainer" class="p-4 grid grid-cols-2 sm:grid-cols-3 gap-3"></div>
        </div>
    </div>

    {{-- Scripts --}}
    @push('scripts')
        <script>
            // Images modal functions
            function showImagesModal(reportId, images) {
                const container = document.getElementById('imagesContainer');
                container.innerHTML = '';

                if (!images || images.length === 0) {
                    container.innerHTML = '<p class="text-gray-500 col-span-full text-center py-8">No images available</p>';
                    document.getElementById('imagesModal').classList.remove('hidden');
                    return;
                }

                images.forEach(imagePath => {
                    const div = document.createElement('div');
                    div.className = 'cursor-pointer relative';

                    const img = document.createElement('img');
                    img.src = imagePath;
                    img.alt = 'Report Image';
                    img.className = 'w-full h-40 object-cover rounded border hover:opacity-75 transition';
                    img.onclick = () => openFullImage(imagePath);

                    // Add error handling for images that fail to load
                    img.onerror = function() {
                        this.onerror = null; // Prevent infinite loop
                        this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2VlZSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5OTkiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5JbWFnZSBub3QgZm91bmQ8L3RleHQ+PC9zdmc+';
                        this.className = 'w-full h-40 object-contain rounded border bg-gray-100';
                        const errorMsg = document.createElement('p');
                        errorMsg.className = 'text-xs text-red-500 mt-1 text-center';
                        errorMsg.textContent = 'Image not found';
                        this.parentElement.appendChild(errorMsg);
                    };

                    // Add loading state
                    img.onload = function() {
                        this.classList.add('loaded');
                    };

                    div.appendChild(img);
                    container.appendChild(div);
                });

                document.getElementById('imagesModal').classList.remove('hidden');
            }

            function closeImagesModal() {
                document.getElementById('imagesModal').classList.add('hidden');
            }

            function openFullImage(imageSrc) {
                window.open(imageSrc, '_blank');
            }

            // Close modals on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeImagesModal();
                }
            });

            // Livewire event listener for reports refresh
            document.addEventListener('livewire:init', () => {
                Livewire.on('reports-refreshed', () => {
                    // Show a brief success notification
                    const notification = document.createElement('div');
                    notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in-down';
                    notification.innerHTML = `
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="font-semibold">Reports refreshed successfully!</span>
                        </div>
                    `;
                    document.body.appendChild(notification);

                    // Remove notification after 3 seconds
                    setTimeout(() => {
                        notification.style.transition = 'opacity 0.3s ease-out';
                        notification.style.opacity = '0';
                        setTimeout(() => notification.remove(), 300);
                    }, 3000);
                });
            });
        </script>
    @endpush
</x-admin-layout>
