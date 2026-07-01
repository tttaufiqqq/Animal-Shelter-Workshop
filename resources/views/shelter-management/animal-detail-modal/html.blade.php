<!-- Animal Detail Modal -->
<div id="animalDetailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-md flex items-center justify-center z-[70] p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[70vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold flex items-center">
                        <i class="fas fa-paw mr-2"></i>
                        <span id="animalDetailName">Animal Details</span>
                    </h2>
                    <p class="text-green-100 mt-1" id="animalDetailSubtitle"></p>
                </div>
                <button onclick="closeAnimalDetailModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div id="animalDetailLoading" class="p-12 text-center">
            <i class="fas fa-spinner fa-spin text-4xl text-green-600 mb-4"></i>
            <p class="text-gray-600">Loading animal details...</p>
        </div>

        <!-- Detail Content -->
        <div id="animalDetailContent" class="hidden p-6 space-y-6">
            <!-- Image Swiper -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="relative" id="imageSwiper">
                    <div class="relative w-full h-96 bg-gray-100 flex items-center justify-center">
                        <div id="imageSwiperContent" class="w-full h-full flex items-center justify-center">
                            <div class="text-center text-gray-400">
                                <i class="fas fa-image text-6xl mb-3 opacity-50"></i>
                                <p>No images available</p>
                            </div>
                        </div>

                        <button id="prevImageBtn" class="hidden absolute left-4 top-1/2 -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-70 text-white rounded-full w-12 h-12 flex items-center justify-center z-10">
                            <i class="fas fa-chevron-left text-xl"></i>
                        </button>
                        <button id="nextImageBtn" class="hidden absolute right-4 top-1/2 -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-70 text-white rounded-full w-12 h-12 flex items-center justify-center z-10">
                            <i class="fas fa-chevron-right text-xl"></i>
                        </button>

                        <div id="imageCounter" class="hidden absolute bottom-4 right-4 bg-black bg-opacity-70 text-white px-4 py-2 rounded-full text-sm font-semibold">
                            <i class="fas fa-images mr-1"></i>
                            <span id="currentImageIndex">1</span> / <span id="totalImages">1</span>
                        </div>
                    </div>

                    <div id="thumbnailContainer" class="hidden bg-gray-50 p-4 border-t">
                        <div class="flex gap-2 overflow-x-auto" id="thumbnailStrip">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Basic Information -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-200">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-green-600 mr-2"></i>
                    Basic Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white rounded-lg p-4">
                        <p class="text-gray-600 text-sm mb-1">Animal Name</p>
                        <p class="font-bold text-gray-800 text-lg" id="detailAnimalName"></p>
                    </div>
                    <div class="bg-white rounded-lg p-4">
                        <p class="text-gray-600 text-sm mb-1">Adoption Status</p>
                        <p class="font-bold text-lg" id="detailAdoptionStatus"></p>
                    </div>
                </div>
            </div>

            <!-- Health Details -->
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="bg-gradient-to-r from-blue-50 to-cyan-50 p-4 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-heartbeat text-blue-600 mr-2"></i>
                        Health Details
                    </h3>
                </div>
                <div class="p-6">
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <p class="text-gray-700 whitespace-pre-wrap" id="detailHealthDetails"></p>
                    </div>
                </div>
            </div>

            <!-- Medical Records -->
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-stethoscope text-purple-600 mr-2"></i>
                            Medical Records
                            <span id="detailMedicalCount" class="ml-2 bg-purple-600 text-white px-3 py-1 rounded-full text-sm">0</span>
                        </h3>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Total Medical Cost</p>
                            <p class="text-2xl font-bold text-purple-600" id="totalMedicalCost">RM 0.00</p>
                        </div>
                    </div>
                </div>
                <div id="detailMedicalsContainer" class="p-4"></div>
            </div>

            <!-- Vaccination Records -->
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="bg-gradient-to-r from-orange-50 to-yellow-50 p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-syringe text-orange-600 mr-2"></i>
                            Vaccination Records
                            <span id="detailVaccinationCount" class="ml-2 bg-orange-600 text-white px-3 py-1 rounded-full text-sm">0</span>
                        </h3>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Total Vaccination Cost</p>
                            <p class="text-2xl font-bold text-orange-600" id="totalVaccinationCost">RM 0.00</p>
                        </div>
                    </div>
                </div>
                <div id="detailVaccinationsContainer" class="p-4"></div>
            </div>

            <!-- Total Cost Summary -->
            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-6 border-2 border-indigo-300">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="bg-indigo-600 text-white rounded-full w-12 h-12 flex items-center justify-center mr-4">
                            <i class="fas fa-calculator text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Total Healthcare Expenses</p>
                            <p class="text-xs text-gray-500 mt-1">Medical + Vaccination costs</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-bold text-indigo-600" id="totalAllCosts">RM 0.00</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 p-6 border-t flex justify-end gap-3">
            <button onclick="closeAnimalDetailModal()" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300">
                Close
            </button>
        </div>
    </div>
</div>
