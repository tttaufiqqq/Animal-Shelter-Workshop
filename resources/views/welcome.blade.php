<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Stray Animals Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #9333ea;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #7e22ce;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-600 via-purple-700 to-indigo-800 min-h-screen flex flex-col relative overflow-x-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-10 w-32 h-32 bg-white opacity-5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-10 w-40 h-40 bg-purple-300 opacity-10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/3 w-24 h-24 bg-indigo-400 opacity-5 rounded-full blur-2xl"></div>
    </div>

    <!-- Include Navbar (hidden for admin users) -->
    @guest
        @include('navbar')
    @else
        @unlessrole('admin')
            @include('navbar')
        @endunlessrole
    @endguest
    @include('stray-reporting.create')
    @include('stray-reporting.my-submitted-report')

    <!-- Main Content -->
    <div class="flex-1 flex items-center justify-center p-4 relative z-10">
        <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden fade-in-up">
            {{-- Success Alert --}}
            @if (session('success'))
                <div class="flex items-start gap-3 p-4 mb-6 bg-green-50 border border-green-200 rounded-xl shadow-sm mx-6 mt-6">
                    <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="font-semibold text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            {{-- Error Alert --}}
            @if (session('error'))
                <div class="flex items-start gap-3 p-4 mb-6 bg-red-50 border border-red-200 rounded-xl shadow-sm mx-6 mt-6">
                    <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <p class="font-semibold text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2">
                <!-- Left Section: Hero -->
                <x-welcome.hero-section />

                <!-- Right Section: Authentication -->
                <div class="p-10 md:p-12 flex flex-col justify-center">
                    @auth
                        <x-welcome.authenticated-section :adopterProfile="$adopterProfile ?? null" :matches="$matches ?? collect()" />
                    @else
                        <x-welcome.guest-section />
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <x-database-status-modal />
    <x-user-guide-modal />
    <x-modals.add-caretaker />

    <!-- Scripts -->
    <x-welcome.scripts />
</body>
</html>
