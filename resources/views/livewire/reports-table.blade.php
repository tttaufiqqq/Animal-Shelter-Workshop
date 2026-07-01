{{-- reports-table Orchestrator --}}
<div wire:poll.10s="checkForNewReports">
    @include('livewire.reports-table.auto-refresh-banner')
    @include('livewire.reports-table.filter-cards')
    @include('livewire.reports-table.search-table')
    @include('livewire.reports-table.scripts')
</div>
