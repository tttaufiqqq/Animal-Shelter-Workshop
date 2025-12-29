<x-admin-layout>
    {{-- Page Title --}}
    <x-slot name="title">Stray Animal Reports</x-slot>

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
            <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1">
                <p class="text-xs font-semibold text-yellow-800">Limited Connectivity - {{ count($dbDisconnected) }} database(s) unavailable. Some features may not work.</p>
            </div>
        </div>
    @endif

    {{-- Page Content --}}
    <div class="space-y-4">
        {{-- Page Header --}}
        <div class="bg-purple-600 shadow p-4 -mx-6 -mt-6 mb-6 rounded-b-xl">
            <h1 class="text-2xl font-bold text-white">Stray Animal Reports</h1>
            <p class="text-purple-100 text-xs mt-1">View and manage all submitted reports</p>
        </div>

        {{-- Success/Error Messages --}}
        @if(session('success'))
            <div class="flex items-start gap-2 p-3 bg-green-50 border-l-4 border-green-500 rounded">
                <svg class="w-4 h-4 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <p class="text-xs font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="flex items-start gap-2 p-3 bg-red-50 border-l-4 border-red-500 rounded">
                <svg class="w-4 h-4 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <p class="text-xs font-semibold text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Status Filter Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <!-- Total Reports -->
            <a href="{{ route('reports.index') }}"
               class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ !request('status') ? 'ring-2 ring-purple-500' : '' }}">
                <div class="text-2xl mb-1">📋</div>
                <p class="text-xl font-bold text-purple-700 mb-0.5">{{ $totalReports }}</p>
                <p class="text-gray-600 text-xs">Total</p>
            </a>

            <!-- Pending -->
            <a href="{{ route('reports.index', ['status' => 'Pending']) }}"
               class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ request('status') == 'Pending' ? 'ring-2 ring-yellow-500' : '' }}">
                <div class="text-2xl mb-1">⏳</div>
                <p class="text-xl font-bold text-yellow-600 mb-0.5">{{ $statusCounts['Pending'] ?? 0 }}</p>
                <p class="text-gray-600 text-xs">Pending</p>
            </a>

            <!-- Assigned -->
            <a href="{{ route('reports.index', ['status' => 'Assigned']) }}"
               class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ request('status') == 'Assigned' ? 'ring-2 ring-blue-500' : '' }}">
                <div class="text-2xl mb-1">📋</div>
                <p class="text-xl font-bold text-blue-600 mb-0.5">{{ $statusCounts['Assigned'] ?? 0 }}</p>
                <p class="text-gray-600 text-xs">Assigned</p>
            </a>

            <!-- In Progress -->
            <a href="{{ route('reports.index', ['status' => 'In Progress']) }}"
               class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ request('status') == 'In Progress' ? 'ring-2 ring-purple-500' : '' }}">
                <div class="text-2xl mb-1">🔄</div>
                <p class="text-xl font-bold text-purple-600 mb-0.5">{{ $statusCounts['In Progress'] ?? 0 }}</p>
                <p class="text-gray-600 text-xs">In Progress</p>
            </a>

            <!-- Completed -->
            <a href="{{ route('reports.index', ['status' => 'Completed']) }}"
               class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ request('status') == 'Completed' ? 'ring-2 ring-green-500' : '' }}">
                <div class="text-2xl mb-1">✅</div>
                <p class="text-xl font-bold text-green-600 mb-0.5">{{ $statusCounts['Completed'] ?? 0 }}</p>
                <p class="text-gray-600 text-xs">Completed</p>
            </a>
        </div>

        {{-- Search and Filter Form --}}
        <div class="bg-white rounded-lg shadow-lg p-4">
            <form method="GET" action="{{ route('reports.index') }}" class="space-y-3">
                <!-- Keep current status filter -->
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif

                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <h3 class="text-sm font-semibold text-gray-900">Search & Filter Reports</h3>
                </div>

                <div class="flex gap-3 items-end">
                    <!-- User Search -->
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Reporter Name or Email</label>
                        <div class="relative">
                            <input type="text"
                                   name="user_search"
                                   value="{{ request('user_search') }}"
                                   placeholder="Search by reporter name or email..."
                                   class="w-full px-3 py-2 pl-9 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>

                    <button type="submit"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-semibold transition duration-300 flex items-center gap-1.5 shadow-md">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Search
                    </button>
                    <a href="{{ route('reports.index') }}"
                       class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-semibold transition duration-300 flex items-center gap-1.5 shadow-md">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Clear
                    </a>

                    @if(request('user_search'))
                        <div class="flex items-center text-xs text-purple-600 font-medium">
                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <span>1 filter active</span>
                        </div>
                    @endif
                </div>
            </form>
        </div>

        {{-- Reports Table or Empty State --}}
        @if($reports->isEmpty())
            <div class="bg-white rounded shadow p-6 text-center">
                <p class="text-sm text-gray-600">No stray animal reports have been submitted.</p>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-purple-500 to-purple-600">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">Report </th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">Status</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">Location</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">City/State</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">Submitted</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">Images</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-white uppercase tracking-wider">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reports as $report)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">REP {{ $report->id }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded
                                        @if($report->report_status == 'Pending') bg-yellow-100 text-yellow-800
                                        @elseif($report->report_status == 'Assigned') bg-blue-100 text-blue-800
                                        @elseif($report->report_status == 'In Progress') bg-purple-100 text-purple-800
                                        @elseif($report->report_status == 'Completed') bg-green-100 text-green-800
                                        @elseif($report->report_status == 'Rejected') bg-red-100 text-red-800
                                        @endif">
                                        {{ $report->report_status }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-xs text-gray-900">
                                    <div class="max-w-xs truncate" title="{{ $report->address }}">{{ $report->address }}</div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                    {{ $report->city }}, {{ $report->state }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                    {{ $report->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                    @if($report->images->count() > 0)
                                        <div class="flex items-center gap-1 cursor-pointer" onclick="event.preventDefault(); showImagesModal({{ $report->id }}, {{ json_encode($report->images->map(fn($img) => $img->url)) }})">
                                            @foreach($report->images->take(3) as $image)
                                                <img src="{{ $image->url }}"
                                                     alt="Report image"
                                                     class="w-8 h-8 rounded-full object-cover border-2 border-white shadow-sm hover:scale-110 transition-transform"
                                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjMyIiBoZWlnaHQ9IjMyIiBmaWxsPSIjZTVlN2ViIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxMCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPj88L3RleHQ+PC9zdmc+'">
                                            @endforeach
                                            @if($report->images->count() > 3)
                                                <span class="text-xs text-gray-500 font-medium ml-1">+{{ $report->images->count() - 3 }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs">
                                    <a href="{{ route('reports.show', $report->id) }}"
                                       class="inline-flex items-center gap-1 bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition duration-200 shadow-sm hover:shadow-md">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $reports->links() }}
            </div>
        @endif
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
        </script>
    @endpush
</x-admin-layout>
