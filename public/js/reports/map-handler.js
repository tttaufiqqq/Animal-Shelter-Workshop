/**
 * Map Handler Module
 * Handles Leaflet map initialization and interactions
 */

class MapHandler {
    constructor(mapElement) {
        this.mapElement = mapElement;
        this.map = null;
        this.marker = null;
        this.circle = null;
        this.init();
    }

    init() {
        const lat = parseFloat(this.mapElement.dataset.latitude);
        const lng = parseFloat(this.mapElement.dataset.longitude);
        const address = this.mapElement.dataset.address;
        const city = this.mapElement.dataset.city;
        const state = this.mapElement.dataset.state;

        // Initialize the map
        this.map = L.map('map').setView([lat, lng], 15);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(this.map);

        // Add a marker for the report location
        this.marker = L.marker([lat, lng]).addTo(this.map);

        // Add popup with location info
        this.marker.bindPopup(`
            <div class="p-2">
                <strong class="text-sm">Report Location</strong><br>
                <span class="text-xs">${address}</span><br>
                <span class="text-xs">${city}, ${state}</span>
            </div>
        `).openPopup();

        // Add a circle to highlight the area
        this.circle = L.circle([lat, lng], {
            color: '#9333ea',
            fillColor: '#9333ea',
            fillOpacity: 0.15,
            radius: 100
        }).addTo(this.map);
    }

    disable() {
        // Close any open popups
        this.map.closePopup();

        // Disable all interactions
        this.map.dragging.disable();
        this.map.touchZoom.disable();
        this.map.doubleClickZoom.disable();
        this.map.scrollWheelZoom.disable();
        this.map.boxZoom.disable();
        this.map.keyboard.disable();
        if (this.map.tap) this.map.tap.disable();

        // Make map completely non-interactive
        this.mapElement.style.pointerEvents = 'none';
        this.mapElement.style.zIndex = '1';

        // Disable marker interactions
        this.marker.closePopup();
        this.marker.off('click');
    }

    enable() {
        // Re-enable all interactions
        this.map.dragging.enable();
        this.map.touchZoom.enable();
        this.map.doubleClickZoom.enable();
        this.map.scrollWheelZoom.enable();
        this.map.boxZoom.enable();
        this.map.keyboard.enable();
        if (this.map.tap) this.map.tap.enable();

        // Restore interactivity
        this.mapElement.style.pointerEvents = 'auto';
        this.mapElement.style.zIndex = '';

        // Re-enable marker click
        this.marker.on('click', () => {
            this.marker.openPopup();
        });
    }
}

// Export for use in global scope
window.MapHandler = MapHandler;
