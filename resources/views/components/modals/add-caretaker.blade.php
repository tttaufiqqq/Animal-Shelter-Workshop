<!-- Add Caretaker Modal -->
<div id="caretakerModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm z-50 items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-gradient-to-r from-teal-600 to-teal-700 text-white p-6 rounded-t-2xl z-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-plus text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">Add New Caretaker</h2>
                        <p class="text-teal-100 text-sm">Create a new caretaker account</p>
                    </div>
                </div>
                <button onclick="closeCaretakerModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            {{-- Success Alert --}}
            @if (session('caretaker_success'))
                <div class="flex items-start gap-3 p-4 mb-6 bg-green-50 border border-green-200 rounded-xl shadow-sm">
                    <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="font-semibold text-green-700">{{ session('caretaker_success') }}</p>
                </div>
            @endif

            {{-- Error Alert --}}
            @if (session('caretaker_error'))
                <div class="flex items-start gap-3 p-4 mb-6 bg-red-50 border border-red-200 rounded-xl shadow-sm">
                    <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <p class="font-semibold text-red-700">{{ session('caretaker_error') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.caretaker.store') }}" class="space-y-4">
                @csrf

                <!-- Name -->
                <div>
                    <label for="caretaker_name" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                        <i class="fas fa-user text-teal-600 text-xs"></i>
                        Name
                    </label>
                    <input id="caretaker_name"
                           name="name"
                           type="text"
                           value="{{ old('name') }}"
                           required
                           autofocus
                           autocomplete="name"
                           class="block w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition @error('name', 'caretaker') border-red-500 @enderror"
                           placeholder="John Doe">
                    @error('name', 'caretaker')
                    <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="caretaker_email" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                        <i class="fas fa-envelope text-teal-600 text-xs"></i>
                        Email
                    </label>
                    <input id="caretaker_email"
                           name="email"
                           type="email"
                           value="{{ old('email') }}"
                           required
                           autocomplete="username"
                           class="block w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition @error('email', 'caretaker') border-red-500 @enderror"
                           placeholder="caretaker@example.com">
                    @error('email', 'caretaker')
                    <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Phone Number -->
                <div>
                    <label for="caretaker_phoneNum" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                        <i class="fas fa-phone text-teal-600 text-xs"></i>
                        Phone Number
                    </label>
                    <input id="caretaker_phoneNum"
                           name="phoneNum"
                           type="text"
                           value="{{ old('phoneNum') }}"
                           required
                           class="block w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition @error('phoneNum', 'caretaker') border-red-500 @enderror"
                           placeholder="+60123456789">
                    @error('phoneNum', 'caretaker')
                    <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Address -->
                <div>
                    <label for="caretaker_address" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                        <i class="fas fa-map-marker-alt text-teal-600 text-xs"></i>
                        Address
                    </label>
                    <input id="caretaker_address"
                           name="address"
                           type="text"
                           value="{{ old('address') }}"
                           required
                           class="block w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition @error('address', 'caretaker') border-red-500 @enderror"
                           placeholder="123 Main Street">
                    @error('address', 'caretaker')
                    <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- City and State (Two Columns) -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- City -->
                    <div>
                        <label for="caretaker_city" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                            <i class="fas fa-city text-teal-600 text-xs"></i>
                            City
                        </label>
                        <input id="caretaker_city"
                               name="city"
                               type="text"
                               value="{{ old('city') }}"
                               required
                               class="block w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition @error('city', 'caretaker') border-red-500 @enderror"
                               placeholder="Kuala Lumpur">
                        @error('city', 'caretaker')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- State -->
                    <div>
                        <label for="caretaker_state" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                            <i class="fas fa-flag text-teal-600 text-xs"></i>
                            State
                        </label>
                        <input id="caretaker_state"
                               name="state"
                               type="text"
                               value="{{ old('state') }}"
                               required
                               class="block w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition @error('state', 'caretaker') border-red-500 @enderror"
                               placeholder="Selangor">
                        @error('state', 'caretaker')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>
                </div>

                <!-- Password and Confirm Password (Two Columns) -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Password -->
                    <div>
                        <label for="caretaker_password" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                            <i class="fas fa-lock text-teal-600 text-xs"></i>
                            Password
                        </label>
                        <input id="caretaker_password"
                               name="password"
                               type="password"
                               required
                               autocomplete="new-password"
                               class="block w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition @error('password', 'caretaker') border-red-500 @enderror"
                               placeholder="••••••••">
                        @error('password', 'caretaker')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="caretaker_password_confirmation" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                            <i class="fas fa-lock text-teal-600 text-xs"></i>
                            Confirm Password
                        </label>
                        <input id="caretaker_password_confirmation"
                               name="password_confirmation"
                               type="password"
                               required
                               autocomplete="new-password"
                               class="block w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition @error('password_confirmation', 'caretaker') border-red-500 @enderror"
                               placeholder="••••••••">
                        @error('password_confirmation', 'caretaker')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex gap-3 pt-4">
                    <button type="button"
                            onclick="closeCaretakerModal()"
                            class="flex-1 bg-gray-200 text-gray-700 font-semibold py-3 px-4 rounded-lg hover:bg-gray-300 transition-all duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 bg-gradient-to-r from-teal-600 to-teal-700 text-white font-semibold py-3 px-4 rounded-lg shadow-lg hover:from-teal-700 hover:to-teal-800 hover:shadow-xl transition-all duration-200">
                        <i class="fas fa-user-plus mr-2"></i>
                        Create Caretaker
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
