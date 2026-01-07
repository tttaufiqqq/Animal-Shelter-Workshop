<!-- GPS Permission Instructions -->
<div id="gpsInstructions" class="hidden bg-blue-50 border-l-4 border-blue-400 p-4">
    <div class="flex">
        <svg class="h-5 w-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <div class="text-sm text-blue-700">
            <strong>Location Access Required</strong>
            <p class="mt-1">Please allow location access in your browser to use this feature.</p>
            <button onclick="requestLocationPermission()" class="mt-2 px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                Grant Permission
            </button>
        </div>
    </div>
</div>
