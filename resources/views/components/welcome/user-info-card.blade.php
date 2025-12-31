<!-- User Info Card -->
<div class="bg-purple-50 border-l-4 border-purple-600 p-6 rounded-xl shadow-sm text-left">
    <p class="text-gray-700 mb-2">
        <span class="font-semibold text-gray-800">Name:</span>
        {{ Auth::user()->name }}
    </p>

    <p class="text-gray-700 mb-4">
        <span class="font-semibold text-gray-800">Email:</span>
        {{ Auth::user()->email }}
    </p>

    <!-- Roles Display -->
    <div class="flex flex-wrap gap-3 mt-3 mb-4">
        @php
            $userRoles = Auth::user()->getRoleNames();
            $rolesToDisplay = $userRoles->isEmpty() ? collect(['user']) : $userRoles;

            $badgeColors = [
                'staff' => 'from-purple-600 to-purple-700',
                'adopter' => 'from-purple-600 to-purple-700',
                'moderator' => 'from-blue-600 to-blue-700',
                'user' => 'from-gray-600 to-gray-700',
                'public user' => 'from-gray-600 to-gray-700',
                'caretaker' => 'from-teal-600 to-teal-700',
            ];
        @endphp

        @foreach ($rolesToDisplay as $role)
            <span class="inline-block bg-gradient-to-r {{ $badgeColors[$role] ?? 'from-gray-600 to-gray-700' }} text-white px-4 py-2 rounded-full text-sm font-semibold capitalize shadow-sm">
                {{ $role }}
            </span>
        @endforeach
    </div>

    <!-- Action Buttons Container -->
    <div class="flex flex-col gap-3 mt-4">
        {{ $slot }}
    </div>
</div>
