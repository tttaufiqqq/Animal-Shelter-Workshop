<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinics & Vets - Stray Animal Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated mesh gradient background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg,
                #667eea 0%,
                #764ba2 25%,
                #f093fb 50%,
                #4facfe 75%,
                #00f2fe 100%);
            background-size: 400% 400%;
            animation: gradientFlow 20s ease infinite;
            opacity: 0.04;
            z-index: -1;
        }

        @keyframes gradientFlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        /* Floating orbs */
        .floating-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.12;
            pointer-events: none;
            z-index: 0;
            animation: floatOrb 25s ease-in-out infinite;
        }

        .orb-1 {
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            top: -250px;
            left: -250px;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            bottom: -200px;
            right: -200px;
            animation-delay: 8s;
        }

        .orb-3 {
            width: 350px;
            height: 350px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            top: 40%;
            left: 50%;
            animation-delay: 16s;
        }

        @keyframes floatOrb {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(50px, -80px) scale(1.1); }
            66% { transform: translate(-30px, 40px) scale(0.9); }
        }

        /* Card hover effects with shimmer */
        .card-modern {
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-modern::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent 30%,
                rgba(255, 255, 255, 0.3) 50%,
                transparent 70%
            );
            transform: rotate(45deg);
            animation: shimmerMove 3s infinite;
        }

        @keyframes shimmerMove {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .card-modern:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* Glassmorphism effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        /* Icon pulse animation */
        .icon-pulse {
            animation: iconPulse 2s ease-in-out infinite;
        }

        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
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
            width: 10px;
            height: 10px;
        }

        ::-webkit-scrollbar-track {
            background: linear-gradient(to bottom, #f1f5f9, #e2e8f0);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, #9333ea, #7e22ce);
            border-radius: 10px;
            border: 2px solid #f1f5f9;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(to bottom, #7e22ce, #6b21a8);
        }

        /* Neon glow effect */
        .neon-glow {
            box-shadow: 0 0 20px rgba(147, 51, 234, 0.3),
                        0 0 40px rgba(147, 51, 234, 0.2),
                        0 0 60px rgba(147, 51, 234, 0.1);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-purple-50 to-blue-50 min-h-screen">
    <!-- Floating gradient orbs -->
    <div class="floating-orb orb-1"></div>
    <div class="floating-orb orb-2"></div>
    <div class="floating-orb orb-3"></div>
@include('navbar')

<!-- Limited Connectivity Warning Banner -->
@if(isset($dbDisconnected) && count($dbDisconnected) > 0)
    <div id="connectivityBanner" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 shadow-sm">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-semibold text-yellow-800">Limited Connectivity</h3>
                <p class="text-sm text-yellow-700 mt-1">{{ count($dbDisconnected) }} database(s) currently unavailable. Some features may not work properly.</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach($dbDisconnected as $connection => $info)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        {{ $info['module'] }}
                    </span>
                    @endforeach
                </div>
            </div>
            <button onclick="closeConnectivityBanner()" class="flex-shrink-0 ml-4 text-yellow-400 hover:text-yellow-600 transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>

    <script>
        function closeConnectivityBanner() {
            document.getElementById('connectivityBanner').style.display = 'none';
        }
    </script>
@endif

<div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white py-16">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-5xl font-bold mb-4">Clinics & Veterinarians</h1>
                <p class="text-xl text-purple-100">Manage medical partners and veterinary professionals</p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="px-4 sm:px-6 lg:px-8 py-8">
    @include('animal-management.partials.cv-content', ['clinics' => $clinics, 'vets' => $vets])
</div>

{{-- Modals at body level to cover entire viewport --}}
@include('animal-management.partials.cv-modals', ['clinics' => $clinics])

@include('animal-management.partials.cv-scripts')
</body>
</html>
