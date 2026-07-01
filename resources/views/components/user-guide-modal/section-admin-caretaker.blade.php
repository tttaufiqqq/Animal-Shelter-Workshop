
            <!-- Admin Guide -->
            @if($showAdmin)
            <div id="admin" class="mb-8 scroll-mt-4">
                <div class="bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 rounded-xl p-6 shadow-sm">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="bg-red-500 text-white p-3 rounded-lg">
                            <i class="fas fa-user-shield text-xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800">Admin Role</h3>
                    </div>

                    <p class="text-gray-700 mb-4 font-semibold">As an administrator, you have full access to manage the entire shelter system:</p>

                    <div class="space-y-3">
                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg shadow-sm">
                            <div class="bg-red-100 text-red-600 p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">View Submitted Reports</h4>
                                <p class="text-sm text-gray-600">Review all stray animal reports submitted by public users and adopters. Navigate to <span class="font-semibold text-purple-600">Stray Reporting</span> in the menu.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg shadow-sm">
                            <div class="bg-red-100 text-red-600 p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Assign Rescue Missions</h4>
                                <p class="text-sm text-gray-600">Assign submitted reports to caretakers for rescue operations. Click on any report to assign it to available caretakers.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg shadow-sm">
                            <div class="bg-red-100 text-red-600 p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-paw"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Animal Management</h4>
                                <p class="text-sm text-gray-600">View and manage all animals in the shelter. Access via <span class="font-semibold text-purple-600">Animal Management</span> menu.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg shadow-sm">
                            <div class="bg-red-100 text-red-600 p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Booking Management</h4>
                                <p class="text-sm text-gray-600">View all adoption bookings and appointments made by users. Go to <span class="font-semibold text-purple-600">Booking & Adoption</span>.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg shadow-sm">
                            <div class="bg-red-100 text-red-600 p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-warehouse"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Shelter Management</h4>
                                <p class="text-sm text-gray-600">Manage sections, slots, and inventory of the shelter. Access <span class="font-semibold text-purple-600">Shelter Management</span> to organize shelter spaces.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg shadow-sm">
                            <div class="bg-red-100 text-red-600 p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-stethoscope"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Clinics & Vets</h4>
                                <p class="text-sm text-gray-600">Manage veterinarians and clinics associated with the shelter. Navigate to <span class="font-semibold text-purple-600">Clinics & Vets</span> section.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg shadow-sm">
                            <div class="bg-red-100 text-red-600 p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Audit Logs</h4>
                                <p class="text-sm text-gray-600">Monitor system activity including user authentication, payments, animal welfare, and rescue operations. Click <span class="font-semibold text-purple-600">View Audit Logs</span> button below.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Caretaker Guide -->
            @if($showCaretaker)
            <div id="caretaker" class="mb-8 scroll-mt-4">
                <div class="bg-gradient-to-r from-teal-50 to-cyan-50 border-l-4 border-teal-500 rounded-xl p-6 shadow-sm">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="bg-teal-500 text-white p-3 rounded-lg">
                            <i class="fas fa-heart text-xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800">Caretaker Role</h3>
                    </div>

                    <p class="text-gray-700 mb-4 font-semibold">Caretakers are responsible for rescue operations and animal care:</p>

                    <div class="space-y-3">
                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg shadow-sm">
                            <div class="bg-teal-100 text-teal-600 p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-ambulance"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">View Assigned Rescues</h4>
                                <p class="text-sm text-gray-600">Check your assigned rescue missions. Click <span class="font-semibold text-teal-600">View Assigned Rescue Reports</span> button on the welcome page.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg shadow-sm">
                            <div class="bg-teal-100 text-teal-600 p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Add Rescued Animals</h4>
                                <p class="text-sm text-gray-600">After rescuing a stray animal, add it to the shelter system through <span class="font-semibold text-purple-600">Animal Management</span> menu.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg shadow-sm">
                            <div class="bg-teal-100 text-teal-600 p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-notes-medical"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Manage Medical Records</h4>
                                <p class="text-sm text-gray-600">Update animal medical records and vaccination history. Access animal details and click on <span class="font-semibold text-purple-600">Medical</span> or <span class="font-semibold text-purple-600">Vaccinations</span> tabs.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg shadow-sm">
                            <div class="bg-teal-100 text-teal-600 p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-clinic-medical"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Access Clinics & Vets</h4>
                                <p class="text-sm text-gray-600">View associated clinics and veterinarians through the <span class="font-semibold text-purple-600">Clinics & Vets</span> navigation menu.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg shadow-sm">
                            <div class="bg-teal-100 text-teal-600 p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Adopt Animals</h4>
                                <p class="text-sm text-gray-600">Caretakers can also adopt animals from the shelter. Browse available animals in <span class="font-semibold text-purple-600">Booking & Adoption</span>.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white p-4 rounded-lg shadow-sm">
                            <div class="bg-teal-100 text-teal-600 p-2 rounded-lg flex-shrink-0">
                                <i class="fas fa-smile"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Manage Animal Profiles</h4>
                                <p class="text-sm text-gray-600">Document animal personality and behavior traits through the animal profile system to help with adoptions.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
