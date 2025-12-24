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

        <!-- GPS Permission Instructions -->
        <div id="gpsInstructions" class="hidden bg-blue-50 border-l-4 border-blue-400 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm text-blue-700">
                    <strong>Location Access Required</strong>
                    <p class="mt-1">Please allow location access in your browser to use this feature.</p>
                    <button onclick="requestLocationPermission()" class="mt-2 px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                        Grant Permission
                    </button>
                </div>
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
                        <div id="map" class="rounded-lg shadow-md border border-gray-300" style="height: 300px;"></div>
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
                        </select>
                        <p class="text-xs text-gray-600 mt-1">This helps caretakers prioritize rescues based on urgency</p>
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
<script src="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.min.js"></script>

<style>
    /* Smooth animations */
    #toastContainer > div {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* Loading spinner */
    .animate-spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Accuracy indicator colors */
    .accuracy-good { color: #10B981; }
    .accuracy-medium { color: #F59E0B; }
    .accuracy-poor { color: #EF4444; }

    /* Map controls */
    .leaflet-control-geocoder {
        border-radius: 8px !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    }
</style>

<script>
    // =============================================
    // GPS LOCATION TRACKING - FIXED VERSION
    // =============================================

    // Global variables
    let map, marker, circle;
    let mapInitialized = false;
    let watchPositionId = null;
    let currentPosition = null;

    // Malaysian state mapping
    const malaysiaStates = {
        'Johor': ['johor', 'johore', 'johor bahru'],
        'Kedah': ['kedah', 'alor setar'],
        'Kelantan': ['kelantan', 'kota bharu'],
        'Malacca': ['malacca', 'melaka'],
        'Negeri Sembilan': ['negeri sembilan', 'n.sembilan', 'n sembilan', 'seremban'],
        'Pahang': ['pahang', 'kuantan'],
        'Penang': ['penang', 'pulau pinang', 'georgetown', 'george town'],
        'Perak': ['perak', 'ipoh'],
        'Perlis': ['perlis', 'kangar'],
        'Sabah': ['sabah', 'kota kinabalu'],
        'Sarawak': ['sarawak', 'kuching'],
        'Selangor': ['selangor', 'shah alam', 'petaling jaya'],
        'Terengganu': ['terengganu', 'kuala terengganu'],
        'Kuala Lumpur': ['kuala lumpur', 'kl'],
        'Putrajaya': ['putrajaya'],
        'Labuan': ['labuan', 'w.p. labuan']
    };

    // Toast notification system
    function showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        const colors = {
            error: 'bg-red-500',
            success: 'bg-green-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };

        const toast = document.createElement('div');
        toast.className = `px-4 py-3 rounded-lg shadow-lg text-white font-medium ${colors[type]} transform transition-all duration-300 flex items-center gap-2`;

        // Add icon based on type
        const icons = {
            error: '❌',
            success: '✅',
            warning: '⚠️',
            info: 'ℹ️'
        };

        toast.innerHTML = `
        <span class="text-lg">${icons[type]}</span>
        <span>${message}</span>
    `;

        container.appendChild(toast);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 5000);

        return toast;
    }

    // Check if browser supports geolocation
    function checkGeolocationSupport() {
        if (!navigator.geolocation) {
            showToast('Your browser does not support geolocation. Please update your browser.', 'error');
            document.getElementById('gpsBtn').disabled = true;
            document.getElementById('gpsBtnText').textContent = 'Not supported';
            return false;
        }
        return true;
    }

    // Request location permission
    async function requestLocationPermission() {
        if (!checkGeolocationSupport()) return;

        try {
            // Test permission with a simple request
            const permission = await navigator.permissions.query({ name: 'geolocation' });

            if (permission.state === 'granted') {
                startLocationTracking();
                document.getElementById('gpsInstructions').classList.add('hidden');
            } else if (permission.state === 'prompt') {
                // Show instructions
                document.getElementById('gpsInstructions').classList.remove('hidden');
                showToast('Please allow location access in the browser prompt.', 'info');
            } else {
                document.getElementById('gpsInstructions').classList.remove('hidden');
                showToast('Location access denied. Please enable it in browser settings.', 'error');
            }
        } catch (error) {
            console.error('Permission check failed:', error);
            // Fallback to direct geolocation request
            getCurrentLocation();
        }
    }

    // Get current location - MAIN FIXED FUNCTION
    function getCurrentLocation() {
        if (!checkGeolocationSupport()) return;

        const btn = document.getElementById('gpsBtn');
        const statusDiv = document.getElementById('gpsStatus');

        // Show loading state
        btn.disabled = true;
        btn.innerHTML = `
        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span id="gpsBtnText">Getting location...</span>
    `;

        statusDiv.innerHTML = '<span class="text-blue-600">🔍 Detecting your location...</span>';

        // Clear any previous watcher
        if (watchPositionId !== null) {
            navigator.geolocation.clearWatch(watchPositionId);
            watchPositionId = null;
        }

        // First try with high accuracy
        navigator.geolocation.getCurrentPosition(
            // Success callback
            async (position) => {
                console.log('GPS Position obtained:', position);
                await handlePositionSuccess(position, btn, statusDiv);
            },
            // Error callback
            (error) => {
                handlePositionError(error, btn, statusDiv);
            },
            // Options - IMPORTANT: enableHighAccuracy and longer timeout
            {
                enableHighAccuracy: true,  // Use GPS if available
                timeout: 30000,           // 30 second timeout
                maximumAge: 0             // Don't use cached position
            }
        );

        // Also start watching for position updates
        watchPositionId = navigator.geolocation.watchPosition(
            (position) => {
                console.log('GPS Update:', position);
                handlePositionSuccess(position, document.getElementById('gpsBtn'), statusDiv);
            },
            (error) => {
                console.error('Watch position error:', error);
            },
            {
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 10000  // Accept positions up to 10 seconds old
            }
        );
    }

    // Handle successful position
    async function handlePositionSuccess(position, btn, statusDiv) {
        currentPosition = position;
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const accuracy = position.coords.accuracy;

        console.log(`Location: ${lat}, ${lng}, Accuracy: ${accuracy}m`);

        // Update button to show success
        btn.innerHTML = `
        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span id="gpsBtnText">Location Found!</span>
    `;
        btn.disabled = false;

        // Update status
        if (accuracy <= 20) {
            statusDiv.innerHTML = `<span class="text-green-600">✅ High accuracy location (${Math.round(accuracy)}m)</span>`;
        } else if (accuracy <= 100) {
            statusDiv.innerHTML = `<span class="text-yellow-600">⚠️ Moderate accuracy (${Math.round(accuracy)}m)</span>`;
        } else {
            statusDiv.innerHTML = `<span class="text-red-600">📡 Low accuracy (${Math.round(accuracy)}m) - Move to open area</span>`;
        }

        // Update map
        await updateLocationOnMap(lat, lng, accuracy);

        // Get address details
        await getAddressDetails(lat, lng);

        // Reset button after 2 seconds
        setTimeout(() => {
            btn.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span id="gpsBtnText">Use My Current Location</span>
        `;
        }, 2000);
    }

    // Handle position error
    function handlePositionError(error, btn, statusDiv) {
        console.error('Geolocation error:', error);

        let errorMessage = 'Unable to get location';

        switch(error.code) {
            case 1: // PERMISSION_DENIED
                errorMessage = 'Location permission denied. Please allow location access.';
                document.getElementById('gpsInstructions').classList.remove('hidden');
                break;
            case 2: // POSITION_UNAVAILABLE
                errorMessage = 'Location unavailable. Check GPS/Wi-Fi and try again.';
                break;
            case 3: // TIMEOUT
                errorMessage = 'Location request timed out. Try again in an open area.';
                break;
        }

        // Show error
        showToast(errorMessage, 'error');
        statusDiv.innerHTML = `<span class="text-red-600">❌ ${errorMessage}</span>`;

        // Reset button
        btn.innerHTML = `
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        <span id="gpsBtnText">Use My Current Location</span>
    `;
        btn.disabled = false;

        // Clear watcher
        if (watchPositionId !== null) {
            navigator.geolocation.clearWatch(watchPositionId);
            watchPositionId = null;
        }
    }

    // Start continuous location tracking
    function startLocationTracking() {
        if (watchPositionId !== null) {
            navigator.geolocation.clearWatch(watchPositionId);
        }

        watchPositionId = navigator.geolocation.watchPosition(
            (position) => {
                console.log('Continuous tracking:', position);
                updateLocationOnMap(position.coords.latitude, position.coords.longitude, position.coords.accuracy);
            },
            (error) => {
                console.error('Tracking error:', error);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 5000
            }
        );
    }

    // Stop location tracking
    function stopLocationTracking() {
        if (watchPositionId !== null) {
            navigator.geolocation.clearWatch(watchPositionId);
            watchPositionId = null;
        }
    }

    // Update location on map
    async function updateLocationOnMap(lat, lng, accuracy = null) {
        try {
            // Update coordinates in form
            document.getElementById('latitudeInput').value = lat.toFixed(6);
            document.getElementById('longitudeInput').value = lng.toFixed(6);

            // Initialize map if needed
            if (!mapInitialized) {
                initializeMap();
            }

            // Update accuracy indicator
            if (accuracy !== null) {
                updateAccuracyIndicator(accuracy);

                // Add or update accuracy circle
                if (circle) {
                    circle.setLatLng([lat, lng]).setRadius(accuracy);
                } else {
                    circle = L.circle([lat, lng], {
                        radius: accuracy,
                        color: '#10B981',
                        fillColor: '#10B981',
                        fillOpacity: 0.1,
                        weight: 1
                    }).addTo(map);
                }
            }

            // Update marker
            const markerIcon = L.divIcon({
                html: `
                <div class="relative">
                    <div class="w-8 h-8 bg-green-500 rounded-full border-2 border-white shadow-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-l-transparent border-r-transparent border-t-green-500"></div>
                </div>
            `,
                className: 'custom-marker',
                iconSize: [32, 32],
                iconAnchor: [16, 32]
            });

            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng], {
                    icon: markerIcon,
                    draggable: true,
                    title: 'Your current location'
                }).addTo(map);

                // Allow dragging to adjust
                marker.on('dragend', function(e) {
                    const newPos = e.target.getLatLng();
                    updateLocationOnMap(newPos.lat, newPos.lng, accuracy);
                });
            }

            // Center map on location
            map.setView([lat, lng], 16);

            // Hide any map errors
            document.getElementById('mapError')?.classList.add('hidden');

        } catch (error) {
            console.error('Error updating map:', error);
            showToast('Could not update map location', 'error');
        }
    }

    // Update accuracy indicator
    function updateAccuracyIndicator(accuracy) {
        const indicator = document.getElementById('accuracyIndicator');
        const valueSpan = document.getElementById('accuracyValue');

        if (!indicator || !valueSpan) return;

        valueSpan.textContent = Math.round(accuracy);

        // Color code based on accuracy
        if (accuracy <= 20) {
            valueSpan.className = 'accuracy-good font-bold';
        } else if (accuracy <= 100) {
            valueSpan.className = 'accuracy-medium font-bold';
        } else {
            valueSpan.className = 'accuracy-poor font-bold';
        }

        indicator.classList.remove('hidden');
    }

    // Get address details
    async function getAddressDetails(lat, lng) {
        try {
            showToast('Getting address details...', 'info');

            const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&addressdetails=1&accept-language=en&zoom=18`,
                {
                    headers: {
                        'User-Agent': 'StrayAnimalRescueApp/1.0'
                    }
                }
            );

            if (!response.ok) throw new Error('Geocoding failed');

            const data = await response.json();
            const addr = data.address || {};

            // Update form fields
            if (data.display_name) {
                document.getElementById('addressInput').value = data.display_name;
            }

            // Extract city
            const city = addr.city || addr.town || addr.village || addr.suburb || '';
            if (city) {
                document.getElementById('cityInput').value = city;
            }

            // Extract and match state
            const state = addr.state || '';
            if (state) {
                const matchedState = matchState(state);
                if (matchedState) {
                    const stateSelect = document.getElementById('stateInput');
                    stateSelect.value = matchedState;
                    stateSelect.disabled = false;
                    setTimeout(() => stateSelect.disabled = true, 100);
                }
            }

            showToast('Address details updated', 'success');

        } catch (error) {
            console.warn('Geocoding failed:', error);
            showToast('Could not get address details. Please fill manually.', 'warning');
        }
    }

    // Match state string to Malaysian state
    function matchState(stateStr) {
        if (!stateStr) return '';

        const stateLower = stateStr.toLowerCase();

        for (const [state, variations] of Object.entries(malaysiaStates)) {
            if (variations.some(v => stateLower.includes(v))) {
                return state;
            }
        }

        return '';
    }

    // Initialize map
    function initializeMap() {
        if (mapInitialized || typeof L === 'undefined') return;

        try {
            // Default to Kuala Lumpur
            map = L.map('map', {
                zoomControl: true,
                attributionControl: true,
                scrollWheelZoom: true
            }).setView([3.1390, 101.6869], 13);

            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            // Click to pin location
            map.on('click', function(e) {
                updateLocationOnMap(e.latlng.lat, e.latlng.lng, 50);
            });

            mapInitialized = true;

        } catch (error) {
            console.error('Map initialization failed:', error);
            showToast('Map failed to load', 'error');
        }
    }

    // Open modal
    function openReportModal() {
        document.getElementById('reportModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Check online status
        if (!navigator.onLine) {
            document.getElementById('offlineWarning').classList.remove('hidden');
        }

        // Initialize map
        setTimeout(() => {
            if (!mapInitialized) {
                initializeMap();
            } else if (map) {
                map.invalidateSize();
            }

            // Check GPS permission
            checkGeolocationSupport();
        }, 100);
    }

    // Close modal
    function closeReportModal() {
        document.getElementById('reportModal').classList.add('hidden');
        document.body.style.overflow = 'auto';

        // Stop location tracking
        stopLocationTracking();

        // Reset form
        document.getElementById('reportForm').reset();
        document.getElementById('imagePreview').innerHTML = '';
        document.getElementById('gpsStatus').innerHTML = '';
        document.getElementById('gpsInstructions').classList.add('hidden');

        // Reset GPS button
        document.getElementById('gpsBtn').innerHTML = `
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        <span id="gpsBtnText">Use My Current Location</span>
    `;
        document.getElementById('gpsBtn').disabled = false;
    }

    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('reportForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Enable state field before submission
                const stateField = document.getElementById('stateInput');
                stateField.disabled = false;

                const lat = document.getElementById('latitudeInput').value;
                const lng = document.getElementById('longitudeInput').value;

                if (!lat || !lng) {
                    e.preventDefault();
                    document.getElementById('mapError').classList.remove('hidden');
                    showToast('Please select a location on the map', 'error');
                    stateField.disabled = true;
                    return false;
                }

                return true;
            });
        }

        // GPS button click event
        const gpsBtn = document.getElementById('gpsBtn');
        if (gpsBtn) {
            gpsBtn.addEventListener('click', getCurrentLocation);
        }

        // Modal click to close
        const modal = document.getElementById('reportModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) closeReportModal();
            });
        }

        // Escape key to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                closeReportModal();
            }
        });

        // Online/offline detection
        window.addEventListener('online', () => {
            document.getElementById('offlineWarning').classList.add('hidden');
        });

        window.addEventListener('offline', () => {
            document.getElementById('offlineWarning').classList.remove('hidden');
        });

        // Image preview
        const imageInput = document.getElementById('imageInput');
        if (imageInput) {
            imageInput.addEventListener('change', function() {
                const preview = document.getElementById('imagePreview');
                preview.innerHTML = '';

                Array.from(this.files).slice(0, 5).forEach(file => {
                    if (!file.type.startsWith('image/')) return;

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
        }
    });
</script>
