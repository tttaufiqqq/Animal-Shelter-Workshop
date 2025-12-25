<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Stray Animal Shelter</title>
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
    <div class="flex-1 flex items-center justify-center p-4 relative z-10">
        <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden fade-in-up">
            <div class="grid grid-cols-1 md:grid-cols-2">

                <!-- Left Section -->
                <div class="bg-gradient-to-br from-purple-600 via-purple-700 to-indigo-700 text-white p-10 md:p-12 flex flex-col justify-center relative overflow-hidden">
                    <!-- Decorative Elements -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-5 rounded-full -mr-16 -mt-16"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 bg-purple-400 opacity-10 rounded-full -ml-12 -mb-12"></div>

                    <div class="text-6xl mb-6">🐾</div>

                    <h1 class="text-4xl md:text-5xl font-bold mb-4 leading-tight">Stray Animal Shelter</h1>

                    <p class="text-lg text-purple-100 mb-8 leading-relaxed">
                        A complete system for rescuing stray animals, managing shelter operations, and connecting animals with loving homes.
                    </p>

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

                <!-- Right Section - Login Form -->
                <div class="p-8 md:p-12 flex flex-col justify-center">
                    <div class="text-center mb-8">
                        <div class="mb-6 inline-flex items-center justify-center p-4 bg-purple-100 rounded-full">
                            <i class="fas fa-user-circle text-5xl text-purple-600"></i>
                        </div>

                        <h2 class="text-3xl font-bold text-gray-800 mb-3">Welcome Back</h2>
                        <p class="text-gray-600">
                            Log in to manage rescues, adoptions, and shelter operations
                        </p>
                    </div>

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <!-- Email Address -->
                        <div>
                            <label for="email" class="flex items-center gap-2 text-gray-800 font-semibold mb-2">
                                <i class="fas fa-envelope text-purple-600"></i>
                                Email
                            </label>
                            <input id="email"
                                   class="block w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition @error('email') border-red-500 @enderror"
                                   type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   autofocus
                                   autocomplete="username"
                                   placeholder="your.email@example.com" />
                            @error('email')
                                <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="flex items-center gap-2 text-gray-800 font-semibold mb-2">
                                <i class="fas fa-lock text-purple-600"></i>
                                Password
                            </label>
                            <input id="password"
                                   class="block w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition @error('password') border-red-500 @enderror"
                                   type="password"
                                   name="password"
                                   required
                                   autocomplete="current-password"
                                   placeholder="••••••••" />
                            @error('password')
                                <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="flex flex-col items-center justify-center pt-4 space-y-4">
                            <button type="submit"
                                    class="w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg hover:from-purple-700 hover:to-purple-800 hover:shadow-xl transition-all duration-300 hover:scale-105 flex items-center justify-center gap-2">
                                <i class="fas fa-sign-in-alt"></i>
                                <span>Log In</span>
                            </button>

                            <div class="text-center">
                                <a class="text-sm text-gray-600 hover:text-purple-600 transition font-semibold inline-flex items-center gap-1"
                                   href="{{ route('register') }}">
                                    <i class="fas fa-user-plus"></i>
                                    Don't have an account? Register here
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
