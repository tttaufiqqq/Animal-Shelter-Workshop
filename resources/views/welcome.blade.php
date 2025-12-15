<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Stray Animals Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- In your <head> section -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Before closing </body> tag -->

</head>
<body class="bg-gradient-to-br from-purple-600 to-purple-800 min-h-screen flex flex-col">

    <!-- Include Navbar -->
    @include('navbar')
    @include('stray-reporting.create')
    @include('stray-reporting.my-submitted-report')



    <!-- Main Content -->
    <div class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden">
            {{-- Success Alert --}}
            @if (session('success'))
                <div class="flex items-start gap-3 p-4 mb-6 bg-green-50 border border-green-200 rounded-xl shadow-sm mx-6 mt-6">
                    <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="font-semibold text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2">

                <!-- Left Section -->
                <div class="bg-gradient-to-br from-purple-600 to-purple-800 text-white p-10 md:p-12 flex flex-col justify-center">
                    <div class="text-6xl mb-6">🐾</div>

                    <h1 class="text-4xl font-bold mb-4">Stray Animal Shelter</h1>

                    <p class="text-lg text-purple-100 mb-8 leading-relaxed">
                        Dedicated to caring for and managing stray animals with compassion and professionalism.
                    </p>

                    <ul class="space-y-3">
                        @foreach ([
                            'Track animal records',
                            'Manage adoptions',
                            'Medical history tracking',
                        ] as $item)
                            <li class="flex items-center">
                                <span class="inline-flex items-center justify-center w-6 h-6 bg-purple-500 rounded-full mr-3 text-sm font-bold">✓</span>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Right Section -->
                <div class="p-10 md:p-12 flex flex-col justify-center">
                    @auth

                        <!-- Logged In -->
                        <div class="text-center mb-8">
                            <h2 class="text-3xl font-bold text-gray-800 mb-6">
                                Welcome Back, {{ Auth::user()->name }}!
                            </h2>

                            <!-- User Info Card -->
                            <div class="bg-purple-50 border-l-4 border-purple-600 p-6 rounded-xl shadow-sm text-left">
                                <p class="text-gray-700 mb-2">
                                    <span class="font-semibold text-gray-800">Name:</span>
                                    {{ Auth::user()->name }}
                                </p>

                                <p class="text-gray-700 mb-4">
                                    <span class="font-semibold text-gray-800">Email:</span>
                                    {{ Auth::user()->email }}
                                </p>

                                <!-- Roles Display -->
                                <div class="flex flex-wrap gap-3 mt-3 mb-4">
                                    @php
                                        $userRoles = Auth::user()->getRoleNames();
                                        $rolesToDisplay = $userRoles->isEmpty() ? collect(['user']) : $userRoles;

                                        $badgeColors = [
                                            'staff' => 'from-purple-600 to-purple-700',
                                            'adopter' => 'from-purple-600 to-purple-700',
                                            'moderator' => 'from-blue-600 to-blue-700',
                                            'user' => 'from-gray-600 to-gray-700',
                                            'public user' => 'from-gray-600 to-gray-700',
                                            'caretaker' => 'from-teal-600 to-teal-700',
                                        ];
                                    @endphp

                                    @foreach ($rolesToDisplay as $role)
                                        <span class="inline-block bg-gradient-to-r {{ $badgeColors[$role] ?? 'from-gray-600 to-gray-700' }} text-white px-4 py-2 rounded-full text-sm font-semibold capitalize shadow-sm">
                                            {{ $role }}
                                        </span>
                                    @endforeach
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex flex-col gap-3 mt-4">
                                    @include('adopter-animal-matching.adopter-modal')
                                    <script>
                                    function openAdopterModal() {
                                        document.getElementById('adopterModal').classList.remove('hidden');
                                        document.getElementById('adopterModal').classList.add('flex');
                                    }

                                    function closeAdopterModal() {
                                        document.getElementById('adopterModal').classList.add('hidden');
                                        document.getElementById('adopterModal').classList.remove('flex');
                                    }

                                    // Close modal when clicking outside
                                    document.getElementById('adopterModal')?.addEventListener('click', function(e) {
                                        if (e.target === this) closeAdopterModal();
                                    });
                                    </script>
                                    {{-- Buttons for public users or adopters (excluding caretakers) --}}
                                    @hasanyrole('public user|adopter')
                                        @unlessrole('caretaker')
                                            <button onclick="openReportModal()" class="flex items-center justify-center gap-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold px-5 py-3 rounded-lg shadow hover:from-purple-700 hover:to-purple-800 transition">
                                                📝 Submit Stray Animal Report
                                            </button>

                                            <button onclick="openMyReportsModal()" class="flex items-center justify-center gap-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold px-5 py-3 rounded-lg shadow hover:from-purple-700 hover:to-purple-800 transition">
                                                📄 My Submitted Reports
                                            </button>
                                        @endunlessrole
                                    @endhasanyrole

                                    {{-- Buttons for adopters (visible even if they are caretakers) --}}
                                    @role('adopter')
                                        <button onclick="openAdopterModal()" class="flex items-center justify-center gap-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold px-5 py-3 rounded-lg shadow hover:from-purple-700 hover:to-purple-800 transition">
                                            📖 Help Us Know You Better
                                        </button>
                                    @include('adopter-animal-matching.result')
                                        <button onclick="openResultModal()" class="flex items-center justify-center gap-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold px-5 py-3 rounded-lg shadow hover:from-purple-700 hover:to-purple-800 transition">
                                            🐾 Animal You Might Want To Adopt
                                        </button>
                                    @endrole

                                    {{-- Button for caretakers --}}
                                    @role('caretaker')
                                        <a href="{{ route('rescues.index') }}" class="flex items-center justify-center gap-2 bg-gradient-to-r from-teal-600 to-teal-700 text-white font-semibold px-5 py-3 rounded-lg shadow hover:from-teal-800 hover:to-teal-600 transition">
                                            🐾 View Assigned Rescue Reports
                                        </a>
                                    @endrole

                                </div>
                            </div>
                        </div>

                    @else

                        <!-- Not Logged In -->
                        <div class="text-center">
                            <h2 class="text-3xl font-bold text-gray-800 mb-4">Welcome to Animal Shelter System</h2>
                            <p class="text-gray-600 mb-8 text-lg">
                                Join our mission to care for stray animals in the community.
                            </p>

                            <div class="space-y-3">
                                <a href="{{ route('login') }}" class="block w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white font-bold py-3 rounded-lg shadow hover:from-purple-700 hover:to-purple-800 transition">
                                    Log In
                                </a>
                                <a href="{{ route('register') }}" class="block w-full border-2 border-purple-600 text-purple-600 font-bold py-3 rounded-lg hover:bg-purple-50 transition">
                                    Create Account
                                </a>
                            </div>
                        </div>

                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Database Status Modal (shows on welcome page only if databases are offline) -->
    <x-database-status-modal />

     <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</body>
</html>
