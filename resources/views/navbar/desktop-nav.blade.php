
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
