<!-- IMPROVED VISIT LIST MODAL -->
<div id="visitModal"
     class="fixed inset-0 hidden bg-black/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 transition-opacity duration-300">

    <div id="visitModalContent"
         class="bg-white max-w-4xl w-full rounded-3xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col
                opacity-0 scale-95 transform transition-all duration-300">

        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 p-6 text-white relative overflow-hidden">
            <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>
            <div class="relative flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold flex items-center gap-2">
                        <i class="fas fa-heart"></i>
                        Your Visit List
                    </h1>
                    <p class="text-purple-100 text-sm mt-1">Schedule a visit with your favorite animals</p>
                </div>
                <button onclick="closeVisitModal()"
                        class="text-white/80 hover:text-white text-3xl w-10 h-10 flex items-center justify-center rounded-full hover:bg-white/20 transition-all duration-200">
                    &times;
                </button>
            </div>
        </div>
