<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Stray Animal Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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

<!-- Main Content -->
<div class="flex-1 flex items-center justify-center p-4 relative z-10 py-4">
    <div class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl overflow-hidden fade-in-up">
        <div class="grid grid-cols-1 lg:grid-cols-2">

            <!-- Left Section -->
            <div class="bg-gradient-to-br from-purple-600 via-purple-700 to-indigo-700 text-white p-8 md:p-10 flex flex-col justify-center relative overflow-hidden">
                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-5 rounded-full -mr-16 -mt-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-purple-400 opacity-10 rounded-full -ml-12 -mb-12"></div>

                <div class="text-5xl mb-4">🐾</div>

                <h1 class="text-3xl md:text-4xl font-bold mb-3 leading-tight">Save Lives Together</h1>

                <p class="text-base text-purple-100 mb-6 leading-relaxed">
                    Join our community to report strays, adopt animals, and support rescue operations in your area.
                </p>

                <ul class="space-y-2">
                    @foreach ([
                        ['icon' => 'fa-phone-volume', 'text' => 'Report stray animals & track rescues'],
                        ['icon' => 'fa-notes-medical', 'text' => 'Medical records & vaccinations'],
                        ['icon' => 'fa-warehouse', 'text' => 'Shelter slots & inventory management'],
                        ['icon' => 'fa-heart', 'text' => 'Adoption bookings & animal matching'],
                    ] as $item)
                        <li class="flex items-center group">
                                <span class="inline-flex items-center justify-center w-7 h-7 bg-purple-500 bg-opacity-40 backdrop-blur-sm rounded-lg mr-2.5 text-xs font-bold group-hover:bg-opacity-60 transition">
                                    <i class="fas {{ $item['icon'] }}"></i>
                                </span>
                            <span class="text-sm group-hover:translate-x-1 transition-transform">{{ $item['text'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Right Section - Registration Form -->
            <div class="p-6 md:p-8 flex flex-col justify-center">
                <div class="text-center mb-4">
                    <div class="mb-3 inline-block">
                        <div class="relative">
                            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-purple-700 rounded-full flex items-center justify-center shadow-lg">
                                    <i class="fas fa-user text-lg text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-800 mb-1">Create Your Account</h2>
                    <p class="text-sm text-gray-600 mb-2">
                        Sign up to report strays, adopt pets, and make a difference
                    </p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-3">
                    @csrf

                    <!-- Name -->
                    <div>
                        <label for="name" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                            <i class="fas fa-user text-purple-600 text-xs"></i>
                            Name
                        </label>
                        <input id="name"
                               name="name"
                               type="text"
                               value="{{ old('name') }}"
                               required
                               autofocus
                               autocomplete="name"
                               class="block w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition @error('name') border-red-500 @enderror"
                               placeholder="John Doe">
                        @error('name')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                            <i class="fas fa-envelope text-purple-600 text-xs"></i>
                            Email
                        </label>
                        <input id="email"
                               name="email"
                               type="email"
                               value="{{ old('email') }}"
                               required
                               autocomplete="username"
                               class="block w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition @error('email') border-red-500 @enderror"
                               placeholder="your.email@example.com">
                        @error('email')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label for="phoneNum" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                            <i class="fas fa-phone text-purple-600 text-xs"></i>
                            Phone Number
                        </label>
                        <input id="phoneNum"
                               name="phoneNum"
                               type="text"
                               value="{{ old('phoneNum') }}"
                               required
                               class="block w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition @error('phoneNum') border-red-500 @enderror"
                               placeholder="+60123456789">
                        @error('phoneNum')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="address" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                            <i class="fas fa-map-marker-alt text-purple-600 text-xs"></i>
                            Address
                        </label>
                        <input id="address"
                               name="address"
                               type="text"
                               value="{{ old('address') }}"
                               required
                               class="block w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition @error('address') border-red-500 @enderror"
                               placeholder="123 Main Street">
                        @error('address')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- City and State (Two Columns) -->
                    <div class="grid grid-cols-2 gap-3">
                        <!-- City -->
                        <div>
                            <label for="city" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                                <i class="fas fa-city text-purple-600 text-xs"></i>
                                City
                            </label>
                            <input id="city"
                                   name="city"
                                   type="text"
                                   value="{{ old('city') }}"
                                   required
                                   class="block w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition @error('city') border-red-500 @enderror"
                                   placeholder="Kuala Lumpur">
                            @error('city')
                            <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <!-- State -->
                        <div>
                            <label for="state" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                                <i class="fas fa-flag text-purple-600 text-xs"></i>
                                State
                            </label>
                            <input id="state"
                                   name="state"
                                   type="text"
                                   value="{{ old('state') }}"
                                   required
                                   class="block w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition @error('state') border-red-500 @enderror"
                                   placeholder="Selangor">
                            @error('state')
                            <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Password and Confirm Password (Two Columns) -->
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Password -->
                        <div>
                            <label for="password" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                                <i class="fas fa-lock text-purple-600 text-xs"></i>
                                Password
                            </label>
                            <input id="password"
                                   name="password"
                                   type="password"
                                   required
                                   autocomplete="new-password"
                                   class="block w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition @error('password') border-red-500 @enderror"
                                   placeholder="••••••••">
                            @error('password')
                            <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                                <i class="fas fa-lock text-purple-600 text-xs"></i>
                                Confirm
                            </label>
                            <input id="password_confirmation"
                                   name="password_confirmation"
                                   type="password"
                                   required
                                   autocomplete="new-password"
                                   class="block w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition @error('password_confirmation') border-red-500 @enderror"
                                   placeholder="••••••••">
                            @error('password_confirmation')
                            <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex flex-col items-center justify-center pt-2 space-y-3">
                        <button type="submit"
                                class="w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-lg hover:from-purple-700 hover:to-purple-800 hover:shadow-xl transition-all duration-300 hover:scale-105 flex items-center justify-center gap-2 text-sm">
                            <i class="fas fa-user-plus text-xs"></i>
                            <span>Create Account</span>
                        </button>

                        <div class="text-center">
                            <a class="text-xs text-gray-600 hover:text-purple-600 transition font-semibold inline-flex items-center gap-1"
                               href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt"></i>
                                Already have an account? Log in
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- User Guide Modal -->
<x-user-guide-modal />
</body>
</html>
