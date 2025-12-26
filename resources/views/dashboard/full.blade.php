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
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Welcome Back, Admin!</h1>
            <p class="text-gray-600 mt-2">Here's what's happening with your shelter today.</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Animals</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">142</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <span class="text-3xl">🐕</span>
                    </div>
                </div>
                <p class="text-green-600 text-sm mt-4">+12 this month</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Pending Reports</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">23</p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-3">
                        <span class="text-3xl">📋</span>
                    </div>
                </div>
                <p class="text-orange-600 text-sm mt-4">5 urgent</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Adoptions</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">87</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <span class="text-3xl">❤️</span>
                    </div>
                </div>
                <p class="text-green-600 text-sm mt-4">+8 this month</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Active Shelters</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">5</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <span class="text-3xl">🏠</span>
                    </div>
                </div>
                <p class="text-blue-600 text-sm mt-4">All operational</p>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Reports -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800">Recent Reports</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <span class="text-2xl">🐕</span>
                                <div>
                                    <p class="font-semibold text-gray-800">Injured Dog - Downtown</p>
                                    <p class="text-sm text-gray-500">Reported 2 hours ago</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">Urgent</span>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <span class="text-2xl">🐈</span>
                                <div>
                                    <p class="font-semibold text-gray-800">Stray Cat - Park Area</p>
                                    <p class="text-sm text-gray-500">Reported 5 hours ago</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium">Pending</span>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <span class="text-2xl">🐕</span>
                                <div>
                                    <p class="font-semibold text-gray-800">Abandoned Puppy - Market St</p>
                                    <p class="text-sm text-gray-500">Reported yesterday</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">Resolved</span>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <span class="text-2xl">🐈</span>
                                <div>
                                    <p class="font-semibold text-gray-800">Lost Cat - Residential Area</p>
                                    <p class="text-sm text-gray-500">Reported 2 days ago</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium">Pending</span>
                        </div>
                    </div>

                    <button class="w-full mt-4 py-2 text-purple-700 hover:text-purple-900 font-medium">
                        View All Reports →
                    </button>
                </div>
            </div>

            <!-- Quick Actions & Notifications -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-800">Quick Actions</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <button class="w-full bg-purple-700 hover:bg-purple-800 text-white py-3 rounded-lg font-medium transition duration-300">
                            + Add New Animal
                        </button>
                        <button class="w-full bg-white border-2 border-purple-700 text-purple-700 hover:bg-purple-50 py-3 rounded-lg font-medium transition duration-300">
                            View Reports
                        </button>
                        <button class="w-full bg-white border-2 border-purple-700 text-purple-700 hover:bg-purple-50 py-3 rounded-lg font-medium transition duration-300">
                            Manage Adoptions
                        </button>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-800">Upcoming Events</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-start space-x-3">
                            <div class="bg-purple-100 rounded p-2 text-center">
                                <p class="text-xs font-semibold text-purple-700">OCT</p>
                                <p class="text-lg font-bold text-purple-900">25</p>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Adoption Fair</p>
                                <p class="text-sm text-gray-500">City Park, 10AM</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="bg-purple-100 rounded p-2 text-center">
                                <p class="text-xs font-semibold text-purple-700">OCT</p>
                                <p class="text-lg font-bold text-purple-900">28</p>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Vaccination Drive</p>
                                <p class="text-sm text-gray-500">Main Shelter, 9AM</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="bg-purple-100 rounded p-2 text-center">
                                <p class="text-xs font-semibold text-purple-700">NOV</p>
                                <p class="text-lg font-bold text-purple-900">02</p>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Volunteer Training</p>
                                <p class="text-sm text-gray-500">Online, 2PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
