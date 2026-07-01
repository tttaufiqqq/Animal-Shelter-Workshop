
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

            <!-- Test Accounts -->
            <div class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-5">
                <div class="flex items-center gap-2 mb-3">
                    <i class="fas fa-key text-amber-600"></i>
                    <h3 class="font-bold text-gray-800">Test Accounts</h3>
                    <span class="text-xs text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full font-medium ml-auto">Password: <code class="font-mono font-bold">password</code></span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="bg-white rounded-lg p-3 border border-red-100">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="bg-red-100 text-red-600 p-1.5 rounded-md"><i class="fas fa-user-shield text-sm"></i></span>
                            <span class="font-semibold text-gray-700 text-sm">Admin</span>
                        </div>
                        <p class="font-mono text-xs text-gray-600">admin1@gmail.com</p>
                        <p class="font-mono text-xs text-gray-400">admin2@gmail.com</p>
                    </div>
                    <div class="bg-white rounded-lg p-3 border border-teal-100">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="bg-teal-100 text-teal-600 p-1.5 rounded-md"><i class="fas fa-heart text-sm"></i></span>
                            <span class="font-semibold text-gray-700 text-sm">Caretaker</span>
                        </div>
                        <p class="font-mono text-xs text-gray-600">caretaker1@gmail.com</p>
                        <p class="font-mono text-xs text-gray-400">caretaker2@gmail.com</p>
                    </div>
                    <div class="bg-white rounded-lg p-3 border border-blue-100">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="bg-blue-100 text-blue-600 p-1.5 rounded-md"><i class="fas fa-user text-sm"></i></span>
                            <span class="font-semibold text-gray-700 text-sm">Public User</span>
                        </div>
                        <p class="font-mono text-xs text-gray-600">taufiq@gmail.com</p>
                        <p class="font-mono text-xs text-gray-400">shafiqah@gmail.com</p>
                    </div>
                </div>
            </div>

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
