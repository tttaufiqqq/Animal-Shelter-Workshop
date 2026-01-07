<div class="fade-in bg-white rounded-2xl shadow-xl overflow-hidden relative hover-scale">
    <!-- Main Image Display -->
    <div class="relative w-full aspect-video bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
        <div id="imageSwiperContent" class="w-full h-full flex items-center justify-center transition-all duration-500">
            @if($hasImages)
                <img src="{{ $animalImages->first()->url }}"
                     alt="{{ $animal->name }}"
                     class="max-w-full max-h-full object-contain transition-opacity duration-500"
                     id="mainDisplayImage">
            @else
                <div class="aspect-video bg-gradient-to-br from-purple-300 via-purple-400 to-purple-500 flex items-center justify-center w-full h-full">
                    <span class="text-9xl animate-pulse">
                        @if(strtolower($animal->species) == 'dog')
                            🐕
                        @elseif(strtolower($animal->species) == 'cat')
                            🐈
                        @else
                            🐾
                        @endif
                    </span>
                </div>
            @endif
        </div>

        <!-- Navigation Arrows -->
        <button id="prevImageBtn" class="hidden absolute left-3 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-purple-600 rounded-full w-12 h-12 flex items-center justify-center transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-110 z-10 group">
            <i class="fas fa-chevron-left text-xl group-hover:-translate-x-0.5 transition-transform"></i>
        </button>
        <button id="nextImageBtn" class="hidden absolute right-3 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-purple-600 rounded-full w-12 h-12 flex items-center justify-center transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-110 z-10 group">
            <i class="fas fa-chevron-right text-xl group-hover:translate-x-0.5 transition-transform"></i>
        </button>

        <!-- Image Counter -->
        <div id="imageCounter" class="hidden absolute bottom-4 right-4 glass-effect border border-white/30 text-gray-800 px-4 py-2 rounded-full text-sm font-bold shadow-lg">
            <i class="fas fa-images mr-2 text-purple-600"></i>
            <span id="currentImageIndex">1</span> / <span id="totalImages">{{ $animalImages->count() ?: 1 }}</span>
        </div>

        <!-- Fullscreen Button -->
        @if($hasImages)
        <button onclick="toggleFullscreen()" class="absolute top-4 right-4 glass-effect border border-white/30 text-gray-700 hover:text-purple-600 px-3 py-2 rounded-full text-sm font-semibold shadow-lg transition-all hover:scale-110">
            <i class="fas fa-expand mr-1"></i> View
        </button>
        @endif
    </div>

    <!-- Thumbnails -->
    <div id="thumbnailContainer" class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 border-t border-gray-200">
        <div id="thumbnailStrip" class="flex gap-3 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-purple-300 scrollbar-track-gray-100">
            @if($hasImages)
                @foreach($animalImages as $index => $image)
                    <div onclick="goToImage({{ $index }})"
                         class="group flex-shrink-0 w-24 h-24 cursor-pointer rounded-xl overflow-hidden border-3 transition-all duration-300 {{ $index == 0 ? 'border-purple-600 ring-2 ring-purple-300 shadow-lg' : 'border-gray-300 hover:border-purple-400 hover:shadow-md' }}"
                         id="thumbnail-{{ $index }}">
                        <img src="{{ $image->url }}"
                             alt="{{ $animal->name }}"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                    </div>
                @endforeach
            @else
                <div class="flex-shrink-0 w-24 h-24 cursor-pointer rounded-xl overflow-hidden border-3 border-purple-300 bg-purple-100 flex items-center justify-center text-5xl">
                    @if(strtolower($animal->species) == 'dog')
                        🐕
                    @elseif(strtolower($animal->species) == 'cat')
                        🐈
                    @else
                        🐾
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
