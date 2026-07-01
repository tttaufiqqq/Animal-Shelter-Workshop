<script>
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

        displayCurrentImage();

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

        document.getElementById('currentImageIndex').textContent = currentImageIndex + 1;

        currentImages.forEach((_, index) => {
            const thumbnail = document.getElementById(`thumbnail-${index}`);
            if (thumbnail) {
                thumbnail.className = index === currentImageIndex
                    ? 'flex-shrink-0 w-20 h-20 cursor-pointer rounded-lg overflow-hidden border-2 border-green-600'
                    : 'flex-shrink-0 w-20 h-20 cursor-pointer rounded-lg overflow-hidden border-2 border-gray-300 hover:border-green-400';
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

    document.getElementById('prevImageBtn').addEventListener('click', prevImage);
    document.getElementById('nextImageBtn').addEventListener('click', nextImage);

    document.addEventListener('keydown', function(e) {
        const modalVisible = !document.getElementById('animalDetailModal').classList.contains('hidden');
        if (!modalVisible || currentImages.length === 0) return;

        if (e.key === 'ArrowLeft') {
            prevImage();
        } else if (e.key === 'ArrowRight') {
            nextImage();
        }
    });

    document.getElementById('animalDetailModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAnimalDetailModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !document.getElementById('animalDetailModal').classList.contains('hidden')) {
            closeAnimalDetailModal();
        }
    });
</script>
