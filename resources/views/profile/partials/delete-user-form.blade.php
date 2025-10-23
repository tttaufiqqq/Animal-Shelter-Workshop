<section>
    <header class="mb-6">
        <p class="text-gray-600 leading-relaxed">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button
        type="button"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-gradient-to-r from-red-600 to-red-700 text-white font-bold py-3 px-8 rounded-lg hover:from-red-700 hover:to-red-800 transition duration-300 shadow-lg focus:outline-none focus:ring-4 focus:ring-red-300"
    >
        {{ __('Delete Account') }}
    </button>

    <!-- Delete Confirmation Modal -->
    <div
        x-data="{ show: false }"
        x-on:open-modal.window="$event.detail == 'confirm-user-deletion' ? show = true : null"
        x-on:close-modal.window="show = false"
        x-on:keydown.escape.window="show = false"
        x-show="show"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        <!-- Backdrop -->
        <div 
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900 bg-opacity-75"
            x-on:click="show = false"
        ></div>

        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden"
                x-on:click.stop
            >
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-red-600 to-red-700 p-6">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 bg-red-500 rounded-full mr-3 text-lg">⚠️</span>
                        {{ __('Are you sure you want to delete your account?') }}
                    </h2>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                    </p>

                    <form method="post" action="{{ route('profile.destroy') }}" class="space-y-6">
                        @csrf
                        @method('delete')

                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-800 mb-2">
                                {{ __('Password') }}
                            </label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-red-600 focus:ring-2 focus:ring-red-200 transition duration-200 outline-none"
                                placeholder="{{ __('Password') }}"
                                autocomplete="current-password"
                            />
                            @error('password', 'userDeletion')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4">
                            <button
                                type="button"
                                x-on:click="show = false"
                                class="bg-white border-2 border-gray-300 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-50 transition duration-300 focus:outline-none focus:ring-4 focus:ring-gray-200"
                            >
                                {{ __('Cancel') }}
                            </button>

                            <button
                                type="submit"
                                class="bg-gradient-to-r from-red-600 to-red-700 text-white font-bold py-3 px-6 rounded-lg hover:from-red-700 hover:to-red-800 transition duration-300 shadow-lg focus:outline-none focus:ring-4 focus:ring-red-300"
                            >
                                {{ __('Delete Account') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>