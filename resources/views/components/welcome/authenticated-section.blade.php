@props(['adopterProfile' => null, 'matches' => null])

<!-- Authenticated User Section -->
<div class="text-center mb-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">
        Welcome Back, {{ Auth::user()->name }}!
    </h2>

    <x-welcome.user-info-card>
        {{-- Include adopter modals with required variables --}}
        @include('adopter-animal-matching.adopter-modal', ['adopterProfile' => $adopterProfile])
        @include('adopter-animal-matching.result', ['matches' => $matches])

        {{-- Public User Actions (excluding caretakers) --}}
        @hasanyrole('public user|adopter')
            @unlessrole('caretaker')
                <x-welcome.actions.public-user />
            @endunlessrole
        @endhasanyrole

        {{-- Adopter Actions for Adopters and Public Users (purple) --}}
        @hasanyrole('adopter|public user')
            @unlessrole('caretaker')
                <x-welcome.actions.adopter />
            @endunlessrole
        @endhasanyrole

        {{-- Caretaker Actions --}}
        @role('caretaker')
            <x-welcome.actions.caretaker />
            {{-- Adopter Actions for Caretakers (teal version) --}}
            <x-welcome.actions.adopter-caretaker />
        @endrole

        {{-- Admin Actions --}}
        @role('admin')
            <x-welcome.actions.admin />
        @endrole
    </x-welcome.user-info-card>
</div>
