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
                        <input type="text" name="city" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition" required>
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
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-800 font-semibold mb-2">
                        Upload Images <span class="text-red-600">*</span>
                    </label>
                    <input type="file" name="images[]" multiple accept="image/*" required
                           class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:border-purple-500 focus:ring focus:ring-purple-200 transition">
                    <p class="text-sm text-gray-600 mt-2">You can upload multiple images (hold Ctrl/Cmd to select multiple files)</p>
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

<script>
let map;
let marker;
let mapInitialized = false;

// Malaysian states for lookup
const malaysianStates = [
    'Johor', 'Kedah', 'Kelantan', 'Malacca', 'Negeri Sembilan',
    'Pahang', 'Penang', 'Perak', 'Perlis', 'Sabah',
    'Sarawak', 'Selangor', 'Terengganu'
];

function initializeMap() {
    if (mapInitialized) return;

    const defaultLat = 3.139;  
    const defaultLng = 101.6869;

    map = L.map('map').setView([defaultLat, defaultLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const form = document.querySelector('form');
    const mapError = document.getElementById('mapError');

    // Function to extract state from address parts
    function extractStateFromAddress(addressParts) {
        for (let i = addressParts.length - 1; i >= 0; i--) {
            const part = addressParts[i];
            const foundState = malaysianStates.find(state => 
                part.toLowerCase().includes(state.toLowerCase())
            );
            if (foundState) {
                return foundState;
            }
        }
        return '';
    }

    // Function to update marker and form fields
    function updateLocation(lat, lng, address = '', city = '', state = '') {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng]).addTo(map);
        }

        document.querySelector('input[name="latitude"]').value = lat.toFixed(6);
        document.querySelector('input[name="longitude"]').value = lng.toFixed(6);

        if (address) document.querySelector('input[name="address"]').value = address;
        if (city) document.querySelector('input[name="city"]').value = city;
        
        if (state && malaysianStates.includes(state)) {
            document.querySelector('select[name="state"]').value = state;
        }

        map.setView([lat, lng], 15);
        mapError.classList.add('hidden');
    }

    // Click on map to pin location
    map.on('click', function (e) {
        const { lat, lng } = e.latlng;
        updateLocation(lat, lng);
        reverseGeocode(lat, lng);
    });

    // Reverse geocode function
    function reverseGeocode(lat, lng) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`)
            .then(response => response.json())
            .then(data => {
                if (data && data.address) {
                    const address = data.display_name || '';
                    const city = data.address.city || data.address.town || data.address.village || data.address.county || '';
                    let state = data.address.state || '';
                    
                    state = state.replace('State', '').trim();
                    
                    document.querySelector('input[name="address"]').value = address;
                    document.querySelector('input[name="city"]').value = city;
                    
                    if (state && malaysianStates.includes(state)) {
                        document.querySelector('select[name="state"]').value = state;
                    }
                }
            })
            .catch(error => {
                console.error('Reverse geocoding error:', error);
            });
    }

    // Form validation
    form.addEventListener('submit', function(e) {
        const latitude = document.querySelector('input[name="latitude"]').value;
        const longitude = document.querySelector('input[name="longitude"]').value;
        const state = document.querySelector('select[name="state"]').value;
        
        if (!latitude || !longitude) {
            e.preventDefault();
            mapError.classList.remove('hidden');
            document.getElementById('map').scrollIntoView({ behavior: 'smooth', block: 'center' });
            alert('Please select a location on the map before submitting the form.');
            return false;
        }

        if (!state) {
            e.preventDefault();
            alert('Please select a state from the dropdown.');
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
            alert('Please fill in all required fields.');
            return false;
        }
    });

    // Remove red border on input
    form.querySelectorAll('[required]').forEach(field => {
        field.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('border-red-500');
                this.classList.add('border-gray-300');
            }
        });
    });

    // Search address functionality
    const searchBtn = document.getElementById('searchBtn');
    const addressSearch = document.getElementById('addressSearch');

    searchBtn.addEventListener('click', function() {
        searchAddress();
    });

    addressSearch.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchAddress();
        }
    });

    function searchAddress() {
        const query = addressSearch.value.trim();
        
        if (!query) {
            alert('Please enter an address to search');
            return;
        }

        searchBtn.textContent = 'Searching...';
        searchBtn.disabled = true;

        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1&countrycodes=my`)
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    const result = data[0];
                    const lat = parseFloat(result.lat);
                    const lng = parseFloat(result.lon);
                    
                    const addressParts = result.display_name.split(',').map(part => part.trim());
                    const address = result.display_name;
                    
                    let city = '';
                    let state = '';
                    
                    for (let part of addressParts) {
                        const foundState = malaysianStates.find(s => 
                            part.toLowerCase().includes(s.toLowerCase())
                        );
                        if (foundState) {
                            state = foundState;
                            break;
                        }
                    }
                    
                    const stateIndex = addressParts.findIndex(part => 
                        malaysianStates.some(s => part.toLowerCase().includes(s.toLowerCase()))
                    );
                    if (stateIndex > 0) {
                        city = addressParts[stateIndex - 1];
                    } else {
                        city = addressParts[1] || '';
                    }

                    updateLocation(lat, lng, address, city, state);
                } else {
                    alert('Address not found in Malaysia. Please try a different search term.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error searching address. Please try again.');
            })
            .finally(() => {
                searchBtn.textContent = 'Search';
                searchBtn.disabled = false;
            });
    }

    mapInitialized = true;
}

function openReportModal() {
    document.getElementById('reportModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Initialize map after modal is visible
    setTimeout(function() {
        if (!mapInitialized) {
            initializeMap();
        } else {
            // Invalidate size to fix display issues
            map.invalidateSize();
        }
    }, 100);
}

function closeReportModal() {
    document.getElementById('reportModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.getElementById('reportModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeReportModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('reportModal').classList.contains('hidden')) {
        closeReportModal();
    }
});
</script>