<!-- Guest Section (Not Logged In) -->
<div class="text-center">
    <div class="mb-6 inline-block p-4 bg-purple-100 rounded-full">
        <i class="fas fa-paw text-5xl text-purple-600"></i>
    </div>

    <h2 class="text-3xl font-bold text-gray-800 mb-4">Welcome to Animal Rescue System</h2>
    <p class="text-gray-600 mb-6 text-lg leading-relaxed">
        Report stray animals, adopt pets, and help us save lives together.
    </p>

    <div class="space-y-3">
        <a href="{{ route('login') }}" class="block w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white font-bold py-4 rounded-xl shadow-lg hover:from-purple-700 hover:to-purple-800 hover:shadow-xl transition-all duration-300 hover:scale-105 flex items-center justify-center gap-2">
            <i class="fas fa-sign-in-alt"></i>
            <span>Log In</span>
        </a>
        <a href="{{ route('register') }}" class="block w-full border-2 border-purple-600 text-purple-600 font-bold py-4 rounded-xl hover:bg-purple-50 transition-all duration-300 hover:scale-105 flex items-center justify-center gap-2">
            <i class="fas fa-user-plus"></i>
            <span>Create Account</span>
        </a>
    </div>
</div>
