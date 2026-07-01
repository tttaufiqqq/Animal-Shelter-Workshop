<script>
    // Helper function to escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    document.addEventListener('DOMContentLoaded', function() {
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

                clearTimeout(searchTimeout);

                if (query.length < 3) {
                    searchResults.classList.add('hidden');
                    searchResults.innerHTML = '';
                    return;
                }

                searchResults.classList.remove('hidden');
                searchResults.innerHTML = '<div class="p-3 text-gray-600 text-sm">🔍 Searching...</div>';

                searchTimeout = setTimeout(async () => {
                    try {
                        const response = await fetch(
                            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=my&limit=10&addressdetails=1`,
                            { headers: { 'User-Agent': 'StrayAnimalRescueApp/1.0' } }
                        );

                        if (!response.ok) throw new Error('Search failed');

                        const results = await response.json();

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

                            document.querySelectorAll('.search-result-item').forEach(item => {
                                item.addEventListener('click', async function() {
                                    const lat = parseFloat(this.dataset.lat);
                                    const lon = parseFloat(this.dataset.lon);
                                    const name = this.dataset.name;

                                    await updateLocationOnMap(lat, lon, 10);
                                    await getAddressDetailsWithFallback(lat, lon);

                                    locationSearch.value = name;
                                    searchResults.classList.add('hidden');
                                    searchResults.innerHTML = '';

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

            document.addEventListener('click', function(e) {
                if (!locationSearch.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });

            locationSearch.addEventListener('focus', function() {
                if (searchResults.innerHTML && !searchResults.classList.contains('hidden')) {
                    searchResults.classList.remove('hidden');
                }
            });
        }
    });
</script>
