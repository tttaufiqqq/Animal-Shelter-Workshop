@props(['report'])

{{-- Images Section --}}
<div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-pink-600 p-2 rounded">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">Images</h2>
            </div>
            @if($report->images && $report->images->count() > 0)
                <span class="bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1 rounded-full">
                    {{ $report->images->count() }} {{ Str::plural('Photo', $report->images->count()) }}
                </span>
            @endif
        </div>
    </div>
    <div class="p-6">
        <div class="relative w-full h-64 bg-gray-100 rounded border border-gray-200 overflow-hidden">
            <!-- Main Image Display -->
            <div id="imageSwiperContent" class="w-full h-full flex items-center justify-center">
                @if($report->images && $report->images->count() > 0)
                    <img src="{{ $report->images->first()->url }}"
                        alt="Report Image 1"
                        class="max-w-full max-h-full object-contain cursor-pointer"
                        onclick="openImageModal(this.src)">
                @else
                    <div class="flex flex-col items-center justify-center text-gray-400">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-sm mt-2">No images available</span>
                    </div>
                @endif
            </div>

            <!-- Navigation Arrows -->
            @if($report->images && $report->images->count() > 1)
                <button id="prevImageBtn" class="absolute left-2 top-1/2 -translate-y-1/2 bg-white hover:bg-gray-50 text-gray-700 rounded-full w-8 h-8 flex items-center justify-center shadow">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button id="nextImageBtn" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white hover:bg-gray-50 text-gray-700 rounded-full w-8 h-8 flex items-center justify-center shadow">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <!-- Image Counter -->
                <div id="imageCounter" class="absolute bottom-2 right-2 bg-purple-600 text-white px-2 py-1 rounded text-xs font-medium">
                    <span id="currentImageIndex">1</span> / <span id="totalImages">{{ $report->images->count() }}</span>
                </div>
            @endif
        </div>

        <!-- Thumbnail Strip -->
        @if($report->images && $report->images->count() > 1)
            <div class="mt-4">
                <p class="text-xs font-medium text-gray-500 mb-2">Gallery</p>
                <div class="overflow-x-auto">
                    <div class="flex gap-2">
                        @foreach($report->images as $index => $image)
                            <div onclick="goToImage({{ $index }})"
                                class="flex-shrink-0 w-16 h-16 cursor-pointer rounded overflow-hidden border-2 {{ $index == 0 ? 'border-purple-500' : 'border-gray-200 hover:border-purple-400' }}"
                                id="thumbnail-{{ $index }}">
                                <img src="{{ $image->url }}"
                                    alt="Thumbnail {{ $loop->iteration }}"
                                    class="w-full h-full object-cover">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Pass images data to JavaScript --}}
    @if($report->images && $report->images->count() > 0)
        <script>
            window.reportImages = [
                @foreach($report->images as $image)
                    { path: "{{ $image->url }}" },
                @endforeach
            ];
        </script>
    @else
        <script>
            window.reportImages = [];
        </script>
    @endif
</div>
