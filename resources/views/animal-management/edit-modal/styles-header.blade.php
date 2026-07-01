{{-- Edit Animal Modal --}}
<div id="editAnimalModal-{{ $animal->id }}"
     class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-md hidden z-50 flex items-center justify-center p-4"
     style="animation: fadeIn 0.3s ease-out;">

    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl max-h-[92vh] overflow-hidden"
         style="animation: slideUp 0.4s ease-out;">

        {{-- Modal Header --}}
        <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-purple-800 text-white p-7 sticky top-0 z-10 shadow-xl">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="bg-white/20 p-3 rounded-xl backdrop-blur">
                        <span class="text-3xl">🐾</span>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight">Edit Animal Profile</h2>
                        <p class="text-purple-100 text-sm mt-1">Update information for <span class="font-semibold">{{ $animal->name }}</span></p>
                    </div>
                </div>
                <button onclick="closeEditModal({{ $animal->id }})"
                        class="group bg-white/10 hover:bg-white/20 p-2 rounded-xl transition-all duration-300 hover:rotate-90">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <style>
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideUp {
                from { opacity: 0; transform: translateY(20px) scale(0.95); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }
            .image-preview-container {
                position: relative;
                overflow: hidden;
            }
            .image-preview {
                transition: transform 0.3s ease;
            }
            .image-preview:hover {
                transform: scale(1.05);
            }
            .delete-badge {
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
            }
        </style>

        {{-- Modal Body --}}
        <div class="p-8 overflow-y-auto max-h-[calc(92vh-120px)] bg-gradient-to-b from-white to-gray-50">
            <form action="{{ route('animal-management.update', $animal->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="editAnimalForm">
                @csrf
                @method('PUT')
