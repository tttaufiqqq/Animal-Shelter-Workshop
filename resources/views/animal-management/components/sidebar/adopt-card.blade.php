<!-- Action Card -->
@if($animal->adoption_status == 'Not Adopted')
    @role('public user|caretaker|adopter')
    <div class="bg-white rounded-lg shadow-lg p-4">
        <h2 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
            <i class="fas fa-heart text-purple-600 mr-2"></i>
            Interested in Adopting?
        </h2>
        <div class="bg-purple-50 rounded-lg p-3">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-paw text-purple-600"></i>
                </div>
                <div>
                    <p class="text-gray-800 font-semibold">{{ $animal->name }}</p>
                    <p class="text-gray-500 text-sm">Give this friend a loving home</p>
                </div>
            </div>

            <p class="text-gray-600 text-sm mb-4">
                Add to your visit list and schedule an appointment to meet them in person!
            </p>

            <form action="{{ route('visit.list.add', $animal->id) }}" method="POST" id="addToVisitListForm" onsubmit="handleAddToVisitListSubmit(event)">
                @csrf
                <button type="submit" id="addToVisitListBtn"
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 rounded-lg transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="addToVisitListBtnText" class="flex items-center justify-center gap-2">
                        <i class="fas fa-plus"></i>
                        Add to Visit List
                    </span>
                    <span id="addToVisitListBtnLoading" class="hidden flex items-center justify-center gap-2">
                        <i class="fas fa-spinner fa-spin"></i>
                        Adding...
                    </span>
                </button>
            </form>

            <p class="text-gray-400 text-xs text-center mt-3">
                <i class="fas fa-info-circle mr-1"></i>
                No commitment required
            </p>
        </div>
    </div>
    @else
    <div class="bg-white rounded-lg shadow-lg p-4">
        <h2 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
            <i class="fas fa-heart text-purple-600 mr-2"></i>
            Interested in Adopting?
        </h2>
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
            <i class="fas fa-lock text-gray-400 text-2xl mb-2"></i>
            <p class="text-sm text-gray-600 font-semibold mb-1">Login Required</p>
            <p class="text-xs text-gray-500">Please login as a user or adopter to add animals to your visit list</p>
        </div>
    </div>
    @endrole
@endif
