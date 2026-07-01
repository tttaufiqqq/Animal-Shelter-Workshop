{{-- user-reports-tracker Orchestrator --}}
<div wire:poll.15s="checkForStatusChanges"
     id="myReportsModal"
     x-data="{ open: false }"
     x-show="open"
     x-cloak
     @open-my-reports-modal.window="open = true; document.body.style.overflow = 'hidden'"
     @close-my-reports-modal.window="open = false; document.body.style.overflow = 'auto'"
     @click.self="open = false; document.body.style.overflow = 'auto'"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
     style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-[1400px] max-w-full max-h-[90vh] flex flex-col">
        @include('livewire.user-reports-tracker.modal-header')
        @include('livewire.user-reports-tracker.status-notification')
        @include('livewire.user-reports-tracker.loading-body')
    </div>

    @include('livewire.user-reports-tracker.scripts')
</div>
