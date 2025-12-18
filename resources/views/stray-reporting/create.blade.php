<!-- Modal Overlay -->
<div id="reportModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden my-8">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-6 relative">
            <button type="button" onclick="closeReportModal()" class="absolute top-4 right-4 text-white hover:text-gray-200 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="flex items-center mb-2">
                <span class="text-3xl mr-3">📝</span>
                <h2 class="text-2xl font-bold">Submit Stray Animal Report</h2>
            </div>
            <p class="text-purple-100">
                Help us locate and rescue stray animals in your area
            </p>
        </div>

        <!-- Offline Warning -->
        <div id="offlineWarning" class="hidden bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-yellow-700">
                    <strong>No Internet Connection.</strong> Map features may not work. Please connect to the internet for the best experience.
                </p>
            </div>
        </div>

        <!-- Validation Errors -->
        @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm text-red-700">
                    <strong>Please fix the following errors:</strong>
                    <ul class="list-disc list-inside mt-1">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        <!-- Form Section -->
        <div class="p-6 md:p-8 max-h-[calc(100vh-12rem)] overflow-y-auto">
            <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data" id="reportForm" class="space-y-5">
                @csrf

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
                        <div id="searchResults" class="hidden absolute z-[9999] w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-2xl max-h-60 overflow-y-auto"></div>
                        <p class="text-xs text-gray-600 mt-1">Type to search for a location in Malaysia</p>
                    </div>

                    <!-- GPS Button -->
                    <div class="mb-3">
                        <button type="button" id="gpsBtn"
                                class="flex items-center gap-2 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Use My Current Location
                        </button>
                        <p class="text-xs text-gray-600 mt-1">Or click on the map below to pin the location</p>
                    </div>

                    <!-- Map -->
                    <div class="mb-3">
                        <div id="map" class="rounded-lg shadow-md border border-gray-300" style="height: 300px;"></div>
                        <p class="text-xs text-red-600 mt-1 hidden" id="mapError">⚠️ Please select a location on the map</p>
                    </div>

                    <!-- Coordinates (Read-only) -->
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                            <input type="text" name="latitude" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm" readonly required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                            <input type="text" name="longitude" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm" readonly required>
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Address <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="address" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                    </div>
                </div>

                <!-- Step 2: City & State (Auto-filled and Disabled) -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-blue-900 mb-3 flex items-center">
                        <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-2">2</span>
                        Location Details (Auto-filled)
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                City <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="city" id="cityInput"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed"
                                   readonly required>
                            <p class="text-xs text-gray-500 mt-1">⚠️ Auto-filled based on pinned location</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                State <span class="text-red-600">*</span>
                            </label>
                            <select name="state" id="stateInput"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed appearance-none"
                                    disabled required>
                                <option value="">Select state</option>
                                @foreach(['Johor', 'Kedah', 'Kelantan', 'Malacca', 'Negeri Sembilan', 'Pahang', 'Penang', 'Perak', 'Perlis', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'Kuala Lumpur', 'Putrajaya', 'Labuan'] as $state)
                                    <option value="{{ $state }}">{{ $state }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">⚠️ Auto-filled based on pinned location</p>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Animal Information -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-green-900 mb-3 flex items-center">
                        <span class="bg-green-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-2">3</span>
                        Animal Condition & Priority
                    </h3>

                    <!-- Priority Description Dropdown -->
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Situation / Urgency Level <span class="text-red-600">*</span>
                        </label>
                        <select name="description" id="descriptionSelect"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" required>
                            <option value="">-- Select situation --</option>
                            <optgroup label="🚨 URGENT - Immediate Action Required">
                                <option value="Injured animal - Critical condition" data-priority="critical">🚨 Injured animal - Critical condition</option>
                                <option value="Trapped animal - Immediate rescue needed" data-priority="critical">🚨 Trapped animal - Immediate rescue needed</option>
                                <option value="Aggressive animal - Public safety risk" data-priority="critical">🚨 Aggressive animal - Public safety risk</option>
                            </optgroup>
                            <optgroup label="⚠️ HIGH PRIORITY - Needs Attention Soon">
                                <option value="Sick animal - Needs medical attention" data-priority="high">⚠️ Sick animal - Needs medical attention</option>
                                <option value="Mother with puppies/kittens - Family rescue" data-priority="high">⚠️ Mother with puppies/kittens - Family rescue</option>
                                <option value="Young animal (puppy/kitten) - Vulnerable" data-priority="high">⚠️ Young animal (puppy/kitten) - Vulnerable</option>
                                <option value="Malnourished animal - Needs care" data-priority="high">⚠️ Malnourished animal - Needs care</option>
                            </optgroup>
                            <optgroup label="ℹ️ STANDARD - Non-urgent">
                                <option value="Healthy stray - Needs rescue" data-priority="normal">ℹ️ Healthy stray - Needs rescue</option>
                                <option value="Abandoned pet - Recent" data-priority="normal">ℹ️ Abandoned pet - Recent</option>
                                <option value="Friendly stray - Approachable" data-priority="normal">ℹ️ Friendly stray - Approachable</option>
                            </optgroup>
                            <optgroup label="Other">
                                <option value="Other" data-priority="normal">Other (please specify in additional notes)</option>
                            </optgroup>
                        </select>
                        <p class="text-xs text-gray-600 mt-1">This helps caretakers prioritize rescues based on urgency</p>
                    </div>

                    <!-- Additional Notes (Optional) -->
                    <div id="additionalNotesSection" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Additional Notes (Optional)
                        </label>
                        <textarea name="additional_notes" rows="2" id="additionalNotes"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                  placeholder="Add more details about the animal's condition, behavior, or location..."></textarea>
                    </div>
                </div>

                <!-- Step 4: Upload Images -->
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-orange-900 mb-3 flex items-center">
                        <span class="bg-orange-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-2">4</span>
                        Upload Images
                    </h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Photos of the Animal <span class="text-red-600">*</span>
                        </label>
                        <input type="file" name="images[]" multiple accept="image/*" id="imageInput"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        <p class="text-xs text-gray-600 mt-1">📷 Upload 1-5 images (max 5MB each). Clear photos help caretakers identify the animal.</p>
                        <div id="imagePreview" class="mt-2 grid grid-cols-3 gap-2"></div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="closeReportModal()"
                            class="px-6 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn"
                            class="px-6 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-purple-800 transition shadow-lg">
                        Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toastContainer" class="fixed top-4 right-4 z-[100] space-y-2"></div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      onerror="console.warn('Leaflet CSS failed to load')"/>

<!-- Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        onerror="window.LEAFLET_FAILED = true"></script>
<script src="{{ asset('js/map-utils.js') }}"></script>

<script>
    let map, marker;
    let mapInitialized = false;
    let locationPinned = false;

    // Toast notification
    function showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        const colors = {
            error: 'bg-red-500',
            success: 'bg-green-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };

        const toast = document.createElement('div');
        toast.className = `px-4 py-3 rounded-lg shadow-lg text-white font-medium ${colors[type]} transform transition-all`;
        toast.textContent = message;

        container.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    // Malaysian state name variations for better matching
    const stateVariations = {
        'Johor': ['johor', 'johore'],
        'Kedah': ['kedah'],
        'Kelantan': ['kelantan'],
        'Malacca': ['malacca', 'melaka'],
        'Negeri Sembilan': ['negeri sembilan', 'n. sembilan', 'n sembilan'],
        'Pahang': ['pahang'],
        'Penang': ['penang', 'pulau pinang', 'p. pinang'],
        'Perak': ['perak'],
        'Perlis': ['perlis'],
        'Sabah': ['sabah'],
        'Sarawak': ['sarawak'],
        'Selangor': ['selangor'],
        'Terengganu': ['terengganu', 'trengganu'],
        'Kuala Lumpur': ['kuala lumpur', 'kl', 'w.p. kuala lumpur', 'federal territory of kuala lumpur'],
        'Putrajaya': ['putrajaya', 'w.p. putrajaya'],
        'Labuan': ['labuan', 'w.p. labuan']
    };

    // Match state to dropdown options
    function matchStateToDropdown(stateFromAPI) {
        const stateSelect = document.querySelector('select[name="state"]');
        const stateLower = stateFromAPI.toLowerCase();

        // Try to match against variations
        for (const [officialName, variations] of Object.entries(stateVariations)) {
            if (variations.some(variant => stateLower.includes(variant))) {
                stateSelect.value = officialName;
                return true;
            }
        }

        return false;
    }

    // Update location and auto-fill city/state
    async function updateLocation(lat, lng) {
        // Update coordinates
        document.querySelector('input[name="latitude"]').value = lat.toFixed(6);
        document.querySelector('input[name="longitude"]').value = lng.toFixed(6);

        // Add/update marker
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng]).addTo(map);
        }

        map.setView([lat, lng], 15);
        locationPinned = true;

        // Hide errors
        document.getElementById('mapError').classList.add('hidden');
        document.getElementById('cityStateError')?.classList.add('hidden');

        // Try to get address details
        try {
            const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1&accept-language=en`,
                { signal: AbortSignal.timeout(8000) }
            );

            if (!response.ok) throw new Error('Geocoding failed');

            const data = await response.json();
            if (data && data.address) {
                const addr = data.address;
                const address = data.display_name || '';
                const city = addr.city || addr.town || addr.village || addr.suburb || addr.county || '';
                const state = addr.state || '';

                // Update fields
                document.querySelector('input[name="address"]').value = address;
                document.querySelector('input[name="city"]').value = city;

                // Match state using improved logic
                const stateMatched = matchStateToDropdown(state);

                if (city && stateMatched) {
                    showToast('Location pinned successfully!', 'success');
                } else if (city && !stateMatched) {
                    showToast('Location pinned. Please verify the state manually.', 'warning');
                } else {
                    showToast('Location pinned. Please fill city and state manually.', 'warning');
                }
            }
        } catch (error) {
            console.warn('Reverse geocode failed:', error);
            showToast('Location pinned, but address details unavailable. Please fill manually.', 'warning');
        }
    }

    // Get current location via GPS with timeout handling
    function getCurrentLocation() {
        if (!navigator.geolocation) {
            showToast('Geolocation not supported by your browser', 'error');
            return;
        }

        const btn = document.getElementById('gpsBtn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '⏳ Getting location...';
        btn.disabled = true;

        // Set up 10-second timeout
        const timeoutId = setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            showToast('Location detection timed out. Please search manually.', 'error');
        }, 10000);

        navigator.geolocation.getCurrentPosition(
            (position) => {
                clearTimeout(timeoutId);
                updateLocation(position.coords.latitude, position.coords.longitude);
                btn.innerHTML = originalText;
                btn.disabled = false;
            },
            (error) => {
                clearTimeout(timeoutId);
                let errorMessage = 'Failed to get your location. Please search manually.';

                switch(error.code) {
                    case error.TIMEOUT:
                        errorMessage = 'Location detection timed out. Please search manually.';
                        break;
                    case error.PERMISSION_DENIED:
                        errorMessage = 'Location permission denied. Please search manually.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = 'Location unavailable. Please search manually.';
                        break;
                }

                showToast(errorMessage, 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    // Initialize map
    function initializeMap() {
        if (mapInitialized || typeof L === 'undefined') return;

        try {
            map = L.map('map').setView([3.139, 101.6869], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Click to pin
            map.on('click', (e) => {
                updateLocation(e.latlng.lat, e.latlng.lng);
            });

            mapInitialized = true;
        } catch (error) {
            console.error('Map initialization failed:', error);
            showToast('Map failed to load. Please check your internet connection.', 'error');
        }
    }

    // Enhanced form validation
    document.getElementById('reportForm').addEventListener('submit', function(e) {
        // Enable state field before submission (it's disabled for auto-fill UX)
        const stateField = document.querySelector('select[name="state"]');
        stateField.disabled = false;

        const lat = document.querySelector('input[name="latitude"]').value;
        const lng = document.querySelector('input[name="longitude"]').value;
        const city = document.querySelector('input[name="city"]').value;
        const state = stateField.value;
        const images = document.getElementById('imageInput').files;

        // Validate location
        if (!lat || !lng) {
            e.preventDefault();
            document.getElementById('mapError').classList.remove('hidden');
            showToast('Please pin a location on the map!', 'error');
            return false;
        }

        // Validate city and state together
        if (!city || !state || state === '') {
            e.preventDefault();

            // Add error message below state field if not exists
            let errorDiv = document.getElementById('cityStateError');
            if (!errorDiv) {
                errorDiv = document.createElement('p');
                errorDiv.id = 'cityStateError';
                errorDiv.className = 'text-xs text-red-600 mt-1';
                errorDiv.innerHTML = '⚠️ Both city and state are required';
                document.querySelector('select[name="state"]').parentElement.appendChild(errorDiv);
            }
            errorDiv.classList.remove('hidden');

            showToast('Both city and state are required!', 'error');

            // Scroll to the location details section
            document.querySelector('input[name="city"]').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }

        // Validate images
        if (images.length === 0) {
            e.preventDefault();
            showToast('Please upload at least one image!', 'error');
            return false;
        }

        // File size validation
        for (let file of images) {
            if (file.size > 5 * 1024 * 1024) {
                e.preventDefault();
                showToast(`File "${file.name}" is too large (max 5MB)`, 'error');
                return false;
            }
        }

        return true;
    });

    // Show additional notes if "Other" is selected
    document.getElementById('descriptionSelect').addEventListener('change', function() {
        const notesSection = document.getElementById('additionalNotesSection');
        if (this.value === 'Other') {
            notesSection.classList.remove('hidden');
            document.getElementById('additionalNotes').required = true;
        } else {
            notesSection.classList.add('hidden');
            document.getElementById('additionalNotes').required = false;
        }
    });

    // Image preview
    document.getElementById('imageInput').addEventListener('change', function() {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';

        Array.from(this.files).slice(0, 5).forEach(file => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'w-full h-20 object-cover rounded border';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    });

    // Location search functionality
    let searchTimeout;
    const searchInput = document.getElementById('locationSearch');
    const searchResults = document.getElementById('searchResults');

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 3) {
            searchResults.classList.add('hidden');
            return;
        }

        searchTimeout = setTimeout(async () => {
            try {
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=my&addressdetails=1&limit=5`,
                    { signal: AbortSignal.timeout(5000) }
                );

                if (!response.ok) throw new Error('Search failed');

                const results = await response.json();

                if (results.length === 0) {
                    searchResults.innerHTML = '<div class="p-3 text-sm text-gray-500">No results found</div>';
                    searchResults.classList.remove('hidden');
                    return;
                }

                // Display results
                searchResults.innerHTML = results.map(result => {
                    const addr = result.address || {};
                    const city = addr.city || addr.town || addr.village || addr.suburb || '';
                    const state = addr.state || '';
                    const displayName = result.display_name;

                    return `
                        <div class="p-3 hover:bg-purple-50 cursor-pointer border-b border-gray-200 search-result-item"
                             data-lat="${result.lat}"
                             data-lon="${result.lon}"
                             data-display="${displayName}">
                            <div class="font-medium text-sm text-gray-900">${city || 'Unknown'}, ${state || 'Unknown'}</div>
                            <div class="text-xs text-gray-600 mt-1">${displayName}</div>
                        </div>
                    `;
                }).join('');

                searchResults.classList.remove('hidden');

                // Add click handlers to results
                document.querySelectorAll('.search-result-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const lat = parseFloat(this.getAttribute('data-lat'));
                        const lon = parseFloat(this.getAttribute('data-lon'));

                        updateLocation(lat, lon);
                        searchInput.value = '';
                        searchResults.classList.add('hidden');
                    });
                });

            } catch (error) {
                console.error('Location search error:', error);
                searchResults.innerHTML = '<div class="p-3 text-sm text-red-500">Search failed. Please try again.</div>';
                searchResults.classList.remove('hidden');
            }
        }, 500); // Debounce 500ms
    });

    // Hide search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });

    // Modal controls
    function openReportModal() {
        document.getElementById('reportModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Check online status
        if (!navigator.onLine) {
            document.getElementById('offlineWarning').classList.remove('hidden');
        }

        setTimeout(() => {
            if (!mapInitialized) {
                initializeMap();
            } else if (map) {
                map.invalidateSize();
            }
        }, 100);
    }

    function closeReportModal() {
        document.getElementById('reportModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Event listeners
    document.getElementById('gpsBtn').addEventListener('click', getCurrentLocation);
    document.getElementById('reportModal').addEventListener('click', (e) => {
        if (e.target === e.currentTarget) closeReportModal();
    });

    // Online/offline detection
    window.addEventListener('online', () => {
        document.getElementById('offlineWarning').classList.add('hidden');
        showToast('You are back online', 'success');
    });

    window.addEventListener('offline', () => {
        document.getElementById('offlineWarning').classList.remove('hidden');
        showToast('You are offline. Map may not work.', 'warning');
    });
</script>

<style>
    /* Smooth animations */
    #toastContainer > div {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* Disabled input styling */
    input:disabled, select:disabled {
        cursor: not-allowed !important;
    }
</style>
