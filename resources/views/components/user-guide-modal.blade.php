{{-- user-guide-modal Orchestrator --}}
@php
    // Determine which sections to show based on user role
    $showAdmin = false;
    $showCaretaker = false;
    $showPublicUser = false;
    $showAdopter = false;
    $showAdoptionProcess = false;

    if (Auth::check()) {
        $userRoles = Auth::user()->getRoleNames();
        $showAdmin = $userRoles->contains('admin');
        $showCaretaker = $userRoles->contains('caretaker');
        $showAdopter = $userRoles->contains('adopter');
        $showPublicUser = $userRoles->contains('public user') || $userRoles->contains('user');
        $showAdoptionProcess = $showCaretaker || $showAdopter || $showPublicUser;
    } else {
        $showPublicUser = true;
        $showAdoptionProcess = true;
    }

    $visibleSections = collect([
        $showAdmin, $showCaretaker, $showPublicUser, $showAdopter,
    ])->filter()->count();
@endphp
@include('components.user-guide-modal.modal-header')
@include('components.user-guide-modal.content-nav', compact('showAdmin', 'showCaretaker', 'showPublicUser', 'showAdopter', 'showAdoptionProcess', 'visibleSections'))
@include('components.user-guide-modal.section-admin-caretaker', compact('showAdmin', 'showCaretaker'))
@include('components.user-guide-modal.section-user-adopter', compact('showPublicUser', 'showAdopter', 'showAdoptionProcess'))
@include('components.user-guide-modal.section-adoption-footer', compact('showAdoptionProcess'))
@include('components.user-guide-modal.scripts')
