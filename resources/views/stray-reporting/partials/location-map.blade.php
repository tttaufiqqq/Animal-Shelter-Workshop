<!-- Map Section -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Location Map</h2>
        <a href="https://www.google.com/maps/search/?api=1&query={{ $rescue->report->latitude }},{{ $rescue->report->longitude }}"
           target="_blank"
           class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Open in Maps
        </a>
    </div>
    <div class="p-6">
        <div id="map-{{ $mapId ?? 'default' }}" class="leaflet-map h-96 rounded border border-gray-200 bg-gray-100"></div>
    </div>
</div>
