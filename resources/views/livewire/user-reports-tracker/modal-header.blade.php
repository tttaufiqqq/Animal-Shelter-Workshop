        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-6 flex-shrink-0 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📋</span>
                    <div>
                        <h2 class="text-2xl font-bold">My Reports</h2>
                        <p class="text-purple-100 text-sm">View all your submitted reports (Live status updates)</p>
                    </div>
                </div>
                <button @click="open = false; document.body.style.overflow = 'auto'" class="text-white hover:text-gray-200 transition">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>
