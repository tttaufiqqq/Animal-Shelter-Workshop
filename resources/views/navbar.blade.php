<nav class="bg-gradient-to-r from-purple-700 to-purple-900 shadow-lg">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
  <div class="flex justify-between items-center h-16">
   <!-- Logo/Brand -->
   <div class="flex items-center space-x-2">
        <a href="{{ route('welcome') }}" class="flex items-center space-x-2">
            <span class="text-3xl">🐾</span>
            <span class="text-white font-bold text-xl hover:text-purple-200 transition duration-300">
                Stray Animal Shelter
            </span>
        </a>
    </div>

   <!-- Navigation Links -->
   <div class="hidden md:flex space-x-8">
    @auth
        @role('admin')
            <a href="{{ route('dashboard') }}" class="text-purple-100 hover:text-white transition duration-300 font-medium">
                Dashboard
            </a>
        <a href="{{ route('reports.index') }}" class="text-purple-100 hover:text-white transition duration-300 font-medium">
            Report
        </a>
            <a href="{{ route('animal-management.clinic-index') }}" class="text-purple-100 hover:text-white transition duration-300 font-medium">
            Manage clinics and vets
        </a>
        
        <a href="{{ route('booking:main') }}" class="text-purple-100 hover:text-white transition duration-300 font-medium">
            Adoption
        </a>
        <a href="{{ route('slot:main') }}" class="text-purple-100 hover:text-white transition duration-300 font-medium">
            Slot
        </a>
        @endrole
        @role('caretaker')
        <a href="{{ route('animal:main') }}" class="text-purple-100 hover:text-white transition duration-300 font-medium">
            Animal
        </a>
        @endrole
    @endauth

    
    <a href="{{route ('contact')}}" class="text-purple-100 hover:text-white transition duration-300 font-medium">
     Contact Us
    </a>
   </div>

   <!-- Profile Logo -->
   <div class="hidden md:flex items-center">
    @auth
    <a href="{{route ('profile.edit') }}" class="flex items-center space-x-2 text-purple-100 hover:text-white transition duration-300">
     <div class="w-8 h-8 rounded-full bg-purple-500 flex items-center justify-center">
      <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
       <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
      </svg>
     </div>
    </a>
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