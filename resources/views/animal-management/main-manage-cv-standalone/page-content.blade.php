<div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white py-16">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-5xl font-bold mb-4">Clinics & Veterinarians</h1>
                <p class="text-xl text-purple-100">Manage medical partners and veterinary professionals</p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="px-4 sm:px-6 lg:px-8 py-8">
    @include('animal-management.partials.cv-content', ['clinics' => $clinics, 'vets' => $vets])
</div>

{{-- Modals at body level to cover entire viewport --}}
@include('animal-management.partials.cv-modals', ['clinics' => $clinics])

@include('animal-management.partials.cv-scripts')
</body>
</html>
