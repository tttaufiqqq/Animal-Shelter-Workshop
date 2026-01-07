<!-- Match Results Modal -->
<div id="matchResultModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
    <div id="matchModalContainer" class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Animation Styles -->
        <style>
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes scaleIn {
                from {
                    opacity: 0;
                    transform: scale(0.95);
                }
                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }

            .animate-fade-in-up {
                animation: fadeInUp 0.6s ease-out forwards;
                opacity: 0;
            }

            .animate-scale-in {
                animation: scaleIn 0.5s ease-out forwards;
                opacity: 0;
            }

            /* Smooth hover effect */
            .match-card {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .match-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            }

            /* Score number styling */
            .score-number {
                display: inline-block;
            }

            /* Match score pulse animation */
            @keyframes scorePulse {
                0%, 100% {
                    transform: scale(1);
                    box-shadow: 0 0 0 0 rgba(168, 85, 247, 0.7);
                }
                50% {
                    transform: scale(1.05);
                    box-shadow: 0 0 0 10px rgba(168, 85, 247, 0);
                }
            }

            .score-badge {
                animation: scorePulse 2s ease-in-out infinite;
            }

            .score-badge-top {
                animation: scorePulse 1.5s ease-in-out infinite;
            }

            /* Animal image hover effect */
            .animal-image {
                transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            }

            .match-card:hover .animal-image {
                transform: scale(1.05) rotate(2deg);
            }
        </style>
        <!-- Header -->
        <div class="sticky top-0 bg-purple-600 text-white p-6 rounded-t-2xl flex justify-between items-center z-10 shadow-md">
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

        <!-- Information Section -->
        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border-l-4 border-purple-500 p-5 mx-6 mt-6 rounded-lg">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-purple-900 mb-2">How Animal Matching Works</h3>
                    <div class="text-sm text-purple-800 space-y-2">
                        <p>We've analyzed your adopter profile to find animals that best match your lifestyle, preferences, and home environment.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-3">
                            <div class="flex items-start gap-2">
                                <span class="text-purple-500 font-bold">•</span>
                                <span><strong>Match Score:</strong> Higher percentages indicate better compatibility</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-purple-500 font-bold">•</span>
                                <span><strong>Top Match:</strong> Your most compatible animal appears first</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-purple-500 font-bold">•</span>
                                <span><strong>Match Details:</strong> See specific reasons why each animal suits you</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-purple-500 font-bold">•</span>
                                <span><strong>View Profile:</strong> Click to learn more and schedule a visit</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6 pt-8 space-y-5">
            <div id="matchesContainer">
                <!-- Loading State -->
                <div id="loadingState" class="flex flex-col items-center justify-center py-16 animate-scale-in">
                    <div class="w-12 h-12 border-4 border-purple-200 border-t-purple-600 rounded-full animate-spin"></div>
                    <p class="mt-4 text-sm text-gray-600">Finding your perfect matches...</p>
                </div>

                <!-- Results will be inserted here -->
                <div id="matchesResults" class="hidden space-y-5"></div>

                <!-- No Results State -->
                <div id="noResultsState" class="hidden text-center py-16 animate-fade-in-up">
                    <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No matches found</h3>
                    <p class="text-sm text-gray-600">Complete your adopter profile or check back later for new animals.</p>
                </div>

                <!-- Error State -->
                <div id="errorState" class="hidden text-center py-16 animate-fade-in-up">
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
    const modalContainer = document.getElementById('matchModalContainer');

    modal.classList.remove('hidden');

    // Reset scroll position when opening modal
    if (modalContainer) {
        modalContainer.scrollTop = 0;
    }

    loadMatches();
}

function closeResultModal() {
    const modal = document.getElementById('matchResultModal');
    modal.classList.add('hidden');
}

