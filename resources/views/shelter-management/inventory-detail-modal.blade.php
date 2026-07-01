{{-- inventory-detail-modal Orchestrator --}}
{{-- Inventory detail view with compatibility analysis and edit/delete --}}
{{-- Split into focused part files in inventory-detail-modal/ --}}
@include('shelter-management.inventory-detail-modal.html')
@include('shelter-management.inventory-detail-modal.init-scripts')
@include('shelter-management.inventory-detail-modal.compat-analysis')
@include('shelter-management.inventory-detail-modal.compat-display')
@include('shelter-management.inventory-detail-modal.edit-delete-scripts')
