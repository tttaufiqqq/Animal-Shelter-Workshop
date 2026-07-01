<script>
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
            async (position) => {
                console.log('High accuracy GPS position:', position);
                await handlePositionSuccess(position, btn, statusDiv);
            },
            async (error) => {
                console.log('High accuracy failed, trying medium accuracy...', error);

                // SECOND: Try medium accuracy (for desktop with Wi-Fi)
                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        console.log('Medium accuracy position:', position);
                        await handlePositionSuccess(position, btn, statusDiv);
                    },
                    async (error) => {
                        console.log('Medium accuracy failed, trying low accuracy...', error);

                        // THIRD: Try low accuracy (IP-based, quick response)
                        navigator.geolocation.getCurrentPosition(
                            async (position) => {
                                console.log('Low accuracy (IP-based) position:', position);
                                position.coords.accuracy = 10000; // Mark as low accuracy (10km)
                                await handlePositionSuccess(position, btn, statusDiv);
                            },
                            async (error) => {
                                console.log('All geolocation methods failed, using IP API...', error);
                                await getLocationFromIP(btn, statusDiv);
                            },
                            { enableHighAccuracy: false, timeout: 10000, maximumAge: 60000 }
                        );
                    },
                    { enableHighAccuracy: false, timeout: 15000, maximumAge: 30000 }
                );
            },
            { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 }
        );
    }

    // Enhanced position success handler
    async function handlePositionSuccess(position, btn, statusDiv) {
        currentPosition = position;
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const accuracy = position.coords.accuracy || 10000;

        console.log(`Final Location: ${lat}, ${lng}, Accuracy: ${accuracy}m`);

        let accuracyText = '';
        if (accuracy <= 20) accuracyText = ' (High precision)';
        else if (accuracy <= 100) accuracyText = ' (Good)';
        else if (accuracy <= 1000) accuracyText = ' (Approximate)';
        else accuracyText = ' (Very approximate)';

        btn.innerHTML = `
        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span id="gpsBtnText">Location Found${accuracyText}</span>
        `;
        btn.disabled = false;

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

        document.getElementById('accuracyTips')?.classList.add('hidden');
        await updateLocationOnMap(lat, lng, accuracy);
        await getAddressDetailsWithFallback(lat, lng);

        if (accuracy > 1000 && map) {
            map.setView([lat, lng], 12);
        }

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

        showToast(errorMessage, 'error');
        statusDiv.innerHTML = `<span class="text-red-600">❌ ${errorMessage}</span>`;

        btn.innerHTML = `
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        <span id="gpsBtnText">Use My Current Location</span>
        `;
        btn.disabled = false;

        if (watchPositionId !== null) {
            navigator.geolocation.clearWatch(watchPositionId);
            watchPositionId = null;
        }
    }
</script>
