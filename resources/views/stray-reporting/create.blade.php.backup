<!-- Modal Overlay -->
<div id="reportModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden my-8">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-8 relative">
            <button type="button" onclick="closeReportModal()" class="absolute top-4 right-4 text-white hover:text-gray-200 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="flex items-center mb-2">
                <span class="text-4xl mr-4">📝</span>
                <h2 class="text-3xl font-bold">Submit a New Report</h2>
            </div>
            <p class="text-purple-100 text-lg">
                Help us locate and rescue stray animals in your area
            </p>
        </div>

        <!-- Form Section -->
        <div class="p-8 md:p-12 max-h-[calc(100vh-12rem)] overflow-y-auto">
            <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- GPS Location Button -->
                <div class="flex items-center gap-2 mb-4">
                    <button type="button" id="gpsBtn"
                            class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Use Current Location
                    </button>
                    <span class="text-sm text-gray-600">or click on map</span>
                </div>

                {{-- Address Search --}}
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">Search Address</label>
                    <div class="flex gap-2">
                        <input type="text" id="addressSearch" placeholder="Enter address to search..."
                               class="flex-1 border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition">
                        <button type="button" id="searchBtn"
                                class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-purple-800 transition duration-300 shadow-lg">
                            Search
                        </button>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Search for an address or click on the map to pin a location</p>
                </div>

                {{-- Map --}}
                <div>
                    <label class="block text-gray-800 font-semibold mb-2">
                        Select Location on Map <span class="text-red-600">*</span>
                    </label>
                    <div id="map" class="rounded-xl shadow-lg" style="height: 350px;"></div>
                    <p class="text-sm text-red-600 mt-2 hidden" id="mapError">Please select a location on the map</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-800 font-semibold mb-2">
                            Latitude <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="latitude" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-gray-50" required readonly>
                    </div>

                    <div>
                        <label class="block text-gray-800 font-semibold mb-2">
                            Longitude <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="longitude" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border bg-gray-50" required readonly>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-800 font-semibold mb-2">
                        Address <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="address" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition" required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-800 font-semibold mb-2">
                            City <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="city" list="citySuggestions" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition" required>
                    </div>

                    <div>
                        <label class="block text-gray-800 font-semibold mb-2">
                            State <span class="text-red-600">*</span>
                        </label>
                        <div class="relative">
                            <select name="state" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition appearance-none cursor-pointer bg-white pr-10" required>
                                <option value="" disabled selected>Select your state</option>
                                <option value="Johor">Johor</option>
                                <option value="Kedah">Kedah</option>
                                <option value="Kelantan">Kelantan</option>
                                <option value="Malacca">Malacca</option>
                                <option value="Negeri Sembilan">Negeri Sembilan</option>
                                <option value="Pahang">Pahang</option>
                                <option value="Penang">Penang</option>
                                <option value="Perak">Perak</option>
                                <option value="Perlis">Perlis</option>
                                <option value="Sabah">Sabah</option>
                                <option value="Sarawak">Sarawak</option>
                                <option value="Selangor">Selangor</option>
                                <option value="Terengganu">Terengganu</option>
                                <option value="Kuala Lumpur">Kuala Lumpur</option>
                                <option value="Putrajaya">Putrajaya</option>
                                <option value="Labuan">Labuan</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-800 font-semibold mb-2">
                        Upload Images <span class="text-red-600">*</span>
                    </label>
                    <input type="file" name="images[]" multiple accept="image/*"
                           class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition">
                    <p class="text-sm text-gray-600 mt-2">You can upload multiple images (max 5MB each, hold Ctrl/Cmd to select multiple files)</p>
                </div>

                <div>
                    <label class="block text-gray-800 font-semibold mb-2">
                        Description <span class="text-red-600">*</span>
                    </label>
                    <textarea name="description" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition" placeholder="Provide details about the animal and situation..." required></textarea>
                </div>

                <div class="flex justify-end gap-4 pt-4">
                    <button type="button" onclick="closeReportModal()" class="px-8 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition duration-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-bold rounded-lg hover:from-purple-700 hover:to-purple-800 transition duration-300 shadow-lg">
                        Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toastContainer" class="fixed top-4 right-4 z-[100] space-y-2"></div>

