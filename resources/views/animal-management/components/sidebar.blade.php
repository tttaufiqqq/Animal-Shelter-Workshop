{{-- sidebar Orchestrator --}}
<!-- Right Column - Details -->
<div class="space-y-4 lg:sticky lg:top-4 lg:self-start lg:max-h-[calc(100vh-2rem)] lg:overflow-y-auto">
    @include('animal-management.components.sidebar.animal-info')
    @include('animal-management.components.sidebar.animal-profile')
    @include('animal-management.components.sidebar.slot-card')
    @include('animal-management.components.sidebar.adopt-card')
</div>
@include('animal-management.components.sidebar.scripts')
