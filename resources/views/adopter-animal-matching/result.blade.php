<!-- Match Results Modal -->
<div id="matchResultModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="sticky top-0 bg-purple-600 text-white p-6 rounded-t-2xl flex justify-between items-center">
            <h2 class="text-2xl font-bold flex items-center">
                <i class="fas fa-heart mr-3"></i>
                Your Perfect Matches
            </h2>
            <button type="button" onclick="closeResultModal()" class="hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-5">
            <div id="matchesContainer">
                <!-- Loading State -->
                <div id="loadingState" class="flex flex-col items-center justify-center py-16">
                    <div class="w-12 h-12 border-4 border-purple-200 border-t-purple-600 rounded-full animate-spin"></div>
                    <p class="mt-4 text-sm text-gray-600">Finding your perfect matches...</p>
                </div>

                <!-- Results will be inserted here -->
                <div id="matchesResults" class="hidden space-y-3"></div>

                <!-- No Results State -->
                <div id="noResultsState" class="hidden text-center py-16">
                    <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No matches found</h3>
                    <p class="text-sm text-gray-600">Complete your adopter profile or check back later for new animals.</p>
                </div>

                <!-- Error State -->
                <div id="errorState" class="hidden text-center py-16">
                    <div class="w-20 h-20 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Something went wrong</h3>
                    <p class="text-sm text-gray-600" id="errorMessage"></p>
                    <button onclick="loadMatches()" class="mt-4 px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-semibold hover:from-purple-700 hover:to-indigo-700 transition shadow-lg">
                        Try Again
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openResultModal() {
    const modal = document.getElementById('matchResultModal');
    modal.classList.remove('hidden');
    loadMatches();
}

function closeResultModal() {
    const modal = document.getElementById('matchResultModal');
    modal.classList.add('hidden');
}

async function loadMatches() {
    // Show loading state
    document.getElementById('loadingState').classList.remove('hidden');
    document.getElementById('matchesResults').classList.add('hidden');
    document.getElementById('noResultsState').classList.add('hidden');
    document.getElementById('errorState').classList.add('hidden');

    try {
        // Add timeout to prevent hanging
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout

        const response = await fetch('/animal-matches', {
            signal: controller.signal,
            headers: {
                'Accept': 'application/json',
            }
        });

        clearTimeout(timeoutId);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        // Hide loading
        document.getElementById('loadingState').classList.add('hidden');

        if (!data.success) {
            showError(data.message || 'Unable to load matches');
            return;
        }

        if (!data.matches || data.matches.length === 0) {
            document.getElementById('noResultsState').classList.remove('hidden');
            return;
        }

        displayMatches(data.matches);
    } catch (error) {
        document.getElementById('loadingState').classList.add('hidden');

        if (error.name === 'AbortError') {
            showError('Request timed out. Please check your connection and try again.');
        } else {
            showError('Failed to load matches. Please try again.');
        }

        console.error('Error loading matches:', error);
    }
}

function displayMatches(matches) {
    const container = document.getElementById('matchesResults');
    container.innerHTML = '';
    container.classList.remove('hidden');

    matches.forEach((match, index) => {
        const matchCard = `
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-xl transition-shadow">
                <div class="flex gap-4 p-5">
                    <!-- Animal Image -->
                    ${match.image ? `
                        <div class="flex-shrink-0">
                            <img src="${match.image}" alt="${match.name}" class="w-32 h-32 object-cover rounded-lg">
                        </div>
                    ` : `
                        <div class="flex-shrink-0 w-32 h-32 bg-purple-100 rounded-lg flex items-center justify-center text-5xl">
                            ${match.species.toLowerCase() === 'dog' ? '🐕' : match.species.toLowerCase() === 'cat' ? '🐈' : '🐾'}
                        </div>
                    `}

                    <!-- Animal Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-4 mb-3">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-xl font-bold text-gray-900 mb-1">${match.name}</h3>
                                <p class="text-sm text-gray-600">${match.species} • ${match.age} • ${match.gender}</p>
                            </div>

                            <!-- Match Score Badge -->
                            <div class="flex-shrink-0">
                                <div class="relative">
                                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-purple-500 to-purple-700 flex items-center justify-center text-white">
                                        <div class="text-center">
                                            <div class="text-xl font-bold">${match.score}%</div>
                                            <div class="text-[10px]">Match</div>
                                        </div>
                                    </div>
                                    ${index === 0 ? '<div class="absolute -top-1 -right-1 bg-yellow-400 text-yellow-900 text-[10px] font-bold px-1.5 py-0.5 rounded-full">Top</div>' : ''}
                                </div>
                            </div>
                        </div>

                        <!-- Why This Match -->
                        ${match.match_details && match.match_details.length > 0 ? `
                            <div class="bg-purple-50 rounded-lg p-3 mb-3">
                                <p class="text-xs font-semibold text-purple-900 mb-2">Why this match:</p>
                                <ul class="space-y-1">
                                    ${match.match_details.map(detail => `
                                        <li class="text-xs text-purple-800 flex items-start gap-1.5">
                                            <span class="text-purple-500 mt-0.5">✓</span>
                                            <span>${detail}</span>
                                        </li>
                                    `).join('')}
                                </ul>
                            </div>
                        ` : ''}

                        <a href="/animal/${match.id}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-semibold hover:from-purple-700 hover:to-indigo-700 transition shadow-lg">
                            <span>View Full Profile</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        `;

        container.innerHTML += matchCard;
    });
}

function showError(message) {
    document.getElementById('errorState').classList.remove('hidden');
    document.getElementById('errorMessage').textContent = message;
}

// Close modal on outside click
document.getElementById('matchResultModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeResultModal();
    }
});
</script>