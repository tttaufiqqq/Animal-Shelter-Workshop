<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rescue #{{ $rescue->id }} - Stray Animals Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @include('stray-reporting.show-caretaker.styles')
</head>
<body class="bg-gray-50 min-h-screen">

@include('navbar')
@include('stray-reporting.partials.rescue-header')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('stray-reporting.show-caretaker.alerts')

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            <div class="lg:col-span-3 space-y-6">
                @include('stray-reporting.partials.rescue-images')
                @include('stray-reporting.partials.report-details')
                <div class="block lg:hidden">
                    @include('stray-reporting.partials.location-map', ['mapId' => 'mobile'])
                </div>
            </div>
            <div class="lg:col-span-2 space-y-6">
                <div class="hidden lg:block">
                    @include('stray-reporting.partials.location-map', ['mapId' => 'desktop'])
                </div>
                @include('stray-reporting.partials.status-update')
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay (Full Screen) -->
<div id="loadingOverlay" class="fixed inset-0 bg-white bg-opacity-90 backdrop-blur-md hidden z-[99999] flex items-center justify-center" style="backdrop-filter: blur(8px);">
    <div class="bg-white rounded-lg shadow-2xl p-8 flex flex-col items-center gap-4 border-2 border-gray-200">
        <svg class="animate-spin h-16 w-16 text-purple-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-gray-800 font-bold text-xl">Updating rescue status...</p>
        <p class="text-gray-600 text-base">Please wait</p>
    </div>
</div>

@include('stray-reporting.modals.image-modal')
@include('stray-reporting.modals.remarks-modal')
@include('stray-reporting.modals.success-remarks-modal')
@include('stray-reporting.modals.animal-addition-modal')

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/rescue-status-update.js') }}"></script>
@include('stray-reporting.show-caretaker.scripts')

</body>
</html>
