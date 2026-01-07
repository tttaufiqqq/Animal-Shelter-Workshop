<!-- Medical Records Section -->
<div class="fade-in bg-gradient-to-br from-white to-blue-50/30 rounded-2xl shadow-xl p-4 border border-blue-100 hover-scale">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-2 rounded-lg shadow-lg">
                <i class="fas fa-notes-medical text-white text-lg"></i>
            </div>
            <span>Medical Records</span>
        </h2>
        @role('caretaker')
        <button onclick="openMedicalModal()" class="group bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-3 py-2 rounded-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 flex items-center gap-2">
            <i class="fas fa-plus group-hover:rotate-90 transition-transform text-sm"></i>
        </button>
        @else
        <div class="bg-gray-100 text-gray-500 px-3 py-2 rounded-lg text-xs flex items-center gap-2">
            <i class="fas fa-lock"></i>
            <span>Caretaker Only</span>
        </div>
        @endrole
    </div>

    @if($animal->medicals && $animal->medicals->count() > 0)
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @foreach($animal->medicals->sortByDesc('created_at') as $medical)
                <div class="bg-white border-l-4 border-blue-500 rounded-lg p-3 hover:shadow-md transition-all duration-200">
                    <div class="flex items-start justify-between mb-3">
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-gradient-to-r from-blue-100 to-blue-200 text-blue-700 shadow-sm">
                            <i class="fas fa-stethoscope"></i>
                            {{ $medical->treatment_type }}
                        </span>
                        <span class="text-xs text-gray-500 font-semibold bg-gray-100 px-3 py-1 rounded-full">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            {{ \Carbon\Carbon::parse($medical->created_at)->format('M d, Y') }}
                        </span>
                    </div>
                    <h4 class="font-bold text-gray-800 mb-2 flex items-center gap-2">
                        <i class="fas fa-file-medical text-blue-600"></i>
                        {{ $medical->diagnosis }}
                    </h4>
                    <p class="text-gray-700 text-sm mb-2 bg-gray-50 p-3 rounded-lg"><strong class="text-blue-600">Action:</strong> {{ $medical->action }}</p>
                    @if($medical->remarks)
                        <p class="text-gray-600 text-sm mb-3 italic bg-blue-50 p-3 rounded-lg"><strong class="text-blue-600">Remarks:</strong> {{ $medical->remarks }}</p>
                    @endif
                    <div class="flex flex-wrap items-center gap-4 text-sm pt-2 border-t border-gray-100">
                        @if($medical->vet)
                            <div class="flex items-center text-gray-600">
                                <div class="bg-blue-100 p-1.5 rounded-lg mr-2">
                                    <i class="fas fa-user-md text-blue-600"></i>
                                </div>
                                <span class="font-medium">{{ $medical->vet->name }}</span>
                            </div>
                        @endif
                        @if($medical->costs)
                            <div class="flex items-center text-green-700 font-bold">
                                <div class="bg-green-100 p-1.5 rounded-lg mr-2">
                                    <i class="fas fa-money-bill-wave text-green-600"></i>
                                </div>
                                <span>RM {{ number_format($medical->costs, 2) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-gradient-to-br from-gray-50 to-blue-50/50 rounded-xl p-8 text-center border-2 border-dashed border-gray-300">
            <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-notes-medical text-3xl text-blue-400"></i>
            </div>
            <p class="font-bold text-gray-700 mb-1">No medical records yet</p>
            <p class="text-sm text-gray-500">Medical treatment records will appear here</p>
        </div>
    @endif
</div>
