@props([
    'title' => 'Dashboard',
    'breadcrumbs' => [],
])

<header class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-30">
    <div class="px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between">
            <!-- Left: Hamburger + Breadcrumbs -->
            <div class="flex items-center gap-4">
                <!-- Mobile Menu Button -->
                <button @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500 rounded-lg p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Breadcrumbs -->
                <nav class="hidden sm:flex items-center space-x-2 text-sm">
                    <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 font-medium transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </a>

                    @if(!empty($breadcrumbs))
                        @foreach($breadcrumbs as $crumb)
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            @if(isset($crumb['url']))
                                <a href="{{ $crumb['url'] }}" class="text-gray-500 hover:text-gray-700 font-medium transition flex items-center gap-1.5">
                                    @if(isset($crumb['icon']))
                                        {!! $crumb['icon'] !!}
                                    @endif
                                    {{ $crumb['label'] }}
                                </a>
                            @else
                                <span class="text-gray-900 font-semibold flex items-center gap-1.5">
                                    @if(isset($crumb['icon']))
                                        {!! $crumb['icon'] !!}
                                    @endif
                                    {{ $crumb['label'] }}
                                </span>
                            @endif
                        @endforeach
                    @else
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        @if(trim($title) === 'Dashboard')
                            <span class="text-gray-900 font-semibold flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <span>{{ $title }}</span>
                            </span>
                        @else
                            <span class="text-gray-900 font-semibold">{{ $title }}</span>
                        @endif
                    @endif
                </nav>

                <!-- Mobile Title -->
                <h1 class="sm:hidden text-lg font-bold text-gray-900">{{ $title }}</h1>
            </div>

            <!-- Right: Notifications + User Guide + Profile -->
            <div class="flex items-center gap-3">
                <!-- Notifications (Livewire Component) -->
                @livewire('notifications')

                <!-- User Guide -->
                <x-tooltip.wrapper text="Access user guide and<br>help documentation" position="bottom" size="sm">
                    <button onclick="openGuideModal()"
                            class="text-gray-500 hover:text-purple-600 focus:outline-none focus:ring-2 focus:ring-purple-500 rounded-lg p-2 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                </x-tooltip.wrapper>

                <!-- Profile Dropdown -->
                <div class="relative" x-data="{ profileOpen: false }" @click.away="profileOpen = false">
                    <button @click="profileOpen = !profileOpen"
                            class="flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-purple-500 rounded-lg p-1.5 transition">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center ring-2 ring-purple-200">
                            <span class="text-white text-sm font-bold">
                                {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                            </span>
                        </div>
                        <div class="hidden md:block text-left">
                            <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name ?? 'Admin' }}</p>
                            <p class="text-xs text-gray-500">Administrator</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-500 transition-transform duration-200"
                             :class="profileOpen ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="profileOpen"
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">

                        <!-- User Info -->
                        <div class="px-4 py-3 bg-gradient-to-r from-purple-50 to-purple-100 border-b border-purple-200">
                            <p class="text-sm font-semibold text-purple-900">{{ Auth::user()->name ?? 'Admin' }}</p>
                            <p class="text-xs text-purple-600 truncate">{{ Auth::user()->email ?? '' }}</p>
                        </div>

                        <!-- Menu Items -->
                        <div class="py-2" x-data="{
                            tooltip: {
                                profile: false,
                                publicSite: false,
                                logout: false
                            }
                        }">
                            <div class="relative">
                                <a href="{{ route('profile.edit') }}"
                                   class="flex items-center gap-3 px-4 py-2.5 text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition"
                                   @mouseenter="tooltip.profile = true"
                                   @mouseleave="tooltip.profile = false">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span class="font-medium text-sm">My Profile</span>
                                </a>
                                <x-tooltip
                                    text="Edit your personal account<br>settings and preferences"
                                    position="right"
                                    size="xs"
                                />
                            </div>

                            <div class="relative">
                                <a href="{{ route('welcome') }}"
                                   class="flex items-center gap-3 px-4 py-2.5 text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition"
                                   @mouseenter="tooltip.publicSite = true"
                                   @mouseleave="tooltip.publicSite = false">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                    <span class="font-medium text-sm">Public Site</span>
                                </a>
                                <x-tooltip
                                    text="Return to public-facing<br>website homepage"
                                    position="right"
                                    size="xs"
                                />
                            </div>

                            <div class="border-t border-gray-100 my-1"></div>

                            <div class="relative">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center gap-3 px-4 py-2.5 text-red-600 hover:bg-red-50 transition"
                                            @mouseenter="tooltip.logout = true"
                                            @mouseleave="tooltip.logout = false">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        <span class="font-medium text-sm">Log Out</span>
                                    </button>
                                </form>
                                <x-tooltip
                                    text="Sign out and end your<br>current admin session"
                                    position="right"
                                    size="xs"
                                    color="gray"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
