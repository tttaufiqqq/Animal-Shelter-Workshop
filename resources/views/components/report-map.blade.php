@props(['report'])

{{-- Map Section --}}
<div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-green-600 p-2 rounded">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">Location Map</h2>
            </div>
            <a href="https://www.google.com/maps?q={{ $report->latitude }},{{ $report->longitude }}" target="_blank"
               class="inline-flex items-center gap-2 text-xs font-medium text-purple-600 hover:text-purple-700 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Open in Google Maps
            </a>
        </div>
    </div>
    <div class="p-6">
        <div id="map"
             class="h-96 rounded border border-gray-200 bg-gray-100"
             data-latitude="{{ $report->latitude }}"
             data-longitude="{{ $report->longitude }}"
             data-address="{{ $report->address }}"
             data-city="{{ $report->city }}"
             data-state="{{ $report->state }}">
        </div>
    </div>
</div>
