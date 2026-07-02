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

                <button onclick="openGuideModal()" class="mt-8 w-full flex items-center justify-center gap-2 bg-white bg-opacity-15 hover:bg-opacity-25 border border-white border-opacity-30 text-white font-semibold py-3 px-5 rounded-xl transition backdrop-blur-sm text-sm">
                    <i class="fas fa-key"></i>
                    Trying the system? View test accounts
                </button>
            </div>
