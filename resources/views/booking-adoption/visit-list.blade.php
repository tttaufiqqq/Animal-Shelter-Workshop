{{-- visit-list Orchestrator --}}
@include('booking-adoption.visit-list.modal-header')
@include('booking-adoption.visit-list.modal-body-errors')
@if ($animalList->isNotEmpty())
    @include('booking-adoption.visit-list.animal-grid')
    @include('booking-adoption.visit-list.appointment-form')
@else
        </div>{{-- closes overflow-y-auto div from modal-body-errors --}}
    </div>{{-- closes visitModalContent --}}
</div>{{-- closes visitModal --}}
@endif
@include('booking-adoption.visit-list.remove-confirm')
@include('booking-adoption.visit-list.scripts-remove')
@include('booking-adoption.visit-list.scripts-visit')
