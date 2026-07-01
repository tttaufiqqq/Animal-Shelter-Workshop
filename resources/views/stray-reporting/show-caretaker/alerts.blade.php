{{-- Success Alert --}}
@if(session('success'))
    <div class="flex items-start gap-3 p-4 mb-6 bg-green-50 border border-green-200 rounded-xl shadow-sm fade-in-up">
        <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
        <div class="flex-1">
            <p class="font-semibold text-green-700">{{ session('success') }}</p>
        </div>
        <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
@endif

{{-- Error Alert --}}
@if(session('error'))
    <div class="flex items-start gap-3 p-4 mb-6 bg-red-50 border border-red-200 rounded-xl shadow-sm fade-in-up">
        <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <div class="flex-1">
            <p class="font-semibold text-red-700">{{ session('error') }}</p>
        </div>
        <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
@endif

{{-- Validation Errors --}}
@if($errors->any())
    <div class="flex items-start gap-3 p-4 mb-6 bg-red-50 border border-red-200 rounded-xl shadow-sm fade-in-up">
        <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="flex-1">
            <p class="font-semibold text-red-700 mb-2">Please fix the following errors:</p>
            <ul class="list-disc list-inside space-y-1 text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
@endif
