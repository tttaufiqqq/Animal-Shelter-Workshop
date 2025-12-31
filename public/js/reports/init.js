/**
 * Report Show Page Initialization
 * Initializes all modules for the report detail page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Map Handler
    const mapElement = document.getElementById('map');
    if (mapElement) {
        window.mapHandler = new MapHandler(mapElement);
    }

    // Initialize Image Gallery
    if (typeof window.reportImages !== 'undefined') {
        window.imageGallery = new ImageGallery(window.reportImages, window.mapHandler);
    }

    // Initialize Assignment Handler
    window.assignmentHandler = new AssignmentHandler(window.mapHandler);

    // Initialize Delete Handler
    window.deleteHandler = new DeleteHandler(window.mapHandler);
});
