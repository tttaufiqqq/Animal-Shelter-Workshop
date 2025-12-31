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
                    <!-- Main Image Display -->
                    <div class="relative w-full h-96 bg-gray-100 flex items-center justify-center">
                        <div id="imageSwiperContent" class="w-full h-full flex items-center justify-center">
                            <!-- Images will be loaded here -->
                            <div class="text-center text-gray-400">
                                <i class="fas fa-image text-6xl mb-3 opacity-50"></i>
                                <p>No images available</p>
                            </div>
                        </div>
                        
                        <!-- Navigation Arrows -->
                        <button id="prevImageBtn" class="hidden absolute left-4 top-1/2 -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-70 text-white rounded-full w-12 h-12 flex items-center justify-center z-10">
                            <i class="fas fa-chevron-left text-xl"></i>
                        </button>
                        <button id="nextImageBtn" class="hidden absolute right-4 top-1/2 -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-70 text-white rounded-full w-12 h-12 flex items-center justify-center z-10">
                            <i class="fas fa-chevron-right text-xl"></i>
                        </button>
                        
                        <!-- Image Counter -->
                        <div id="imageCounter" class="hidden absolute bottom-4 right-4 bg-black bg-opacity-70 text-white px-4 py-2 rounded-full text-sm font-semibold">
                            <i class="fas fa-images mr-1"></i>
                            <span id="currentImageIndex">1</span> / <span id="totalImages">1</span>
                        </div>
                    </div>
                    
                    <!-- Thumbnail Navigation -->
                    <div id="thumbnailContainer" class="hidden bg-gray-50 p-4 border-t">
                        <div class="flex gap-2 overflow-x-auto" id="thumbnailStrip">
                            <!-- Thumbnails will be loaded here -->
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
                <div id="detailMedicalsContainer" class="p-4">
                    <!-- Medical records will be loaded here -->
                </div>
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
                <div id="detailVaccinationsContainer" class="p-4">
                    <!-- Vaccination records will be loaded here -->
                </div>
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

