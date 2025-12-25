<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assigned Rescues - Stray Animals Shelter</title>

    {{-- Vite Assets (compiled Tailwind) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
<body class="bg-gray-50 min-h-screen">

<!-- Include Navbar -->
@include('navbar')

<div class="mb-6 bg-purple-600 shadow p-6">
    <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-white">My Assigned Rescues</h1>
        <p class="text-purple-100 text-sm mt-1">Manage your assigned animal rescue missions</p>
    </div>
</div>

<div class="max-w-7xl mx-auto mt-10 p-4 md:p-6 pb-10">
    @if (session('success'))
        <div class="flex items-start gap-3 p-4 mb-6 bg-green-50 border border-green-200 rounded-xl shadow-sm">
            <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <p class="font-semibold text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Status Filter Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <!-- All Rescues -->
        <a href="{{ route('rescues.index') }}"
           class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ !request('status') && !request('priority') ? 'ring-2 ring-purple-500' : '' }}">
            <div class="text-3xl mb-2">🚑</div>
            <p class="text-2xl font-bold text-purple-700 mb-1">{{ $statusCounts->sum() }}</p>
            <p class="text-gray-600 text-sm">All Rescues</p>
        </a>

        <!-- Scheduled -->
        <a href="{{ route('rescues.index', ['status' => 'Scheduled']) }}"
           class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ request('status') == 'Scheduled' ? 'ring-2 ring-yellow-500' : '' }}">
            <div class="text-3xl mb-2">📅</div>
            <p class="text-2xl font-bold text-yellow-600 mb-1">{{ $statusCounts['Scheduled'] ?? 0 }}</p>
            <p class="text-gray-600 text-sm">Scheduled</p>
        </a>

        <!-- In Progress -->
        <a href="{{ route('rescues.index', ['status' => 'In Progress']) }}"
           class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ request('status') == 'In Progress' ? 'ring-2 ring-blue-500' : '' }}">
            <div class="text-3xl mb-2">🔄</div>
            <p class="text-2xl font-bold text-blue-600 mb-1">{{ $statusCounts['In Progress'] ?? 0 }}</p>
            <p class="text-gray-600 text-sm">In Progress</p>
        </a>

        <!-- Success -->
        <a href="{{ route('rescues.index', ['status' => 'Success']) }}"
           class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 {{ request('status') == 'Success' ? 'ring-2 ring-green-500' : '' }}">
            <div class="text-3xl mb-2">✅</div>
            <p class="text-2xl font-bold text-green-600 mb-1">{{ $statusCounts['Success'] ?? 0 }}</p>
            <p class="text-gray-600 text-sm">Success</p>
        </a>
    </div>

    <!-- Priority Filter -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900">Filter by Priority</h3>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('rescues.index', array_filter(['status' => request('status')])) }}"
               class="px-4 py-2 rounded-lg font-semibold transition {{ !request('priority') ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                All Priorities
            </a>
            <a href="{{ route('rescues.index', array_filter(['priority' => 'critical', 'status' => request('status')])) }}"
               class="px-4 py-2 rounded-lg font-semibold transition {{ request('priority') == 'critical' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                🚨 Critical
            </a>
            <a href="{{ route('rescues.index', array_filter(['priority' => 'high', 'status' => request('status')])) }}"
               class="px-4 py-2 rounded-lg font-semibold transition {{ request('priority') == 'high' ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                ⚠️ High
            </a>
            <a href="{{ route('rescues.index', array_filter(['priority' => 'normal', 'status' => request('status')])) }}"
               class="px-4 py-2 rounded-lg font-semibold transition {{ request('priority') == 'normal' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                ℹ️ Normal
            </a>

            @if(request('priority') || request('status'))
                <a href="{{ route('rescues.index') }}" class="ml-auto px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold transition">
                    Clear Filters
                </a>
            @endif
        </div>
    </div>

    @if($rescues->isEmpty())
        <div class="bg-white rounded shadow p-8 text-center">
            <p class="text-gray-600">No rescues assigned yet.</p>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-purple-500 to-purple-600">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Rescue #</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Priority</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Report #</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Location</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">City/State</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Assigned On</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($rescues as $rescue)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">RES {{ $rescue->id }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($rescue->priority == 'critical')
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800">🚨 Critical</span>
                                @elseif($rescue->priority == 'high')
                                    <span class="px-2 py-1 text-xs rounded bg-orange-100 text-orange-800">⚠️ High</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">ℹ️ Normal</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">REP {{ $rescue->report->id }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded
                                    @if($rescue->status == 'Scheduled') bg-yellow-100 text-yellow-800
                                    @elseif($rescue->status == 'In Progress') bg-blue-100 text-blue-800
                                    @elseif($rescue->status == 'Success') bg-green-100 text-green-800
                                    @elseif($rescue->status == 'Failed') bg-red-100 text-red-800
                                    @endif">
                                    {{ $rescue->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                <div class="max-w-xs truncate" title="{{ $rescue->report->address }}">{{ $rescue->report->address }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $rescue->report->city }}, {{ $rescue->report->state }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $rescue->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <a href="{{ route('rescues.show', $rescue->id) }}" class="text-purple-600 hover:underline mr-3">View</a>
                                <a href="#" onclick="event.preventDefault(); showMapModal({{ $rescue->report->latitude }}, {{ $rescue->report->longitude }}, '{{ addslashes($rescue->report->address) }}')" class="text-purple-600 hover:underline">Map</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $rescues->links() }}
        </div>
    @endif
</div>

{{-- Map Modal --}}
<div id="mapModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" onclick="closeMapModal()">
    <div class="bg-white rounded shadow-lg max-w-4xl w-full" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center p-4 border-b">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Rescue Location Map</h3>
                <p id="mapModalAddress" class="text-sm text-gray-600 mt-1"></p>
            </div>
            <button onclick="closeMapModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="modalMap" class="w-full" style="height: 400px;"></div>
    </div>
</div>

{{-- Leaflet JS --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let modalMapInstance = null;

    // Map modal functions
    function showMapModal(lat, lng, address) {
        document.getElementById('mapModalAddress').textContent = address;
        document.getElementById('mapModal').classList.remove('hidden');

        setTimeout(() => {
            if (modalMapInstance) {
                modalMapInstance.remove();
            }

            modalMapInstance = L.map('modalMap').setView([lat, lng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(modalMapInstance);

            L.marker([lat, lng]).addTo(modalMapInstance);
        }, 100);
    }

    function closeMapModal() {
        document.getElementById('mapModal').classList.add('hidden');
        if (modalMapInstance) {
            modalMapInstance.remove();
            modalMapInstance = null;
        }
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMapModal();
        }
    });
</script>
</body>
</html>
