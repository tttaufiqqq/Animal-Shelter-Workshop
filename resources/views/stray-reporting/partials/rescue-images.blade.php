<!-- Photos Section - Caretaker Optimized -->
<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h2 class="text-base font-bold text-white">Animal/Location Photos</h2>
            </div>
            @if($rescue->report->images && $rescue->report->images->count() > 0)
            <span class="text-white text-sm font-medium">{{ $rescue->report->images->count() }} photo(s)</span>
            @endif
        </div>
    </div>

    <div class="p-3 md:p-4">
        @if($rescue->report->images && $rescue->report->images->count() > 0)
            {{-- Large Image Display - Responsive Height --}}
            <div class="relative w-full bg-gray-900 rounded-xl overflow-hidden shadow-lg h-64 sm:h-80 md:h-96 lg:h-[500px]">
                <!-- Main Image Display -->
                <div id="rescueImageSwiperContent" class="w-full h-full flex items-center justify-center">
                    <img src="{{ $rescue->report->images->first()->url }}"
                        alt="Report Image 1"
                        class="max-w-full max-h-full object-contain cursor-pointer"
                        onclick="openImageModal(this.src)">
                </div>

                {{-- Tap to Enlarge Hint --}}
                <div class="absolute top-3 left-3 bg-black bg-opacity-60 text-white px-3 py-1 rounded-full text-xs font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                    </svg>
                    Tap to enlarge
                </div>

                <!-- Navigation Arrows - Bigger for Touch -->
                @if($rescue->report->images->count() > 1)
                    <button id="rescuePrevImageBtn"
                            class="absolute left-3 top-1/2 -translate-y-1/2 bg-white hover:bg-gray-100 text-gray-800 rounded-full w-12 h-12 flex items-center justify-center shadow-lg transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button id="rescueNextImageBtn"
                            class="absolute right-3 top-1/2 -translate-y-1/2 bg-white hover:bg-gray-100 text-gray-800 rounded-full w-12 h-12 flex items-center justify-center shadow-lg transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <!-- Image Counter - Bigger Text -->
                    <div id="rescueImageCounter" class="absolute bottom-3 right-3 bg-black bg-opacity-75 text-white px-3 py-2 rounded-lg text-sm font-semibold">
                        <span id="rescueCurrentImageIndex">1</span> / <span id="rescueTotalImages">{{ $rescue->report->images->count() }}</span>
                    </div>
                @endif
            </div>

            {{-- Thumbnail Strip - Bigger for Touch --}}
            @if($rescue->report->images->count() > 1)
                <div class="mt-4 overflow-x-auto pb-2">
                    <div class="flex gap-3">
                        @foreach($rescue->report->images as $index => $image)
                            <div onclick="rescueGoToImage({{ $index }})"
                                class="flex-shrink-0 w-20 h-20 cursor-pointer rounded-lg overflow-hidden border-3 transition-all {{ $index == 0 ? 'border-purple-500 ring-2 ring-purple-300' : 'border-gray-300 hover:border-purple-400' }}"
                                id="rescueThumbnail-{{ $index }}">
                                <img src="{{ $image->url }}"
                                    alt="Thumbnail {{ $loop->iteration }}"
                                    class="w-full h-full object-cover">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            {{-- No Images Available --}}
            <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                <svg class="w-20 h-20 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-base font-medium">No photos available</span>
                <span class="text-sm">Contact reporter for more info</span>
            </div>
        @endif
    </div>
</div>
