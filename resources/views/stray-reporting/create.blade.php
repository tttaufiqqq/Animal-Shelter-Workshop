<!-- Modal Overlay -->
<div id="reportModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden my-8">

        @include('stray-reporting.components.modal-header')

        @include('stray-reporting.components.offline-warning')

        @include('stray-reporting.components.gps-instructions')

        @include('stray-reporting.components.alert-container')

        @include('stray-reporting.components.validation-errors')

        <!-- Form Section -->
        <div class="p-6 md:p-8 max-h-[calc(100vh-12rem)] overflow-y-auto">
            <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data" id="reportForm" class="space-y-5">
                @csrf

                @include('stray-reporting.components.location-step')

                @include('stray-reporting.components.location-details-step')

                @include('stray-reporting.components.animal-condition-step')

                @include('stray-reporting.components.image-upload-step')

                @include('stray-reporting.components.submit-buttons')
            </form>
        </div>
    </div>
</div>

@include('stray-reporting.components.toast-container')

@include('stray-reporting.components.map-styles')

@include('stray-reporting.components.map-scripts')
