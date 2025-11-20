<!-- Match Results Modal -->
<div id="matchResultModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden shadow-2xl">
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-6">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold">🐾 Your Perfect Matches</h2>
                <button onclick="closeResultModal()" class="text-white hover:text-gray-200 text-3xl leading-none">
                    &times;
                </button>
            </div>
            <p class="text-purple-100 mt-2">Based on your preferences and lifestyle</p>
        </div>

        <!-- Content -->
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
            <div id="matchesContainer">
                <!-- Loading State -->
                <div id="loadingState" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
                    <p class="mt-4 text-gray-600">Finding your perfect matches...</p>
                </div>

                <!-- Results will be inserted here -->
                <div id="matchesResults" class="hidden space-y-4"></div>

                <!-- No Results State -->
                <div id="noResultsState" class="hidden text-center py-12">
                    <div class="text-6xl mb-4">🐕</div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No matches found</h3>
                    <p class="text-gray-600">Please complete your adopter profile or check back later for new animals.</p>
                </div>

                <!-- Error State -->
                <div id="errorState" class="hidden text-center py-12">
                    <div class="text-6xl mb-4">😔</div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Oops! Something went wrong</h3>
                    <p class="text-gray-600" id="errorMessage"></p>
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
        const response = await fetch('/animal-matches');
        const data = await response.json();

        // Hide loading
        document.getElementById('loadingState').classList.add('hidden');

        if (!data.success) {
            showError(data.message);
            return;
        }

        if (data.matches.length === 0) {
            document.getElementById('noResultsState').classList.remove('hidden');
            return;
        }

        displayMatches(data.matches);
    } catch (error) {
        document.getElementById('loadingState').classList.add('hidden');
        showError('Failed to load matches. Please try again.');
        console.error('Error loading matches:', error);
    }
}

function displayMatches(matches) {
    const container = document.getElementById('matchesResults');
    container.innerHTML = '';
    container.classList.remove('hidden');

    matches.forEach((match, index) => {
        const animal = match.animal;
        const profile = match.profile;
        const score = match.score;
        const details = match.match_details;

        const matchCard = `
            <div class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-lg transition">
                <div class="flex items-start gap-4">
                    <!-- Match Badge -->
                    <div class="flex-shrink-0">
                        <div class="relative">
                            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white font-bold text-xl">
                                ${score}%
                            </div>
                            ${index === 0 ? '<div class="absolute -top-2 -right-2 bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-1 rounded-full">Best Match</div>' : ''}
                        </div>
                    </div>

                    <!-- Animal Info -->
                    <div class="flex-grow">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">${animal.name}</h3>
                                <p class="text-gray-600">${animal.species} • ${profile.age} • ${profile.size}</p>
                            </div>
                            <a href="/animal/${animal.id}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition text-sm font-semibold" target="_blank">
                                View Profile
                            </a>
                        </div>

                        <!-- Match Details -->
                        <div class="space-y-2 mb-3">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="font-semibold text-gray-700">Energy Level:</span>
                                <span class="capitalize text-gray-600">${profile.energy_level}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <span class="font-semibold text-gray-700">Temperament:</span>
                                <span class="text-gray-600">${profile.temperament || 'N/A'}</span>
                            </div>
                            <div class="flex flex-wrap gap-2 text-sm">
                                ${profile.good_with_kids ? '<span class="bg-green-100 text-green-800 px-3 py-1 rounded-full">Good with kids</span>' : ''}
                                ${profile.good_with_pets ? '<span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full">Good with pets</span>' : ''}
                                ${profile.medical_needs ? '<span class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full">Has medical needs</span>' : ''}
                            </div>
                        </div>

                        <!-- Why This Match -->
                        ${details.length > 0 ? `
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-3">
                                <p class="text-sm font-semibold text-purple-900 mb-2">Why this is a great match:</p>
                                <ul class="space-y-1">
                                    ${details.map(detail => `<li class="text-sm text-purple-800">✓ ${detail}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
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