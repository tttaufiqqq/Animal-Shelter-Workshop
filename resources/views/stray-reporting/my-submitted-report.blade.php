{{--
    My Submitted Reports Component

    This file includes modular components for displaying user's submitted reports:
    - My Reports List Modal (table view with pagination)
    - Report Detail Modal (detailed view with map and status tracker)
    - Image Modal (full-size image preview)
    - JavaScript functions for all modal interactions
--}}

{{-- Include Modal Components --}}
@include('stray-reporting.modals.my-reports-list-modal')
@include('stray-reporting.modals.report-detail-modal')
@include('stray-reporting.modals.report-image-modal')

{{-- Include JavaScript Functionality --}}
@include('stray-reporting.partials.my-reports-scripts')
