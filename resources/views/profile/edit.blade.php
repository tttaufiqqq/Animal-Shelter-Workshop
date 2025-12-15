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
