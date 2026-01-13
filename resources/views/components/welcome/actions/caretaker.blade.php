<!-- Caretaker Actions -->
<div x-data="{
    newRescueCount: 0,
    intervalId: null,
    cacheKey: 'rescue_count_cache',
    cacheExpiry: 30000,
    isNavigating: false,
    async fetchCount() {
        if (document.hidden) return;

        const cached = sessionStorage.getItem(this.cacheKey);
        const cacheTime = sessionStorage.getItem(this.cacheKey + '_time');

        if (cached && cacheTime && (Date.now() - parseInt(cacheTime)) < this.cacheExpiry) {
            this.newRescueCount = parseInt(cached);
            return;
        }

        try {
            const response = await fetch('{{ route('rescues.count.new') }}');
            const data = await response.json();
            if (data.success) {
                this.newRescueCount = data.count;
                sessionStorage.setItem(this.cacheKey, data.count);
                sessionStorage.setItem(this.cacheKey + '_time', Date.now());
            }
        } catch (err) {
            console.error('Failed to fetch rescue count:', err);
        }
    },
    navigate(event) {
        event.preventDefault();
        this.isNavigating = true;
        window.location.href = event.currentTarget.href;
    },
    init() {
        this.fetchCount();
        this.intervalId = setInterval(() => this.fetchCount(), 30000);

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) this.fetchCount();
        });
    }
}" class="relative">
    <a href="{{ route('rescues.index') }}"
       @click="navigate"
       :class="{ 'pointer-events-none opacity-75': isNavigating }"
       class="flex items-center justify-center gap-2 sm:gap-3 bg-gradient-to-r from-teal-600 to-teal-700 text-white font-semibold px-4 sm:px-5 py-3.5 rounded-lg shadow-md hover:from-teal-700 hover:to-teal-800 hover:shadow-lg transition-all duration-200 group w-full min-h-[56px] relative">

        {{-- Normal State --}}
        <template x-if="!isNavigating">
            <div class="flex items-center justify-center gap-2 sm:gap-3 w-full">
                <svg class="w-5 h-5 flex-shrink-0 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <span class="flex-1 text-justify text-sm sm:text-base leading-tight">View Assigned Rescue Reports</span>
                <svg class="w-4 h-4 flex-shrink-0 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </template>

        {{-- Loading State --}}
        <template x-if="isNavigating">
            <div class="flex items-center justify-center gap-3 w-full">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm sm:text-base leading-tight">Loading rescue reports...</span>
            </div>
        </template>
    </a>

    {{-- Notification Badge --}}
    <span x-show="newRescueCount > 0"
          x-text="newRescueCount > 99 ? '99+' : newRescueCount"
          class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold rounded-full min-w-[1.75rem] h-7 px-2 flex items-center justify-center shadow-lg"
          style="display: none;">
    </span>
</div>
