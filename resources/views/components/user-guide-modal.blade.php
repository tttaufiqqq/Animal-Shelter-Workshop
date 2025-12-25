<!-- User Guide Modal -->
<div id="userGuideModal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">

        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-700 text-white p-6">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="bg-white bg-opacity-20 p-3 rounded-xl backdrop-blur-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">User Guide</h2>
                        <p class="text-purple-100 text-sm">Learn how to use the Animal Shelter System</p>
                    </div>
                </div>
                <button onclick="closeGuideModal()" class="text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-lg transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            @auth
                @php
                    $userRoles = Auth::user()->getRoleNames();
                    $roleIcons = [
                        'admin' => ['icon' => 'fa-user-shield', 'color' => 'bg-red-500', 'text' => 'Admin'],
                        'caretaker' => ['icon' => 'fa-heart', 'color' => 'bg-teal-500', 'text' => 'Caretaker'],
                        'adopter' => ['icon' => 'fa-home', 'color' => 'bg-purple-500', 'text' => 'Adopter'],
                        'public user' => ['icon' => 'fa-user', 'color' => 'bg-blue-500', 'text' => 'Public User'],
                        'user' => ['icon' => 'fa-user', 'color' => 'bg-blue-500', 'text' => 'User'],
                    ];
                @endphp

                <div class="flex items-center gap-2 flex-wrap mt-2">
                    <span class="text-xs text-purple-200">Your Role(s):</span>
                    @foreach($userRoles as $role)
                        @php
                            $roleInfo = $roleIcons[strtolower($role)] ?? ['icon' => 'fa-user', 'color' => 'bg-gray-500', 'text' => ucfirst($role)];
                        @endphp
                        <span class="inline-flex items-center gap-1.5 {{ $roleInfo['color'] }} bg-opacity-90 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-md">
                            <i class="fas {{ $roleInfo['icon'] }}"></i>
                            {{ $roleInfo['text'] }}
                        </span>
                    @endforeach
                </div>
            @else
                <div class="mt-2">
                    <span class="inline-flex items-center gap-1.5 bg-white bg-opacity-20 text-white px-3 py-1 rounded-full text-xs font-semibold">
                        <i class="fas fa-user-circle"></i>
                        Guest User
                    </span>
                </div>
            @endauth
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-6">

            @php
                // Determine which sections to show based on user role
                $showAdmin = false;
                $showCaretaker = false;
                $showPublicUser = false;
                $showAdopter = false;
                $showAdoptionProcess = false;

                if (Auth::check()) {
                    $userRoles = Auth::user()->getRoleNames();
                    $showAdmin = $userRoles->contains('admin');
                    $showCaretaker = $userRoles->contains('caretaker');
                    $showAdopter = $userRoles->contains('adopter');
                    $showPublicUser = $userRoles->contains('public user') || $userRoles->contains('user');

                    // Show adoption process for roles that can adopt
                    $showAdoptionProcess = $showCaretaker || $showAdopter || $showPublicUser;
                } else {
                    // Guest users see public user section and adoption process
                    $showPublicUser = true;
                    $showAdoptionProcess = true;
                }

                // Count visible sections for grid layout
                $visibleSections = collect([
                    $showAdmin,
                    $showCaretaker,
                    $showPublicUser,
                    $showAdopter,
                ])->filter()->count();
            @endphp

            <!-- Quick Navigation - Only show buttons for user's roles -->
            @if($visibleSections > 1 || $showAdoptionProcess)
            <div class="mb-6 grid grid-cols-2 @if($visibleSections >= 3 || ($visibleSections >= 2 && $showAdoptionProcess)) md:grid-cols-4 @else md:grid-cols-2 @endif gap-3">
                @if($showAdmin)
                <button onclick="scrollToSection('admin')" class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg transition font-semibold text-sm">
                    <i class="fas fa-user-shield mr-1"></i> Admin
                </button>
                @endif

                @if($showCaretaker)
                <button onclick="scrollToSection('caretaker')" class="px-4 py-2 bg-teal-50 hover:bg-teal-100 text-teal-700 rounded-lg transition font-semibold text-sm">
                    <i class="fas fa-heart mr-1"></i> Caretaker
                </button>
                @endif

                @if($showPublicUser)
                <button onclick="scrollToSection('publicuser')" class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition font-semibold text-sm">
                    <i class="fas fa-user mr-1"></i> Public User
                </button>
                @endif

                @if($showAdopter)
                <button onclick="scrollToSection('adopter')" class="px-4 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-lg transition font-semibold text-sm">
                    <i class="fas fa-home mr-1"></i> Adopter
                </button>
                @endif

                @if($showAdoptionProcess)
                <button onclick="scrollToSection('adoption')" class="px-4 py-2 bg-green-50 hover:bg-green-100 text-green-700 rounded-lg transition font-semibold text-sm">
                    <i class="fas fa-clipboard-check mr-1"></i> Adoption Process
                </button>
                @endif
            </div>
            @endif

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
                                <i class="fas fa-home-heart"></i>
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

<script>
function openGuideModal(specificSection = null) {
    const modal = document.getElementById('userGuideModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Auto-scroll to user's role section if logged in
    setTimeout(() => {
        @auth
            @php
                $userRoles = Auth::user()->getRoleNames();
                $primaryRole = null;

                // Determine primary role in priority order
                if ($userRoles->contains('admin')) {
                    $primaryRole = 'admin';
                } elseif ($userRoles->contains('caretaker')) {
                    $primaryRole = 'caretaker';
                } elseif ($userRoles->contains('adopter')) {
                    $primaryRole = 'adopter';
                } elseif ($userRoles->contains('public user') || $userRoles->contains('user')) {
                    $primaryRole = 'publicuser';
                }
            @endphp

            @if($primaryRole)
                const userRoleSection = '{{ $primaryRole }}';
                const targetSection = specificSection || userRoleSection;

                if (targetSection) {
                    scrollToSection(targetSection);

                    // Highlight user's role section
                    const roleElement = document.getElementById(targetSection);
                    if (roleElement && !specificSection) {
                        roleElement.classList.add('ring-4', 'ring-purple-400', 'ring-opacity-50');
                        setTimeout(() => {
                            roleElement.classList.remove('ring-4', 'ring-purple-400', 'ring-opacity-50');
                        }, 2000);
                    }
                }
            @else
                // For guests, show public user section
                if (specificSection) {
                    scrollToSection(specificSection);
                } else {
                    scrollToSection('publicuser');
                }
            @endif
        @else
            // For guests, use specific section or default to public user
            if (specificSection) {
                scrollToSection(specificSection);
            } else {
                scrollToSection('publicuser');
            }
        @endauth
    }, 300);
}

function closeGuideModal() {
    document.getElementById('userGuideModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        const modalContent = section.closest('.overflow-y-auto');
        if (modalContent) {
            const offset = section.offsetTop - 20;
            modalContent.scrollTo({ top: offset, behavior: 'smooth' });
        }
    }
}

// Close modal when clicking outside
document.getElementById('userGuideModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeGuideModal();
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeGuideModal();
});
</script>
