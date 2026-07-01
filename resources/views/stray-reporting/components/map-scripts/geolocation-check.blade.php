<script>
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
</script>
