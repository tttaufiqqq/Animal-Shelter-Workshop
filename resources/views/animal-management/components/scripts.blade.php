<script>
    function changeImage(imagePath) {
        document.getElementById('mainImage').src = imagePath;
    }
    function openMedicalModal() {
        const modal = document.getElementById('medicalModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeMedicalModal() {
        const modal = document.getElementById('medicalModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openVaccinationModal() {
        const modal = document.getElementById('vaccinationModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeVaccinationModal() {
        const modal = document.getElementById('vaccinationModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Close modals when clicking outside
    document.getElementById('medicalModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeMedicalModal();
        }
    });

    document.getElementById('vaccinationModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeVaccinationModal();
        }
    });

    let currentImages = [
            @foreach($animalImages as $image)
        { path: "{{ $image->url }}" },
        @endforeach
    ];
    let currentImageIndex = 0;

    function displayCurrentImage() {
        const content = document.getElementById('imageSwiperContent');
        const image = currentImages[currentImageIndex];

        content.innerHTML = `<img src="${image.path}" class="max-w-full max-h-full object-contain">`;
        document.getElementById('currentImageIndex').textContent = currentImageIndex + 1;

        // Update thumbnails
        currentImages.forEach((_, index) => {
            const thumb = document.getElementById(`thumbnail-${index}`);
            if (thumb) {
                thumb.className = `flex-shrink-0 w-20 h-20 cursor-pointer rounded-lg overflow-hidden border-2 transition duration-300 ${
                    index === currentImageIndex ? 'border-green-600' : 'border-gray-300 hover:border-green-400'
                }`;
            }
        });
    }

    function nextImage() {
        currentImageIndex = (currentImageIndex + 1) % currentImages.length;
        displayCurrentImage();
    }

    function prevImage() {
        currentImageIndex = (currentImageIndex - 1 + currentImages.length) % currentImages.length;
        displayCurrentImage();
    }

    function goToImage(index) {
        currentImageIndex = index;
        displayCurrentImage();
    }

    // Init
    if(currentImages.length > 0) {
        displayCurrentImage();
        if(currentImages.length > 1) {
            document.getElementById('prevImageBtn').classList.remove('hidden');
            document.getElementById('nextImageBtn').classList.remove('hidden');
            document.getElementById('imageCounter').classList.remove('hidden');
        }
    }

    document.getElementById('prevImageBtn').addEventListener('click', prevImage);
    document.getElementById('nextImageBtn').addEventListener('click', nextImage);

    // Optional keyboard navigation
    document.addEventListener('keydown', function(e){
        if(currentImages.length < 2) return;
        if(e.key === 'ArrowLeft') prevImage();
        if(e.key === 'ArrowRight') nextImage();
    });

    // Fullscreen functionality
    function toggleFullscreen() {
        const mainImage = document.getElementById('mainDisplayImage');
        if (!mainImage) return;

        if (!document.fullscreenElement) {
            if (mainImage.requestFullscreen) {
                mainImage.requestFullscreen();
            } else if (mainImage.webkitRequestFullscreen) {
                mainImage.webkitRequestFullscreen();
            } else if (mainImage.msRequestFullscreen) {
                mainImage.msRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    }

    // Add image transition effect
    function displayCurrentImage() {
        const content = document.getElementById('imageSwiperContent');
        const image = currentImages[currentImageIndex];
        const mainImage = content.querySelector('img');

        if (mainImage) {
            mainImage.style.opacity = '0';
            setTimeout(() => {
                mainImage.src = image.path;
                mainImage.style.opacity = '1';
            }, 200);
        } else {
            content.innerHTML = `<img src="${image.path}" class="max-w-full max-h-full object-contain transition-opacity duration-500" id="mainDisplayImage">`;
        }

        document.getElementById('currentImageIndex').textContent = currentImageIndex + 1;

        // Update thumbnails
        currentImages.forEach((_, index) => {
            const thumb = document.getElementById(`thumbnail-${index}`);
            if (thumb) {
                thumb.className = `group flex-shrink-0 w-24 h-24 cursor-pointer rounded-xl overflow-hidden border-3 transition-all duration-300 ${
                    index === currentImageIndex ? 'border-purple-600 ring-2 ring-purple-300 shadow-lg' : 'border-gray-300 hover:border-purple-400 hover:shadow-md'
                }`;
            }
        });
    }
</script>
