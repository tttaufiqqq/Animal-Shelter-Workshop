{{-- map-scripts Orchestrator --}}
{{-- GPS location tracking and Leaflet map functionality --}}
{{-- Split into focused part files in map-scripts/ --}}
@include('stray-reporting.components.map-scripts.cdn-and-globals')
@include('stray-reporting.components.map-scripts.toast-and-alert')
@include('stray-reporting.components.map-scripts.geolocation-check')
@include('stray-reporting.components.map-scripts.geolocation-get')
@include('stray-reporting.components.map-scripts.location-tracking')
@include('stray-reporting.components.map-scripts.map-and-marker')
@include('stray-reporting.components.map-scripts.reverse-geocode')
@include('stray-reporting.components.map-scripts.state-utils')
@include('stray-reporting.components.map-scripts.map-init')
@include('stray-reporting.components.map-scripts.report-modal')
@include('stray-reporting.components.map-scripts.form-submit')
@include('stray-reporting.components.map-scripts.dom-events')
@include('stray-reporting.components.map-scripts.styles')
