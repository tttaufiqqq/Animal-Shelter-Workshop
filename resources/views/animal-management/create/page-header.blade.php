    <!-- Back Button -->
    <div class="mb-6 fade-in">
        <a href="{{ route('animal-management.index') }}"
           class="inline-flex items-center gap-2 text-purple-700 hover:text-purple-900 font-semibold transition">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Animals</span>
        </a>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden fade-in">

        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-700 text-white p-8">
            <div class="flex items-center gap-4 mb-3">
                <div class="bg-white bg-opacity-20 p-3 rounded-2xl backdrop-blur-sm">
                    <i class="fas fa-paw text-3xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">Add New Animal</h1>
                    <p class="text-purple-100 text-sm mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Register rescued animal to the shelter
                    </p>
                </div>
            </div>

            @if($rescue_id)
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl p-4 mt-4">
                    <div class="flex items-center gap-2 text-sm">
                        <i class="fas fa-ambulance text-xl"></i>
                        <div>
                            <span class="font-semibold">Rescue ID:</span>
                            <span class="font-bold text-lg ml-2">#{{ $rescue_id }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
