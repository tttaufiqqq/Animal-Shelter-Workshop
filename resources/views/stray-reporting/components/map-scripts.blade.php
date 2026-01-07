<!-- Leaflet Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        onerror="window.LEAFLET_FAILED = true"></script>
<script src="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.min.js"></script>

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

    // Modal alert system (for report submission feedback)
    function showReportModalAlert(type, message) {
        const alertContainer = document.getElementById('reportModalAlert');
        const isSuccess = type === 'success';

        alertContainer.innerHTML = `
            <div class="flex items-start gap-3 p-4 bg-${isSuccess ? 'green' : 'red'}-50 border border-${isSuccess ? 'green' : 'red'}-200 rounded-xl shadow-sm">
                <svg class="w-6 h-6 text-${isSuccess ? 'green' : 'red'}-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    ${isSuccess
                        ? '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />'
                        : '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />'
                    }
                </svg>
                <div class="flex-1">
                    <p class="font-semibold text-${isSuccess ? 'green' : 'red'}-700">${message}</p>
                </div>
                <button onclick="document.getElementById('reportModalAlert').classList.add('hidden')" class="text-${isSuccess ? 'green' : 'red'}-600 hover:text-${isSuccess ? 'green' : 'red'}-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `;
        alertContainer.classList.remove('hidden');

        // Scroll to alert
        alertContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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

                // Add or update accuracy circle with enhanced styling
                if (circle) {
                    circle.setLatLng([lat, lng]).setRadius(accuracy);
                } else {
                    circle = L.circle([lat, lng], {
                        radius: accuracy,
                        color: '#06B6D4',
                        fillColor: '#06B6D4',
                        fillOpacity: 0.3,
                        weight: 3,
                        dashArray: '5, 10'
                    }).addTo(map);
                }
            }

            // Update marker with vibrant gradient
            const markerIcon = L.divIcon({
                html: `
                <div class="relative">
                    <div class="w-10 h-10 bg-gradient-to-br from-pink-500 via-purple-500 to-indigo-600 rounded-full border-3 border-white shadow-2xl flex items-center justify-center animate-pulse">
                        <svg class="w-5 h-5 text-white drop-shadow-lg" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-[6px] border-r-[6px] border-t-[6px] border-l-transparent border-r-transparent border-t-indigo-600"></div>
                </div>
            `,
                className: 'custom-marker',
                iconSize: [40, 40],
                iconAnchor: [20, 40]
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
                marker.on('dragend', async function(e) {
                    const newPos = e.target.getLatLng();
                    await updateLocationOnMap(newPos.lat, newPos.lng, accuracy);
                    await getAddressDetails(newPos.lat, newPos.lng);
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

            // Add tile layer with original colors
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '© OpenStreetMap contributors © CARTO',
                maxZoom: 19
            }).addTo(map);

            // Click to pin location
            map.on('click', async function(e) {
                await updateLocationOnMap(e.latlng.lat, e.latlng.lng, 50);
                await getAddressDetails(e.latlng.lat, e.latlng.lng);
            });

            mapInitialized = true;

        } catch (error) {
            console.error('Map initialization failed:', error);
            showToast('Map failed to load', 'error');
        }
    }

    // Open modal
    function openReportModal() {
        const modal = document.getElementById('reportModal');
        modal.classList.remove('hidden');
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
        const modal = document.getElementById('reportModal');
        modal.classList.add('hidden');
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

    // Form validation and submission
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('reportForm');
        const alertContainer = document.getElementById('reportModalAlert');

        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault(); // Prevent default form submission

                // Enable state field before submission
                const stateField = document.getElementById('stateInput');
                stateField.disabled = false;

                const lat = document.getElementById('latitudeInput').value;
                const lng = document.getElementById('longitudeInput').value;

                if (!lat || !lng) {
                    document.getElementById('mapError').classList.remove('hidden');
                    showToast('Please select a location on the map', 'error');
                    showReportModalAlert('error', 'Please select a location on the map before submitting.');
                    stateField.disabled = true;
                    return false;
                }

                // Show loading spinner on submit button
                const submitBtn = document.getElementById('submitBtn');
                const originalBtnContent = submitBtn.innerHTML;
                const cancelBtn = document.getElementById('cancelBtn');

                // Hide any existing alerts
                alertContainer.classList.add('hidden');
                alertContainer.innerHTML = '';

                submitBtn.disabled = true;
                cancelBtn.disabled = true;

                submitBtn.innerHTML = `
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Submitting Report...</span>
                `;

                showToast('Submitting your report...', 'info');

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Show success message
                        showReportModalAlert('success', data.message || 'Report submitted successfully!');
                        showToast('Report submitted successfully!', 'success');

                        // Reset form after successful submission
                        form.reset();
                        document.getElementById('imagePreview').innerHTML = '';
                        if (marker) map.removeLayer(marker);
                        if (circle) map.removeLayer(circle);
                        document.getElementById('latitudeInput').value = '';
                        document.getElementById('longitudeInput').value = '';

                        // Auto-hide success message and close modal after 2 seconds
                        setTimeout(() => {
                            alertContainer.classList.add('hidden');
                            closeReportModal();
                        }, 2000);
                    } else {
                        // Show error message
                        let errorMessage = data.message || 'An error occurred while submitting your report.';

                        // Handle validation errors
                        if (data.errors) {
                            errorMessage = '<ul class="list-disc list-inside">';
                            for (const field in data.errors) {
                                data.errors[field].forEach(error => {
                                    errorMessage += `<li>${error}</li>`;
                                });
                            }
                            errorMessage += '</ul>';
                        }

                        showReportModalAlert('error', errorMessage);
                        showToast('Please fix the errors and try again', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showReportModalAlert('error', 'Network error. Please check your connection and try again.');
                    showToast('Network error. Please try again.', 'error');
                } finally {
                    // Re-enable buttons and restore original text
                    submitBtn.disabled = false;
                    cancelBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnContent;
                }
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

        // Location search functionality
        const locationSearch = document.getElementById('locationSearch');
        const searchResults = document.getElementById('searchResults');
        let searchTimeout;

        if (locationSearch && searchResults) {
            locationSearch.addEventListener('input', function() {
                const query = this.value.trim();

                // Clear previous timeout
                clearTimeout(searchTimeout);

                // Hide results if query is empty
                if (query.length < 3) {
                    searchResults.classList.add('hidden');
                    searchResults.innerHTML = '';
                    return;
                }

                // Show loading state
                searchResults.classList.remove('hidden');
                searchResults.innerHTML = '<div class="p-3 text-gray-600 text-sm">🔍 Searching...</div>';

                // Debounce search - wait 500ms after user stops typing
                searchTimeout = setTimeout(async () => {
                    try {
                        // Search using Nominatim API (restricted to Malaysia)
                        const response = await fetch(
                            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=my&limit=10&addressdetails=1`,
                            {
                                headers: {
                                    'User-Agent': 'StrayAnimalRescueApp/1.0'
                                }
                            }
                        );

                        if (!response.ok) throw new Error('Search failed');

                        const results = await response.json();

                        // Display results
                        if (results.length === 0) {
                            searchResults.innerHTML = '<div class="p-3 text-gray-500 text-sm">❌ No locations found. Try a different search.</div>';
                        } else {
                            searchResults.innerHTML = results.map(result => `
                                <div class="search-result-item p-3 hover:bg-purple-50 cursor-pointer border-b last:border-b-0 transition"
                                     data-lat="${result.lat}"
                                     data-lon="${result.lon}"
                                     data-name="${escapeHtml(result.display_name)}">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-4 h-4 text-purple-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-900 truncate">${escapeHtml(result.display_name)}</div>
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                ${result.type ? escapeHtml(result.type) : 'Location'} •
                                                ${result.lat.substring(0, 8)}, ${result.lon.substring(0, 8)}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `).join('');

                            // Add click handlers to results
                            document.querySelectorAll('.search-result-item').forEach(item => {
                                item.addEventListener('click', async function() {
                                    const lat = parseFloat(this.dataset.lat);
                                    const lon = parseFloat(this.dataset.lon);
                                    const name = this.dataset.name;

                                    // Update map
                                    await updateLocationOnMap(lat, lon, 50);
                                    await getAddressDetails(lat, lon);

                                    // Update search input
                                    locationSearch.value = name;

                                    // Hide results
                                    searchResults.classList.add('hidden');
                                    searchResults.innerHTML = '';

                                    // Show success message
                                    showToast('Location selected successfully', 'success');
                                });
                            });
                        }
                    } catch (error) {
                        console.error('Search error:', error);
                        searchResults.innerHTML = '<div class="p-3 text-red-600 text-sm">⚠️ Search failed. Please try again.</div>';
                    }
                }, 500);
            });

            // Hide results when clicking outside
            document.addEventListener('click', function(e) {
                if (!locationSearch.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });

            // Show results again when focusing on search input
            locationSearch.addEventListener('focus', function() {
                if (searchResults.innerHTML && !searchResults.classList.contains('hidden')) {
                    searchResults.classList.remove('hidden');
                }
            });
        }
    });

    // Helper function to escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
