<!-- Match Results Modal -->
<div id="matchResultModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
    <div id="matchModalContainer" class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto">
        @include('adopter-animal-matching.result.styles')
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
                    <div id="errorAction" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>
</div>
