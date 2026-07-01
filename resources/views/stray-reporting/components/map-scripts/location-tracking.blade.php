<script>
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
</script>
