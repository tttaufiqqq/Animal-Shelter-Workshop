<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Stray Animals Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-purple-600 to-purple-800 min-h-screen flex flex-col">
    <!-- Navigation Bar -->
    <nav class="bg-gradient-to-r from-purple-700 to-purple-900 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo/Brand -->
                <div class="flex items-center space-x-2">
                    <span class="text-3xl">🐾</span>
                    <span class="text-white font-bold text-xl">Pawfect Buddy</span>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex space-x-8">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-purple-100 hover:text-white transition duration-300 font-medium">
                        Dashboard
                        </a>
                        <a href="#" class="text-purple-100 hover:text-white transition duration-300 font-medium">

                        Report
                        </a>
                    @endauth
                    
                    <a href="#" class="text-purple-100 hover:text-white transition duration-300 font-medium">
                        Contact
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button class="text-white hover:text-purple-100 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2">
                <!-- Left Section -->
                <div class="bg-gradient-to-br from-purple-600 to-purple-800 text-white p-8 md:p-12 flex flex-col justify-center">
                    <div class="text-6xl mb-6">🐾</div>
                    
                    <h1 class="text-4xl font-bold mb-4">Pawfect Buddy</h1>
                    
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
                            <div>
                                <div class="mt-2">
                                    @php
                                        $userRole = Auth::user()->getRoleNames()->first() ?? 'user';
                                        $badgeColors = [
                                            'staff' => 'from-purple-600 to-purple-700',
                                            'adopter' => 'from-purple-600 to-purple-700',
                                            'moderator' => 'from-blue-600 to-blue-700',
                                            'user' => 'from-gray-600 to-gray-700',
                                            'public user' => 'from-gray-600 to-gray-700',
                                        ];
                                        $badgeColor = $badgeColors[$userRole] ?? 'from-gray-600 to-gray-700';
                                    @endphp
                                    
                                    <span class="inline-block bg-gradient-to-r {{ $badgeColor }} text-white px-4 py-2 rounded-full text-sm font-semibold capitalize">
                                        {{ $userRole }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <!-- Not Logged In Section -->
                    <div class="text-center">
                        <h2 class="text-3xl font-bold text-gray-800 mb-4">Welcome to Pawfect Buddy</h2>
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
</body>
</html>