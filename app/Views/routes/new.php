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
        <!-- Route Planning Form -->
        <div class="lg:col-span-1 bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Plan Your Route</h2>
            
            <form id="routePlanningForm" class="space-y-4">
                <div>
                    <label for="origin" class="block text-sm font-medium text-gray-700 mb-2">From</label>
                    <input type="text" id="origin" name="origin" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="e.g., Johannesburg, South Africa">
                </div>
                
                <div>
                    <label for="destination" class="block text-sm font-medium text-gray-700 mb-2">To</label>
                    <input type="text" id="destination" name="destination" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="e.g., Harare, Zimbabwe">
                </div>
                
                <button type="submit" id="planRouteBtn" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                    <span id="planRouteText">Plan Route</span>
                    <span id="planRouteSpinner" class="hidden">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Planning...
                    </span>
                </button>
            </form>

            <!-- Route Info -->
            <div id="routeInfo" class="mt-6 hidden">
                <h3 class="text-lg font-semibold mb-3">Route Details</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>Distance:</span>
                        <span id="routeDistance" class="font-medium"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Duration:</span>
                        <span id="routeDuration" class="font-medium"></span>
                    </div>
                </div>
                
                <button id="saveRouteBtn" 
                        class="w-full mt-4 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">
                    Save Route
                </button>
            </div>
        </div>

        <!-- Map -->
        <div class="lg:col-span-2">
            <div id="map" class="h-96 lg:h-full rounded-lg shadow-lg"></div>
        </div>

        <!-- Merchants Panel -->
        <div class="lg:col-span-1 bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Merchants Along Route</h2>
            
            <div id="merchantsLoading" class="hidden text-center py-8">
                <svg class="animate-spin mx-auto h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-2 text-gray-600">Finding merchants...</p>
            </div>
            
            <div id="merchantsList" class="space-y-4">
                <p class="text-gray-500 text-center py-8">Plan a route to see merchants along your path</p>
            </div>
        </div>
    </div>
</div>

<script>
let map;
let routeLayer;
let markersLayer;
let currentRoute = null;
let currentMerchants = [];

// Initialize map
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map centered on Africa
    map = L.map('map').setView([-1.2921, 36.8219], 5);
    
    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Initialize layers
    routeLayer = L.layerGroup().addTo(map);
    markersLayer = L.layerGroup().addTo(map);
});

// Handle form submission
document.getElementById('routePlanningForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const origin = document.getElementById('origin').value.trim();
    const destination = document.getElementById('destination').value.trim();
    
    if (!origin || !destination) {
        alert('Please enter both origin and destination');
        return;
    }
    
    await planRoute(origin, destination);
});

async function planRoute(origin, destination) {
    try {
        showLoading(true);
        
        // Geocode origin
        const originCoords = await geocodeAddress(origin);
        if (!originCoords) {
            alert('Could not find origin location');
            return;
        }
        
        // Geocode destination
        const destCoords = await geocodeAddress(destination);
        if (!destCoords) {
            alert('Could not find destination location');
            return;
        }
        
        // Calculate route
        const routeData = await calculateRoute(originCoords, destCoords);
        if (!routeData) {
            alert('Could not calculate route');
            return;
        }
        
        // Display route on map
        displayRoute(routeData);
        
        // Show route info
        showRouteInfo(routeData);
        
        // Display merchants
        displayMerchants(routeData.merchants);
        
        // Store current route data
        currentRoute = {
            origin_lat: originCoords.latitude,
            origin_lng: originCoords.longitude,
            destination_lat: destCoords.latitude,
            destination_lng: destCoords.longitude,
            origin_address: originCoords.formatted_address,
            destination_address: destCoords.formatted_address,
            route_data: JSON.stringify(routeData.route)
        };
        currentMerchants = routeData.merchants;
        
    } catch (error) {
        console.error('Error planning route:', error);
        alert('An error occurred while planning the route');
    } finally {
        showLoading(false);
    }
}

async function geocodeAddress(address) {
    try {
        const response = await fetch('/api/geocode', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `address=${encodeURIComponent(address)}`
        });
        
        const data = await response.json();
        return data.success ? data : null;
    } catch (error) {
        console.error('Geocoding error:', error);
        return null;
    }
}

