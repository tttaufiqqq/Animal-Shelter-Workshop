{{-- show Orchestrator --}}
{{-- Animal detail view — split into focused part files in show/ --}}
@auth
    @if(Auth::user()->hasRole('admin'))
        {{-- Admin View with Admin Layout --}}
        @php
        $breadcrumbs = [
            ['label' => 'Animal Management', 'url' => route('animal-management.index'), 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>'],
            ['label' => $animal->name, 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>']
        ];
        $animalImages = $animal->getImagesOrEmpty();
        $hasImages = $animalImages->isNotEmpty();
        @endphp

        <x-admin-layout title="{{ $animal->name }} - Animal Details" :breadcrumbs="$breadcrumbs">
            @push('styles')
                @include('animal-management.show.styles')
            @endpush

            <div class="space-y-4">
                @include('animal-management.show.page-content')
            </div>

            {{-- Modals --}}
            @include('adopter-animal-matching.animal-modal')
            @include('animal-management.components.medical-modal', ['animal' => $animal, 'vets' => $vets ?? collect()])
            @include('animal-management.components.vaccination-modal', ['animal' => $animal, 'vets' => $vets ?? collect()])
            @include('booking-adoption.book-animal')

            @role('caretaker|public user|adopter')
            @include('booking-adoption.visit-list')
            @include('animal-management.edit-modal', ['animal' => $animal])
            @endrole

            @push('scripts')
                @include('animal-management.show.page-scripts')
            @endpush
        </x-admin-layout>
    @else
        {{-- Non-Admin View with Standalone Layout --}}
        @php
        $animalImages = $animal->getImagesOrEmpty();
        $hasImages = $animalImages->isNotEmpty();
        @endphp

        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{{ $animal->name }} - Animal Details</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            @include('animal-management.show.styles')
        </head>
        <body class="bg-gradient-to-br from-gray-50 to-purple-50 min-h-screen">
            @include('navbar')

            <div class="px-4 sm:px-6 lg:px-8 py-8 space-y-4">
                {{-- Back Button --}}
                <div class="mb-4">
                    <a href="{{ route('animal-management.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 font-medium rounded-lg shadow-sm border border-gray-200 transition-all duration-200 hover:shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Back to Animals</span>
                    </a>
                </div>

                @include('animal-management.show.page-content')
            </div>

            {{-- Modals --}}
            @include('adopter-animal-matching.animal-modal')
            @include('animal-management.components.medical-modal', ['animal' => $animal, 'vets' => $vets ?? collect()])
            @include('animal-management.components.vaccination-modal', ['animal' => $animal, 'vets' => $vets ?? collect()])
            @include('booking-adoption.book-animal')

            @role('caretaker|public user|adopter')
            @include('booking-adoption.visit-list')
            @include('animal-management.edit-modal', ['animal' => $animal])
            @endrole

            @include('animal-management.show.page-scripts')
        </body>
        </html>
    @endif
@endauth
