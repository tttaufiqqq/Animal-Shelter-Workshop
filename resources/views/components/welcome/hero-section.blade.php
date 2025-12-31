<!-- Left Section: Hero/Branding -->
<div class="bg-gradient-to-br from-purple-600 via-purple-700 to-indigo-700 text-white p-10 md:p-12 flex flex-col justify-center relative overflow-hidden">
    <!-- Decorative Elements -->
    <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-5 rounded-full -mr-16 -mt-16"></div>
    <div class="absolute bottom-0 left-0 w-24 h-24 bg-purple-400 opacity-10 rounded-full -ml-12 -mb-12"></div>

    <div class="text-6xl mb-6">🐾</div>

    <h1 class="text-4xl md:text-5xl font-bold mb-4 leading-tight">Stray Animal Shelter</h1>

    <p class="text-lg text-purple-100 mb-6 leading-relaxed">
        A complete system for rescuing stray animals, managing shelter operations, and connecting animals with loving homes.
    </p>

    <!-- Quick Guide Button -->
    <button onclick="openGuideModal()"
            class="mb-6 px-6 py-3 bg-white bg-opacity-20 hover:bg-opacity-30 backdrop-blur-sm border-2 border-white border-opacity-40 rounded-xl font-semibold transition-all duration-300 hover:scale-105 shadow-lg flex items-center justify-center gap-2">
        <i class="fas fa-book-open"></i>
        <span>View User Guide</span>
        <i class="fas fa-arrow-right"></i>
    </button>

    <!-- Features List -->
    <ul class="space-y-3">
        @foreach ([
            ['icon' => 'fa-phone-volume', 'text' => 'Report stray animals & track rescues'],
            ['icon' => 'fa-notes-medical', 'text' => 'Medical records & vaccinations'],
            ['icon' => 'fa-warehouse', 'text' => 'Shelter slots & inventory management'],
            ['icon' => 'fa-heart', 'text' => 'Adoption bookings & animal matching'],
        ] as $item)
            <li class="flex items-center group">
                <span class="inline-flex items-center justify-center w-8 h-8 bg-purple-500 bg-opacity-40 backdrop-blur-sm rounded-lg mr-3 text-sm font-bold group-hover:bg-opacity-60 transition">
                    <i class="fas {{ $item['icon'] }}"></i>
                </span>
                <span class="group-hover:translate-x-1 transition-transform">{{ $item['text'] }}</span>
            </li>
        @endforeach
    </ul>
</div>
