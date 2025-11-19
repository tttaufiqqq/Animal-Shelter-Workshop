@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col items-center space-y-4">
        <!-- Pagination Info -->
        <div class="text-sm text-gray-600">
            Showing 
            @if ($paginator->firstItem())
                <span class="font-semibold text-purple-700">{{ $paginator->firstItem() }}</span>
                to
                <span class="font-semibold text-purple-700">{{ $paginator->lastItem() }}</span>
            @else
                <span class="font-semibold text-purple-700">{{ $paginator->count() }}</span>
            @endif
            of
            <span class="font-semibold text-purple-700">{{ $paginator->total() }}</span>
            results
        </div>

        <!-- Pagination Controls -->
        <div class="flex items-center space-x-2">
            {{-- Previous Button --}}
            @if ($paginator->onFirstPage())
                <span class="px-4 py-2 bg-gray-200 text-gray-400 rounded-lg font-medium cursor-not-allowed">
                    <i class="fas fa-chevron-left mr-2"></i>Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" 
                   class="px-4 py-2 bg-white hover:bg-purple-50 text-purple-700 border border-purple-200 rounded-lg font-medium transition duration-300 shadow-sm hover:shadow">
                    <i class="fas fa-chevron-left mr-2"></i>Previous
                </a>
            @endif

            {{-- Page Numbers --}}
            <div class="hidden sm:flex items-center space-x-1">
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="px-3 py-2 text-gray-500">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="px-4 py-2 bg-purple-700 text-white rounded-lg font-semibold shadow-md">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" 
                                   class="px-4 py-2 bg-white hover:bg-purple-50 text-gray-700 hover:text-purple-700 border border-gray-300 hover:border-purple-300 rounded-lg font-medium transition duration-300 shadow-sm hover:shadow">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Mobile Page Indicator --}}
            <div class="sm:hidden px-4 py-2 bg-purple-100 text-purple-700 rounded-lg font-semibold">
                Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
            </div>

            {{-- Next Button --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" 
                   class="px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white rounded-lg font-medium transition duration-300 shadow-md hover:shadow-lg">
                    Next<i class="fas fa-chevron-right ml-2"></i>
                </a>
            @else
                <span class="px-4 py-2 bg-gray-200 text-gray-400 rounded-lg font-medium cursor-not-allowed">
                    Next<i class="fas fa-chevron-right ml-2"></i>
                </span>
            @endif
        </div>

        {{-- Quick Jump (Optional - only shows if there are many pages) --}}
        @if ($paginator->lastPage() > 10)
            <div class="flex items-center space-x-2 text-sm">
                <span class="text-gray-600">Jump to page:</span>
                <select onchange="window.location.href=this.value" 
                        class="px-3 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-700">
                    @for ($i = 1; $i <= $paginator->lastPage(); $i++)
                        <option value="{{ $paginator->url($i) }}" {{ $i == $paginator->currentPage() ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>
        @endif
    </nav>
@endif