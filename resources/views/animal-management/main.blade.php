@auth
    @if(Auth::user()->hasRole('admin'))
        {{-- Admin View with Admin Layout --}}
        @php
        $breadcrumbs = [
            ['label' => 'Animals', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle cx="7" cy="5" r="1.5" stroke-width="1.5"/><circle cx="17" cy="5" r="1.5" stroke-width="1.5"/><circle cx="5" cy="11" r="1.5" stroke-width="1.5"/><circle cx="19" cy="11" r="1.5" stroke-width="1.5"/><ellipse cx="12" cy="16" rx="4" ry="5" stroke-width="1.5"/></svg>']
        ];
        @endphp

        <x-admin-layout title="Animals" :breadcrumbs="$breadcrumbs">
            @push('styles')
                <style>
                    .line-clamp-2 {
                        display: -webkit-box;
                        -webkit-line-clamp: 2;
                        -webkit-box-orient: vertical;
                        overflow: hidden;
                    }
                </style>
            @endpush

            {{-- Database Warning Banner --}}
            @if(isset($dbDisconnected) && count($dbDisconnected) > 0)
                <div class="mb-4 flex items-center gap-2 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded-lg">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-yellow-800">Limited Connectivity</h3>
                        <p class="text-sm text-yellow-700 mt-1">{{ count($dbDisconnected) }} database(s) currently unavailable. Some features may not work properly.</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($dbDisconnected as $connection => $info)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ $info['module'] }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <button onclick="this.parentElement.remove()" class="flex-shrink-0 text-yellow-400 hover:text-yellow-600 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            @endif

            {{-- Page Header for Admin --}}
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Animals Management</h1>
                <p class="text-sm text-gray-600 mt-1">Manage all animals in shelter care</p>
            </div>

            {{-- Main Content --}}
            <div class="space-y-6">
                @include('animal-management.partials.content', ['animals' => $animals])
            </div>
        </x-admin-layout>
    @else
        {{-- Non-Admin View with Standalone Layout --}}
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Animals - Stray Animal Shelter</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
                /* Card hover effects */
                .animal-card {
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                }

                .animal-card:hover {
                    transform: translateY(-8px);
                    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                }

                .animal-card:hover .animal-image {
                    transform: scale(1.05);
                }

                .animal-image {
                    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                }

                .line-clamp-2 {
                    display: -webkit-box;
                    -webkit-line-clamp: 2;
                    -webkit-box-orient: vertical;
                    overflow: hidden;
                }

                /* Custom scrollbar */
                ::-webkit-scrollbar {
                    width: 8px;
                    height: 8px;
                }

                ::-webkit-scrollbar-track {
                    background: #f1f1f1;
                    border-radius: 10px;
                }

                ::-webkit-scrollbar-thumb {
                    background: #9333ea;
                    border-radius: 10px;
                }

                ::-webkit-scrollbar-thumb:hover {
                    background: #7e22ce;
                }
            </style>
        </head>
        <body class="bg-gradient-to-br from-gray-50 to-purple-50 min-h-screen">
            @include('navbar')

            {{-- Database Warning Banner --}}
            @if(isset($dbDisconnected) && count($dbDisconnected) > 0)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 shadow-sm">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-semibold text-yellow-800">Limited Connectivity</h3>
                            <p class="text-sm text-yellow-700 mt-1">{{ count($dbDisconnected) }} database(s) currently unavailable. You may experience limited functionality or missing data.</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($dbDisconnected as $connection => $info)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                        {{ $info['module'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto flex-shrink-0 text-yellow-400 hover:text-yellow-600 transition">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Page Header --}}
            <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-700 text-white py-16 shadow-xl">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                        {{-- Left: Title --}}
                        <div>
                            <div class="flex items-center gap-3 mb-3">
                                <div class="bg-white bg-opacity-20 p-3 rounded-2xl backdrop-blur-sm">
                                    <i class="fas fa-paw text-4xl"></i>
                                </div>
                                <div>
                                    <h1 class="text-4xl md:text-5xl font-bold mb-1">Our Animals</h1>
                                    <p class="text-purple-100 text-sm md:text-base">
                                        <i class="fas fa-heart mr-1"></i>
                                        Browse all animals currently in our care
                                    </p>
                                </div>
                            </div>
                            <p class="text-purple-200 text-sm max-w-2xl">
                                Add animals to your visit list to schedule an appointment and meet them in person. Each animal is waiting for their forever home.
                            </p>
                        </div>

                        @role('public user|caretaker|adopter')
                        {{-- Right: Visit List Button --}}
                        <button onclick="openVisitModal()"
                                class="bg-white bg-opacity-20 hover:bg-white hover:text-purple-700 text-white px-6 py-4 rounded-2xl transition-all duration-300 flex items-center gap-3 shadow-lg backdrop-blur-sm hover:shadow-xl transform hover:scale-105">
                            <div class="bg-white bg-opacity-30 p-2 rounded-lg">
                                <i class="fas fa-clipboard-list text-2xl"></i>
                            </div>
                            <div class="text-left">
                                <div class="font-bold text-lg">Visit List</div>
                                <div class="text-xs opacity-90">View saved animals</div>
                            </div>
                        </button>
                        @endrole
                    </div>
                </div>
            </div>

            @include('booking-adoption.visit-list')

            {{-- Main Content --}}
            <div class="px-4 sm:px-6 lg:px-8 py-8">
                @include('animal-management.partials.content', ['animals' => $animals])
            </div>
        </body>
        </html>
    @endif
@else
    {{-- Guest View (redirect or show limited view) --}}
    @include('animal-management.main-guest')
@endauth
