{{-- Clinics Section --}}
<div class="bg-white border border-gray-200 rounded-lg shadow-sm">
    {{-- Section Header --}}
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-600 rounded flex items-center justify-center">
                    <i class="fas fa-hospital text-white"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Clinics</h2>
                    <p class="text-xs text-gray-600">{{ $clinics->count() }} registered clinics</p>
                </div>
            </div>
            @role('admin')
            <button onclick="openModal('clinic')" class="px-4 py-2 bg-blue-600 text-white font-medium rounded flex items-center gap-2">
                <i class="fas fa-plus"></i>
                <span>Add Clinic</span>
            </button>
            @endrole
        </div>
    </div>

    {{-- Clinics Table --}}
    @if($clinics->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Clinic Name</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Address</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Coordinates</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($clinics as $clinic)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-clinic-medical text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $clinic->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 max-w-xs">{{ $clinic->address }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $clinic->contactNum }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($clinic->latitude && $clinic->longitude)
                                <div class="text-xs text-gray-600">
                                    <div>{{ number_format($clinic->latitude, 4) }}</div>
                                    <div>{{ number_format($clinic->longitude, 4) }}</div>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">Not set</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-2">
                                @if($clinic->latitude && $clinic->longitude)
                                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $clinic->latitude }},{{ $clinic->longitude }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded border border-blue-200"
                                       title="Navigate to clinic">
                                        <i class="fas fa-directions"></i>
                                    </a>
                                @endif
                                @role('admin')
                                <button onclick="editClinic({{ $clinic->id }})"
                                        class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded border border-gray-200"
                                        title="Edit clinic">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteClinic({{ $clinic->id }})"
                                        class="px-3 py-1 bg-red-100 text-red-700 text-xs font-medium rounded border border-red-200"
                                        title="Delete clinic">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endrole
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        {{-- Empty State --}}
        <div class="p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-hospital text-2xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Clinics Registered</h3>
            <p class="text-sm text-gray-600 mb-4">Add your first clinic to get started</p>
            @role('admin')
            <button onclick="openModal('clinic')" class="px-4 py-2 bg-blue-600 text-white font-medium rounded">
                <i class="fas fa-plus mr-2"></i>Add First Clinic
            </button>
            @endrole
        </div>
    @endif
</div>
