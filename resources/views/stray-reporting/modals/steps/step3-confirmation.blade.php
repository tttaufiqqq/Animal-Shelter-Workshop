<!-- Step 3: Confirmation -->
<div id="step3Content" class="hidden">
    <div class="space-y-6">
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 p-5 rounded-r shadow-sm">
            <div class="flex items-start gap-3">
                <div class="bg-green-500 text-white p-2 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-base font-bold text-green-900 mb-1">
                        <i class="fas fa-check-circle mr-1"></i>
                        All animals have been added!
                    </p>
                    <p class="text-sm text-green-800">Review the information below before submitting to the shelter system.</p>
                </div>
            </div>
        </div>

        <!-- Rescue Information -->
        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border-2 border-purple-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="bg-purple-600 text-white p-2 rounded-lg">
                    <i class="fas fa-ambulance text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Rescue Operation #{{ $rescue->id }}</h3>
            </div>
            <div class="bg-white bg-opacity-60 p-4 rounded-lg border border-purple-100">
                <p class="text-sm text-gray-700 mb-2">
                    <i class="fas fa-info-circle text-purple-600 mr-2"></i>
                    All <span class="font-bold text-purple-700" id="totalAnimalCount"></span> animal(s) will be assigned to this rescue operation and marked as <span class="font-bold text-purple-700">"Not Adopted"</span>.
                </p>
                <p class="text-xs text-gray-600">
                    <i class="fas fa-link text-purple-600 mr-2"></i>
                    Rescue ID: <span class="font-mono font-bold text-purple-700">{{ $rescue->id }}</span>
                </p>
            </div>
        </div>

        <!-- Summary of added animals displayed by JavaScript -->
        <div id="animalsSummary" class="space-y-3">
            <!-- Summary cards will be inserted here dynamically -->
        </div>

        <!-- Confirmation Checkbox -->
        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 p-5 rounded-xl border-2 border-purple-200 shadow-sm">
            <div class="flex items-start gap-3">
                <input type="checkbox" id="confirmCheck" class="mt-1 w-5 h-5 text-purple-600 border-2 border-gray-300 rounded focus:ring-2 focus:ring-purple-500 cursor-pointer">
                <label for="confirmCheck" class="text-sm text-gray-800 font-medium cursor-pointer">
                    <i class="fas fa-shield-alt text-purple-600 mr-1"></i>
                    I confirm that all information provided is accurate and complete. The rescued animals will be permanently added to the shelter system with the details specified above.
                </label>
            </div>
        </div>
    </div>
</div>
