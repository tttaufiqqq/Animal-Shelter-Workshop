<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assigned Rescues - Stray Animals Shelter</title>

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body class="bg-white min-h-screen">

    <!-- Include Navbar -->
    @include('navbar')

    <div class="max-w-7xl mx-auto mt-10 p-4 md:p-6 pb-10">
        <!-- Header Section -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-6 md:p-8">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center">
                        <span class="text-4xl md:text-5xl mr-4">🚑</span>
                        <div>
                            <h2 class="text-3xl md:text-4xl font-bold">My Assigned Rescues</h2>
                            <p class="text-blue-100 text-sm md:text-base mt-1">Manage your assigned animal rescue missions</p>
                        </div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                        <p class="text-sm font-semibold">Total Assigned: <span class="text-2xl">{{ $rescues->total() }}</span></p>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                <div class="bg-green-50 border-l-4 border-green-600 text-green-700 p-4">
                    <p class="font-semibold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Filter Tabs -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden mb-6">
            <div class="flex flex-wrap gap-2 p-4">
                <a href="{{ route('rescues.index') }}" 
                   class="px-4 py-2 rounded-lg font-semibold transition {{ !request('status') ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    All Rescues
                </a>
                <a href="{{ route('rescues.index', ['status' => 'Scheduled']) }}" 
                   class="px-4 py-2 rounded-lg font-semibold transition {{ request('status') == 'Scheduled' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Scheduled
                </a>
                <a href="{{ route('rescues.index', ['status' => 'In Progress']) }}" 
                   class="px-4 py-2 rounded-lg font-semibold transition {{ request('status') == 'In Progress' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    In Progress
                </a>
                <a href="{{ route('rescues.index', ['status' => 'Success']) }}" 
                   class="px-4 py-2 rounded-lg font-semibold transition {{ request('status') == 'Success' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Success
                </a>
                <a href="{{ route('rescues.index', ['status' => 'Failed']) }}" 
                   class="px-4 py-2 rounded-lg font-semibold transition {{ request('status') == 'Failed' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Failed
                </a>
            </div>
        </div>

        @if($rescues->isEmpty())
            <div class="bg-white rounded-2xl shadow-2xl p-12 text-center">
                <div class="text-6xl mb-4">📋</div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">No rescues assigned yet</h3>
                <p class="text-gray-600 mb-6 text-lg">You don't have any rescue missions assigned at the moment.</p> 
            </div>
        @else
            <div class="grid grid-cols-1 gap-6">
                @foreach($rescues as $rescue)
                    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                        <div class="p-6 md:p-8">
                            <div class="flex justify-between items-start mb-6 flex-wrap gap-3">
                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="text-2xl">🚨</span>
                                        <h3 class="text-2xl font-bold text-gray-800">Rescue #{{ $rescue->id }} - Report #{{ $rescue->report->id }}</h3>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        Assigned on {{ $rescue->created_at->format('M d, Y - h:i A') }}
                                    </p>
                                    @if($rescue->date)
                                        <p class="text-sm text-gray-600">
                                            Scheduled for: {{ \Carbon\Carbon::parse($rescue->date)->format('M d, Y - h:i A') }}
                                        </p>
                                    @endif
                                </div>
                                <div class="flex flex-col gap-2 items-end">
                                    <span class="px-4 py-2 rounded-full text-sm font-semibold
                                        @if($rescue->status == 'Scheduled') bg-gradient-to-r from-yellow-400 to-yellow-500 text-yellow-900
                                        @elseif($rescue->status == 'In Progress') bg-gradient-to-r from-blue-500 to-blue-600 text-white
                                        @elseif($rescue->status == 'Success') bg-gradient-to-r from-green-500 to-green-600 text-white
                                        @elseif($rescue->status == 'Failed') bg-gradient-to-r from-red-500 to-red-600 text-white
                                        @endif">
                                        {{ $rescue->status }}
                                    </span>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                                        Report: {{ $rescue->report->report_status }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                {{-- Location Details --}}
                                <div class="bg-blue-50 border-l-4 border-blue-600 p-6 rounded-lg">
                                    <h4 class="font-bold text-gray-800 mb-4 text-lg">Location Details</h4>
                                    <div class="space-y-3 text-sm">
                                        <div>
                                            <span class="font-semibold text-gray-700">Address:</span>
                                            <p class="text-gray-800 mt-1">{{ $rescue->report->address }}</p>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <span class="font-semibold text-gray-700">City:</span>
                                                <p class="text-gray-800 mt-1">{{ $rescue->report->city }}</p>
                                            </div>
                                            <div>
                                                <span class="font-semibold text-gray-700">State:</span>
                                                <p class="text-gray-800 mt-1">{{ $rescue->report->state }}</p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <span class="font-semibold text-gray-700">Latitude:</span>
                                                <p class="text-gray-800 mt-1">{{ $rescue->report->latitude }}</p>
                                            </div>
                                            <div>
                                                <span class="font-semibold text-gray-700">Longitude:</span>
                                                <p class="text-gray-800 mt-1">{{ $rescue->report->longitude }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    @if($rescue->report->description)
                                        <div class="mt-4 pt-4 border-t border-blue-200">
                                            <span class="font-semibold text-gray-700 text-sm">Description:</span>
                                            <p class="text-gray-800 mt-2">{{ $rescue->report->description }}</p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Map --}}
                                <div>
                                    <h4 class="font-bold text-gray-800 mb-3 text-lg">Map Location</h4>
                                    <div id="map-{{ $rescue->id }}" class="rounded-xl shadow-lg" style="height: 250px;"></div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="mt-6 pt-6 border-t border-gray-200 flex justify-end gap-3 flex-wrap">
                                <a href="{{ route('rescues.show', $rescue->id) }}" 
                                   class="px-5 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 transition duration-300 shadow-lg">
                                    View Full Report
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6 bg-white rounded-xl shadow-lg p-4">
                {{ $rescues->links() }}
            </div>
        @endif
    </div>

    {{-- Image Modal --}}
    <div id="imageModal" class="hidden fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4" onclick="closeImageModal()">
        <div class="relative max-w-6xl max-h-full">
            <button onclick="closeImageModal()" class="absolute -top-12 right-0 text-white hover:text-gray-300 transition">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <img id="modalImage" src="" alt="Full size image" class="max-w-full max-h-screen rounded-xl shadow-2xl">
        </div>
    </div>

    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize maps for each rescue
            @foreach($rescues as $rescue)
                const map{{ $rescue->id }} = L.map('map-{{ $rescue->id }}').setView([{{ $rescue->report->latitude }}, {{ $rescue->report->longitude }}], 15);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map{{ $rescue->id }});

                L.marker([{{ $rescue->report->latitude }}, {{ $rescue->report->longitude }}]).addTo(map{{ $rescue->id }});
            @endforeach
        });

        // Image modal functions
        function openImageModal(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('imageModal').classList.remove('hidden');
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
        }
    </script>
</body>
</html>