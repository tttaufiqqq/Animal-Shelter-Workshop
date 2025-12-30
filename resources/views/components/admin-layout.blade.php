<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Animal Shelter') }} - Admin Panel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Livewire Styles -->
    @livewireStyles

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Custom scrollbar for sidebar */
        aside::-webkit-scrollbar {
            width: 6px;
        }

        aside::-webkit-scrollbar-track {
            background: rgba(139, 92, 246, 0.1);
        }

        aside::-webkit-scrollbar-thumb {
            background: rgba(139, 92, 246, 0.3);
            border-radius: 3px;
        }

        aside::-webkit-scrollbar-thumb:hover {
            background: rgba(139, 92, 246, 0.5);
        }

        /* Custom scrollbar for all content */
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

        /* Smooth transitions */
        * {
            transition-property: background-color, border-color, color, fill, stroke;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50 antialiased">
    <div class="min-h-screen flex" x-data="{ sidebarOpen: false }">
        <!-- Sidebar -->
        <x-admin.sidebar />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-h-screen lg:ml-0">
            <!-- Topbar -->
            <x-admin.topbar
                :title="$title ?? 'Dashboard'"
                :breadcrumbs="$breadcrumbs ?? []"
            />

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto">
                <!-- Page Header (Optional) -->
                @if(isset($header))
                    <div class="bg-white border-b border-gray-200 shadow-sm">
                        <div class="px-4 sm:px-6 lg:px-8 py-6">
                            {{ $header }}
                        </div>
                    </div>
                @endif

                <!-- Main Content -->
                <div class="px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Success Message -->
                    @if(session('success'))
                        <div x-data="{ show: true }"
                             x-show="show"
                             x-init="setTimeout(() => show = false, 5000)"
                             class="mb-6 flex items-start gap-3 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg shadow-sm">
                            <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="font-semibold text-green-800">{{ session('success') }}</p>
                            </div>
                            <button @click="show = false" class="text-green-600 hover:text-green-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @endif

                    <!-- Error Message -->
                    @if(session('error'))
                        <div x-data="{ show: true }"
                             x-show="show"
                             class="mb-6 flex items-start gap-3 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg shadow-sm">
                            <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="font-semibold text-red-800">{{ session('error') }}</p>
                            </div>
                            <button @click="show = false" class="text-red-600 hover:text-red-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @endif

                    <!-- Validation Errors -->
                    @if($errors->any())
                        <div x-data="{ show: true }"
                             x-show="show"
                             class="mb-6 flex items-start gap-3 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg shadow-sm">
                            <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="font-semibold text-red-800 mb-2">Please fix the following errors:</p>
                                <ul class="list-disc list-inside space-y-1 text-sm text-red-700">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <button @click="show = false" class="text-red-600 hover:text-red-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @endif

                    <!-- Page Content Slot -->
                    {{ $slot }}
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 py-4">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-600">
                        <p>&copy; {{ date('Y') }} Animal Shelter. All rights reserved.</p>
                        <div class="flex items-center gap-4">
                            <a href="#" class="hover:text-purple-600 transition">Privacy Policy</a>
                            <span class="text-gray-400">|</span>
                            <a href="#" class="hover:text-purple-600 transition">Terms of Service</a>
                            <span class="text-gray-400">|</span>
                            <a href="{{ route('contact') }}" class="hover:text-purple-600 transition">Contact</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Modals -->
    <x-user-guide-modal />

    <!-- Livewire Scripts -->
    @livewireScripts

    @stack('scripts')
</body>
</html>
