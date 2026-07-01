
            <!-- Adoption Process Guide -->
            @if($showAdoptionProcess)
            <div id="adoption" class="mb-4 scroll-mt-4">
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-xl p-6 shadow-sm">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="bg-green-500 text-white p-3 rounded-lg">
                            <i class="fas fa-clipboard-check text-xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800">Adoption Process</h3>
                    </div>

                    <p class="text-gray-700 mb-4 font-semibold">Follow these steps to adopt an animal from our shelter:</p>

                    <div class="space-y-4">
                        <div class="flex gap-4">
                            <div class="bg-green-500 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold flex-shrink-0">1</div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-800">Browse Animals</h4>
                                <p class="text-sm text-gray-600">Navigate to <span class="font-semibold text-purple-600">Booking & Adoption</span> menu to browse all animals available for adoption. Filter by species, age, or health status.</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="bg-green-500 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold flex-shrink-0">2</div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-800">View Animal Details</h4>
                                <p class="text-sm text-gray-600">Click on any animal to view comprehensive details including photos, personality traits, medical history, vaccination records, and special care requirements.</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="bg-green-500 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold flex-shrink-0">3</div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-800">Add Animals to Visit List</h4>
                                <p class="text-sm text-gray-600">If you're interested in an animal, click <span class="font-semibold text-green-600">"Add to Visit List"</span>. You can add multiple animals to your list for consideration.</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="bg-green-500 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold flex-shrink-0">4</div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-800">Create a Booking</h4>
                                <p class="text-sm text-gray-600">Open your visit list, select the animals you want to meet, choose an available date and time slot, then <span class="font-semibold text-green-600">confirm your booking</span>.</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="bg-green-500 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold flex-shrink-0">5</div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-800">Visit the Shelter</h4>
                                <p class="text-sm text-gray-600">Come to the shelter at your scheduled appointment time. Our staff will introduce you to the animals in your booking. Spend time interacting to ensure compatibility.</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="bg-green-500 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold flex-shrink-0">6</div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-800">Adopt Selected Animals</h4>
                                <p class="text-sm text-gray-600">After your visit, choose which animal(s) from your booking you'd like to adopt. Complete the adoption paperwork and make payment through our secure <span class="font-semibold text-green-600">ToyyibPay</span> integration.</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="bg-green-500 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold flex-shrink-0">7</div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-800">Take Your New Companion Home</h4>
                                <p class="text-sm text-gray-600">Once the adoption is finalized and payment confirmed, you can take your new family member home! We'll provide you with care instructions and follow-up support.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Important Note -->
                    <div class="mt-6 bg-white border-2 border-green-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-info-circle text-green-600 text-xl flex-shrink-0 mt-1"></i>
                            <div>
                                <h5 class="font-bold text-gray-800 mb-1">Important Notes:</h5>
                                <ul class="text-sm text-gray-600 space-y-1 list-disc list-inside">
                                    <li>You can only book animals that don't already have active bookings</li>
                                    <li>Payment is required to finalize the adoption after your shelter visit</li>
                                    <li>All adoptions include initial vaccinations and health check</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>

        <!-- Footer -->
        <div class="bg-gray-50 border-t border-gray-200 p-4 flex items-center justify-between">
            <p class="text-sm text-gray-600">
                <i class="fas fa-info-circle text-purple-600 mr-1"></i>
                Need help? Contact your system administrator.
            </p>
            <button onclick="closeGuideModal()" class="px-6 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-purple-800 transition shadow-md">
                Got It!
            </button>
        </div>
    </div>
</div>
