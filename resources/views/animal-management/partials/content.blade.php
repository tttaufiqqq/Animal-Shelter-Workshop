{{-- content Orchestrator --}}
@include('animal-management.partials.content.stats-messages')

@if($animals->count() > 0)
    @if(Auth::check() && (Auth::user()->hasRole('admin') || (Auth::user()->hasRole('caretaker') && request('rescued_by_me') === 'true')))
        @include('animal-management.partials.content.animals-table')
    @else
        @include('animal-management.partials.content.animals-card')
    @endif
@else
    @include('animal-management.partials.empty-state')
@endif

@include('animal-management.partials.content.loading-overlay')