async function calculateRoute(origin, destination) {
    try {
        const response = await fetch('/api/calculate-route', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `origin_lat=${origin.latitude}&origin_lng=${origin.longitude}&dest_lat=${destination.latitude}&dest_lng=${destination.longitude}`
        });
        
        const data = await response.json();
        return data.success ? data : null;
    } catch (error) {
        console.error('Route calculation error:', error);
        return null;
    }
}

function displayRoute(routeData) {
    // Clear existing route
    routeLayer.clearLayers();
    markersLayer.clearLayers();
    
    // Add route polyline
    const coordinates = routeData.route.geometry.coordinates.map(coord => [coord[1], coord[0]]);
    const polyline = L.polyline(coordinates, {color: 'blue', weight: 4}).addTo(routeLayer);
    
    // Add start marker
    L.marker([currentRoute.origin_lat, currentRoute.origin_lng])
        .bindPopup(`<b>Start:</b> ${currentRoute.origin_address}`)
        .addTo(markersLayer);
    
    // Add end marker
    L.marker([currentRoute.destination_lat, currentRoute.destination_lng])
        .bindPopup(`<b>End:</b> ${currentRoute.destination_address}`)
        .addTo(markersLayer);
    
    // Fit map to route
    map.fitBounds(polyline.getBounds(), {padding: [20, 20]});
}

function showRouteInfo(routeData) {
    const distance = (routeData.distance / 1000).toFixed(1); // Convert to km
    const duration = Math.round(routeData.duration / 60); // Convert to minutes
    
    document.getElementById('routeDistance').textContent = `${distance} km`;
    document.getElementById('routeDuration').textContent = `${duration} min`;
    document.getElementById('routeInfo').classList.remove('hidden');
}

function displayMerchants(merchants) {
    const merchantsList = document.getElementById('merchantsList');
    
    if (!merchants || merchants.length === 0) {
        merchantsList.innerHTML = '<p class="text-gray-500 text-center py-8">No merchants found along this route</p>';
        return;
    }
    
    merchantsList.innerHTML = merchants.map(merchant => `
        <div class="border rounded-lg p-4 hover:bg-gray-50">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900">${merchant.business_name || merchant.owner_name}</h4>
                    <p class="text-sm text-gray-600">${merchant.address || 'Address not available'}</p>
                    <p class="text-xs text-blue-600 mt-1">${merchant.distance} km from route</p>
                </div>
                ${merchant.profile_image ? `<img src="/${merchant.profile_image}" alt="Profile" class="w-12 h-12 rounded-full object-cover">` : ''}
            </div>
            <div class="mt-2 flex items-center text-sm text-gray-500">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
                ${merchant.phone || 'No phone'}
            </div>
        </div>
    `).join('');
    
    // Add merchant markers to map
    merchants.forEach(merchant => {
        if (merchant.latitude && merchant.longitude) {
            L.marker([merchant.latitude, merchant.longitude])
                .bindPopup(`
                    <b>${merchant.business_name || merchant.owner_name}</b><br>
                    ${merchant.address || ''}<br>
                    <small>${merchant.distance} km from route</small>
                `)
                .addTo(markersLayer);
        }
    });
}

function showLoading(show) {
    const btn = document.getElementById('planRouteBtn');
    const text = document.getElementById('planRouteText');
    const spinner = document.getElementById('planRouteSpinner');
    
    if (show) {
        btn.disabled = true;
        text.classList.add('hidden');
        spinner.classList.remove('hidden');
    } else {
        btn.disabled = false;
        text.classList.remove('hidden');
        spinner.classList.add('hidden');
    }
}

// Save route
document.getElementById('saveRouteBtn').addEventListener('click', async function() {
    if (!currentRoute) {
        alert('No route to save');
        return;
    }
    
    try {
        const formData = new FormData();
        Object.keys(currentRoute).forEach(key => {
            formData.append(key, currentRoute[key]);
        });
        formData.append('merchants', JSON.stringify(currentMerchants));
        
        const response = await fetch('/routes/create', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Route saved successfully!');
            window.location.href = '/routes';
        } else {
            alert('Error saving route: ' + data.error);
        }
    } catch (error) {
        console.error('Error saving route:', error);
        alert('An error occurred while saving the route');
    }
});
</script>

<?= view('driver/templates/footer') ?>
