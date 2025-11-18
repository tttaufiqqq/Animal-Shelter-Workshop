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

    @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-600 text-green-700 p-4 rounded-lg mb-6">
                        <p class="font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Main Content -->
    <div class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2">
                <!-- Left Section -->
                <div class="bg-gradient-to-br from-purple-600 to-purple-800 text-white p-8 md:p-12 flex flex-col justify-center">
                    <div class="text-6xl mb-6">🐾</div>
                    
                    <h1 class="text-4xl font-bold mb-4">Stray Animal Shelter</h1>
                    
                    <p class="text-lg text-purple-100 mb-8 leading-relaxed">
                        Dedicated to caring for and managing stray animals in our community with compassion and professionalism.
                    </p>
                    
                    <ul class="space-y-3">
                        <li class="flex items-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 bg-purple-500 rounded-full mr-3 text-sm font-bold">✓</span>
                            <span>Track animal records</span>
                        </li>
                        <li class="flex items-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 bg-purple-500 rounded-full mr-3 text-sm font-bold">✓</span>
                            <span>Manage adoptions</span>
                        </li>
                        <li class="flex items-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 bg-purple-500 rounded-full mr-3 text-sm font-bold">✓</span>
                            <span>Medical history tracking</span>
                        </li>
                        <li class="flex items-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 bg-purple-500 rounded-full mr-3 text-sm font-bold">✓</span>
                            <span>Volunteer coordination</span>
                        </li>
                    </ul>
                </div>

                <!-- Right Section -->
                <div class="p-8 md:p-12 flex flex-col justify-center">
                    @auth
                    <!-- Logged In Section -->
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-800 mb-6">Welcome Back, {{ Auth::user()->name }}!</h2>
                        
                        <!-- User Info Card -->
                        <div class="bg-purple-50 border-l-4 border-purple-600 p-6 rounded-lg mb-8 text-left">
                            <p class="text-gray-700 mb-3">
                                <span class="font-semibold text-gray-800">Name:</span>
                                <span>{{ Auth::user()->name }}</span>
                            </p>
                            <p class="text-gray-700 mb-4">
                                <span class="font-semibold text-gray-800">Email:</span>
                                <span>{{ Auth::user()->email }}</span>
                            </p>
                            <div class="flex items-center justify-between flex-wrap gap-3">
                                @php
                                    $userRoles = Auth::user()->getRoleNames(); 
                                    $rolesToDisplay = $userRoles->isEmpty() ? collect(['user']) : $userRoles; 

                                    $badgeColors = [
                                        'staff' => 'from-purple-600 to-purple-700',
                                        'adopter' => 'from-purple-600 to-purple-700',
                                        'moderator' => 'from-blue-600 to-blue-700',
                                        'user' => 'from-gray-600 to-gray-700',
                                        'public user' => 'from-gray-600 to-gray-700',
                                    ];
                                @endphp

                                @foreach ($rolesToDisplay as $role)
                                    @php
                                        $badgeColor = $badgeColors[$role] ?? 'from-gray-600 to-gray-700';
                                    @endphp

                                    <span class="inline-block bg-gradient-to-r {{ $badgeColor }} text-white px-4 py-2 rounded-full text-sm font-semibold capitalize mr-2 mb-2">
                                        {{ $role }}
                                    </span>
                                @endforeach
                                @role('public user')
                                <div>
                                    <button type="button" onclick="openReportModal()" class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold px-5 py-2 rounded-lg hover:from-purple-700 hover:to-purple-800 transition duration-300 shadow-lg">
                                        <span class="text-lg">📝</span>
                                        <span>Submit Stray Animal Report</span>
                                    </button>
                                </div>
                                <div>
                                    <button onclick="openMyReportsModal()" class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold px-5 py-2 rounded-lg hover:from-purple-700 hover:to-purple-800 transition duration-300 shadow-lg">
                                         <span class="text-lg">📝</span>
                                        <span>My Submitted Reports</span>
                                    </button>
                                </div>
                                @endrole
                                @role('caretaker')
                                    <div>
                                        <a href="{{ route('rescues.index') }}" 
                                        class="inline-flex items-center gap-2 bg-gradient-to-r from-green-600 to-green-700 text-white font-semibold px-5 py-2 rounded-lg hover:from-green-700 hover:to-green-800 transition duration-300 shadow-lg">
                                            <span class="text-lg">🐾</span>
                                            <span>View Assigned Rescue Reports</span>
                                        </a>
                                    </div>
                                @endrole
                            </div>
                        </div>
                    </div>
                    @else
                    <!-- Not Logged In Section -->
                    <div class="text-center">
                        <h2 class="text-3xl font-bold text-gray-800 mb-4">Welcome to Animal Shelter System</h2>
                        <p class="text-gray-600 mb-8 text-lg">
                            Join us in our mission to care for stray animals in our community. Log in to get started.
                        </p>
                        
                        <div class="space-y-3">
                            <a href="{{ route('login') }}" class="inline-block w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white font-bold py-3 px-6 rounded-lg hover:from-purple-700 hover:to-purple-800 transition duration-300 shadow-lg">
                                Log In
                            </a>
                            <a href="{{ route('register') }}" class="inline-block w-full bg-white border-2 border-purple-600 text-purple-600 font-bold py-3 px-6 rounded-lg hover:bg-purple-50 transition duration-300">
                                Create Account
                            </a>
                        </div>
                    </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
     <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</body>
</html>