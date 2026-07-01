
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
