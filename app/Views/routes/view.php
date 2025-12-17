<?= view('driver/templates/header', ['page_title' => $title]) ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900"><?= $title ?></h1>
        <a href="/routes" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg flex items-center">
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
                        <p class="text-sm text-gray-900"><?= esc($route['origin_address']) ?></p>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">To</h3>
                    <div class="flex items-start">
                        <div class="w-3 h-3 bg-red-500 rounded-full mt-1 mr-3"></div>
                        <p class="text-sm text-gray-900"><?= esc($route['destination_address']) ?></p>
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
                
                <?php if (!empty($route['route_data'])): ?>
                    <?php $routeData = json_decode($route['route_data'], true); ?>
                    <?php if ($routeData && isset($routeData['properties'])): ?>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Route Statistics</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>Distance:</span>
                                    <span class="font-medium"><?= number_format($routeData['properties']['distance'] / 1000, 1) ?> km</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Duration:</span>
                                    <span class="font-medium"><?= gmdate('H:i', $routeData['properties']['time']) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
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

        <!-- Merchants -->
        <div class="lg:col-span-1 bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Merchants Along Route</h2>
            
            <div id="merchantsList" class="space-y-4">
                <?php if (empty($route['merchants'])): ?>
                    <p class="text-gray-500 text-center py-8">No merchants found along this route</p>
                <?php else: ?>
                    <?php foreach ($route['merchants'] as $merchant): ?>
                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900"><?= esc($merchant['business_name'] ?: $merchant['owner_name']) ?></h4>
                                    <p class="text-sm text-gray-600"><?= esc($merchant['address'] ?: 'Address not available') ?></p>
                                    <p class="text-xs text-blue-600 mt-1"><?= esc($merchant['distance_from_route']) ?> km from route</p>
                                </div>
                                <?php if ($merchant['profile_image']): ?>
                                    <img src="/<?= esc($merchant['profile_image']) ?>" alt="Profile" class="w-12 h-12 rounded-full object-cover">
                                <?php endif; ?>
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <?= esc($merchant['phone'] ?: 'No phone') ?>
                            </div>
                            <div class="mt-2">
                                <a href="/merchant/profile/<?= $merchant['merchant_id'] ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View Profile →
                                </a>
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
    // Initialize map
    map = L.map('map').setView([routeData.origin_lat, routeData.origin_lng], 8);
    
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
    // Add start marker
    L.marker([routeData.origin_lat, routeData.origin_lng])
        .bindPopup(`<b>Start:</b> ${routeData.origin_address}`)
        .addTo(markersLayer);
    
    // Add end marker
    L.marker([routeData.destination_lat, routeData.destination_lng])
        .bindPopup(`<b>End:</b> ${routeData.destination_address}`)
        .addTo(markersLayer);
    
    // If we have route geometry data, display the route line
    if (routeData.route_data) {
        try {
            const routeGeometry = JSON.parse(routeData.route_data);
            if (routeGeometry.geometry && routeGeometry.geometry.coordinates) {
                const coordinates = routeGeometry.geometry.coordinates.map(coord => [coord[1], coord[0]]);
                const polyline = L.polyline(coordinates, {color: 'blue', weight: 4}).addTo(routeLayer);
                
                // Fit map to route
                map.fitBounds(polyline.getBounds(), {padding: [20, 20]});
            } else {
                // Fallback: fit to start and end points
                const bounds = L.latLngBounds([
                    [routeData.origin_lat, routeData.origin_lng],
                    [routeData.destination_lat, routeData.destination_lng]
                ]);
                map.fitBounds(bounds, {padding: [50, 50]});
            }
        } catch (error) {
            console.error('Error parsing route data:', error);
            // Fallback: fit to start and end points
            const bounds = L.latLngBounds([
                [routeData.origin_lat, routeData.origin_lng],
                [routeData.destination_lat, routeData.destination_lng]
            ]);
            map.fitBounds(bounds, {padding: [50, 50]});
        }
    } else {
        // Fallback: fit to start and end points
        const bounds = L.latLngBounds([
            [routeData.origin_lat, routeData.origin_lng],
            [routeData.destination_lat, routeData.destination_lng]
        ]);
        map.fitBounds(bounds, {padding: [50, 50]});
    }
    
    // Add merchant markers
    if (routeData.merchants && routeData.merchants.length > 0) {
        routeData.merchants.forEach(merchant => {
            if (merchant.latitude && merchant.longitude) {
                L.marker([merchant.latitude, merchant.longitude])
                    .bindPopup(`
                        <b>${merchant.business_name || merchant.owner_name}</b><br>
                        ${merchant.address || ''}<br>
                        <small>${merchant.distance_from_route} km from route</small><br>
                        <a href="/merchant/profile/${merchant.merchant_id}" class="text-blue-600">View Profile</a>
                    `)
                    .addTo(markersLayer);
            }
        });
    }
}

function startNavigation() {
    // Open in Google Maps for navigation
    const googleMapsUrl = `https://www.google.com/maps/dir/${routeData.origin_lat},${routeData.origin_lng}/${routeData.destination_lat},${routeData.destination_lng}`;
    window.open(googleMapsUrl, '_blank');
}

function deleteRoute(routeId) {
    if (confirm('Are you sure you want to delete this route?')) {
        fetch(`/routes/delete/${routeId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Route deleted successfully');
                window.location.href = '/routes';
            } else {
                alert('Error deleting route: ' + data.error);
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
