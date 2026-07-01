{{-- database-status-indicator Orchestrator --}}
@if(isset($dbDisconnected) && count($dbDisconnected) > 0)
@include('components.database-status-indicator.indicator-html')
@include('components.database-status-indicator.scripts')
@endif
