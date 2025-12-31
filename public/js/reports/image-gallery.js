/**
 * Image Gallery Module
 * Handles image carousel and modal functionality
 */

class ImageGallery {
    constructor(images, mapHandler) {
        this.images = images || [];
        this.currentIndex = 0;
        this.mapHandler = mapHandler;
        this.init();
    }

    init() {
        this.displayCurrentImage();
        this.attachEventListeners();
    }

    displayCurrentImage() {
        const content = document.getElementById('imageSwiperContent');

        if (this.images.length === 0) return;

        // Update main image
        content.innerHTML = `<img src="${this.images[this.currentIndex].path}"
                                  class="max-w-full max-h-full object-contain cursor-pointer"
                                  onclick="imageGallery.openModal(this.src)">`;

        // Update counter
        const counterElement = document.getElementById('currentImageIndex');
        if (counterElement) {
            counterElement.textContent = this.currentIndex + 1;
        }

        // Update thumbnails
        this.images.forEach((_, index) => {
            const thumb = document.getElementById(`thumbnail-${index}`);
            if (thumb) {
                thumb.className = `flex-shrink-0 w-16 h-16 cursor-pointer rounded overflow-hidden border-2 ${
                    index === this.currentIndex ? 'border-purple-500' : 'border-gray-200 hover:border-purple-400'
                }`;
            }
        });
    }

    goToImage(index) {
        if (index >= 0 && index < this.images.length) {
            this.currentIndex = index;
            this.displayCurrentImage();
        }
    }

    nextImage() {
        this.currentIndex = (this.currentIndex + 1) % this.images.length;
        this.displayCurrentImage();
    }

    prevImage() {
        this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
        this.displayCurrentImage();
    }

    openModal(imageSrc) {
        const modal = document.getElementById('imageModal');
        document.getElementById('modalImage').src = imageSrc;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (this.mapHandler) {
            this.mapHandler.disable();
        }
    }

    closeModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        if (this.mapHandler) {
            this.mapHandler.enable();
        }
    }

    attachEventListeners() {
        // Navigation buttons
        const prevBtn = document.getElementById('prevImageBtn');
        const nextBtn = document.getElementById('nextImageBtn');

        if (prevBtn) {
            prevBtn.addEventListener('click', () => this.prevImage());
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => this.nextImage());
        }

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (!document.getElementById('imageModal').classList.contains('hidden')) {
                if (e.key === 'Escape') {
                    this.closeModal();
                }
                return;
            }

            if (this.images.length > 1) {
                if (e.key === 'ArrowLeft') this.prevImage();
                if (e.key === 'ArrowRight') this.nextImage();
            }
        });
    }
}

// Global functions for onclick attributes
function openImageModal(src) {
    if (window.imageGallery) {
        window.imageGallery.openModal(src);
    }
}

function closeImageModal() {
    if (window.imageGallery) {
        window.imageGallery.closeModal();
    }
}

function goToImage(index) {
    if (window.imageGallery) {
        window.imageGallery.goToImage(index);
    }
}

// Export for use in global scope
window.ImageGallery = ImageGallery;
