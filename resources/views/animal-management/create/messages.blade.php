        <!-- Messages -->
        @if (session('success'))
            <div class="flex items-start gap-3 p-4 mb-6 bg-green-50 border border-green-200 rounded-xl shadow-sm mx-6 mt-6">
                <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <p class="font-semibold text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="m-6 bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 text-red-800 p-4 rounded-xl shadow-md fade-in">
                <div class="flex items-start gap-3">
                    <div class="bg-red-500 text-white p-2 rounded-full">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div>
                        <p class="font-bold mb-2">Please correct the following errors:</p>
                        <ul class="list-disc list-inside space-y-1 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
