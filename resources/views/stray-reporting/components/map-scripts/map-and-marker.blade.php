<script>
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

                marker.on('dragstart', function() {
                    isManualAdjustment = true;
                    showToast('Drag the pin to adjust location', 'info');
                });

                marker.on('dragend', async function(e) {
                    const newPos = e.target.getLatLng();
                    const manualAccuracy = 10;

                    showToast('Getting address for new location...', 'info');

                    await updateLocationOnMap(newPos.lat, newPos.lng, manualAccuracy);
                    await getAddressDetailsWithFallback(newPos.lat, newPos.lng);

                    showToast('Location manually adjusted', 'success');

                    document.getElementById('accuracyValue').textContent = manualAccuracy;
                    document.getElementById('accuracyIndicator').classList.remove('hidden');
                    document.getElementById('adjustLocationSection')?.classList.add('hidden');
                });
            }

            let zoomLevel = 16;
            if (accuracy > 1000) zoomLevel = 13;
            if (accuracy > 5000) zoomLevel = 11;

            map.setView([lat, lng], zoomLevel);
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

        if (accuracy >= 1000) {
            valueSpan.textContent = (accuracy / 1000).toFixed(1) + 'km';
        } else {
            valueSpan.textContent = Math.round(accuracy) + 'm';
        }

        if (accuracy <= 100) {
            valueSpan.className = 'accuracy-good font-bold';
        } else if (accuracy <= 1000) {
            valueSpan.className = 'accuracy-medium font-bold';
        } else {
            valueSpan.className = 'accuracy-poor font-bold';
        }

        indicator.classList.remove('hidden');
    }
</script>
