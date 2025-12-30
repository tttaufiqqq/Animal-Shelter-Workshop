<!-- Sidebar Overlay (Mobile Only) -->
<div x-show="sidebarOpen"
     @click="sidebarOpen = false"
     class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
     style="display: none;"
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
</div>

<!-- Sidebar - Always visible on desktop, toggle on mobile -->
<aside class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-purple-800 to-purple-900 shadow-2xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:shadow-none flex flex-col"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       @click.away="sidebarOpen = false">

    <!-- Sidebar Header -->
    <div class="flex items-center justify-between px-6 py-5 border-b border-purple-700/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-lg">
                <span class="text-2xl">🐾</span>
            </div>
            <div>
                <h2 class="text-white font-bold text-lg">Admin Panel</h2>
                <p class="text-purple-300 text-xs">Stray Animal Shelter</p>
            </div>
        </div>
        <!-- Close Button (Mobile) -->
        <button @click="sidebarOpen = false"
                class="lg:hidden text-purple-300 hover:text-white transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 overflow-y-auto">
        <div class="space-y-2">
            <!-- Dashboard -->
            <x-admin.sidebar-item
                route="dashboard"
                label="Dashboard"
                :icon="'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                    <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6\'/>
                </svg>'"
            />

            <!-- Reports Section -->
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-purple-400 uppercase tracking-wider mb-2">
                    Rescue Operations
                </p>

                <!-- Map -->
                <x-admin.sidebar-item
                    route="rescue.map"
                    label="Rescue Map"
                    :icon="'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                        <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7\'/>
                    </svg>'"
                />

                <!-- Reports -->
                <x-admin.sidebar-item
                    route="reports.index"
                    label="Stray Reports"
                    :icon="'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                        <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z\'/>
                    </svg>'"
                />
            </div>

            <!-- Animal Management -->
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-purple-400 uppercase tracking-wider mb-2">
                    Animal Management
                </p>

                <!-- Animals -->
                <x-admin.sidebar-item
                    route="animal:main"
                    label="Animals"
                    :icon="'<svg class=\'w-5 h-5\' fill=\'currentColor\' viewBox=\'0 0 24 24\'>
                        <path d=\'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z\'/>
                    </svg>'"
                />

                <!-- Clinics & Vets -->
                <x-admin.sidebar-item
                    route="animal-management.clinic-index"
                    label="Clinics & Vets"
                    :icon="'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                        <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4\'/>
                    </svg>'"
                />
            </div>

            <!-- Shelter Management -->
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-purple-400 uppercase tracking-wider mb-2">
                    Shelter Management
                </p>

                <!-- Slots -->
                <x-admin.sidebar-item
                    route="admin.shelter-management.index"
                    label="Slots & Inventory"
                    :icon="'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                        <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z\'/>
                    </svg>'"
                />
            </div>

            <!-- Adoption Management -->
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-purple-400 uppercase tracking-wider mb-2">
                    Adoption Management
                </p>

                <!-- Bookings -->
                <x-admin.sidebar-item
                    route="bookings.index-admin"
                    label="Bookings"
                    :icon="'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                        <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z\'/>
                    </svg>'"
                />
            </div>

            <!-- System -->
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-purple-400 uppercase tracking-wider mb-2">
                    System
                </p>

                <!-- Caretakers -->
                <x-admin.sidebar-item
                    route="admin.caretaker.index"
                    label="Caretakers"
                    :icon="'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                        <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z\'/>
                    </svg>'"
                />

                <!-- Audit Log -->
                <x-admin.sidebar-item
                    route="admin.audit.index"
                    label="Audit Log"
                    :icon="'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'>
                        <path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z\'/>
                    </svg>'"
                />
            </div>
        </div>
    </nav>

    <!-- Sidebar Footer -->
    <div class="px-4 py-4 border-t border-purple-700/50">
        <div class="bg-purple-700/50 rounded-lg p-3">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-4 h-4 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xs font-semibold text-purple-200">Quick Tip</p>
            </div>
            <p class="text-xs text-purple-300">Use the dashboard to monitor all shelter activities</p>
        </div>
    </div>
</aside>