async function loadMatches() {
    // Reset scroll to top at start of loading
    const modalContainer = document.getElementById('matchModalContainer');
    if (modalContainer) {
        modalContainer.scrollTop = 0;
    }

    // Show loading state
    document.getElementById('loadingState').classList.remove('hidden');
    document.getElementById('matchesResults').classList.add('hidden');
    document.getElementById('noResultsState').classList.add('hidden');
    document.getElementById('errorState').classList.add('hidden');

    try {
        // Add timeout to prevent hanging - increased to 60 seconds for slow database connections
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 60000); // 60 second timeout (was 30)

        const response = await fetch('/animal-matches', {
            signal: controller.signal,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest', // Ensure Laravel treats this as AJAX
            }
        });

        clearTimeout(timeoutId);

        // Parse JSON regardless of response status to get error messages
        const data = await response.json();

        // DEBUG: Log response data to console
        console.log('Match API Response:', {
            success: data.success,
            matchCount: data.matches?.length || 0,
            matches: data.matches
        });

        // Hide loading
        document.getElementById('loadingState').classList.add('hidden');

        // Check if response was not ok (4xx, 5xx errors)
        if (!response.ok) {
            console.error('Response not OK:', response.status, data.message);

            // Special handling for 503 (database offline)
            if (response.status === 503) {
                showError(data.message || 'The Animal Management database is currently offline. Please ensure the Shafiqah database (port 3309) is running.');
            } else {
                showError(data.message || `Server error (${response.status}). Please try again later.`);
            }
            return;
        }

        // Check if the operation was successful
        if (!data.success) {
            console.error('Operation failed:', data.message);
            showError(data.message || 'Unable to load matches');
            return;
        }

        // Check if there are any matches
        if (!data.matches || data.matches.length === 0) {
            console.warn('No matches found:', data);
            document.getElementById('noResultsState').classList.remove('hidden');
            return;
        }

        console.log('Displaying matches:', data.matches.length);
        displayMatches(data.matches);
    } catch (error) {
        document.getElementById('loadingState').classList.add('hidden');

        if (error.name === 'AbortError') {
            showError('Request timed out. Please check your connection and try again.');
        } else {
            showError('Failed to load matches. Please check your connection and try again.');
        }

        console.error('Error loading matches:', error);
    }
}

function displayMatches(matches) {
    const container = document.getElementById('matchesResults');
    container.innerHTML = '';
    container.classList.remove('hidden');

    // CRITICAL FIX: Reset scroll position to top to prevent first card from being cut off
    const modalContainer = document.getElementById('matchModalContainer');
    if (modalContainer) {
        modalContainer.scrollTop = 0;
    }

    matches.forEach((match, index) => {
        const matchCard = `
            <div class="match-card animate-fade-in-up bg-white border border-gray-200 rounded-xl overflow-hidden" style="animation-delay: ${index * 0.1}s;">
                <div class="flex gap-4 p-5">
                    <!-- Animal Image -->
                    ${match.image ? `
                        <div class="flex-shrink-0 overflow-hidden rounded-lg">
                            <img src="${match.image}" alt="${match.name}" class="animal-image w-32 h-32 object-cover rounded-lg">
                        </div>
                    ` : `
                        <div class="flex-shrink-0 w-32 h-32 bg-purple-100 rounded-lg flex items-center justify-center text-5xl animal-image">
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
                                    <div class="${index === 0 ? 'score-badge-top' : 'score-badge'} w-16 h-16 rounded-full bg-gradient-to-br from-purple-500 to-purple-700 flex items-center justify-center text-white">
                                        <div class="text-center">
                                            <div class="text-xl font-bold">
                                                <span class="score-number" data-target="${match.score}">0</span>%
                                            </div>
                                            <div class="text-[10px]">Match</div>
                                        </div>
                                    </div>
                                    ${index === 0 ? '<div class="absolute -top-1 -right-1 bg-yellow-400 text-yellow-900 text-[10px] font-bold px-1.5 py-0.5 rounded-full animate-pulse">Top</div>' : ''}
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

                        <a href="/animal/${match.id}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-semibold hover:from-purple-700 hover:to-indigo-700 transition shadow-lg">
                            <span>View Full Profile</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        `;

        container.innerHTML += matchCard;
    });

    // Animate score numbers after cards are rendered
    animateScoreNumbers();
}

function animateScoreNumbers() {
    const scoreElements = document.querySelectorAll('.score-number');

    scoreElements.forEach((element, index) => {
        const target = parseInt(element.getAttribute('data-target'));
        const duration = 1500; // 1.5 seconds
        const delay = index * 100; // Stagger animation by 100ms per element
        const startTime = Date.now() + delay;

        const animate = () => {
            const now = Date.now();
            const elapsed = now - startTime;

            if (elapsed < 0) {
                // Still waiting for delay
                requestAnimationFrame(animate);
                return;
            }

            if (elapsed < duration) {
                // Calculate progress with easing (ease-out cubic)
                const progress = elapsed / duration;
                const easeOut = 1 - Math.pow(1 - progress, 3);
                const current = Math.floor(easeOut * target);

                element.textContent = current;
                requestAnimationFrame(animate);
            } else {
                // Animation complete - set to final value
                element.textContent = target;
            }
        };

        requestAnimationFrame(animate);
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