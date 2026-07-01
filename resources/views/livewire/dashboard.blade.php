{{-- dashboard Orchestrator --}}
<div>
    @include('livewire.dashboard-parts.warning-banner')

    <!-- Dashboard Content -->
    <div class="space-y-6">
        @include('livewire.dashboard-parts.metrics-cards')
        @include('livewire.dashboard-parts.charts-html')
        @include('livewire.dashboard-parts.scripts-pie-month')
        @include('livewire.dashboard-parts.scripts-volume-events')
    </div>
</div>
