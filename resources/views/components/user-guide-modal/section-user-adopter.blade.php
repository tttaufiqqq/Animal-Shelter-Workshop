
            <!-- Public User Guide -->
            @if($showPublicUser)
            <div id="publicuser" class="mb-8 scroll-mt-4">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 rounded-xl p-6 shadow-sm">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="bg-blue-500 text-white p-3 rounded-lg">
                            <i class="fas fa-user text-xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800">Public User Role</h3>
                    </div>

                    <p class="text-gray-700 mb-4 font-semibold">Public users can report stray animals and adopt from the shelter:</p>

                    <div class="space-y-3">
                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg shadow-sm">
                            <div class="bg-blue-100 text-blue-600 p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Submit Stray Animal Reports</h4>
                                <p class="text-sm text-gray-600">Report stray animals you encounter by clicking <span class="font-semibold text-blue-600">Submit Stray Animal Report</span> button. Include location, photos, and description.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg shadow-sm">
                            <div class="bg-blue-100 text-blue-600 p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Adopt Animals</h4>
                                <p class="text-sm text-gray-600">Browse available animals and book adoption appointments through <span class="font-semibold text-purple-600">Booking & Adoption</span> menu.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg shadow-sm">
                            <div class="bg-blue-100 text-blue-600 p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-list-alt"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Track Your Reports</h4>
                                <p class="text-sm text-gray-600">View status of your submitted reports by clicking <span class="font-semibold text-blue-600">My Submitted Reports</span> button.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Adopter Guide -->
            @if($showAdopter)
            <div id="adopter" class="mb-8 scroll-mt-4">
                <div class="bg-gradient-to-r from-purple-50 to-fuchsia-50 border-l-4 border-purple-500 rounded-xl p-6 shadow-sm">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="bg-purple-500 text-white p-3 rounded-lg">
                            <i class="fas fa-home text-xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800">Adopter Role</h3>
                    </div>

                    <p class="text-gray-700 mb-4 font-semibold">Adopters have all public user features plus adoption matching:</p>

                    <div class="space-y-3">
                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg shadow-sm">
                            <div class="bg-purple-100 text-purple-600 p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Submit Reports</h4>
                                <p class="text-sm text-gray-600">Same as public users - report stray animals via <span class="font-semibold text-purple-600">Submit Stray Animal Report</span>.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg shadow-sm">
                            <div class="bg-purple-100 text-purple-600 p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Adopt Animals</h4>
                                <p class="text-sm text-gray-600">Browse and book animals for adoption through the <span class="font-semibold text-purple-600">Booking & Adoption</span> system.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg shadow-sm">
                            <div class="bg-purple-100 text-purple-600 p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-user-edit"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Set Your Preferences</h4>
                                <p class="text-sm text-gray-600">Click <span class="font-semibold text-purple-600">Help Us Know You Better</span> to fill out your adopter profile with preferences for living situation, experience, and preferences.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg shadow-sm">
                            <div class="bg-purple-100 text-purple-600 p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-paw"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Get Matched Animals</h4>
                                <p class="text-sm text-gray-600">Based on your profile, the system suggests animals that best match your lifestyle. Click <span class="font-semibold text-purple-600">Animal You Might Want To Adopt</span>.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
