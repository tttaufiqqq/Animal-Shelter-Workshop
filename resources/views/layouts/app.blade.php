<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    
    <!-- Tailwind or your CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Livewire Styles - ONLY ONCE -->
    @livewireStyles
</head>
<body>
    @include('navbar')
    {{ $slot }}
    
    <!-- Livewire Scripts - ONLY ONCE -->
    @livewireScripts
    
    <!-- Your custom scripts -->
    @stack('scripts')
</body>
</html>