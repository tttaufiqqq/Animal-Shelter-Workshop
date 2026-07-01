{{-- my-reports-scripts Orchestrator --}}
{{-- Report modal, status tracker, and event handlers --}}
{{-- Split into focused part files in my-reports-scripts/ --}}
<script>
    let miniMaps = {};
    let detailMap = null;

    function getReportsData() {
        return window.reportsData || [];
    }
</script>
@include('stray-reporting.partials.my-reports-scripts.status-tracker')
@include('stray-reporting.partials.my-reports-scripts.report-detail-modal')
@include('stray-reporting.partials.my-reports-scripts.modal-events')
