<!-- Leaflet Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        onerror="window.LEAFLET_FAILED = true"></script>
<script src="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.min.js"></script>

<script>
    // =============================================
    // GPS LOCATION TRACKING - ENHANCED ACCURACY VERSION
    // =============================================

    // Global variables
    let map, marker, circle;
    let mapInitialized = false;
    let watchPositionId = null;
    let currentPosition = null;
    let isManualAdjustment = false;

    // Enhanced Malaysian state mapping
    const malaysiaStates = {
        'Johor': ['johor', 'johore', 'johor bahru', 'jb', 'j.b.', 'j.b'],
        'Kedah': ['kedah', 'alor setar', 'alor star'],
        'Kelantan': ['kelantan', 'kota bharu', 'kota bahru'],
        'Malacca': ['malacca', 'melaka', 'malaka'],
        'Negeri Sembilan': ['negeri sembilan', 'n.sembilan', 'n sembilan', 'seremban', 'n.s', 'n.s.'],
        'Pahang': ['pahang', 'kuantan', 'kuala lipis'],
        'Penang': ['penang', 'pulau pinang', 'georgetown', 'george town', 'penang island'],
        'Perak': ['perak', 'ipoh', 'taiping'],
        'Perlis': ['perlis', 'kangar'],
        'Sabah': ['sabah', 'kota kinabalu', 'kk', 'sandakan', 'tawau'],
        'Sarawak': ['sarawak', 'kuching', 'sibu', 'miri'],
        'Selangor': ['selangor', 'shah alam', 'petaling jaya', 'pj', 'subang jaya', 'klang'],
        'Terengganu': ['terengganu', 'kuala terengganu', 'k.terengganu'],
        'Kuala Lumpur': ['kuala lumpur', 'kl', 'k.l.', 'k.lumpur', 'wilayah persekutuan kuala lumpur'],
        'Putrajaya': ['putrajaya', 'putra jaya', 'wilayah persekutuan putrajaya'],
        'Labuan': ['labuan', 'w.p. labuan', 'wilayah persekutuan labuan']
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

    // Get location from IP address as fallback
    async function getLocationFromIP(btn, statusDiv) {
        try {
            statusDiv.innerHTML = '<span class="text-yellow-600">📍 Getting approximate location from IP...</span>';

            // Try multiple free IP geolocation APIs
            const ipApis = [
                'https://ipapi.co/json/',
                'https://ipinfo.io/json',
                'https://geolocation-db.com/json/'
            ];

            let locationData = null;

            for (const apiUrl of ipApis) {
                try {
                    const response = await fetch(apiUrl, { timeout: 5000 });
                    if (response.ok) {
                        locationData = await response.json();
                        console.log('IP location data:', locationData);
                        break;
                    }
                } catch (e) {
                    console.log(`API ${apiUrl} failed:`, e);
                    continue;
                }
            }

            if (locationData) {
                // Extract lat/lng from different API formats
                let lat, lng, city, state;

                if (locationData.latitude && locationData.longitude) {
                    lat = parseFloat(locationData.latitude);
                    lng = parseFloat(locationData.longitude);
                    city = locationData.city || locationData.city_name || '';
                    state = locationData.region || locationData.region_name || locationData.state || '';
                } else if (locationData.loc) {
                    // ipinfo.io format: "loc": "3.1390,101.6869"
                    const [latStr, lngStr] = locationData.loc.split(',');
                    lat = parseFloat(latStr);
                    lng = parseFloat(lngStr);
                    city = locationData.city || '';
                    state = locationData.region || '';
                } else if (locationData.lat && locationData.lon) {
                    lat = parseFloat(locationData.lat);
                    lng = parseFloat(locationData.lon);
                    city = locationData.city || '';
                    state = locationData.state || '';
                }

                if (lat && lng) {
                    // Create artificial position object
                    const artificialPosition = {
                        coords: {
                            latitude: lat,
                            longitude: lng,
                            accuracy: 5000, // IP location is very approximate (5km)
                            altitude: null,
                            altitudeAccuracy: null,
                            heading: null,
                            speed: null
                        },
                        timestamp: Date.now()
                    };

                    await handlePositionSuccess(artificialPosition, btn, statusDiv);
                    showToast('Using IP-based location (5km accuracy)', 'warning');
                    return;
                }
            }

            // If all IP APIs fail, use default Kuala Lumpur location
            throw new Error('All location methods failed');

        } catch (error) {
            console.error('IP location failed:', error);
            handlePositionError(error, btn, statusDiv);
        }
    }

    // Get current location - ENHANCED ACCURACY VERSION
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
        <span id="gpsBtnText">Getting precise location...</span>
        `;

        statusDiv.innerHTML = '<span class="text-blue-600">🔍 Getting precise location (this may take 10-20 seconds)...</span>';

        // Show accuracy tips
        document.getElementById('accuracyTips')?.classList.remove('hidden');

        // Clear any previous watcher
        if (watchPositionId !== null) {
            navigator.geolocation.clearWatch(watchPositionId);
            watchPositionId = null;
        }

        // FIRST: Try high accuracy with maximum settings (for mobile/outdoor)
        navigator.geolocation.getCurrentPosition(
            // Success callback
            async (position) => {
                console.log('High accuracy GPS position:', position);
                await handlePositionSuccess(position, btn, statusDiv);
            },
            // Error callback - try multiple fallback methods
            async (error) => {
                console.log('High accuracy failed, trying medium accuracy...', error);

                // SECOND: Try medium accuracy (for desktop with Wi-Fi)
                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        console.log('Medium accuracy position:', position);
                        await handlePositionSuccess(position, btn, statusDiv);
                    },
                    // Error callback - try low accuracy
                    async (error) => {
                        console.log('Medium accuracy failed, trying low accuracy...', error);

                        // THIRD: Try low accuracy (IP-based, quick response)
                        navigator.geolocation.getCurrentPosition(
                            async (position) => {
                                console.log('Low accuracy (IP-based) position:', position);
                                position.coords.accuracy = 10000; // Mark as low accuracy (10km)
                                await handlePositionSuccess(position, btn, statusDiv);
                            },
                            // Final error - use IP geolocation API
                            async (error) => {
                                console.log('All geolocation methods failed, using IP API...', error);
                                await getLocationFromIP(btn, statusDiv);
                            },
                            {
                                enableHighAccuracy: false,
                                timeout: 10000,
                                maximumAge: 60000
                            }
                        );
                    },
                    {
                        enableHighAccuracy: false,
                        timeout: 15000,
                        maximumAge: 30000
                    }
                );
            },
            // Options - MAXIMUM accuracy for first attempt
            {
                enableHighAccuracy: true,  // Force GPS if available
                timeout: 20000,           // 20 second timeout
                maximumAge: 0             // Force fresh location
            }
        );
    }

    // Enhanced position success handler
    async function handlePositionSuccess(position, btn, statusDiv) {
        currentPosition = position;
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const accuracy = position.coords.accuracy || 10000; // Default to 10km if not specified

        console.log(`Final Location: ${lat}, ${lng}, Accuracy: ${accuracy}m`);
        console.log('Full position data:', position);

        // Update button to show success with accuracy info
        let accuracyText = '';
        if (accuracy <= 20) {
            accuracyText = ' (High precision)';
        } else if (accuracy <= 100) {
            accuracyText = ' (Good)';
        } else if (accuracy <= 1000) {
            accuracyText = ' (Approximate)';
        } else {
            accuracyText = ' (Very approximate)';
        }

        btn.innerHTML = `
        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span id="gpsBtnText">Location Found${accuracyText}</span>
        `;
        btn.disabled = false;

        // Update status with detailed accuracy info
        if (accuracy <= 20) {
            statusDiv.innerHTML = `<span class="text-green-600">✅ High precision GPS (${Math.round(accuracy)}m) - Excellent accuracy</span>`;
        } else if (accuracy <= 100) {
            statusDiv.innerHTML = `<span class="text-green-600">✅ Good location (${Math.round(accuracy)}m) - Suitable for reporting</span>`;
        } else if (accuracy <= 500) {
            statusDiv.innerHTML = `<span class="text-yellow-600">⚠️ Moderate accuracy (${Math.round(accuracy)}m) - May need adjustment</span>`;
            document.getElementById('adjustLocationSection')?.classList.remove('hidden');
        } else if (accuracy <= 5000) {
            statusDiv.innerHTML = `<span class="text-orange-600">⚠️ Approximate location (${Math.round(accuracy)}m) - Consider moving outdoors</span>`;
            document.getElementById('adjustLocationSection')?.classList.remove('hidden');
            showToast('Location is approximate. Please drag the pin to exact position.', 'warning');
        } else {
            statusDiv.innerHTML = `<span class="text-red-600">📡 Very approximate (${Math.round(accuracy/1000)}km) - IP-based location, drag pin to correct position</span>`;
            document.getElementById('adjustLocationSection')?.classList.remove('hidden');
            showToast('Location is very approximate. Please drag the pin to exact position.', 'warning');
        }

        // Hide accuracy tips
        document.getElementById('accuracyTips')?.classList.add('hidden');

        // Update map
        await updateLocationOnMap(lat, lng, accuracy);

        // Get address details
        await getAddressDetailsWithFallback(lat, lng);

        // If accuracy is poor, zoom out to show context
        if (accuracy > 1000 && map) {
            map.setView([lat, lng], 12); // Zoom out more for approximate locations
        }

        // Reset button after 3 seconds
        setTimeout(() => {
            btn.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span id="gpsBtnText">Use My Current Location</span>
            `;
        }, 3000);
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
                console.log('Continuous tracking update:', position);
                // Only update if accuracy improves significantly
                if (!currentPosition || position.coords.accuracy < currentPosition.coords.accuracy * 0.7) {
                    updateLocationOnMap(position.coords.latitude, position.coords.longitude, position.coords.accuracy);
                }
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
                        color: accuracy <= 100 ? '#10B981' : accuracy <= 1000 ? '#F59E0B' : '#EF4444',
                        fillColor: accuracy <= 100 ? '#10B981' : accuracy <= 1000 ? '#F59E0B' : '#EF4444',
                        fillOpacity: accuracy <= 100 ? 0.2 : accuracy <= 1000 ? 0.15 : 0.1,
                        weight: accuracy <= 100 ? 2 : 1,
                        dashArray: accuracy <= 100 ? null : '5, 5'
                    }).addTo(map);
                }
            }

            // Update marker with color based on accuracy
            const markerColor = accuracy <= 100 ? 'from-green-500 to-emerald-600' :
                accuracy <= 1000 ? 'from-yellow-500 to-amber-600' :
                    'from-red-500 to-pink-600';

            const markerIcon = L.divIcon({
                html: `
                <div class="relative">
                    <div class="w-10 h-10 bg-gradient-to-br ${markerColor} rounded-full border-3 border-white shadow-2xl flex items-center justify-center animate-pulse">
                        <svg class="w-5 h-5 text-white drop-shadow-lg" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-[6px] border-r-[6px] border-t-[6px] border-l-transparent border-r-transparent ${accuracy <= 100 ? 'border-t-emerald-600' : accuracy <= 1000 ? 'border-t-amber-600' : 'border-t-pink-600'}"></div>
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
                    title: 'Your current location - Drag to adjust'
                }).addTo(map);

                // Allow dragging to adjust
                marker.on('dragstart', function() {
                    isManualAdjustment = true;
                    showToast('Drag the pin to adjust location', 'info');
                });

                marker.on('dragend', async function(e) {
                    const newPos = e.target.getLatLng();
                    const manualAccuracy = 10; // Manual placement is high accuracy

                    // Show loading
                    showToast('Getting address for new location...', 'info');

                    await updateLocationOnMap(newPos.lat, newPos.lng, manualAccuracy);
                    await getAddressDetailsWithFallback(newPos.lat, newPos.lng);

                    showToast('Location manually adjusted', 'success');

                    // Update accuracy display
                    document.getElementById('accuracyValue').textContent = manualAccuracy;
                    document.getElementById('accuracyIndicator').classList.remove('hidden');

                    // Hide adjustment section
                    document.getElementById('adjustLocationSection')?.classList.add('hidden');
                });
            }

            // Center map on location with appropriate zoom
            let zoomLevel = 16;
            if (accuracy > 1000) zoomLevel = 13;
            if (accuracy > 5000) zoomLevel = 11;

            map.setView([lat, lng], zoomLevel);

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

        // Convert to km if > 1000m
        if (accuracy >= 1000) {
            valueSpan.textContent = (accuracy / 1000).toFixed(1) + 'km';
        } else {
            valueSpan.textContent = Math.round(accuracy) + 'm';
        }

        // Color code based on accuracy
        if (accuracy <= 20) {
            valueSpan.className = 'accuracy-good font-bold';
        } else if (accuracy <= 100) {
            valueSpan.className = 'accuracy-good font-bold';
        } else if (accuracy <= 1000) {
            valueSpan.className = 'accuracy-medium font-bold';
        } else {
            valueSpan.className = 'accuracy-poor font-bold';
        }

        indicator.classList.remove('hidden');
    }

    // Get address details with guaranteed city and state (NEVER blank)
    async function getAddressDetailsWithFallback(lat, lng) {
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

            console.log('Full address data:', data);

            // Update form fields
            if (data.display_name) {
                document.getElementById('addressInput').value = data.display_name;
            }

            // ==================== CITY EXTRACTION (GUARANTEED) ====================
            let city = '';

            // Try multiple possible city fields (in order of preference)
            const citySources = [
                addr.city,
                addr.town,
                addr.village,
                addr.suburb,
                addr.municipality,
                addr.county,
                addr.district,
                addr.neighbourhood,
                addr.quarter,
                addr.residential,
                addr.road,  // Use road name if nothing else
                addr.hamlet,
                addr.locality,
                addr.region
            ];

            for (const source of citySources) {
                if (source && typeof source === 'string' && source.trim().length > 0) {
                    city = source.trim();
                    console.log(`City found in ${Object.keys(addr).find(key => addr[key] === source)}: ${city}`);
                    break;
                }
            }

            // If still no city, use coordinates as fallback
            if (!city) {
                city = `Location near ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                console.log('Using coordinates as city fallback');
            }

            // Update city field - GUARANTEED to have value
            const cityInput = document.getElementById('cityInput');
            cityInput.value = city;
            cityInput.classList.add('auto-filled');
            setTimeout(() => cityInput.classList.add('auto-filled-success'), 500);
            setTimeout(() => {
                cityInput.classList.remove('auto-filled', 'auto-filled-success');
            }, 3000);

            console.log('Final city value:', city);
            // ==================== END CITY EXTRACTION ====================

            // ==================== STATE EXTRACTION (GUARANTEED) ====================
            let state = '';

            // Try to find state from address components
            const stateSources = [
                addr.state,
                addr.region,
                addr.province,
                addr.county,
                addr.district,
                addr.island
            ];

            for (const source of stateSources) {
                if (source && typeof source === 'string') {
                    const matchedState = matchState(source);
                    if (matchedState) {
                        state = matchedState;
                        console.log(`State found: ${state} from ${source}`);
                        break;
                    }
                }
            }

            // If state not found in address, try to match from display_name
            if (!state && data.display_name) {
                state = matchState(data.display_name);
                if (state) console.log(`State matched from display_name: ${state}`);
            }

            // If still no state, try to estimate from GPS coordinates
            if (!state) {
                state = estimateStateFromCoordinates(lat, lng);
                if (state) console.log(`State estimated from coordinates: ${state}`);
            }

            // LAST RESORT: If still no state, use Kuala Lumpur as default
            if (!state) {
                state = 'Kuala Lumpur';
                console.log('Using default state: Kuala Lumpur');
            }

            // Update state field - GUARANTEED to have value
            const stateSelect = document.getElementById('stateInput');
            stateSelect.value = state;
            stateSelect.disabled = false;
            stateSelect.classList.add('auto-filled');
            setTimeout(() => stateSelect.classList.add('auto-filled-success'), 500);
            setTimeout(() => {
                stateSelect.classList.remove('auto-filled', 'auto-filled-success');
                stateSelect.disabled = true;
            }, 3000);
            // ==================== END STATE EXTRACTION ====================

            showToast('Address, city, and state updated successfully', 'success');
            return { city, state };

        } catch (error) {
            console.warn('Geocoding failed:', error);

            // ==================== FALLBACK VALUES WHEN API FAILS ====================
            // Set fallback values to ensure fields are NEVER null

            const cityInput = document.getElementById('cityInput');
            const stateSelect = document.getElementById('stateInput');

            // If city is empty, use coordinates
            if (!cityInput.value) {
                cityInput.value = `Location near ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                cityInput.classList.add('auto-filled');
            }

            // If state is empty, try to estimate or use default
            if (!stateSelect.value) {
                const estimatedState = estimateStateFromCoordinates(lat, lng) || 'Kuala Lumpur';
                stateSelect.value = estimatedState;
                stateSelect.disabled = false;
                stateSelect.classList.add('auto-filled');
                setTimeout(() => {
                    stateSelect.disabled = true;
                }, 100);
            }
            // ==================== END FALLBACK ====================

            showToast('Using fallback values for city/state', 'warning');
            return null;
        }
    }

    // Estimate state from GPS coordinates in Malaysia
    function estimateStateFromCoordinates(lat, lng) {
        // Malaysia coordinate boundaries by state (approximate)
        const stateBoundaries = [
            { state: 'Johor', bounds: { minLat: 1.2, maxLat: 2.8, minLng: 102.5, maxLng: 104.5 } },
            { state: 'Kedah', bounds: { minLat: 5.0, maxLat: 6.5, minLng: 99.5, maxLng: 101.5 } },
            { state: 'Kelantan', bounds: { minLat: 4.5, maxLat: 6.0, minLng: 101.5, maxLng: 103.5 } },
            { state: 'Malacca', bounds: { minLat: 2.0, maxLat: 2.5, minLng: 102.0, maxLng: 102.5 } },
            { state: 'Negeri Sembilan', bounds: { minLat: 2.5, maxLat: 3.5, minLng: 101.5, maxLng: 102.5 } },
            { state: 'Pahang', bounds: { minLat: 2.5, maxLat: 4.5, minLng: 101.5, maxLng: 103.5 } },
            { state: 'Penang', bounds: { minLat: 5.1, maxLat: 5.5, minLng: 100.1, maxLng: 100.5 } },
            { state: 'Perak', bounds: { minLat: 3.5, maxLat: 5.5, minLng: 100.5, maxLng: 101.5 } },
            { state: 'Perlis', bounds: { minLat: 6.5, maxLat: 6.8, minLng: 99.5, maxLng: 100.5 } },
            { state: 'Sabah', bounds: { minLat: 4.0, maxLat: 7.5, minLng: 115.0, maxLng: 119.0 } },
            { state: 'Sarawak', bounds: { minLat: 0.5, maxLat: 4.5, minLng: 109.5, maxLng: 115.5 } },
            { state: 'Selangor', bounds: { minLat: 2.5, maxLat: 3.5, minLng: 101.0, maxLng: 102.0 } },
            { state: 'Terengganu', bounds: { minLat: 4.0, maxLat: 5.5, minLng: 102.5, maxLng: 103.5 } },
            { state: 'Kuala Lumpur', bounds: { minLat: 3.0, maxLat: 3.3, minLng: 101.6, maxLng: 101.8 } },
            { state: 'Putrajaya', bounds: { minLat: 2.9, maxLat: 3.0, minLng: 101.6, maxLng: 101.7 } },
            { state: 'Labuan', bounds: { minLat: 5.2, maxLat: 5.4, minLng: 115.1, maxLng: 115.3 } }
        ];

        for (const { state, bounds } of stateBoundaries) {
            if (lat >= bounds.minLat && lat <= bounds.maxLat &&
                lng >= bounds.minLng && lng <= bounds.maxLng) {
                console.log(`Estimated state from coordinates: ${state}`);
                return state;
            }
        }

        console.log('Could not estimate state from coordinates');
        return '';
    }

    // Enhanced state matching
    function matchState(stateStr) {
        if (!stateStr || typeof stateStr !== 'string') return '';

        const stateLower = stateStr.toLowerCase().trim();

        // Check for exact matches first
        for (const [state, variations] of Object.entries(malaysiaStates)) {
            for (const variation of variations) {
                if (stateLower === variation ||
                    stateLower.includes(variation) ||
                    variation.includes(stateLower)) {
                    console.log(`Matched "${stateStr}" to "${state}" via variation "${variation}"`);
                    return state;
                }
            }
        }

        // Check for partial matches (words in string)
        const words = stateLower.split(/[\s,\-\.\(\)]+/);
        for (const word of words) {
            if (word.length < 2) continue;

            for (const [state, variations] of Object.entries(malaysiaStates)) {
                for (const variation of variations) {
                    if (variation.includes(word) || word.includes(variation)) {
                        console.log(`Matched "${stateStr}" to "${state}" via word "${word}"`);
                        return state;
                    }
                }
            }
        }

        console.log(`Could not match state: "${stateStr}"`);
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
                scrollWheelZoom: true,
                doubleClickZoom: true
            }).setView([3.1390, 101.6869], 13);

            // Add tile layer with original colors
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '© OpenStreetMap contributors © CARTO',
                maxZoom: 19
            }).addTo(map);

            // Click to pin location - ALSO GETS CITY/STATE
            map.on('click', async function(e) {
                showToast('Getting address for clicked location...', 'info');
                await updateLocationOnMap(e.latlng.lat, e.latlng.lng, 50);
                await getAddressDetailsWithFallback(e.latlng.lat, e.latlng.lng);
            });

            mapInitialized = true;

        } catch (error) {
            console.error('Map initialization failed:', error);
            showToast('Map failed to load', 'error');
        }
    }

    // Enable manual adjustment mode
    function enableManualAdjustment() {
        if (!marker) return;

        showToast('Drag the pin to adjust location', 'info');
        document.getElementById('adjustLocationSection').classList.add('hidden');

        // Briefly animate the marker
        marker.getElement().classList.add('animate-bounce');
        setTimeout(() => {
            marker.getElement().classList.remove('animate-bounce');
        }, 1000);
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
        document.getElementById('accuracyTips').classList.add('hidden');
        document.getElementById('adjustLocationSection').classList.add('hidden');

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

                // ==================== VALIDATE CITY AND STATE ARE FILLED ====================
                const cityField = document.getElementById('cityInput');
                const stateField = document.getElementById('stateInput');
                const addressField = document.getElementById('addressInput');

                let validationErrors = [];

                if (!addressField.value) {
                    validationErrors.push('Address is required');
                    addressField.classList.add('border-red-500', 'border-2');
                }

                if (!cityField.value) {
                    validationErrors.push('City is required');
                    cityField.classList.add('border-red-500', 'border-2');
                }

                if (!stateField.value) {
                    validationErrors.push('State is required');
                    stateField.classList.add('border-red-500', 'border-2');
                }

                // Remove error styling after 3 seconds
                setTimeout(() => {
                    [addressField, cityField, stateField].forEach(field => {
                        field.classList.remove('border-red-500', 'border-2');
                    });
                }, 3000);

                if (validationErrors.length > 0) {
                    showToast('Please fill all location fields', 'error');
                    showReportModalAlert('error',
                        '<strong>Location information incomplete:</strong><br>' +
                        validationErrors.map(err => `• ${err}`).join('<br>')
                    );
                    return false;
                }
                // ==================== END VALIDATION ====================

                // Enable state field before submission
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

        // Location search functionality - ALSO GETS CITY/STATE
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

                                    // Update map and get city/state
                                    await updateLocationOnMap(lat, lon, 10); // Search results are precise
                                    await getAddressDetailsWithFallback(lat, lon);

                                    // Update search input
                                    locationSearch.value = name;

                                    // Hide results
                                    searchResults.classList.add('hidden');
                                    searchResults.innerHTML = '';

                                    // Show success message
                                    showToast('Location selected. City and state auto-filled.', 'success');
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

<style>
    /* Visual feedback for auto-filled fields */
    .auto-filled {
        background-color: #f0f9ff !important;
        border-color: #0ea5e9 !important;
        color: #0369a1 !important;
        transition: all 0.3s ease;
    }

    .auto-filled-success {
        background-color: #f0fdf4 !important;
        border-color: #22c55e !important;
        color: #15803d !important;
    }

    /* Accuracy indicator colors */
    .accuracy-good { color: #10B981; }
    .accuracy-medium { color: #F59E0B; }
    .accuracy-poor { color: #EF4444; }

    /* Toast animations */
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

    /* Bounce animation for manual adjustment */
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    .animate-bounce {
        animation: bounce 0.5s ease-in-out 2;
    }

    /* Map marker pulse animation */
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    .animate-pulse {
        animation: pulse 2s ease-in-out infinite;
    }
</style>
