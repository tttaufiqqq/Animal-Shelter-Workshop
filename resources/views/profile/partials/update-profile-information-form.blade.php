<section>
    <header class="mb-6">
        <p class="text-gray-600 leading-relaxed">
            {{ __("Update your account's profile information and details.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-semibold text-gray-800 mb-2">
                {{ __('Name') }}
            </label>
            <input 
                id="name" 
                name="name" 
                type="text" 
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-purple-600 focus:ring-2 focus:ring-purple-200 transition duration-200 outline-none" 
                value="{{ old('name', $user->name) }}" 
                required 
                autofocus 
                autocomplete="name"
            />
            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-800 mb-2">
                {{ __('Email') }}
            </label>
            <input 
                id="email" 
                name="email" 
                type="email" 
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-purple-600 focus:ring-2 focus:ring-purple-200 transition duration-200 outline-none" 
                value="{{ old('email', $user->email) }}" 
                required 
                autocomplete="username"
            />
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            
        </div>

        <!-- Phone Number -->
        <div>
            <label for="phoneNum" class="block text-sm font-semibold text-gray-800 mb-2">
                {{ __('Phone Number') }}
            </label>
            <input 
                id="phoneNum" 
                name="phoneNum" 
                type="text" 
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-purple-600 focus:ring-2 focus:ring-purple-200 transition duration-200 outline-none" 
                value="{{ old('phoneNum', $user->phoneNum) }}" 
                required 
            />
            @error('phoneNum')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Address -->
        <div>
            <label for="address" class="block text-sm font-semibold text-gray-800 mb-2">
                {{ __('Address') }}
            </label>
            <input 
                id="address" 
                name="address" 
                type="text" 
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-purple-600 focus:ring-2 focus:ring-purple-200 transition duration-200 outline-none" 
                value="{{ old('address', $user->address) }}" 
                required 
            />
            @error('address')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- City -->
        <div>
            <label for="city" class="block text-sm font-semibold text-gray-800 mb-2">
                {{ __('City') }}
            </label>
            <input 
                id="city" 
                name="city" 
                type="text" 
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-purple-600 focus:ring-2 focus:ring-purple-200 transition duration-200 outline-none" 
                value="{{ old('city', $user->city) }}" 
                required 
            />
            @error('city')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- State -->
        <div>
            <label for="state" class="block text-sm font-semibold text-gray-800 mb-2">
                {{ __('State') }}
            </label>
            <input 
                id="state" 
                name="state" 
                type="text" 
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-purple-600 focus:ring-2 focus:ring-purple-200 transition duration-200 outline-none" 
                value="{{ old('state', $user->state) }}" 
                required 
            />
            @error('state')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Save Button -->
        <div class="flex items-center gap-4 pt-4">
            <button 
                type="submit" 
                class="bg-gradient-to-r from-purple-600 to-purple-700 text-white font-bold py-3 px-8 rounded-lg hover:from-purple-700 hover:to-purple-800 transition duration-300 shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-300"
            >
                {{ __('Save Changes') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-medium text-green-600 flex items-center"
                >
                    <span class="inline-block w-5 h-5 bg-green-500 rounded-full mr-2 text-white text-center leading-5">✓</span>
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>
