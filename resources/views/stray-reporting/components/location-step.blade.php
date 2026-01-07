<!-- Step 1: Location -->
<div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
    <h3 class="text-lg font-semibold text-purple-900 mb-3 flex items-center">
        <span class="bg-purple-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-2">1</span>
        Pin Location
    </h3>

    <!-- Location Search -->
    <div class="mb-3 relative">
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Search Location
        </label>
        <div class="relative">
            <input type="text" id="locationSearch"
                   class="w-full px-3 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                   placeholder="Search for city, state, or landmark..." autocomplete="off">
            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <div id="searchResults" class="hidden absolute z-[9999] w-full mt-1 bg-white border-2 border-cyan-200 rounded-lg shadow-2xl max-h-60 overflow-y-auto backdrop-blur-sm"></div>
        <p class="text-xs text-gray-600 mt-1">Type to search for a location in Malaysia</p>
    </div>

    <!-- GPS Button -->
    <div class="mb-3">
        <button type="button" id="gpsBtn"
                class="flex items-center gap-2 px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg hover:from-green-600 hover:to-emerald-700 transition shadow-md w-full justify-center disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span id="gpsBtnText">Use My Current Location</span>
        </button>
        <div id="gpsStatus" class="text-xs mt-1"></div>
    </div>

    <!-- Map -->
    <div class="mb-3 relative">
        <div id="map" class="rounded-lg shadow-2xl border-2 border-cyan-300" style="height: 300px;"></div>
        <div id="accuracyIndicator" class="hidden absolute top-2 right-2 bg-white/90 backdrop-blur-sm rounded-lg px-3 py-1 text-xs shadow-lg">
            <span class="font-semibold">Accuracy:</span> <span id="accuracyValue">--</span>m
        </div>
        <p class="text-xs text-red-600 mt-1 hidden" id="mapError">⚠️ Please select a location on the map</p>
    </div>

    <!-- Coordinates (Read-only) -->
    <div class="grid grid-cols-2 gap-3 mb-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
            <input type="text" name="latitude" id="latitudeInput" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm" readonly required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
            <input type="text" name="longitude" id="longitudeInput" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm" readonly required>
        </div>
    </div>

    <!-- Address -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Address <span class="text-red-600">*</span>
        </label>
        <input type="text" name="address" id="addressInput" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
    </div>
</div>
