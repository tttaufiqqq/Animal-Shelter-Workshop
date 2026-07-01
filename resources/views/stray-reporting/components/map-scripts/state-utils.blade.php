<script>
    // Estimate state from GPS coordinates in Malaysia
    function estimateStateFromCoordinates(lat, lng) {
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
</script>
