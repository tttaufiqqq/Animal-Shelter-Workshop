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
