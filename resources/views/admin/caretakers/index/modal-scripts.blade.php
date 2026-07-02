    <!-- Include the Caretaker Modal -->
    <x-modals.add-caretaker />

    <!-- Scripts -->
    @push('scripts')
    <script>
        function openCaretakerModal() {
            const modal = document.getElementById('caretakerModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeCaretakerModal() {
            const modal = document.getElementById('caretakerModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Auto-open modal if there are validation errors
        @if($errors->caretaker->any())
            document.addEventListener('DOMContentLoaded', function() {
                openCaretakerModal();
            });
        @endif

        // Auto-close modal on success
        @if(session('caretaker_success'))
            document.addEventListener('DOMContentLoaded', function() {
                closeCaretakerModal();
            });
        @endif
    </script>
    @endpush
