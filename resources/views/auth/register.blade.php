<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Pawfect Buddy</title>
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
                        <a href="{{ route('report') }}" class="text-purple-100 hover:text-white transition duration-300 font-medium">
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
                        Join our community and help us care for stray animals with compassion and dedication.
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

                <!-- Right Section - Registration Form -->
                <div class="p-8 md:p-12 flex flex-col justify-center">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-800 mb-6">Create Your Account</h2>
                        <p class="text-gray-600 mb-8">
                            Sign up to get started with Pawfect Buddy
                        </p>
                    </div>

                    <form method="POST" action="{{ route('register') }}" class="space-y-4">
                        @csrf

                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input id="name" class="block mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent @error('name') border-red-500 @enderror" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" />
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email Address -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input id="email" class="block mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent @error('email') border-red-500 @enderror" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" />
                            @error('email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input id="password" class="block mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent @error('password') border-red-500 @enderror" type="password" name="password" required autocomplete="new-password" />
                            @error('password')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input id="password_confirmation" class="block mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent @error('password_confirmation') border-red-500 @enderror" type="password" name="password_confirmation" required autocomplete="new-password" />
                            @error('password_confirmation')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="flex flex-col items-center justify-center mt-8 space-y-3">
                            <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white font-bold py-3 px-6 rounded-lg hover:from-purple-700 hover:to-purple-800 transition duration-300 shadow-lg">
                                Create Account
                            </button>

                            <a class="text-sm text-gray-600 hover:text-gray-900 underline" href="{{ route('login') }}">
                                Already have an account? Log in
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>