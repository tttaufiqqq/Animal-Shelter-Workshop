<!-- Alpine.js - Required for dropdown functionality -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<nav class="bg-gradient-to-r from-purple-700 to-purple-900 shadow-lg">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
  <div class="flex justify-between items-center h-16">
   <!-- Logo/Brand -->
   <div class="flex items-center space-x-2 relative" x-data="{ showTooltip: false }">
        <a href="{{ route('welcome') }}"
           class="flex items-center space-x-2 group"
           @mouseenter="showTooltip = true"
           @mouseleave="showTooltip = false">
            <span class="text-3xl group-hover:scale-110 transition-transform duration-300">🐾</span>
            <span class="text-white font-bold text-xl group-hover:text-purple-200 transition duration-300">
                Stray Animal Shelter
            </span>
        </a>

        <!-- Tooltip -->
        <div x-show="showTooltip"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-1"
             class="absolute left-0 top-full mt-2 px-3 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg shadow-lg border border-purple-200 whitespace-nowrap z-50"
             style="display: none;">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="text-purple-900">Click to return to homepage</span>
            </div>
            <!-- Arrow pointing up -->
            <div class="absolute left-6 -top-1 w-2 h-2 bg-white border-l border-t border-purple-200 transform rotate-45"></div>
        </div>
    </div>

   <div class="hidden md:flex space-x-3" x-data="{
        tooltip: {
            dashboard: false,
            map: false,
            report: false,
            submitReport: false,
            animal: false,
            bookings: false,
            slots: false,
            clinics: false,
            myBooking: false,
            contact: false
        }
    }">
        {{-- Must be logged in for everything except Contact Us --}}
        @auth
           {{-- Public User and Adopter: Submit Report (not on welcome page) --}}
           @role('public user|adopter')
           @unless(request()->routeIs('welcome'))
               <div class="relative">
                   <a href="{{ route('welcome') }}"
                      class="text-purple-100 hover:text-white transition duration-300 font-medium px-3 py-2 inline-block"
                      @mouseenter="tooltip.submitReport = true"
                      @mouseleave="tooltip.submitReport = false">
                       Submit Report
                   </a>
                   <div x-show="tooltip.submitReport"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute left-1/2 transform -translate-x-1/2 top-full mt-2 px-3 py-2 bg-white text-gray-700 text-xs font-medium rounded-lg shadow-lg border border-purple-200 whitespace-nowrap z-50"
                        style="display: none;">
                       <span class="text-purple-900">Report a stray animal</span>
                       <div class="absolute left-1/2 transform -translate-x-1/2 -top-1 w-2 h-2 bg-white border-l border-t border-purple-200 rotate-45"></div>
                   </div>
               </div>
           @endunless
           @endrole

            {{-- ADMIN + CARETAKER + USER: Animal --}}
            <div class="relative">
                <a href="{{ route('animal:main') }}"
                   class="text-purple-100 hover:text-white transition duration-300 font-medium px-3 py-2 inline-block"
                   @mouseenter="tooltip.animal = true"
                   @mouseleave="tooltip.animal = false">
                    Animal
                </a>
                <div x-show="tooltip.animal"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-1"
                     class="absolute left-1/2 transform -translate-x-1/2 top-full mt-2 px-3 py-2 bg-white text-gray-700 text-xs font-medium rounded-lg shadow-lg border border-purple-200 whitespace-nowrap z-50"
                     style="display: none;">
                    <span class="text-purple-900">Browse animals available for adoption</span>
                    <div class="absolute left-1/2 transform -translate-x-1/2 -top-1 w-2 h-2 bg-white border-l border-t border-purple-200 rotate-45"></div>
                </div>
            </div>

            {{-- ADMIN + CARETAKER: Clinics & Vets --}}
            @role('admin|caretaker')
                <div class="relative">
                    <a href="{{ route('shelter-management.index') }}"
                       class="text-purple-100 hover:text-white transition duration-300 font-medium px-3 py-2 inline-block"
                       @mouseenter="tooltip.slots = true"
                       @mouseleave="tooltip.slots = false">
                        Slots
                    </a>
                    <div x-show="tooltip.slots"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         class="absolute left-1/2 transform -translate-x-1/2 top-full mt-2 px-3 py-2 bg-white text-gray-700 text-xs font-medium rounded-lg shadow-lg border border-purple-200 whitespace-nowrap z-50"
                         style="display: none;">
                        <span class="text-purple-900">Manage shelter slots and inventory</span>
                        <div class="absolute left-1/2 transform -translate-x-1/2 -top-1 w-2 h-2 bg-white border-l border-t border-purple-200 rotate-45"></div>
                    </div>
                </div>

                <div class="relative">
                    <a href="{{ route('animal-management.clinic-index') }}"
                       class="text-purple-100 hover:text-white transition duration-300 font-medium px-3 py-2 inline-block"
                       @mouseenter="tooltip.clinics = true"
                       @mouseleave="tooltip.clinics = false">
                        Clinics & Vets
                    </a>
                    <div x-show="tooltip.clinics"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         class="absolute left-1/2 transform -translate-x-1/2 top-full mt-2 px-3 py-2 bg-white text-gray-700 text-xs font-medium rounded-lg shadow-lg border border-purple-200 whitespace-nowrap z-50"
                         style="display: none;">
                        <span class="text-purple-900">Manage clinics and veterinarians</span>
                        <div class="absolute left-1/2 transform -translate-x-1/2 -top-1 w-2 h-2 bg-white border-l border-t border-purple-200 rotate-45"></div>
                    </div>
                </div>
            @endrole

            {{-- Caretaker and Public User: My Booking --}}
            @role('public user|caretaker|adopter')
            <div class="relative">
                <a href="{{ route('bookings.index') }}"
                   class="text-purple-100 hover:text-white transition duration-300 font-medium px-3 py-2 inline-block"
                   @mouseenter="tooltip.myBooking = true"
                   @mouseleave="tooltip.myBooking = false">
                    My Booking
                </a>
                <div x-show="tooltip.myBooking"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-1"
                     class="absolute left-1/2 transform -translate-x-1/2 top-full mt-2 px-3 py-2 bg-white text-gray-700 text-xs font-medium rounded-lg shadow-lg border border-purple-200 whitespace-nowrap z-50"
                     style="display: none;">
                    <span class="text-purple-900">View and manage your bookings</span>
                    <div class="absolute left-1/2 transform -translate-x-1/2 -top-1 w-2 h-2 bg-white border-l border-t border-purple-200 rotate-45"></div>
                </div>
            </div>
            @endrole
        @endauth

        {{-- Public: Contact Us --}}
        <div class="relative">
            <a href="{{ route('contact') }}"
               class="text-purple-100 hover:text-white transition duration-300 font-medium px-3 py-2 inline-block"
               @mouseenter="tooltip.contact = true"
               @mouseleave="tooltip.contact = false">
                Contact Us
            </a>
            <div x-show="tooltip.contact"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-1"
                 class="absolute left-1/2 transform -translate-x-1/2 top-full mt-2 px-3 py-2 bg-white text-gray-700 text-xs font-medium rounded-lg shadow-lg border border-purple-200 whitespace-nowrap z-50"
                 style="display: none;">
                <span class="text-purple-900">Get in touch with us</span>
                <div class="absolute left-1/2 transform -translate-x-1/2 -top-1 w-2 h-2 bg-white border-l border-t border-purple-200 rotate-45"></div>
            </div>
        </div>
    </div>


   <!-- Profile Dropdown -->
   <div class="hidden md:flex items-center relative" x-data="{ open: false }" @click.away="open = false">
    @auth
    <!-- Profile Button -->
    <button @click="open = !open" class="flex items-center space-x-2 text-purple-100 hover:text-white transition duration-300 focus:outline-none group">
     <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center ring-2 ring-purple-400 group-hover:ring-white transition-all duration-300 shadow-lg">
      <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
       <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
      </svg>
     </div>
     <svg class="w-4 h-4 text-purple-200 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
     </svg>
    </button>

    <!-- Dropdown Menu -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
         class="absolute right-0 top-full mt-3 w-56 bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-100 z-50"
         style="display: none;">

     <!-- User Info Header -->
     <div class="px-4 py-3 bg-gradient-to-r from-purple-50 to-purple-100 border-b border-purple-200">
      <p class="text-sm font-semibold text-purple-900">{{ Auth::user()->name }}</p>
      <p class="text-xs text-purple-600 truncate">{{ Auth::user()->email }}</p>
     </div>

     <!-- Menu Items -->
     <div class="py-2">
      <!-- Profile Link -->
      <a href="{{ route('profile.edit') }}"
         class="flex items-center px-4 py-3 text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition duration-200 group">
       <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-purple-600 transition duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
       </svg>
       <span class="font-medium">My Profile</span>
      </a>

      <!-- Divider -->
      <div class="border-t border-gray-100 my-1"></div>

      <!-- Logout Form -->
      <form method="POST" action="{{ route('logout') }}">
       @csrf
       <button type="submit"
               class="w-full flex items-center px-4 py-3 text-red-600 hover:bg-red-50 transition duration-200 group">
        <svg class="w-5 h-5 mr-3 text-red-500 group-hover:text-red-600 transition duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
        </svg>
        <span class="font-medium">Log Out</span>
       </button>
      </form>
     </div>
    </div>
    @else

    @endauth
   </div>

   <!-- Mobile Menu Button -->
   <div class="md:hidden">
    <button class="text-white hover:text-purple-100 focus:outline-none">
     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
       d="M4 6h16M4 12h16M4 18h16"></path>
     </svg>
    </button>
   </div>
  </div>
 </div>
</nav>
