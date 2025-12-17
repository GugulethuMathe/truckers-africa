<?= view('driver/templates/header', ['page_title' => $title]) ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900"><?= $title ?></h1>
        <a href="<?= base_url('driver/routes') ?>" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Routes
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Route Details -->
        <div class="lg:col-span-1 bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Route Details</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">From</h3>
                    <div class="flex items-start">
                        <div class="w-3 h-3 bg-green-500 rounded-full mt-1 mr-3"></div>
                        <p class="text-sm text-gray-900"><?= esc($route['start_address'] ?? 'Not specified') ?></p>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">To</h3>
                    <div class="flex items-start">
                        <div class="w-3 h-3 bg-red-500 rounded-full mt-1 mr-3"></div>
                        <p class="text-sm text-gray-900"><?= esc($route['end_address'] ?? 'Not specified') ?></p>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Created: <?= date('M j, Y g:i A', strtotime($route['created_at'])) ?>
                    </div>
                </div>

                <?php if (!empty($route['total_distance_km']) || !empty($route['estimated_duration_minutes'])): ?>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 mb-2">Route Statistics</h4>
                        <div class="space-y-2 text-sm">
                            <?php if (!empty($route['total_distance_km'])): ?>
                                <div class="flex justify-between">
                                    <span>Distance:</span>
                                    <span class="font-medium"><?= number_format($route['total_distance_km'], 1) ?> km</span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($route['estimated_duration_minutes'])): ?>
                                <div class="flex justify-between">
                                    <span>Duration:</span>
                                    <span class="font-medium">
                                        <?= floor($route['estimated_duration_minutes'] / 60) ?>h <?= $route['estimated_duration_minutes'] % 60 ?>m
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="pt-4">
                    <button onclick="startNavigation()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg mb-2">
                        Start Navigation
                    </button>
                    <button onclick="deleteRoute(<?= $route['id'] ?>)" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg">
                        Delete Route
                    </button>
                </div>
            </div>
        </div>

        <!-- Map -->
        <div class="lg:col-span-2">
            <div id="map" class="h-96 lg:h-full rounded-lg shadow-lg"></div>
        </div>

        <!-- Merchants or Route Stops -->
        <div class="lg:col-span-1 bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Route Stops</h2>

            <div id="stopsList" class="space-y-4">
                <?php if (empty($route['stops'])): ?>
                    <p class="text-gray-500 text-center py-8">No stops along this route</p>
                <?php else: ?>
                    <?php foreach ($route['stops'] as $index => $stop): ?>
                        <div class="border rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-semibold mr-3">
                                    <?= $index + 1 ?>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900"><?= esc($stop['stop_name'] ?? 'Stop ' . ($index + 1)) ?></h4>
                                    <p class="text-sm text-gray-600"><?= esc($stop['address'] ?? 'Address not available') ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
let map;
let routeLayer;
let markersLayer;

// Route data from PHP
const routeData = <?= json_encode($route) ?>;

// Initialize map
document.addEventListener('DOMContentLoaded', function() {
    // Default center (use start if available, otherwise a default location)
    const defaultLat = routeData.start_lat || -28.4793;
    const defaultLng = routeData.start_lng || 24.6727;

    // Initialize map
    map = L.map('map').setView([defaultLat, defaultLng], 8);

    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Initialize layers
    routeLayer = L.layerGroup().addTo(map);
    markersLayer = L.layerGroup().addTo(map);

    // Display the route
    displayRoute();
});

function displayRoute() {
    const bounds = [];

    // Add start marker if available
    if (routeData.start_lat && routeData.start_lng) {
        const startMarker = L.marker([routeData.start_lat, routeData.start_lng], {
            icon: L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            })
        })
            .bindPopup(`<b>Start:</b> ${routeData.start_address || 'Not specified'}`)
            .addTo(markersLayer);
        bounds.push([routeData.start_lat, routeData.start_lng]);
    }

    // Add end marker if available
    if (routeData.end_lat && routeData.end_lng) {
        const endMarker = L.marker([routeData.end_lat, routeData.end_lng], {
            icon: L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            })
        })
            .bindPopup(`<b>End:</b> ${routeData.end_address || 'Not specified'}`)
            .addTo(markersLayer);
        bounds.push([routeData.end_lat, routeData.end_lng]);
    }

    // Add stop markers
    if (routeData.stops && routeData.stops.length > 0) {
        routeData.stops.forEach((stop, index) => {
            if (stop.latitude && stop.longitude) {
                L.marker([stop.latitude, stop.longitude])
                    .bindPopup(`<b>Stop ${index + 1}:</b> ${stop.stop_name || stop.address || 'Stop'}`)
                    .addTo(markersLayer);
                bounds.push([stop.latitude, stop.longitude]);
            }
        });
    }

    // If we have route polyline, display it
    if (routeData.route_polyline) {
        try {
            // Decode polyline or handle as JSON
            const coordinates = JSON.parse(routeData.route_polyline);
            if (Array.isArray(coordinates)) {
                const polyline = L.polyline(coordinates, {color: 'blue', weight: 4}).addTo(routeLayer);
                map.fitBounds(polyline.getBounds(), {padding: [20, 20]});
                return;
            }
        } catch (error) {
            console.log('Route polyline not available or invalid format');
        }
    }

    // Fallback: fit to markers
    if (bounds.length > 0) {
        const latLngBounds = L.latLngBounds(bounds);
        map.fitBounds(latLngBounds, {padding: [50, 50]});
    }
}

function startNavigation() {
    if (!routeData.start_lat || !routeData.start_lng || !routeData.end_lat || !routeData.end_lng) {
        alert('Route coordinates not available for navigation');
        return;
    }

    // Open in Google Maps for navigation
    const googleMapsUrl = `https://www.google.com/maps/dir/${routeData.start_lat},${routeData.start_lng}/${routeData.end_lat},${routeData.end_lng}`;
    window.open(googleMapsUrl, '_blank');
}

function deleteRoute(routeId) {
    if (confirm('Are you sure you want to delete this route?')) {
        fetch(`<?= base_url('driver/routes/delete/') ?>${routeId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Route deleted successfully');
                window.location.href = '<?= base_url('driver/routes') ?>';
            } else {
                alert('Error deleting route: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the route.');
        });
    }
}
</script>

<?= view('driver/templates/footer') ?>
