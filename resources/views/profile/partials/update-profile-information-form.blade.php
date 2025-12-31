<section>
    <header class="mb-6">
        <p class="text-gray-600 leading-relaxed">
            {{ __("Update your account's profile information and details.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6" x-data="{ loading: false }" @submit="loading = true">
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
                :disabled="loading"
                :class="loading ? 'opacity-75 cursor-not-allowed' : 'hover:from-purple-700 hover:to-purple-800'"
                class="bg-gradient-to-r from-purple-600 to-purple-700 text-white font-bold py-3 px-8 rounded-lg transition duration-300 shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-300 flex items-center gap-2"
            >
                <!-- Loading Spinner -->
                <svg x-show="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>

                <!-- Button Text -->
                <span x-text="loading ? 'Saving...' : 'Save Changes'">{{ __('Save Changes') }}</span>
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm font-medium text-green-600 flex items-center"
                >
                    <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ __('Saved successfully!') }}
                </p>
            @endif
        </div>
    </form>
</section>