<script>
    let currentAnimalId = null;
    let currentImages = [];
    let currentImageIndex = 0;

    function openAnimalDetailModal(animalId) {
        console.log('Opening animal modal for ID:', animalId);
        
        currentAnimalId = animalId;
        document.getElementById('animalDetailModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        document.getElementById('animalDetailLoading').classList.remove('hidden');
        document.getElementById('animalDetailContent').classList.add('hidden');

        const url = `/shelter-management/animals/${animalId}/details`;
        console.log('Fetching from URL:', url);

        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data);
                displayAnimalDetails(data);
            })
            .catch(error => {
                console.error('Error fetching animal details:', error);
                console.error('Error details:', error.message);
                alert(`Failed to load animal details: ${error.message}`);
                closeAnimalDetailModal();
            });
    }

    function displayAnimalDetails(data) {
        document.getElementById('animalDetailLoading').classList.add('hidden');
        document.getElementById('animalDetailContent').classList.remove('hidden');

        // Display images
        displayImages(data.images || []);

        // Populate header
        document.getElementById('animalDetailName').textContent = data.name;
        document.getElementById('animalDetailSubtitle').textContent = `${data.species || 'Unknown'} ${data.breed ? '• ' + data.breed : ''}`;

        // Populate basic info
        document.getElementById('detailAnimalName').textContent = data.name;
        
        // Adoption Status with color
        const adoptionStatusEl = document.getElementById('detailAdoptionStatus');
        const statusColors = {
            'available': 'text-green-600',
            'adopted': 'text-blue-600',
            'pending': 'text-orange-600',
            'not_available': 'text-red-600'
        };
        const statusText = data.adoption_status ? data.adoption_status.replace('_', ' ').charAt(0).toUpperCase() + data.adoption_status.slice(1).replace('_', ' ') : 'Unknown';
        adoptionStatusEl.textContent = statusText;
        adoptionStatusEl.className = `font-bold text-lg ${statusColors[data.adoption_status] || 'text-gray-600'}`;

        // Health Details
        document.getElementById('detailHealthDetails').textContent = data.health_details || 'No health details available.';

        // Display Medical Records and calculate total
        const medicalTotal = displayMedicalRecords(data.medicals || []);
        
        // Display Vaccination Records and calculate total
        const vaccinationTotal = displayVaccinationRecords(data.vaccinations || []);
        
        // Display totals
        document.getElementById('totalMedicalCost').textContent = `RM ${medicalTotal.toFixed(2)}`;
        document.getElementById('totalVaccinationCost').textContent = `RM ${vaccinationTotal.toFixed(2)}`;
        document.getElementById('totalAllCosts').textContent = `RM ${(medicalTotal + vaccinationTotal).toFixed(2)}`;
    }

    function displayMedicalRecords(medicals) {
        const container = document.getElementById('detailMedicalsContainer');
        document.getElementById('detailMedicalCount').textContent = medicals.length;

        let totalCost = 0;

        if (medicals.length === 0) {
            container.innerHTML = `
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-stethoscope text-4xl mb-2 opacity-50"></i>
                    <p>No medical records found</p>
                </div>
            `;
            return totalCost;
        }

        const medicalsHtml = medicals.map((record, index) => {
            const cost = parseFloat(record.cost) || 0;
            totalCost += cost;

            return `
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-4 border border-purple-200 hover:shadow-md mb-3">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="bg-purple-600 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold">
                                ${index + 1}
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">${record.treatment || 'Medical Record'}</h4>
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-calendar text-purple-600 mr-1"></i>
                                    ${record.date ? new Date(record.date).toLocaleDateString() : 'N/A'}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Cost</p>
                            <p class="font-bold text-purple-600 text-lg">RM ${cost.toFixed(2)}</p>
                        </div>
                    </div>
                    ${record.diagnosis ? `
                        <div class="bg-white rounded-lg p-3 mb-2">
                            <p class="text-xs text-gray-600 mb-1">Diagnosis</p>
                            <p class="text-sm text-gray-800">${record.diagnosis}</p>
                        </div>
                    ` : ''}
                    ${record.notes ? `
                        <div class="bg-white rounded-lg p-3 mb-2">
                            <p class="text-xs text-gray-600 mb-1">Notes</p>
                            <p class="text-sm text-gray-800">${record.notes}</p>
                        </div>
                    ` : ''}
                    ${record.vet_name ? `
                        <div class="mt-2 text-sm text-gray-600">
                            <i class="fas fa-user-md text-purple-600 mr-1"></i>
                            Dr. ${record.vet_name}
                        </div>
                    ` : ''}
                </div>
            `;
        }).join('');

        container.innerHTML = medicalsHtml;
        return totalCost;
    }

    function displayVaccinationRecords(vaccinations) {
        const container = document.getElementById('detailVaccinationsContainer');
        document.getElementById('detailVaccinationCount').textContent = vaccinations.length;

        let totalCost = 0;

        if (vaccinations.length === 0) {
            container.innerHTML = `
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-syringe text-4xl mb-2 opacity-50"></i>
                    <p>No vaccination records found</p>
                </div>
            `;
            return totalCost;
        }

        const vaccinationsHtml = vaccinations.map((record, index) => {
            const cost = parseFloat(record.cost) || 0;
            totalCost += cost;

            return `
                <div class="bg-gradient-to-br from-orange-50 to-yellow-50 rounded-lg p-4 border border-orange-200 hover:shadow-md mb-3">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="bg-orange-600 text-white rounded-full w-10 h-10 flex items-center justify-center">
                                <i class="fas fa-syringe"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">${record.vaccine_name || 'Vaccination'}</h4>
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-calendar text-orange-600 mr-1"></i>
                                    ${record.date ? new Date(record.date).toLocaleDateString() : 'N/A'}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Cost</p>
                            <p class="font-bold text-orange-600 text-lg">RM ${cost.toFixed(2)}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        ${record.next_due_date ? `
                            <div class="bg-white rounded-lg p-2">
                                <p class="text-xs text-gray-600">Next Due</p>
                                <p class="font-semibold text-gray-800 text-sm">${new Date(record.next_due_date).toLocaleDateString()}</p>
                            </div>
                        ` : ''}
                        ${record.batch_number ? `
                            <div class="bg-white rounded-lg p-2">
                                <p class="text-xs text-gray-600">Batch Number</p>
                                <p class="font-semibold text-gray-800 text-sm">${record.batch_number}</p>
                            </div>
                        ` : ''}
                        ${record.administered_by ? `
                            <div class="bg-white rounded-lg p-2">
                                <p class="text-xs text-gray-600">Administered By</p>
                                <p class="font-semibold text-gray-800 text-sm">${record.administered_by}</p>
                            </div>
                        ` : ''}
                    </div>
                    ${record.notes ? `
                        <div class="bg-white rounded-lg p-3 mt-2">
                            <p class="text-xs text-gray-600 mb-1">Notes</p>
                            <p class="text-sm text-gray-800">${record.notes}</p>
                        </div>
                    ` : ''}
                </div>
            `;
        }).join('');

        container.innerHTML = vaccinationsHtml;
        return totalCost;
    }

    function closeAnimalDetailModal() {
        document.getElementById('animalDetailModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        currentAnimalId = null;
        currentImages = [];
        currentImageIndex = 0;
    }

    function displayImages(images) {
        currentImages = images;
        currentImageIndex = 0;

        const imageSwiperContent = document.getElementById('imageSwiperContent');
        const prevBtn = document.getElementById('prevImageBtn');
        const nextBtn = document.getElementById('nextImageBtn');
        const imageCounter = document.getElementById('imageCounter');
        const thumbnailContainer = document.getElementById('thumbnailContainer');
        const thumbnailStrip = document.getElementById('thumbnailStrip');

        if (images.length === 0) {
            imageSwiperContent.innerHTML = `
                <div class="text-center text-gray-400">
                    <i class="fas fa-image text-6xl mb-3 opacity-50"></i>
                    <p>No images available</p>
                </div>
            `;
            prevBtn.classList.add('hidden');
            nextBtn.classList.add('hidden');
            imageCounter.classList.add('hidden');
            thumbnailContainer.classList.add('hidden');
            return;
        }

        // Show navigation elements
        if (images.length > 1) {
            prevBtn.classList.remove('hidden');
            nextBtn.classList.remove('hidden');
            thumbnailContainer.classList.remove('hidden');
        } else {
            prevBtn.classList.add('hidden');
            nextBtn.classList.add('hidden');
            thumbnailContainer.classList.add('hidden');
        }
        imageCounter.classList.remove('hidden');

        // Display first image
        displayCurrentImage();

        // Generate thumbnails
        if (images.length > 1) {
            const thumbnailsHtml = images.map((image, index) => `
                <div onclick="goToImage(${index})"
                     class="flex-shrink-0 w-20 h-20 cursor-pointer rounded-lg overflow-hidden border-2 ${index === 0 ? 'border-green-600' : 'border-gray-300 hover:border-green-400'}"
                     id="thumbnail-${index}">
                    <img src="${image.url || image.path}"
                         alt="Thumbnail ${index + 1}"
                         class="w-full h-full object-cover">
                </div>
            `).join('');
            thumbnailStrip.innerHTML = thumbnailsHtml;
        }

        // Update counter
        document.getElementById('totalImages').textContent = images.length;
    }

    function displayCurrentImage() {
        if (currentImages.length === 0) return;

        const imageSwiperContent = document.getElementById('imageSwiperContent');
        const image = currentImages[currentImageIndex];

        imageSwiperContent.innerHTML = `
            <img src="${image.url || image.path}"
                 alt="${image.description || 'Animal image'}"
                 class="max-w-full max-h-full object-contain"
                 onerror="this.src='/images/placeholder.jpg'">
        `;

        // Update counter
        document.getElementById('currentImageIndex').textContent = currentImageIndex + 1;

        // Update thumbnail borders
        currentImages.forEach((_, index) => {
            const thumbnail = document.getElementById(`thumbnail-${index}`);
            if (thumbnail) {
                if (index === currentImageIndex) {
                    thumbnail.className = 'flex-shrink-0 w-20 h-20 cursor-pointer rounded-lg overflow-hidden border-2 border-green-600';
                } else {
                    thumbnail.className = 'flex-shrink-0 w-20 h-20 cursor-pointer rounded-lg overflow-hidden border-2 border-gray-300 hover:border-green-400';
                }
            }
        });
    }

    function goToImage(index) {
        if (index >= 0 && index < currentImages.length) {
            currentImageIndex = index;
            displayCurrentImage();
        }
    }

    function nextImage() {
        currentImageIndex = (currentImageIndex + 1) % currentImages.length;
        displayCurrentImage();
    }

    function prevImage() {
        currentImageIndex = (currentImageIndex - 1 + currentImages.length) % currentImages.length;
        displayCurrentImage();
    }

    // Event listeners for navigation
    document.getElementById('prevImageBtn').addEventListener('click', prevImage);
    document.getElementById('nextImageBtn').addEventListener('click', nextImage);

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        const modalVisible = !document.getElementById('animalDetailModal').classList.contains('hidden');
        if (!modalVisible || currentImages.length === 0) return;

        if (e.key === 'ArrowLeft') {
            prevImage();
        } else if (e.key === 'ArrowRight') {
            nextImage();
        }
    });

    // Close modal when clicking outside
    document.getElementById('animalDetailModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAnimalDetailModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !document.getElementById('animalDetailModal').classList.contains('hidden')) {
            closeAnimalDetailModal();
        }
    });
</script>