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

    const isProfileError = message.toLowerCase().includes('adopter profile') || message.toLowerCase().includes('complete your');
    const actionDiv = document.getElementById('errorAction');

    if (isProfileError) {
        actionDiv.innerHTML = `<button onclick="closeResultModal(); openAdopterModal();" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-semibold hover:from-purple-700 hover:to-indigo-700 transition shadow-lg">Complete Your Profile</button>`;
    } else {
        actionDiv.innerHTML = `<button onclick="loadMatches()" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-semibold hover:from-purple-700 hover:to-indigo-700 transition shadow-lg">Try Again</button>`;
    }
}

// Close modal on outside click
document.getElementById('matchResultModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeResultModal();
    }
});
</script>
