<script>
// ==================== GLOBAL VARIABLE INITIALIZATION ====================
// Variables that use Blade template values must be initialized here

// Rescue ID for this page
const rescueId = {{ $rescue->id }};

// Initialize the Leaflet maps (both mobile and desktop)
function initializeMap(mapId) {
    const mapElement = document.getElementById('map-' + mapId);
    if (!mapElement) return null;

    const map = L.map('map-' + mapId).setView([{{ $rescue->report->latitude }}, {{ $rescue->report->longitude }}], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    const marker = L.marker([{{ $rescue->report->latitude }}, {{ $rescue->report->longitude }}]).addTo(map);
    marker.bindPopup(`
        <div class="p-2">
            <strong class="text-sm">Rescue Location</strong><br>
            <span class="text-xs">{{ $rescue->report->address }}</span><br>
            <span class="text-xs">{{ $rescue->report->city }}, {{ $rescue->report->state }}</span>
        </div>
    `).openPopup();

    const circle = L.circle([{{ $rescue->report->latitude }}, {{ $rescue->report->longitude }}], {
        color: '#9333ea',
        fillColor: '#9333ea',
        fillOpacity: 0.15,
        radius: 100
    }).addTo(map);

    return map;
}

// Initialize both maps after DOM is loaded
let mobileMap, desktopMap;
document.addEventListener('DOMContentLoaded', function() {
    mobileMap = initializeMap('mobile');
    desktopMap = initializeMap('desktop');
});

// Initialize rescue images array from Blade template
let rescueImages = [
    @if($rescue->report->images && $rescue->report->images->count() > 0)
        @foreach($rescue->report->images as $image)
            { path: "{{ $image->url }}" },
        @endforeach
    @endif
];
let rescueImageIndex = 0;

// Initialize status variables
let selectedStatus = '';

// Initialize animal form variables
let currentStep = 1;
let totalAnimals = 0;
let currentAnimalIndex = 0;
let addedAnimals = [];
let animalImagesMap = {}; // Store images for each animal

// ==================== EVENT LISTENERS ====================

// Initialize image swiper on page load
rescueDisplayImage();

// Setup image navigation buttons (if multiple images exist)
@if($rescue->report->images && $rescue->report->images->count() > 1)
    document.getElementById('rescuePrevImageBtn').addEventListener('click', rescuePrevImage);
    document.getElementById('rescueNextImageBtn').addEventListener('click', rescueNextImage);
@endif

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
        closeRemarksModal();
    }
    @if($rescue->report->images && $rescue->report->images->count() > 1)
        if (e.key === 'ArrowLeft') rescuePrevImage();
        if (e.key === 'ArrowRight') rescueNextImage();
    @endif
});

// Auto-scroll to alerts and auto-dismiss success alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.fade-in-up');
    if (alerts.length > 0) {
        alerts[0].scrollIntoView({ behavior: 'smooth', block: 'start' });

        const successAlert = document.querySelector('.bg-green-50');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.opacity = '0';
                successAlert.style.transform = 'translateY(-20px)';
                successAlert.style.transition = 'all 0.5s ease-out';
                setTimeout(() => successAlert.remove(), 500);
            }, 5000);
        }
    }
});
</script>
