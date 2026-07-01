<script>
    // Get address details with guaranteed city and state (NEVER blank)
    async function getAddressDetailsWithFallback(lat, lng) {
        try {
            showToast('Getting address details...', 'info');

            const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&addressdetails=1&accept-language=en&zoom=18`,
                { headers: { 'User-Agent': 'StrayAnimalRescueApp/1.0' } }
            );

            if (!response.ok) throw new Error('Geocoding failed');

            const data = await response.json();
            const addr = data.address || {};

            console.log('Full address data:', data);

            if (data.display_name) {
                document.getElementById('addressInput').value = data.display_name;
            }

            // ==================== CITY EXTRACTION (GUARANTEED) ====================
            let city = '';

            const citySources = [
                addr.city, addr.town, addr.village, addr.suburb, addr.municipality,
                addr.county, addr.district, addr.neighbourhood, addr.quarter,
                addr.residential, addr.road, addr.hamlet, addr.locality, addr.region
            ];

            for (const source of citySources) {
                if (source && typeof source === 'string' && source.trim().length > 0) {
                    city = source.trim();
                    console.log(`City found: ${city}`);
                    break;
                }
            }

            if (!city) {
                city = `Location near ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                console.log('Using coordinates as city fallback');
            }

            const cityInput = document.getElementById('cityInput');
            cityInput.value = city;
            cityInput.classList.add('auto-filled');
            setTimeout(() => cityInput.classList.add('auto-filled-success'), 500);
            setTimeout(() => {
                cityInput.classList.remove('auto-filled', 'auto-filled-success');
            }, 3000);

            // ==================== STATE EXTRACTION (GUARANTEED) ====================
            let state = '';

            const stateSources = [
                addr.state, addr.region, addr.province, addr.county, addr.district, addr.island
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

            if (!state && data.display_name) {
                state = matchState(data.display_name);
                if (state) console.log(`State matched from display_name: ${state}`);
            }

            if (!state) {
                state = estimateStateFromCoordinates(lat, lng);
                if (state) console.log(`State estimated from coordinates: ${state}`);
            }

            if (!state) {
                state = 'Kuala Lumpur';
                console.log('Using default state: Kuala Lumpur');
            }

            const stateSelect = document.getElementById('stateInput');
            stateSelect.value = state;
            stateSelect.disabled = false;
            stateSelect.classList.add('auto-filled');
            setTimeout(() => stateSelect.classList.add('auto-filled-success'), 500);
            setTimeout(() => {
                stateSelect.classList.remove('auto-filled', 'auto-filled-success');
                stateSelect.disabled = true;
            }, 3000);

            showToast('Address, city, and state updated successfully', 'success');
            return { city, state };

        } catch (error) {
            console.warn('Geocoding failed:', error);

            // ==================== FALLBACK VALUES WHEN API FAILS ====================
            const cityInput = document.getElementById('cityInput');
            const stateSelect = document.getElementById('stateInput');

            if (!cityInput.value) {
                cityInput.value = `Location near ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                cityInput.classList.add('auto-filled');
            }

            if (!stateSelect.value) {
                const estimatedState = estimateStateFromCoordinates(lat, lng) || 'Kuala Lumpur';
                stateSelect.value = estimatedState;
                stateSelect.disabled = false;
                stateSelect.classList.add('auto-filled');
                setTimeout(() => { stateSelect.disabled = true; }, 100);
            }

            showToast('Using fallback values for city/state', 'warning');
            return null;
        }
    }
</script>
