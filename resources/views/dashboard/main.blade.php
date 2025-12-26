<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Stray Animal Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<style>
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
<body class="bg-gray-50">
    @include('navbar')

    <!-- Dashboard Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('dashboard.welcome-section')

        @include('dashboard.stats-cards')

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @include('dashboard.recent-reports')

            <!-- Quick Actions & Notifications -->
            <div class="space-y-6">
                @include('dashboard.quick-actions')

                @include('dashboard.upcoming-events')
            </div>
        </div>
    </div>
</body>
</html>
