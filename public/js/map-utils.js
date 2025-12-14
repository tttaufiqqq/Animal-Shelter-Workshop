/**
 * Map Utilities for Stray Reporting
 * Handles map initialization, geocoding, and location services
 */

// Malaysia bounding box for validation
const MALAYSIA_BOUNDS = {
    north: 7.35,
    south: 0.85,
    east: 119.27,
    west: 99.64
};

// Malaysian states
const MALAYSIAN_STATES = [
    'Johor', 'Kedah', 'Kelantan', 'Malacca', 'Negeri Sembilan',
    'Pahang', 'Penang', 'Perak', 'Perlis', 'Sabah',
    'Sarawak', 'Selangor', 'Terengganu', 'Kuala Lumpur', 'Putrajaya', 'Labuan'
];

// City-State mapping
const CITY_STATE_MAP = {
    // Johor
    'Johor Bahru': 'Johor', 'JB': 'Johor', 'Pasir Gudang': 'Johor', 'Muar': 'Johor',
    'Batu Pahat': 'Johor', 'Segamat': 'Johor', 'Kluang': 'Johor', 'Kulai': 'Johor',
    // Selangor
    'Shah Alam': 'Selangor', 'Petaling Jaya': 'Selangor', 'PJ': 'Selangor',
    'Subang Jaya': 'Selangor', 'Klang': 'Selangor', 'Kajang': 'Selangor',
    'Puchong': 'Selangor', 'Cyberjaya': 'Selangor', 'Ampang': 'Selangor',
    // Kuala Lumpur
    'Kuala Lumpur': 'Kuala Lumpur', 'KL': 'Kuala Lumpur',
    // Add more as needed...
};

/**
 * Check if coordinates are within Malaysia
 */
function isInMalaysiaBounds(lat, lng) {
    return lat >= MALAYSIA_BOUNDS.south && lat <= MALAYSIA_BOUNDS.north &&
           lng >= MALAYSIA_BOUNDS.west && lng <= MALAYSIA_BOUNDS.east;
}

/**
 * Get state from city name
 */
function getStateFromCity(city) {
    if (!city) return null;
    const cleanCity = city.trim();
    return CITY_STATE_MAP[cleanCity] || null;
}

/**
 * Fetch with timeout and retry
 */
async function fetchWithTimeout(url, timeout = 8000, retries = 2) {
    for (let i = 0; i <= retries; i++) {
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), timeout);

            const response = await fetch(url, { signal: controller.signal });
            clearTimeout(timeoutId);

            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return await response.json();
        } catch (error) {
            if (i === retries) throw error;
            await new Promise(resolve => setTimeout(resolve, 1000 * (i + 1)));
        }
    }
}

/**
 * Reverse geocode coordinates to address
 */
async function reverseGeocode(lat, lng) {
    try {
        const data = await fetchWithTimeout(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1&accept-language=en`
        );

        if (data && data.address) {
            const addr = data.address;
            return {
                address: data.display_name || '',
                city: addr.city || addr.town || addr.village || addr.suburb || '',
                state: addr.state || '',
                success: true
            };
        }
        return { success: false };
    } catch (error) {
        console.error('Reverse geocode failed:', error);
        return { success: false, error: error.message };
    }
}

/**
 * Search for address
 */
async function searchAddress(query) {
    if (!query) return { success: false, error: 'Empty query' };

    try {
        const data = await fetchWithTimeout(
            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ', Malaysia')}&limit=1&accept-language=en`
        );

        if (data && data.length > 0) {
            const result = data[0];
            return {
                success: true,
                lat: parseFloat(result.lat),
                lng: parseFloat(result.lon),
                display_name: result.display_name
            };
        }
        return { success: false, error: 'No results found' };
    } catch (error) {
        console.error('Search failed:', error);
        return { success: false, error: error.message };
    }
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        isInMalaysiaBounds,
        getStateFromCity,
        reverseGeocode,
        searchAddress,
        MALAYSIAN_STATES
    };
}
