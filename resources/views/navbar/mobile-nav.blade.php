 <!-- Mobile Navigation Menu -->
 <div x-show="mobileMenuOpen"
      x-cloak
      x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 -translate-y-2"
      x-transition:enter-end="opacity-100 translate-y-0"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 translate-y-0"
      x-transition:leave-end="opacity-0 -translate-y-2"
      class="md:hidden border-t border-purple-600"
      style="display: none;">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 space-y-1">
   @auth
    @role('public user|adopter')
    @unless(request()->routeIs('welcome'))
        <a href="{{ route('welcome') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-purple-100 hover:text-white hover:bg-purple-800 font-medium">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Submit Report
        </a>
    @endunless
    @endrole

    <a href="{{ route('animal:main') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-purple-100 hover:text-white hover:bg-purple-800 font-medium">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle cx="7" cy="5" r="1.5" stroke-width="1.5"/><circle cx="17" cy="5" r="1.5" stroke-width="1.5"/>
            <circle cx="5" cy="11" r="1.5" stroke-width="1.5"/><circle cx="19" cy="11" r="1.5" stroke-width="1.5"/>
            <ellipse cx="12" cy="16" rx="4" ry="5" stroke-width="1.5"/>
        </svg>
        Animal
    </a>

    @role('admin|caretaker')
        <a href="{{ route('shelter-management.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-purple-100 hover:text-white hover:bg-purple-800 font-medium">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            Slots
        </a>
        <a href="{{ route('animal-management.clinic-index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-purple-100 hover:text-white hover:bg-purple-800 font-medium">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Clinics & Vets
        </a>
    @endrole

    @role('public user|caretaker|adopter')
        <a href="{{ route('bookings.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-purple-100 hover:text-white hover:bg-purple-800 font-medium">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            My Booking
        </a>
    @endrole
   @endauth

   <a href="{{ route('contact') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-purple-100 hover:text-white hover:bg-purple-800 font-medium">
       <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
       Contact Us
   </a>

   @auth
    <div class="border-t border-purple-600 my-2"></div>

    <div class="px-3 py-2">
     <p class="text-sm font-semibold text-white">{{ Auth::user()->name }}</p>
     <p class="text-xs text-purple-300 truncate">{{ Auth::user()->email }}</p>
    </div>

    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-purple-100 hover:text-white hover:bg-purple-800 font-medium">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        My Profile
    </a>

    <form method="POST" action="{{ route('logout') }}">
     @csrf
     <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-red-300 hover:text-white hover:bg-red-600/50 font-medium">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        Log Out
     </button>
    </form>
   @endauth
  </div>
 </div>
