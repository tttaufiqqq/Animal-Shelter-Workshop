<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <!-- Prevent Loading Hang -->
    <script>
        // Inline critical script to prevent page hanging
        window.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.documentElement.style.opacity = '1';
                document.body.style.opacity = '1';
                document.documentElement.style.visibility = 'visible';
                document.body.style.visibility = 'visible';
            }, 100);
        });
    </script>

    <!-- Tailwind or your CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles - ONLY ONCE -->
    @livewireStyles
</head>
<body>
    @include('navbar')

    <!-- Database Offline Warning Banner -->
    @if(session('db_offline'))
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>Limited Connectivity:</strong> Some databases are currently unavailable. You may experience limited functionality or missing data.
                        @if(session('db_error'))
                            {{ session('db_error') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{ $slot }}
    
    <!-- Livewire Scripts - ONLY ONCE -->
    @livewireScripts
    
    <!-- Your custom scripts -->
    @stack('scripts')
</body>
</html>