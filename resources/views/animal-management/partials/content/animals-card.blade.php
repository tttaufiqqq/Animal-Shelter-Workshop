{{-- Card View for Regular Users --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
    @foreach($animals as $animal)
        @include('animal-management.partials.animal-card', ['animal' => $animal])
    @endforeach
</div>

{{-- Pagination for Card View --}}
<div class="mt-12 flex justify-center">
    <div class="bg-white rounded-lg shadow-sm p-4">
        {{ $animals->links() }}
    </div>
</div>
