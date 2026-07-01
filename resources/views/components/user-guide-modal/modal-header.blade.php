<!-- User Guide Modal -->
<div id="userGuideModal" class="hidden fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
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
