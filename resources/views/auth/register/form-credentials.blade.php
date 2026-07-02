                    <!-- City and State (Two Columns) -->
                    <div class="grid grid-cols-2 gap-3">
                        <!-- City -->
                        <div>
                            <label for="city" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                                <i class="fas fa-city text-purple-600 text-xs"></i>
                                City
                            </label>
                            <input id="city"
                                   name="city"
                                   type="text"
                                   value="{{ old('city') }}"
                                   required
                                   class="block w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition @error('city') border-red-500 @enderror"
                                   placeholder="Kuala Lumpur">
                            @error('city')
                            <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <!-- State -->
                        <div>
                            <label for="state" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                                <i class="fas fa-flag text-purple-600 text-xs"></i>
                                State
                            </label>
                            <input id="state"
                                   name="state"
                                   type="text"
                                   value="{{ old('state') }}"
                                   required
                                   class="block w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition @error('state') border-red-500 @enderror"
                                   placeholder="Selangor">
                            @error('state')
                            <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Password and Confirm Password (Two Columns) -->
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Password -->
                        <div>
                            <label for="password" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                                <i class="fas fa-lock text-purple-600 text-xs"></i>
                                Password
                            </label>
                            <input id="password"
                                   name="password"
                                   type="password"
                                   required
                                   autocomplete="new-password"
                                   class="block w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition @error('password') border-red-500 @enderror"
                                   placeholder="••••••••">
                            @error('password')
                            <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="flex items-center gap-1.5 text-gray-800 font-semibold mb-1 text-sm">
                                <i class="fas fa-lock text-purple-600 text-xs"></i>
                                Confirm
                            </label>
                            <input id="password_confirmation"
                                   name="password_confirmation"
                                   type="password"
                                   required
                                   autocomplete="new-password"
                                   class="block w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition @error('password_confirmation') border-red-500 @enderror"
                                   placeholder="••••••••">
                            @error('password_confirmation')
                            <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex flex-col items-center justify-center pt-2 space-y-3">
                        <button type="submit"
                                class="w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-lg hover:from-purple-700 hover:to-purple-800 hover:shadow-xl transition-all duration-300 hover:scale-105 flex items-center justify-center gap-2 text-sm">
                            <i class="fas fa-user-plus text-xs"></i>
                            <span>Create Account</span>
                        </button>

                        <div class="text-center">
                            <a class="text-xs text-gray-600 hover:text-purple-600 transition font-semibold inline-flex items-center gap-1"
                               href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt"></i>
                                Already have an account? Log in
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- User Guide Modal -->
<x-user-guide-modal />
</body>
</html>