@verbatim
    <script>
        // ============================================
        // GLOBAL VARIABLES AND CONFIGURATION
        // ============================================
        let map;
        let marker;
        let mapInitialized = false;
        let lastSearchTime = 0;
        const SEARCH_COOLDOWN = 2000; // 2 seconds

        // Malaysian states for lookup
        const malaysianStates = [
            'Johor', 'Kedah', 'Kelantan', 'Malacca', 'Negeri Sembilan',
            'Pahang', 'Penang', 'Perak', 'Perlis', 'Sabah',
            'Sarawak', 'Selangor', 'Terengganu', 'Kuala Lumpur', 'Putrajaya', 'Labuan'
        ];

        // Comprehensive Malaysian city-state mapping
        const malaysianCities = {
            // Johor (200+ cities)
            'Johor Bahru': 'Johor', 'Johor Baharu': 'Johor', 'JB': 'Johor',
            'Pasir Gudang': 'Johor', 'Muar': 'Johor', 'Batu Pahat': 'Johor',
            'Segamat': 'Johor', 'Kluang': 'Johor', 'Kulai': 'Johor',
            'Pontian': 'Johor', 'Tangkak': 'Johor', 'Mersing': 'Johor',
            'Kota Tinggi': 'Johor', 'Senai': 'Johor', 'Ulu Tiram': 'Johor',
            'Skudai': 'Johor', 'Taman Daya': 'Johor', 'Gelang Patah': 'Johor',
            'Nusajaya': 'Johor', 'Iskandar Puteri': 'Johor',

            // Kedah (150+ cities)
            'Alor Setar': 'Kedah', 'Sungai Petani': 'Kedah', 'Kulim': 'Kedah',
            'Jitra': 'Kedah', 'Baling': 'Kedah', 'Bandar Baharu': 'Kedah',
            'Langkawi': 'Kedah', 'Yan': 'Kedah', 'Sik': 'Kedah',

            // Kelantan
            'Kota Bharu': 'Kelantan', 'Kuala Krai': 'Kelantan', 'Tanah Merah': 'Kelantan',
            'Pasir Mas': 'Kelantan', 'Tumpat': 'Kelantan', 'Bachok': 'Kelantan',

            // Malacca
            'Malacca City': 'Malacca', 'Melaka': 'Malacca', 'Alor Gajah': 'Malacca',
            'Jasin': 'Malacca', 'Masjid Tanah': 'Malacca', 'Ayer Keroh': 'Malacca',

            // Negeri Sembilan
            'Seremban': 'Negeri Sembilan', 'Port Dickson': 'Negeri Sembilan',
            'Nilai': 'Negeri Sembilan', 'Rembau': 'Negeri Sembilan',
            'Kuala Pilah': 'Negeri Sembilan', 'Tampin': 'Negeri Sembilan',
            'Bahau': 'Negeri Sembilan', 'Mantin': 'Negeri Sembilan',

            // Pahang
            'Kuantan': 'Pahang', 'Temerloh': 'Pahang', 'Bentong': 'Pahang',
            'Raub': 'Pahang', 'Jerantut': 'Pahang', 'Pekan': 'Pahang',
            'Cameron Highlands': 'Pahang', 'Genting Highlands': 'Pahang',

            // Penang
            'Georgetown': 'Penang', 'George Town': 'Penang', 'Butterworth': 'Penang',
            'Bayan Lepas': 'Penang', 'Bukit Mertajam': 'Penang', 'Sungai Ara': 'Penang',
            'Air Itam': 'Penang', 'Batu Ferringhi': 'Penang', 'Tanjung Bungah': 'Penang',

            // Perak
            'Ipoh': 'Perak', 'Taiping': 'Perak', 'Sitiawan': 'Perak',
            'Teluk Intan': 'Perak', 'Kuala Kangsar': 'Perak', 'Batu Gajah': 'Perak',
            'Kampar': 'Perak', 'Lumut': 'Perak', 'Gopeng': 'Perak',

            // Perlis
            'Kangar': 'Perlis', 'Arau': 'Perlis', 'Padang Besar': 'Perlis',

            // Sabah
            'Kota Kinabalu': 'Sabah', 'KK': 'Sabah', 'Sandakan': 'Sabah',
            'Tawau': 'Sabah', 'Lahad Datu': 'Sabah', 'Keningau': 'Sabah',
            'Kudat': 'Sabah', 'Semporna': 'Sabah', 'Ranau': 'Sabah',

            // Sarawak
            'Kuching': 'Sarawak', 'Miri': 'Sarawak', 'Sibu': 'Sarawak',
            'Bintulu': 'Sarawak', 'Limbang': 'Sarawak', 'Sarikei': 'Sarawak',
            'Sri Aman': 'Sarawak', 'Kapit': 'Sarawak', 'Samarahan': 'Sarawak',

            // Selangor (250+ cities)
            'Shah Alam': 'Selangor', 'Petaling Jaya': 'Selangor', 'PJ': 'Selangor',
            'Subang Jaya': 'Selangor', 'Klang': 'Selangor', 'Kajang': 'Selangor',
            'Selayang': 'Selangor', 'Rawang': 'Selangor', 'Sungai Buloh': 'Selangor',
            'Bangi': 'Selangor', 'Ampang': 'Selangor', 'Puchong': 'Selangor',
            'Cyberjaya': 'Selangor', 'Damansara': 'Selangor', 'Cheras': 'Selangor',
            'Kepong': 'Selangor', 'Setapak': 'Selangor', 'Gombak': 'Selangor',
            'Sepang': 'Selangor', 'Kuala Selangor': 'Selangor',

            // Kuala Lumpur (Federal Territory)
            'Kuala Lumpur': 'Kuala Lumpur', 'KL': 'Kuala Lumpur',
            'Bukit Bintang': 'Kuala Lumpur', 'KLCC': 'Kuala Lumpur',
            'Mont Kiara': 'Kuala Lumpur', 'Bangsar': 'Kuala Lumpur',
            'Desa ParkCity': 'Kuala Lumpur', 'Taman Tun Dr Ismail': 'Kuala Lumpur',
            'Wangsa Maju': 'Kuala Lumpur', 'Sri Hartamas': 'Kuala Lumpur',

            // Terengganu
            'Kuala Terengganu': 'Terengganu', 'Chukai': 'Terengganu',
            'Kemaman': 'Terengganu', 'Dungun': 'Terengganu',
            'Marang': 'Terengganu', 'Kuala Berang': 'Terengganu',

            // Labuan (Federal Territory)
            'Labuan': 'Labuan', 'Victoria': 'Labuan',

            // Putrajaya (Federal Territory)
            'Putrajaya': 'Putrajaya'
        };

        // Malaysia bounding box for validation
        const MALAYSIA_BOUNDS = {
            north: 7.35,
            south: 0.85,
            east: 119.27,
            west: 99.64
        };

        // State capitals for auto-correction
        const stateCapitals = {
            'Johor': 'Johor Bahru',
            'Kedah': 'Alor Setar',
            'Kelantan': 'Kota Bharu',
            'Malacca': 'Malacca City',
            'Negeri Sembilan': 'Seremban',
            'Pahang': 'Kuantan',
            'Penang': 'Georgetown',
            'Perak': 'Ipoh',
            'Perlis': 'Kangar',
            'Sabah': 'Kota Kinabalu',
            'Sarawak': 'Kuching',
            'Selangor': 'Shah Alam',
            'Terengganu': 'Kuala Terengganu',
            'Kuala Lumpur': 'Kuala Lumpur',
            'Putrajaya': 'Putrajaya',
            'Labuan': 'Labuan'
        };

        // ============================================
        // UTILITY FUNCTIONS
        // ============================================

        // Function to check if text contains Chinese characters
        function containsChinese(text) {
            if (!text) return false;
            const chineseRegex = /[\u4e00-\u9fff\u3400-\u4dbf\uf900-\ufaff]/;
            return chineseRegex.test(text);
        }

        // Function to filter out Chinese characters from text
        function filterChinese(text) {
            if (!text) return '';
            const filtered = text.replace(/[\u4e00-\u9fff\u3400-\u4dbf\uf900-\ufaff]/g, '').trim();
            return filtered.replace(/\s*,\s*,/g, ',').replace(/,+/g, ',').replace(/,\s*$/, '');
        }

        // Function to clean address data by removing Chinese characters
        function cleanAddressData(data) {
            if (!data) return '';

            if (typeof data === 'string') {
                return filterChinese(data);
            }

            if (Array.isArray(data)) {
                return data.map(item => filterChinese(item)).filter(item => item !== '');
            }

            return data;
        }

        // Function to get state from city name
        function getStateFromCity(city) {
            if (!city) return null;

            const cleanCity = city.trim().toLowerCase();

            // Direct lookup
            for (const [cityName, state] of Object.entries(malaysianCities)) {
                if (cleanCity === cityName.toLowerCase()) {
                    return state;
                }
            }

            // Partial matches
            for (const [cityName, state] of Object.entries(malaysianCities)) {
                if (cleanCity.includes(cityName.toLowerCase()) ||
                    cityName.toLowerCase().includes(cleanCity)) {
                    return state;
                }
            }

            // Common abbreviations
            const abbreviationMap = {
                'jb': 'Johor',
                'pj': 'Selangor',
                'kk': 'Sabah',
                'kl': 'Kuala Lumpur'
            };

            if (abbreviationMap[cleanCity]) {
                return abbreviationMap[cleanCity];
            }

            return null;
        }

        // Function to auto-correct city based on selected state
        function autoCorrectCity(city, state) {
            if (!city || !state) return city;

            const citiesInState = Object.entries(malaysianCities)
                .filter(([_, cityState]) => cityState === state)
                .map(([cityName]) => cityName);

            if (citiesInState.length === 0) return city;

            const cleanCity = city.trim().toLowerCase();
            for (const validCity of citiesInState) {
                if (cleanCity === validCity.toLowerCase() ||
                    cleanCity.includes(validCity.toLowerCase()) ||
                    validCity.toLowerCase().includes(cleanCity)) {
                    return validCity;
                }
            }

            return stateCapitals[state] || city;
        }

        // Function to detect and fix city-state mismatch
        function detectAndFixMismatch(cityInput, stateInput) {
            const city = cityInput.value.trim();
            const state = stateInput.value;

            if (!city || !state) return false;

            const correctState = getStateFromCity(city);

            if (correctState && correctState !== state) {
                const shouldFix = confirm(
                    `The city "${city}" is typically in ${correctState}, not ${state}.\n\n` +
                    `Do you want to:\n` +
                    `1. Change state to ${correctState}?\n` +
                    `2. Keep state as ${state} and auto-correct city?`
                );

                if (shouldFix) {
                    stateInput.value = correctState;
                    showToast(`State changed to ${correctState} to match city`, 'info');
                } else {
                    const correctedCity = autoCorrectCity(city, state);
                    if (correctedCity !== city) {
                        cityInput.value = correctedCity;
                        showToast(`City auto-corrected to "${correctedCity}" to match state`, 'info');
                    }
                }
                return true;
            }

            return false;
        }

        // Show city suggestions based on selected state
        function showCitySuggestions(state) {
            const citiesInState = Object.entries(malaysianCities)
                .filter(([_, cityState]) => cityState === state)
                .map(([cityName]) => cityName);

            if (citiesInState.length === 0) return;

            let datalist = document.getElementById('citySuggestions');
            if (!datalist) {
                datalist = document.createElement('datalist');
                datalist.id = 'citySuggestions';
                document.body.appendChild(datalist);
            }

            datalist.innerHTML = '';
            citiesInState.forEach(city => {
                const option = document.createElement('option');
                option.value = city;
                datalist.appendChild(option);
            });
        }

        // Check if coordinates are within Malaysia bounds
        function isInMalaysiaBounds(lat, lng) {
            return (
                lat >= MALAYSIA_BOUNDS.south &&
                lat <= MALAYSIA_BOUNDS.north &&
                lng >= MALAYSIA_BOUNDS.west &&
                lng <= MALAYSIA_BOUNDS.east
            );
        }

        // Rate limiting for searches
        function checkRateLimit() {
            const now = Date.now();
            if (now - lastSearchTime < SEARCH_COOLDOWN) {
                showToast('Please wait a moment before searching again', 'warning');
                return false;
            }
            lastSearchTime = now;
            return true;
        }

        // Toast notification system
        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `px-6 py-3 rounded-lg shadow-lg text-white font-medium transform transition-all duration-300 ${
                type === 'error' ? 'bg-red-500' :
                    type === 'success' ? 'bg-green-500' :
                        type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
            }`;
            toast.textContent = message;

            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        // ============================================
        // ENHANCED FETCH WITH NETWORK ERROR HANDLING
        // ============================================

        // Enhanced fetch function with timeout and retry logic
        async function enhancedFetch(url, options = {}, maxRetries = 2, timeout = 10000) {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), timeout);

            for (let attempt = 0; attempt <= maxRetries; attempt++) {
                try {
                    // Add delay for retries (exponential backoff)
                    if (attempt > 0) {
                        const delay = Math.min(1000 * Math.pow(2, attempt), 5000);
                        await new Promise(resolve => setTimeout(resolve, delay));
                        console.log(`Retry attempt ${attempt} for: ${url}`);
                    }

                    const response = await fetch(url, {
                        ...options,
                        signal: controller.signal
                    });

                    clearTimeout(timeoutId);

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status} ${response.statusText}`);
                    }

                    return response;
                } catch (error) {
                    clearTimeout(timeoutId);

                    // Check error type
                    if (error.name === 'AbortError') {
                        throw new Error(`Request timeout after ${timeout}ms`);
                    } else if (error.name === 'TypeError' && error.message.includes('fetch')) {
                        if (attempt === maxRetries) {
                            throw new Error('Network error: Unable to connect to server. Please check your internet connection.');
                        }
                        console.log(`Network error on attempt ${attempt + 1}, retrying...`);
                    } else if (error.message.includes('Failed to fetch')) {
                        if (attempt === maxRetries) {
                            throw new Error('Network error: Cannot reach the server. Please check your connection.');
                        }
                        console.log(`Fetch failed on attempt ${attempt + 1}, retrying...`);
                    } else {
                        // Re-throw other errors
                        throw error;
                    }
                }
            }

            throw new Error('Max retries exceeded');
        }

        // Safe fetch wrapper for API calls
        async function safeFetch(url, options = {}) {
            try {
                const response = await enhancedFetch(url, options);
                return await response.json();
            } catch (error) {
                console.error('Fetch error:', error);

                // Show appropriate error message
                if (error.message.includes('Network error') ||
                    error.message.includes('Cannot reach') ||
                    error.message.includes('Unable to connect')) {
                    showToast('Network error. Please check your internet connection and try again.', 'error');
                } else if (error.message.includes('timeout')) {
                    showToast('Request timeout. The server is taking too long to respond.', 'error');
                } else if (error.message.includes('HTTP error')) {
                    showToast('Server error. Please try again later.', 'error');
                } else {
                    showToast('An error occurred. Please try again.', 'error');
                }

                throw error;
            }
        }

        // ============================================
        // MAP AND LOCATION FUNCTIONS
        // ============================================

        // Function to update marker and form fields with city-state validation
        function updateLocation(lat, lng, address = '', city = '', state = '') {
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng]).addTo(map);
            }

            document.querySelector('input[name="latitude"]').value = lat.toFixed(6);
            document.querySelector('input[name="longitude"]').value = lng.toFixed(6);

            // Clean the data first
            address = cleanAddressData(address);
            city = cleanAddressData(city);
            state = cleanAddressData(state);

            // Update address field
            if (address) document.querySelector('input[name="address"]').value = address;

            // Auto-detect state if not provided
            if (!state && city) {
                const detectedState = getStateFromCity(city);
                if (detectedState) {
                    state = detectedState;
                }
            }

            // Auto-correct city based on state
            if (state && city) {
                const correctedCity = autoCorrectCity(city, state);
                if (correctedCity !== city) {
                    city = correctedCity;
                }
            }

            // Update city and state fields
            if (city) document.querySelector('input[name="city"]').value = city;
            if (state && malaysianStates.includes(state)) {
                document.querySelector('select[name="state"]').value = state;
                showCitySuggestions(state);
            }

            map.setView([lat, lng], 15);
            document.getElementById('mapError').classList.add('hidden');

            // Check for mismatch after a short delay
            setTimeout(() => {
                const cityInput = document.querySelector('input[name="city"]');
                const stateInput = document.querySelector('select[name="state"]');
                detectAndFixMismatch(cityInput, stateInput);
            }, 500);
        }

        // Reverse geocode function with network error handling
        async function reverseGeocode(lat, lng) {
            try {
                const data = await safeFetch(
                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1&accept-language=en`,
                    {},
                    1,
                    8000
                );

                if (data && data.address) {
                    let address = data.display_name || '';
                    let city = data.address.city || data.address.town || data.address.village || data.address.county || data.address.suburb || data.address.neighbourhood || '';
                    let state = data.address.state || '';

                    // Clean all fields
                    address = cleanAddressData(address);
                    city = cleanAddressData(city);
                    state = cleanAddressData(state);

                    // If state contains "State", remove it
                    state = state.replace('State', '').trim();

                    // Special handling for Kuala Lumpur
                    if (state === 'Kuala Lumpur' || state === 'Federal Territory of Kuala Lumpur') {
                        city = 'Kuala Lumpur';
                        state = 'Kuala Lumpur';
                    }

                    // Auto-correct city based on state
                    if (state && city) {
                        const correctedCity = autoCorrectCity(city, state);
                        if (correctedCity !== city) {
                            city = correctedCity;
                        }
                    }

                    // Update form fields
                    document.querySelector('input[name="address"]').value = address;
                    document.querySelector('input[name="city"]').value = city;

                    // Set state if found
                    if (state) {
                        const matchedState = malaysianStates.find(s =>
                            state.toLowerCase().includes(s.toLowerCase()) ||
                            s.toLowerCase().includes(state.toLowerCase())
                        );

                        if (matchedState) {
                            document.querySelector('select[name="state"]').value = matchedState;
                            showCitySuggestions(matchedState);
                        }
                    }

                    // Trigger mismatch check
                    setTimeout(() => {
                        const cityInput = document.querySelector('input[name="city"]');
                        const stateInput = document.querySelector('select[name="state"]');
                        detectAndFixMismatch(cityInput, stateInput);
                    }, 500);
                }
            } catch (error) {
                // Error already handled by safeFetch
                console.log('Reverse geocode skipped due to network error');
            }
        }

        // Get current location via GPS with network error handling
        async function getCurrentLocation() {
            if (!navigator.geolocation) {
                showToast('Geolocation is not supported by your browser', 'error');
                return;
            }

            const gpsBtn = document.getElementById('gpsBtn');
            const originalText = gpsBtn.innerHTML;

            gpsBtn.innerHTML = '<span class="loading-spinner"></span> Getting location...';
            gpsBtn.disabled = true;

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    // Check if within Malaysia
                    if (!isInMalaysiaBounds(lat, lng)) {
                        showToast('Your location appears to be outside Malaysia', 'warning');
                        gpsBtn.innerHTML = originalText;
                        gpsBtn.disabled = false;
                        return;
                    }

                    updateLocation(lat, lng);

                    try {
                        const data = await safeFetch(
                            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1&accept-language=en`
                        );

                        if (data && data.address) {
                            const addr = data.address;
                            const address = data.display_name || '';
                            const city = addr.city || addr.town || addr.village || '';
                            const state = addr.state || '';

                            document.querySelector('input[name="address"]').value = cleanAddressData(address);
                            document.querySelector('input[name="city"]').value = cleanAddressData(city);

                            if (state) {
                                const matchedState = malaysianStates.find(s =>
                                    state.toLowerCase().includes(s.toLowerCase())
                                );
                                if (matchedState) {
                                    document.querySelector('select[name="state"]').value = matchedState;
                                    showCitySuggestions(matchedState);
                                }
                            }

                            showToast('Location found successfully!', 'success');
                        }
                    } catch (error) {
                        showToast('Location found, but address details unavailable due to network error', 'info');
                    }

                    gpsBtn.innerHTML = originalText;
                    gpsBtn.disabled = false;
                },
                (error) => {
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            showToast('Location access denied. Please enable location services.', 'error');
                            break;
                        case error.POSITION_UNAVAILABLE:
                            showToast('Location information unavailable.', 'error');
                            break;
                        case error.TIMEOUT:
                            showToast('Location request timeout.', 'error');
                            break;
                        default:
                            showToast('Unable to get your location.', 'error');
                    }
                    gpsBtn.innerHTML = originalText;
                    gpsBtn.disabled = false;
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        // ============================================
        // SEARCH FUNCTIONS
        // ============================================

        // Enhanced search strategy with network error handling
        async function enhancedSearchStrategy(query, strategyType) {
            let url;

            switch (strategyType) {
                case 'withMalaysia':
                    url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ', Malaysia')}&limit=5&accept-language=en`;
                    break;
                case 'withoutMalaysia':
                    url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&accept-language=en`;
                    break;
                case 'simplified':
                    const simplifiedQuery = query.replace(/\d+/g, '').trim();
                    if (simplifiedQuery.length > 3) {
                        url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(simplifiedQuery + ', Malaysia')}&limit=5&accept-language=en`;
                    } else {
                        return null;
                    }
                    break;
                case 'shortened':
                    const words = query.split(' ');
                    if (words.length > 3) {
                        const shortQuery = words.slice(0, 3).join(' ') + ', Malaysia';
                        url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(shortQuery)}&limit=5&accept-language=en`;
                    } else {
                        return null;
                    }
                    break;
                default:
                    return null;
            }

            try {
                const data = await safeFetch(url, {}, 1, 10000);
                return data && data.length > 0 ? data[0] : null;
            } catch (error) {
                // If it's a network error, we want to continue to next strategy
                if (error.message.includes('Network error') ||
                    error.message.includes('timeout') ||
                    error.message.includes('Cannot reach')) {
                    console.log(`Strategy ${strategyType} failed due to network, trying next...`);
                    return null;
                }
                throw error; // Re-throw other errors
            }
        }

        // Try multiple search strategies with network resilience
        async function searchStrategies(query) {
            const strategies = [
                {type: 'withMalaysia', name: 'Search with Malaysia suffix'},
                {type: 'withoutMalaysia', name: 'Search without suffix'},
                {type: 'simplified', name: 'Simplified search'},
                {type: 'shortened', name: 'Shortened search'}
            ];

            // Try each strategy until we find a result
            for (const strategy of strategies) {
                try {
                    const result = await enhancedSearchStrategy(query, strategy.type);
                    if (result && !containsChinese(result.display_name)) {
                        console.log(`Found result using strategy: ${strategy.name}`);
                        return result;
                    }
                } catch (error) {
                    console.error(`Strategy ${strategy.name} error:`, error);
                    // Continue to next strategy for non-network errors too
                }
            }

            return null;
        }

        // Get detailed address with fallback and network error handling
        async function getDetailedAddress(lat, lng) {
            try {
                const data = await safeFetch(
                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1&accept-language=en`,
                    {},
                    1,
                    8000
                );
                return data;
            } catch (error) {
                console.warn('Detailed address fetch failed, using basic info');
                return null;
            }
        }

        // Main search address function
        async function searchAddress() {
            const query = document.getElementById('addressSearch').value.trim();

            if (!query) {
                showToast('Please enter an address to search', 'warning');
                return;
            }

            // Check if search query contains Chinese
            if (containsChinese(query)) {
                showToast('Please search using English only. Chinese characters are not allowed in the search.', 'warning');
                return;
            }

            // Rate limiting
            if (!checkRateLimit()) return;

            const searchBtn = document.getElementById('searchBtn');
            searchBtn.textContent = 'Searching...';
            searchBtn.disabled = true;

            try {
                const result = await searchStrategies(query);

                if (result) {
                    const lat = parseFloat(result.lat);
                    const lng = parseFloat(result.lon);
                    const address = cleanAddressData(result.display_name);

                    // Validate coordinates are within Malaysia
                    if (!isInMalaysiaBounds(lat, lng)) {
                        showToast('Selected location is outside Malaysia', 'error');
                        return;
                    }

                    // Get detailed address info
                    const detailedData = await getDetailedAddress(lat, lng);

                    if (detailedData) {
                        const addr = detailedData.address;
                        let city = addr.city || addr.town || addr.village || addr.suburb || addr.county || '';
                        let state = addr.state || '';

                        // Clean data
                        city = cleanAddressData(city);
                        state = cleanAddressData(state);
                        state = state.replace('State', '').trim();

                        // Special handling for Kuala Lumpur
                        if (state === 'Kuala Lumpur' || state === 'Federal Territory of Kuala Lumpur') {
                            city = 'Kuala Lumpur';
                            state = 'Kuala Lumpur';
                        }

                        updateLocation(lat, lng, address, city, state);
                        showToast('Address found successfully!', 'success');
                    } else {
                        // If we can't get detailed info, at least update with basic coordinates
                        updateLocation(lat, lng, address, '', '');
                        showToast('Address found, but detailed information unavailable', 'info');
                    }
                } else {
                    showToast('Address not found. Try being more specific or use map click', 'error');
                }
            } catch (error) {
                console.error('Search error:', error);

                // Check if it's a network error
                if (error.message.includes('Network error') ||
                    error.message.includes('Cannot reach') ||
                    error.message.includes('Unable to connect')) {
                    showToast('Network error. Please check your internet connection.', 'error');
                } else if (error.message.includes('timeout')) {
                    showToast('Search timeout. Please try again.', 'error');
                } else {
                    showToast('Search failed. Please try again.', 'error');
                }
            } finally {
                searchBtn.textContent = 'Search';
                searchBtn.disabled = false;
            }
        }

        // ============================================
        // FORM VALIDATION AND EVENT HANDLERS
        // ============================================

        // Setup city-state validation
        function setupCityStateValidation() {
            const cityInput = document.querySelector('input[name="city"]');
            const stateInput = document.querySelector('select[name="state"]');

            // Validate when city changes
            cityInput.addEventListener('blur', function () {
                if (this.value.trim() && stateInput.value) {
                    detectAndFixMismatch(this, stateInput);
                }
            });

            // Validate when state changes
            stateInput.addEventListener('change', function () {
                if (cityInput.value.trim() && this.value) {
                    detectAndFixMismatch(cityInput, this);
                }
                showCitySuggestions(this.value);
            });
        }

        // Form validation
        function setupFormValidation() {
            const form = document.querySelector('form');
            const mapError = document.getElementById('mapError');

            form.addEventListener('submit', function (e) {
                const latitude = document.querySelector('input[name="latitude"]').value;
                const longitude = document.querySelector('input[name="longitude"]').value;
                const state = document.querySelector('select[name="state"]').value;
                const address = document.querySelector('input[name="address"]').value;
                const city = document.querySelector('input[name="city"]').value;
                const cityInput = document.querySelector('input[name="city"]');
                const stateInput = document.querySelector('select[name="state"]');

                // Check for Chinese characters
                if (containsChinese(address) || containsChinese(city)) {
                    e.preventDefault();
                    showToast('Please enter address and city without Chinese characters. Use English only.', 'error');
                    return false;
                }

                // Check for city-state mismatch
                if (city && state) {
                    const correctState = getStateFromCity(city);
                    if (correctState && correctState !== state) {
                        e.preventDefault();

                        const userChoice = confirm(
                            `Warning: City-State Mismatch!\n\n` +
                            `The city "${city}" is typically in ${correctState}, but you selected ${state}.\n\n` +
                            `Do you want to:\n` +
                            `1. Change state to ${correctState} (Recommended)\n` +
                            `2. Keep as is and submit\n` +
                            `3. Cancel and fix manually`
                        );

                        if (userChoice === true) {
                            stateInput.value = correctState;
                            setTimeout(() => this.submit(), 100);
                            return false;
                        } else if (userChoice === false) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                }

                if (!latitude || !longitude) {
                    e.preventDefault();
                    mapError.classList.remove('hidden');
                    document.getElementById('map').scrollIntoView({behavior: 'smooth', block: 'center'});
                    showToast('Please select a location on the map before submitting the form.', 'error');
                    return false;
                }

                if (!state) {
                    e.preventDefault();
                    showToast('Please select a state from the dropdown.', 'error');
                    document.querySelector('select[name="state"]').focus();
                    return false;
                }

                const requiredFields = form.querySelectorAll('[required]');
                let allValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        allValid = false;
                        field.classList.add('border-red-500');
                        field.classList.remove('border-gray-300');
                    } else {
                        field.classList.remove('border-red-500');
                        field.classList.add('border-gray-300');
                    }
                });

                if (!allValid) {
                    e.preventDefault();
                    showToast('Please fill in all required fields.', 'error');
                    return false;
                }

                // File validation
                const fileInput = form.querySelector('input[name="images[]"]');
                const maxSize = 5 * 1024 * 1024; // 5MB

                if (fileInput.files.length === 0) {
                    e.preventDefault();
                    showToast('Please upload at least one image.', 'error');
                    return false;
                }

                for (let file of fileInput.files) {
                    if (!file.type.startsWith('image/')) {
                        e.preventDefault();
                        showToast(`File "${file.name}" is not an image.`, 'error');
                        return false;
                    }
                    if (file.size > maxSize) {
                        e.preventDefault();
                        showToast(`File "${file.name}" is too large (max 5 MB).`, 'error');
                        return false;
                    }
                }

                return true;
            });

            // Remove red border on input
            form.querySelectorAll('[required]').forEach(field => {
                field.addEventListener('input', function () {
                    if (this.value.trim()) {
                        this.classList.remove('border-red-500');
                        this.classList.add('border-gray-300');
                    }
                });
            });
        }

        // ============================================
        // INITIALIZATION FUNCTIONS
        // ============================================

        function initializeMap() {
            if (mapInitialized) return;

            const defaultLat = 3.139;
            const defaultLng = 101.6869;

            map = L.map('map').setView([defaultLat, defaultLng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Click on map to pin location
            map.on('click', function (e) {
                const {lat, lng} = e.latlng;
                updateLocation(lat, lng);
                reverseGeocode(lat, lng);
            });

            // Setup event listeners
            setupCityStateValidation();
            setupFormValidation();

            // Search button event
            document.getElementById('searchBtn').addEventListener('click', searchAddress);

            // Enter key for search
            document.getElementById('addressSearch').addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchAddress();
                }
            });

            // GPS button event
            document.getElementById('gpsBtn').addEventListener('click', getCurrentLocation);

            mapInitialized = true;
        }

        // ============================================
        // MODAL CONTROL FUNCTIONS
        // ============================================

        function openReportModal() {
            document.getElementById('reportModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Check online status
            if (!navigator.onLine) {
                showToast('You appear to be offline. Some features may not work.', 'warning');
            }

            // Initialize map after modal is visible
            setTimeout(function () {
                if (!mapInitialized) {
                    initializeMap();
                } else {
                    map.invalidateSize();
                }
            }, 100);
        }

        function closeReportModal() {
            document.getElementById('reportModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.getElementById('reportModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeReportModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !document.getElementById('reportModal').classList.contains('hidden')) {
                closeReportModal();
            }
        });

        // Online/offline detection
        window.addEventListener('online', () => {
            showToast('You are back online', 'success');
        });

        window.addEventListener('offline', () => {
            showToast('You are offline. Map may not load.', 'warning');
        });

        // Add CSS for loading spinner
        const style = document.createElement('style');
        style.textContent = `
    .loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255,255,255,.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    #toastContainer {
        pointer-events: none;
    }

    #toastContainer > div {
        pointer-events: auto;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
`;
        document.head.appendChild(style);
</script>
@endverbatim
