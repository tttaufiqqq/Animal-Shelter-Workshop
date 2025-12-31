{{-- Admin Dashboard Header --}}
<div class="mb-6 bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-700 text-white p-6 -mx-6 -mt-6 rounded-b-xl shadow-lg">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        {{-- Left: Title and Description --}}
        <div class="flex items-center gap-3">
            <div class="bg-white bg-opacity-20 p-3 rounded-2xl backdrop-blur-sm">
                <i class="fas fa-paw text-3xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold">Animal Management</h1>
                <p class="text-purple-100 text-sm mt-1">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Admin Dashboard - Complete oversight and control
                </p>
            </div>
        </div>

        {{-- Right: Quick Action Buttons --}}
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('animal-management.create') }}"
               class="quick-action-btn bg-white bg-opacity-20 hover:bg-white hover:text-purple-700 px-4 py-2 rounded-lg backdrop-blur-sm transition flex items-center gap-2 text-sm font-semibold shadow-lg">
                <i class="fas fa-plus-circle"></i>
                Add Animal
            </a>
            <a href="{{ route('animal-management.clinic-index') }}"
               class="quick-action-btn bg-white bg-opacity-20 hover:bg-white hover:text-purple-700 px-4 py-2 rounded-lg backdrop-blur-sm transition flex items-center gap-2 text-sm font-semibold shadow-lg">
                <i class="fas fa-clinic-medical"></i>
                Clinics & Vets
            </a>
            <a href="{{ route('medical-records.create') }}"
               class="quick-action-btn bg-white bg-opacity-20 hover:bg-white hover:text-purple-700 px-4 py-2 rounded-lg backdrop-blur-sm transition flex items-center gap-2 text-sm font-semibold shadow-lg">
                <i class="fas fa-heartbeat"></i>
                Add Medical Record
            </a>
            <button onclick="window.print()"
                    class="quick-action-btn bg-white bg-opacity-20 hover:bg-white hover:text-purple-700 px-4 py-2 rounded-lg backdrop-blur-sm transition flex items-center gap-2 text-sm font-semibold shadow-lg">
                <i class="fas fa-file-export"></i>
                Export
            </button>
        </div>
    </div>
</div>
