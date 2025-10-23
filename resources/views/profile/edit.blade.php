<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Stray Animals Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-purple-600 to-purple-800 min-h-screen flex flex-col">
    
    <!-- Include Navbar -->
    @include('navbar')

    <!-- Main Content -->
    <div class="flex-1 p-4 py-12">
        <div class="max-w-7xl mx-auto space-y-6">
            
            <!-- Page Header -->
<div class="bg-white rounded-2xl shadow-2xl p-8 mb-6">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <!-- Left Section: Title and User Info -->
        <div class="flex items-start gap-4">
            <div class="text-5xl">👤</div>
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-3">Profile</h1>
                
                <!-- User Information -->
                
            </div>
        </div>

        <!-- Right Section: Logout Button -->
        <div class="flex items-start">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button 
                    type="submit"
                    class="bg-gradient-to-r from-red-600 to-red-700 text-white font-bold py-3 px-6 rounded-lg hover:from-red-700 hover:to-red-800 transition duration-300 shadow-lg focus:outline-none focus:ring-4 focus:ring-red-300 flex items-center gap-2"
                >
                    <span class="text-lg">🚪</span>
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </div>
</div>

            <!-- Update Profile Information Section -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-purple-700 p-6">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 bg-purple-500 rounded-full mr-3 text-lg">ℹ️</span>
                        Profile Information
                    </h2>
                </div>
                <div class="p-8">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>

            <!-- Update Password Section -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-purple-700 p-6">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 bg-purple-500 rounded-full mr-3 text-lg">🔒</span>
                        Update Password
                    </h2>
                </div>
                <div class="p-8">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>

            <!-- Delete Account Section -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-red-600 to-red-700 p-6">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 bg-red-500 rounded-full mr-3 text-lg">⚠️</span>
                        Delete Account
                    </h2>
                </div>
                <div class="p-8">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>