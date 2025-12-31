{{-- Sections View --}}
<div id="sectionsContent" class="content-section hidden">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        @if($sections->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-purple-500 to-purple-600">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                            Section Name
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                            Description
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                            Slots
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-white uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($sections as $section)
                        @php
                            $sectionSlots = $section->slots ?? collect([]);
                            $slotCount = $sectionSlots->count();
                        @endphp
                        <tr class="section-row hover:bg-gray-50 transition-colors duration-150"
                            data-section-name="{{ strtolower($section->name) }}">
                            <td class="px-4 py-4">
                                <div class="flex items-center">
                                    <i class="fas fa-layer-group text-indigo-500 mr-3 text-lg"></i>
                                    <div class="text-sm font-bold text-gray-900">{{ $section->name }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-sm text-gray-700">{{ $section->description }}</div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas fa-door-open text-purple-500 mr-2"></i>
                                    <span class="text-sm font-semibold text-gray-700">{{ $slotCount }}</span>
                                    <span class="text-xs text-gray-500 ml-1">slot{{ $slotCount != 1 ? 's' : '' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="viewSectionDetails({{ $section->id }})"
                                            class="text-indigo-600 hover:text-indigo-900 transition duration-150"
                                            title="View Details">
                                        <i class="fas fa-info-circle text-lg"></i>
                                    </button>
                                    @role('admin')
                                    <button onclick="editSection({{ $section->id }})"
                                            class="text-gray-600 hover:text-gray-900 transition duration-150"
                                            title="Edit Section">
                                        <i class="fas fa-edit text-lg"></i>
                                    </button>
                                    <button onclick="deleteSection({{ $section->id }})"
                                            class="text-red-600 hover:text-red-900 transition duration-150"
                                            title="Delete Section">
                                        <i class="fas fa-trash text-lg"></i>
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
            <div class="p-12 text-center">
                <div class="text-6xl mb-4">🏢</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">No Sections Yet</h3>
                <p class="text-gray-600 mb-6">Create your first section to organize shelter slots.</p>
                @role('admin')
                <button onclick="openSectionModal()" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-lg hover:from-indigo-600 hover:to-purple-700 transition duration-300">
                    <i class="fas fa-plus mr-2"></i>Add Your First Section
                </button>
                @endrole
            </div>
        @endif
    </div>
</div>
