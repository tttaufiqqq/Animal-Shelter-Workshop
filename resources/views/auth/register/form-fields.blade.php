            <!-- Right Section - Registration Form -->
            <div class="p-6 md:p-8 flex flex-col justify-center">
                <div class="text-center mb-4">
                    <div class="mb-3 inline-block">
                        <div class="relative">
                            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-purple-700 rounded-full flex items-center justify-center shadow-lg">
                                    <i class="fas fa-user text-lg text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-800 mb-1">Create Your Account</h2>
                    <p class="text-sm text-gray-600 mb-2">
                        Sign up to report strays, adopt pets, and make a difference
                    </p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-3">
                    @csrf

                    <!-- Name -->
                    <div>
                        <label for="name" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                            <i class="fas fa-user text-purple-600 text-xs"></i>
                            Name
                        </label>
                        <input id="name"
                               name="name"
                               type="text"
                               value="{{ old('name') }}"
                               required
                               autofocus
                               autocomplete="name"
                               class="block w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition @error('name') border-red-500 @enderror"
                               placeholder="John Doe">
                        @error('name')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                            <i class="fas fa-envelope text-purple-600 text-xs"></i>
                            Email
                        </label>
                        <input id="email"
                               name="email"
                               type="email"
                               value="{{ old('email') }}"
                               required
                               autocomplete="username"
                               class="block w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition @error('email') border-red-500 @enderror"
                               placeholder="your.email@example.com">
                        @error('email')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label for="phoneNum" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                            <i class="fas fa-phone text-purple-600 text-xs"></i>
                            Phone Number
                        </label>
                        <input id="phoneNum"
                               name="phoneNum"
                               type="text"
                               value="{{ old('phoneNum') }}"
                               required
                               class="block w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition @error('phoneNum') border-red-500 @enderror"
                               placeholder="+60123456789">
                        @error('phoneNum')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="address" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                            <i class="fas fa-map-marker-alt text-purple-600 text-xs"></i>
                            Address
                        </label>
                        <input id="address"
                               name="address"
                               type="text"
                               value="{{ old('address') }}"
                               required
                               class="block w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition @error('address') border-red-500 @enderror"
                               placeholder="123 Main Street">
                        @error('address')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>
