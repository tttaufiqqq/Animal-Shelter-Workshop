<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <!-- Error Icon -->
            <div class="mb-6">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-orange-100 rounded-full">
                    <i class="fas fa-exclamation-triangle text-4xl text-orange-600"></i>
                </div>
            </div>

            <!-- Error Code -->
            <h1 class="text-6xl font-bold text-gray-900 mb-2">500</h1>

            <!-- Error Message -->
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Server Error</h2>

            <!-- Description -->
            <p class="text-gray-600 mb-8">
                Something went wrong on our end. We're working to fix it.
            </p>

            <!-- Actions -->
            <div class="space-y-3">
                <a href="{{ url('/') }}" class="block w-full px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition">
                    Go to Homepage
                </a>
                <button onclick="location.reload()" class="block w-full px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                    Refresh Page
                </button>
            </div>
        </div>

        <!-- Help Text -->
        <p class="text-center text-gray-500 text-sm mt-6">
            If the problem persists, please contact support.
        </p>
    </div>
</body>
</html>
