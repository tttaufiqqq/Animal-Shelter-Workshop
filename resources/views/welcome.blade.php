<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Stray Animals Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        /* Smooth line clamp */
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

    <!-- Include Navbar -->
    @include('navbar')
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

                <!-- Left Section -->
                <div class="bg-gradient-to-br from-purple-600 via-purple-700 to-indigo-700 text-white p-10 md:p-12 flex flex-col justify-center relative overflow-hidden">
                    <!-- Decorative Elements -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-5 rounded-full -mr-16 -mt-16"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 bg-purple-400 opacity-10 rounded-full -ml-12 -mb-12"></div>

                    <div class="text-6xl mb-6">🐾</div>

                    <h1 class="text-4xl md:text-5xl font-bold mb-4 leading-tight">Stray Animal Shelter</h1>

                    <p class="text-lg text-purple-100 mb-6 leading-relaxed">
                        A complete system for rescuing stray animals, managing shelter operations, and connecting animals with loving homes.
                    </p>

                    <!-- Quick Guide Button -->
                    <button onclick="openGuideModal()"
                            class="mb-6 px-6 py-3 bg-white bg-opacity-20 hover:bg-opacity-30 backdrop-blur-sm border-2 border-white border-opacity-40 rounded-xl font-semibold transition-all duration-300 hover:scale-105 shadow-lg flex items-center justify-center gap-2">
                        <i class="fas fa-book-open"></i>
                        <span>View User Guide</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>

                    <ul class="space-y-3">
                        @foreach ([
                            ['icon' => 'fa-phone-volume', 'text' => 'Report stray animals & track rescues'],
                            ['icon' => 'fa-notes-medical', 'text' => 'Medical records & vaccinations'],
                            ['icon' => 'fa-warehouse', 'text' => 'Shelter slots & inventory management'],
                            ['icon' => 'fa-heart', 'text' => 'Adoption bookings & animal matching'],
                        ] as $item)
                            <li class="flex items-center group">
                                <span class="inline-flex items-center justify-center w-8 h-8 bg-purple-500 bg-opacity-40 backdrop-blur-sm rounded-lg mr-3 text-sm font-bold group-hover:bg-opacity-60 transition">
                                    <i class="fas {{ $item['icon'] }}"></i>
                                </span>
                                <span class="group-hover:translate-x-1 transition-transform">{{ $item['text'] }}</span>
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
                                            <button onclick="openReportModal()"
                                                    class="flex items-center justify-center gap-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold px-5 py-3.5 rounded-lg shadow-md hover:from-purple-700 hover:to-purple-800 hover:shadow-lg transition-all duration-200 group w-full">
                                                <svg class="w-5 h-5 flex-shrink-0 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                                </svg>
                                                <span class="whitespace-nowrap flex-1 text-center">Submit Stray Animal Report</span>
                                                <svg class="w-4 h-4 flex-shrink-0 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </button>

                                            <button onclick="openMyReportsModal()"
                                                    class="flex items-center justify-center gap-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold px-5 py-3.5 rounded-lg shadow-md hover:from-purple-700 hover:to-purple-800 hover:shadow-lg transition-all duration-200 group w-full">
                                                <svg class="w-5 h-5 flex-shrink-0 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <span class="whitespace-nowrap flex-1 text-center">My Submitted Reports</span>
                                                <svg class="w-4 h-4 flex-shrink-0 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </button>
                                        @endunlessrole
                                    @endhasanyrole

                                    {{-- Buttons for adopters (visible even if they are caretakers) --}}
                                    @role('adopter')
                                        <button onclick="openAdopterModal()"
                                                class="flex items-center justify-center gap-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold px-5 py-3.5 rounded-lg shadow-md hover:from-purple-700 hover:to-purple-800 hover:shadow-lg transition-all duration-200 group w-full">
                                            <svg class="w-5 h-5 flex-shrink-0 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <span class="whitespace-nowrap flex-1 text-center">Help Us Know You Better</span>
                                            <svg class="w-4 h-4 flex-shrink-0 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </button>
                                    @include('adopter-animal-matching.result')
                                        <button onclick="openResultModal()"
                                                class="flex items-center justify-center gap-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold px-5 py-3.5 rounded-lg shadow-md hover:from-purple-700 hover:to-purple-800 hover:shadow-lg transition-all duration-200 group w-full">
                                            <svg class="w-5 h-5 flex-shrink-0 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                            </svg>
                                            <span class="whitespace-nowrap flex-1 text-center">Animals You Might Want To Adopt</span>
                                            <svg class="w-4 h-4 flex-shrink-0 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </button>
                                    @endrole

                                    {{-- Button for caretakers --}}
                                    @role('caretaker')
                                        <a href="{{ route('rescues.index') }}"
                                           class="flex items-center justify-center gap-3 bg-gradient-to-r from-teal-600 to-teal-700 text-white font-semibold px-5 py-3.5 rounded-lg shadow-md hover:from-teal-700 hover:to-teal-800 hover:shadow-lg transition-all duration-200 group w-full">
                                            <svg class="w-5 h-5 flex-shrink-0 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                            <span class="whitespace-nowrap flex-1 text-center">View Assigned Rescue Reports</span>
                                            <svg class="w-4 h-4 flex-shrink-0 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    @endrole
                                    @role('admin')
                                    <a href="{{ route('admin.audit.index') }}"
                                       class="flex items-center justify-center gap-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-semibold px-5 py-3.5 rounded-lg shadow-md hover:from-indigo-700 hover:to-indigo-800 hover:shadow-lg transition-all duration-200 group w-full">
                                        <svg class="w-5 h-5 flex-shrink-0 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="whitespace-nowrap flex-1 text-center">View Audit Logs</span>
                                        <svg class="w-4 h-4 flex-shrink-0 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>

                                    <button onclick="openCaretakerModal()"
                                            class="flex items-center justify-center gap-3 bg-gradient-to-r from-teal-600 to-teal-700 text-white font-semibold px-5 py-3.5 rounded-lg shadow-md hover:from-teal-700 hover:to-teal-800 hover:shadow-lg transition-all duration-200 group w-full">
                                        <svg class="w-5 h-5 flex-shrink-0 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                        </svg>
                                        <span class="whitespace-nowrap flex-1 text-center">Add New Caretaker</span>
                                        <svg class="w-4 h-4 flex-shrink-0 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>
                                    @endrole
                                </div>
                            </div>
                        </div>

                    @else

                        <!-- Not Logged In -->
                        <div class="text-center">
                            <div class="mb-6 inline-block p-4 bg-purple-100 rounded-full">
                                <i class="fas fa-home-heart text-5xl text-purple-600"></i>
                            </div>

                            <h2 class="text-3xl font-bold text-gray-800 mb-4">Welcome to Animal Rescue System</h2>
                            <p class="text-gray-600 mb-6 text-lg leading-relaxed">
                                Report stray animals, adopt pets, and help us save lives together.
                            </p>

                            <div class="space-y-3">
                                <a href="{{ route('login') }}" class="block w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white font-bold py-4 rounded-xl shadow-lg hover:from-purple-700 hover:to-purple-800 hover:shadow-xl transition-all duration-300 hover:scale-105 flex items-center justify-center gap-2">
                                    <i class="fas fa-sign-in-alt"></i>
                                    <span>Log In</span>
                                </a>
                                <a href="{{ route('register') }}" class="block w-full border-2 border-purple-600 text-purple-600 font-bold py-4 rounded-xl hover:bg-purple-50 transition-all duration-300 hover:scale-105 flex items-center justify-center gap-2">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Create Account</span>
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

    <!-- User Guide Modal -->
    <x-user-guide-modal />

    <!-- Add Caretaker Modal -->
    <div id="caretakerModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-gradient-to-r from-teal-600 to-teal-700 text-white p-6 rounded-t-2xl z-10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <i class="fas fa-user-plus text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">Add New Caretaker</h2>
                            <p class="text-teal-100 text-sm">Create a new caretaker account</p>
                        </div>
                    </div>
                    <button onclick="closeCaretakerModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <div class="p-6">
                {{-- Success Alert --}}
                @if (session('caretaker_success'))
                    <div class="flex items-start gap-3 p-4 mb-6 bg-green-50 border border-green-200 rounded-xl shadow-sm">
                        <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <p class="font-semibold text-green-700">{{ session('caretaker_success') }}</p>
                    </div>
                @endif

                {{-- Error Alert --}}
                @if (session('caretaker_error'))
                    <div class="flex items-start gap-3 p-4 mb-6 bg-red-50 border border-red-200 rounded-xl shadow-sm">
                        <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <p class="font-semibold text-red-700">{{ session('caretaker_error') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.caretaker.store') }}" class="space-y-4">
                    @csrf

                    <!-- Name -->
                    <div>
                        <label for="caretaker_name" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                            <i class="fas fa-user text-teal-600 text-xs"></i>
                            Name
                        </label>
                        <input id="caretaker_name"
                               name="name"
                               type="text"
                               value="{{ old('name') }}"
                               required
                               autofocus
                               autocomplete="name"
                               class="block w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition @error('name', 'caretaker') border-red-500 @enderror"
                               placeholder="John Doe">
                        @error('name', 'caretaker')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="caretaker_email" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                            <i class="fas fa-envelope text-teal-600 text-xs"></i>
                            Email
                        </label>
                        <input id="caretaker_email"
                               name="email"
                               type="email"
                               value="{{ old('email') }}"
                               required
                               autocomplete="username"
                               class="block w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition @error('email', 'caretaker') border-red-500 @enderror"
                               placeholder="caretaker@example.com">
                        @error('email', 'caretaker')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label for="caretaker_phoneNum" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                            <i class="fas fa-phone text-teal-600 text-xs"></i>
                            Phone Number
                        </label>
                        <input id="caretaker_phoneNum"
                               name="phoneNum"
                               type="text"
                               value="{{ old('phoneNum') }}"
                               required
                               class="block w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition @error('phoneNum', 'caretaker') border-red-500 @enderror"
                               placeholder="+60123456789">
                        @error('phoneNum', 'caretaker')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="caretaker_address" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                            <i class="fas fa-map-marker-alt text-teal-600 text-xs"></i>
                            Address
                        </label>
                        <input id="caretaker_address"
                               name="address"
                               type="text"
                               value="{{ old('address') }}"
                               required
                               class="block w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition @error('address', 'caretaker') border-red-500 @enderror"
                               placeholder="123 Main Street">
                        @error('address', 'caretaker')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- City and State (Two Columns) -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- City -->
                        <div>
                            <label for="caretaker_city" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                                <i class="fas fa-city text-teal-600 text-xs"></i>
                                City
                            </label>
                            <input id="caretaker_city"
                                   name="city"
                                   type="text"
                                   value="{{ old('city') }}"
                                   required
                                   class="block w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition @error('city', 'caretaker') border-red-500 @enderror"
                                   placeholder="Kuala Lumpur">
                            @error('city', 'caretaker')
                            <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <!-- State -->
                        <div>
                            <label for="caretaker_state" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                                <i class="fas fa-flag text-teal-600 text-xs"></i>
                                State
                            </label>
                            <input id="caretaker_state"
                                   name="state"
                                   type="text"
                                   value="{{ old('state') }}"
                                   required
                                   class="block w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition @error('state', 'caretaker') border-red-500 @enderror"
                                   placeholder="Selangor">
                            @error('state', 'caretaker')
                            <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Password and Confirm Password (Two Columns) -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Password -->
                        <div>
                            <label for="caretaker_password" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                                <i class="fas fa-lock text-teal-600 text-xs"></i>
                                Password
                            </label>
                            <input id="caretaker_password"
                                   name="password"
                                   type="password"
                                   required
                                   autocomplete="new-password"
                                   class="block w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition @error('password', 'caretaker') border-red-500 @enderror"
                                   placeholder="••••••••">
                            @error('password', 'caretaker')
                            <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="caretaker_password_confirmation" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                                <i class="fas fa-lock text-teal-600 text-xs"></i>
                                Confirm Password
                            </label>
                            <input id="caretaker_password_confirmation"
                                   name="password_confirmation"
                                   type="password"
                                   required
                                   autocomplete="new-password"
                                   class="block w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition @error('password_confirmation', 'caretaker') border-red-500 @enderror"
                                   placeholder="••••••••">
                            @error('password_confirmation', 'caretaker')
                            <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex gap-3 pt-4">
                        <button type="button"
                                onclick="closeCaretakerModal()"
                                class="flex-1 bg-gray-200 text-gray-700 font-semibold py-3 px-4 rounded-lg hover:bg-gray-300 transition-all duration-200">
                            <i class="fas fa-times mr-2"></i>
                            Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 bg-gradient-to-r from-teal-600 to-teal-700 text-white font-semibold py-3 px-4 rounded-lg shadow-lg hover:from-teal-700 hover:to-teal-800 hover:shadow-xl transition-all duration-200">
                            <i class="fas fa-user-plus mr-2"></i>
                            Create Caretaker
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

     <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

     <script>
        // Auto-open report modal if there are validation errors
        @if ($errors->any() || session('error'))
            document.addEventListener('DOMContentLoaded', function() {
                openReportModal();
            });
        @endif

        // Show guide modal for first-time visitors (optional - can be enabled)
        // Uncomment below to auto-show guide for new users
        /*
        document.addEventListener('DOMContentLoaded', function() {
            if (!localStorage.getItem('guideShown')) {
                setTimeout(() => {
                    openGuideModal();
                    localStorage.setItem('guideShown', 'true');
                }, 1000);
            }
        });
        */

        // Caretaker Modal Functions
        function openCaretakerModal() {
            document.getElementById('caretakerModal').classList.remove('hidden');
            document.getElementById('caretakerModal').classList.add('flex');
        }

        function closeCaretakerModal() {
            document.getElementById('caretakerModal').classList.add('hidden');
            document.getElementById('caretakerModal').classList.remove('flex');
        }

        // Close modal when clicking outside
        document.getElementById('caretakerModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeCaretakerModal();
        });

        // Auto-open caretaker modal if there are validation errors for caretaker form
        @if (session('caretaker_error') || $errors->caretaker->any())
            document.addEventListener('DOMContentLoaded', function() {
                openCaretakerModal();
            });
        @endif

        // Auto-close and show success if caretaker was created successfully
        @if (session('caretaker_success'))
            document.addEventListener('DOMContentLoaded', function() {
                openCaretakerModal();
                setTimeout(() => {
                    closeCaretakerModal();
                }, 3000); // Close after 3 seconds
            });
        @endif
     </script>
</body>
</html>
