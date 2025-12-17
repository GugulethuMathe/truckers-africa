<?= view('driver/templates/header', ['page_title' => $page_title]) ?>
<style>
    nav#bottomNav {
    z-index: 100;
}

/* Hide any location detection banners */
[class*="location-detected"],
[class*="location-banner"],
[id*="location-status"],
[id*="location-banner"],
.location-notification,
.geolocation-banner {
    display: none !important;
}
button#searchBtn {
    color: green;
}

/* Mobile-specific improvements */
@media (max-width: 640px) {
    /* Ensure map takes full height on mobile */
    #mapContainer {
        height: calc(100vh - 4rem);
    }

    /* Improve touch targets */
    .leaflet-control-zoom a {
        width: 36px !important;
        height: 36px !important;
        line-height: 36px !important;
        font-size: 18px !important;
    }
   
    /* Better popup sizing on mobile */
    .leaflet-popup-content-wrapper {
        max-width: calc(100vw - 4rem) !important;
    }

    /* Improve bottom sheet on mobile */
    #merchantSheet {
        max-height: 60vh;
        overflow-y: auto;
    }

    /* Better scrolling for horizontal cards */
    #merchantSheet .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    #merchantSheet .overflow-x-auto::-webkit-scrollbar {
        display: none;
    }

    /* Improve filter panel on mobile */
    #filterPanel {
        max-height: calc(100vh - 8rem);
        overflow-y: auto;
    }
    
}
.leaflet-container a {
    color: #ffffff !important;
}
/* Smooth transitions */
#merchantSheet {
    transition: transform 0.3s ease-in-out;
}

/* Improve touch scrolling */
.overflow-x-auto {
    -webkit-overflow-scrolling: touch;
}
 .bottom-sheet {
            max-height: 500px !important;
    }
    .absolute.top-2.right-2.sm\:top-4.sm\:right-4.z-20.space-y-2 {
    display: none;
}

</style>
<!-- Search Bar -->
<div id="searchContainer" class="fixed top-16 left-2 right-2 sm:left-4 sm:right-4 z-40" style="display: none;">
    <div class="bg-white rounded-lg shadow-lg p-2 sm:p-3">
        <!-- Search Input (now a GET form to /search for robustness) -->
        <form id="searchForm" action="<?= base_url('driver/search') ?>" method="get" class="flex items-center space-x-1 sm:space-x-3 mb-2 sm:mb-3" onsubmit="return submitSearchForm(event)">
            <i class="fas fa-search text-gray-400 text-xs sm:text-sm"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search services..." class="flex-1 outline-none text-gray-700 text-xs sm:text-sm py-1 min-w-0">
            <input type="hidden" name="category" id="categoryInput" value="all">
            <button id="categoryFilterToggle" type="button" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-2 py-1 sm:px-3 sm:py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors flex-shrink-0" title="Toggle category filters">
                <i class="fas fa-filter"></i>
            </button>
            <button id="searchBtn" type="submit" class="bg-safari-green text-white px-2 py-1 sm:px-4 sm:py-2 rounded-lg text-xs sm:text-sm font-medium flex-shrink-0">Search</button>
        </form>
        
        <!-- Category Filters -->
        <div id="categoryFiltersSection" class="border-t pt-3 hidden">
            <p class="text-xs text-gray-500 mb-2">Filter by category:</p>
            <div class="flex flex-wrap gap-2">
                <button class="category-filter active bg-green-500 text-white px-3 py-1 rounded-full text-xs font-medium" data-category="all">
                    All Services
                </button>
                <?php if (isset($categories) && !empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <button class="category-filter bg-gray-100 text-gray-700 hover:bg-gray-200 px-3 py-1 rounded-full text-xs font-medium" data-category="<?= $category['id'] ?>">
                            <?= esc($category['name']) ?>
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Main Map Container -->
<main id="mapContainer" class="pt-15 pb-20 h-screen relative">
    <!-- Leaflet Map -->
    <div id="map" class="w-full h-full relative z-10"></div>
    
    <!-- Map Controls -->
    <div class="absolute top-2 right-2 sm:top-4 sm:right-4 z-20 space-y-2">
        <!-- Filter Toggle Button -->
        <button id="filterToggleBtn" class="bg-white rounded-lg p-2 sm:p-3 shadow-lg hover:bg-gray-50 transition-colors" title="Filter Merchants">
            <i class="fas fa-filter text-gray-600 text-base sm:text-lg"></i>
        </button>
    </div>

    <!-- Filter Panel -->
    <div id="filterPanel" class="absolute top-2 left-2 right-2 sm:top-4 sm:left-4 sm:right-auto z-20 bg-white rounded-lg shadow-lg p-3 sm:p-4 w-auto sm:w-64 max-w-full hidden">
        <div class="flex justify-between items-center mb-3">
            <h3 class="font-semibold text-gray-800">Filter Merchants</h3>
            <button id="closeFilterBtn" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Show All Button -->
        <button id="showAllBtn" class="w-full mb-3 px-3 py-2 bg-blue-500 text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition-colors">
            <i class="fas fa-eye mr-2"></i>Show All Merchants
        </button>
        
        <!-- Category Filters -->
        <div class="space-y-2">
            <p class="text-sm font-medium text-gray-700 mb-2">Filter by Service Category:</p>
            <?php if (isset($categories) && !empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" class="category-checkbox" value="<?= $category['id'] ?>" data-category-name="<?= esc($category['name']) ?>">
                        <span class="text-sm text-gray-700"><?= esc($category['name']) ?></span>
                    </label>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Apply Filters Button -->
        <button id="applyFiltersBtn" class="w-full mt-4 px-3 py-2 bg-green-500 text-white rounded-lg text-sm font-medium hover:bg-green-600 transition-colors">
            <i class="fas fa-check mr-2"></i>Apply Filters
        </button>
    </div>
    
</main>

<?php 
$current_page = 'home';
echo view('driver/templates/bottom_nav', ['current_page' => $current_page]); 
?>

<!-- Merchant List Bottom Sheet -->
<div id="merchantSheet" class="fixed bottom-16 left-0 right-0 bg-white rounded-t-3xl shadow-2xl slide-up z-30 transition-transform duration-300">
    <!-- Center Location Button - Attached to Sheet -->
    <button id="locateBtn" class="absolute -top-12 sm:-top-16 right-2 sm:right-4 z-40 bg-blue-500 text-white p-2 sm:p-3 rounded-lg shadow-lg hover:bg-blue-600 transition-colors" title="Center on my location">
        <i class="fas fa-crosshairs text-base sm:text-lg"></i>
    </button>

    <!-- Plan Route Button - Below Locate Button -->
    <a href="<?= base_url('driver/routes') ?>" id="planRouteBtn" class="absolute -top-12 sm:-top-16 right-14 sm:right-20 z-40 bg-yellow-400 text-gray-800 px-2 py-2 sm:px-3 sm:py-2 rounded-lg shadow-lg text-xs sm:text-sm font-semibold flex items-center space-x-1 hover:bg-yellow-500 transition-colors" title="Plan your route">
        <i class="fas fa-route text-xs sm:text-sm"></i>
        <span class="hidden sm:inline">Route</span>
    </a>
    
    <div class="p-3 sm:p-4">
        <div id="sheetHandle" class="w-12 h-1 bg-gray-300 rounded-full mx-auto mb-3 sm:mb-4 cursor-pointer hover:bg-gray-400 transition-colors"></div>

        <!-- Merchants and Branches Section -->
        <h3 class="text-base sm:text-lg font-bold text-dark-grey mb-3 sm:mb-4">Merchants and Branches</h3>

        <div class="flex space-x-2 sm:space-x-3 overflow-x-auto pb-2 -mx-3 px-3 sm:mx-0 sm:px-0">
            <?php if (!empty($business_locations)): ?>
                <?php foreach ($business_locations as $location): ?>
                    <div class="block bg-white border border-gray-200 rounded-lg p-3 sm:p-4 w-[260px] sm:w-[280px] flex-shrink-0 hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-start mb-2 sm:mb-3">
                            <img src="<?= !empty($location['business_image_url']) ? base_url($location['business_image_url']) : 'https://via.placeholder.com/40x40/e5e7eb/6b7280?text=' . urlencode(substr($location['business_name'], 0, 2)) ?>"
                                 alt="<?= esc($location['business_name']) ?> Logo"
                                 class="w-8 h-8 sm:w-10 sm:h-10 rounded-full mr-2 sm:mr-3 object-cover flex-shrink-0">
                            <div class="flex-1 min-w-0 overflow-hidden">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <h4 class="font-bold text-dark-grey text-xs sm:text-sm truncate flex-1 overflow-hidden" title="<?= esc($location['location_name']) ?>"><?= esc($location['location_name']) ?></h4>
                                    <?php if (isset($location['distance'])): ?>
                                        <span class="text-xs text-gray-500 flex-shrink-0 whitespace-nowrap">
                                            <i class="fas fa-location-arrow text-xs mr-0.5"></i><?= number_format($location['distance'], 1) ?> km
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-gray-600 mb-1 truncate overflow-hidden" title="<?= esc($location['business_name']) ?>"><?= esc($location['business_name']) ?></p>
                                <div class="flex items-center gap-1 flex-wrap">
                                    <?php if ($location['is_primary'] == 1): ?>
                                        <span class="inline-block bg-blue-100 text-blue-800 text-xs px-1.5 sm:px-2 py-0.5 rounded-full">Primary</span>
                                    <?php else: ?>
                                        <span class="inline-block text-xs px-1.5 sm:px-2 py-0.5 rounded-full" style="background-color: #e6e8eb; color: #0e2140;">BR</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1.5 sm:space-y-2 text-xs text-gray-600">
                            <?php if (!empty($location['physical_address'])): ?>
                                <?php
                                    // Get first 4 words of address
                                    $addressWords = explode(' ', $location['physical_address']);
                                    $shortAddress = implode(' ', array_slice($addressWords, 0, 4));
                                    if (count($addressWords) > 4) {
                                        $shortAddress .= '...';
                                    }
                                ?>
                                <div class="flex items-start min-w-0">
                                    <i class="fas fa-map-marker-alt text-gray-400 mr-1.5 sm:mr-2 mt-0.5 flex-shrink-0 text-xs"></i>
                                    <span class="truncate text-xs flex-1 overflow-hidden" title="<?= esc($location['physical_address']) ?>"><?= esc($shortAddress) ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($location['contact_number'])): ?>
                                <div class="flex items-center min-w-0">
                                    <i class="fas fa-phone text-gray-400 mr-1.5 sm:mr-2 flex-shrink-0 text-xs"></i>
                                    <a href="tel:<?= esc($location['contact_number']) ?>" class="text-blue-600 hover:text-blue-800 text-xs truncate flex-1 overflow-hidden">
                                        <?= esc($location['contact_number']) ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($location['whatsapp_number'])): ?>
                                <div class="flex items-center">
                                    <i class="fab fa-whatsapp text-green-500 mr-1.5 sm:mr-2 flex-shrink-0 text-xs"></i>
                                    <a href="https://wa.me/<?= str_replace(['+', ' ', '-'], '', $location['whatsapp_number']) ?>"
                                       target="_blank"
                                       class="text-green-600 hover:text-green-800 text-xs">
                                        WhatsApp
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="border-t border-gray-100 pt-2 mt-2 sm:mt-3">
                            <a href="<?= base_url('driver/location/' . esc($location['id'], 'url')) ?>"
                               class="w-full flex items-center justify-center text-xs text-white px-2 py-1.5 sm:py-2 rounded" style="background-color: #000f25;">
                                <i class="fas fa-concierge-bell mr-1 text-xs"></i><span class="text-xs">Services</span>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500">No merchants or branches available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Route Planning Modal -->
<div id="routeModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl p-6 slide-up-modal">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-800">Plan Your Route</h3>
            <button id="closeModal" class="text-gray-400">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Location</label>
                <div class="flex items-center space-x-3 p-3 border border-gray-300 rounded-lg">
                    <i class="fas fa-map-marker-alt text-green-500"></i>
                    <input type="text" placeholder="Current location" class="flex-1 outline-none bg-transparent">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Destination</label>
                <div class="flex items-center space-x-3 p-3 border border-gray-300 rounded-lg">
                    <i class="fas fa-flag text-red-500"></i>
                    <input type="text" placeholder="Enter destination" class="flex-1 outline-none bg-transparent">
                </div>
            </div>
            
            <button class="w-full text-green-700 border border-green-700 py-3 rounded-lg font-medium">
                + Add Stop
            </button>
            
            <button class="w-full bg-green-700 text-white py-3 rounded-lg font-bold">
                Plan Route
            </button>
        </div>
    </div>
</div>



<script>
// Pass merchant data from PHP to JavaScript (using business_locations only, same as routes page)
const merchantData = <?= json_encode($business_locations ?? []) ?>;
const geoapifyApiKey = '<?= esc($geoapify_api_key ?? '') ?>';
const driverLocation = {
    lat: <?= isset($driver['current_latitude']) && $driver['current_latitude'] ? $driver['current_latitude'] : '-26.2041' ?>,
    lng: <?= isset($driver['current_longitude']) && $driver['current_longitude'] ? $driver['current_longitude'] : '28.0473' ?>
};

// Debug logging
console.log('=== INITIAL DEBUG INFO ===');
console.log('Business locations loaded:', merchantData);
console.log('Driver location:', driverLocation);
console.log('Total business locations:', merchantData.length);
console.log('Geoapify API Key:', geoapifyApiKey ? 'Present' : 'Missing');
console.log('=== END INITIAL DEBUG ===');

// Global map variable
let map;
let userLocationMarker;
let merchantMarkers = [];

// Initialize Leaflet Map
function initializeMap() {
    console.log('Initializing map...');
    
    try {
        // Create map centered on Johannesburg (default) or user location
        map = L.map('map').setView([driverLocation.lat, driverLocation.lng], 12);
        console.log('Map created successfully');

        // Add OpenStreetMap tile layer (same as routes page)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        console.log('Tile layer added successfully');
    
    // Add user location marker
    addUserLocationMarker();

    // Add merchant/location markers (all business locations with green store icons)
    addMerchantMarkers();

    // Adjust map bounds to fit all markers
    fitMapToMarkers();

    // Initialize filter count display
    updateFilterCount([]);
    
        // Test Geoapify API first
        if (geoapifyApiKey) {
            testGeoapifyAPI();
        }
        
        // Try to get user's actual location on page load (with delay to avoid user gesture warning)
        console.log('Starting automatic location detection...');
        setTimeout(() => {
            getCurrentLocation();
        }, 1000);
        
        // Also update location every 5 minutes
        setInterval(getCurrentLocation, 300000); // 5 minutes
        
        console.log('Map initialization completed successfully');
    } catch (error) {
        console.error('Error initializing map:', error);
        alert('Error loading map. Please refresh the page.');
    }
}

// Add user location marker
function addUserLocationMarker() {
    // Use custom div icon (same style as routes page)
    const userIcon = L.divIcon({
        className: 'user-location-marker',
        html: '<div class="w-6 h-6 bg-blue-500 rounded-full border-4 border-white shadow-lg"></div>',
        iconSize: [24, 24],
        iconAnchor: [12, 12]
    });

    userLocationMarker = L.marker([driverLocation.lat, driverLocation.lng], {
        icon: userIcon
    }).addTo(map)
      .bindPopup('<b>Your Location</b><br>Current position')
      .openPopup();
}

// Add merchant/location markers to map (same as routes page)
function addMerchantMarkers() {
    console.log('Adding business location markers...');
    let addedCount = 0;

    merchantData.forEach((location, index) => {
        console.log(`Processing location ${index + 1}:`, {
            id: location.id,
            name: location.location_name,
            business: location.business_name,
            latitude: location.latitude,
            longitude: location.longitude,
            is_active: location.is_active
        });

        // Only add locations with valid coordinates and active status
        if (location.latitude != null && location.longitude != null && location.is_active) {
            const lat = parseFloat(location.latitude);
            const lng = parseFloat(location.longitude);

            // Skip if both coordinates are exactly 0 (invalid location)
            if (lat === 0 && lng === 0) {
                console.log(`Skipped location ${location.location_name || 'Unknown'} - coordinates are 0,0 (invalid)`);
                return;
            }

            addedCount++;

            // Create custom merchant icon (green store icon - same as routes page)
            const merchantIcon = L.divIcon({
                className: 'merchant-marker',
                html: '<div class="bg-white rounded-full p-2 shadow-lg border-2 border-green-500"><i class="fas fa-store text-green-600 text-sm"></i></div>',
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });

            // Create marker
            const marker = L.marker([lat, lng], {
                icon: merchantIcon
            }).addTo(map);

            // Store location data on marker
            marker.locationData = location;

            // Create popup content - show Primary or Branch badge
            const locationName = location.location_name || location.business_name;
            const businessName = location.business_name;
            const isPrimary = location.is_primary == 1;
            const locationBadge = isPrimary
                ? '<span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs ml-2">Primary</span>'
                : '<span class="bg-gray-100 text-gray-800 px-2 py-0.5 rounded-full text-xs ml-2">Branch</span>';

            const distanceBadge = location.distance
                ? `<span class="text-xs text-gray-500"><i class="fas fa-location-arrow mr-1"></i>${parseFloat(location.distance).toFixed(1)} km away</span>`
                : '';

            const popupContent = `
                <div class="p-2">
                    <h3 class="font-bold text-gray-800 mb-1">${locationName}${locationBadge}</h3>
                    <p class="text-xs text-gray-600 mb-2">${businessName}</p>
                    <p class="text-sm text-gray-600 mb-2">${location.physical_address || 'Address not available'}</p>
                    ${distanceBadge ? `<p class="mb-2">${distanceBadge}</p>` : ''}
                    <div class="flex space-x-2">
                        <a href="<?= base_url('driver/location/') ?>${location.id}"
                           class="bg-green-500 text-white px-3 py-1 rounded text-xs hover:bg-green-600 transition-colors">
                            View Branch
                        </a>
                        <a href="<?= base_url('driver/routes') ?>"
                           class="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600 transition-colors">
                            Plan Route
                        </a>
                    </div>
                </div>
            `;

            marker.bindPopup(popupContent);
            merchantMarkers.push(marker);

            console.log(`Added location marker for ${locationName} at [${lat}, ${lng}]`);
        } else {
            console.log(`Skipped location ${location.location_name || 'Unknown'} - missing coordinates or not active`);
        }
    });

    console.log(`Total location markers added: ${addedCount}`);
    console.log(`Total markers in array: ${merchantMarkers.length}`);
}

// Fit map bounds to show all markers
function fitMapToMarkers() {
    console.log('Adjusting map bounds to fit all markers...');

    const allLatLngs = [];

    // Add user location
    allLatLngs.push([driverLocation.lat, driverLocation.lng]);

    // Add all merchant/location markers
    merchantMarkers.forEach(marker => {
        allLatLngs.push(marker.getLatLng());
    });

    console.log(`Total points to fit: ${allLatLngs.length}`);

    if (allLatLngs.length > 1) {
        // Create bounds from all points
        const bounds = L.latLngBounds(allLatLngs);

        // Fit map to bounds with padding
        map.fitBounds(bounds, {
            padding: [50, 50],
            maxZoom: 12  // Don't zoom in too much
        });

        console.log('Map bounds adjusted successfully');
    } else {
        console.log('Not enough markers to adjust bounds');
    }
}

// Get user's current location using browser geolocation with Geoapify fallback
function getCurrentLocation() {
    console.log('Getting current location...');
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;
                
                console.log(`GPS location found: ${lat}, ${lng} (accuracy: ${accuracy}m)`);
                
                // Update user location marker
                userLocationMarker.setLatLng([lat, lng]);
                map.setView([lat, lng], 14);
                
                // Update driver location in database
                updateDriverLocation(lat, lng);
                
                // Show success message
                // showLocationStatus('Location updated successfully', 'success');
            },
            function(error) {
                console.log('GPS geolocation error code:', error.code);
                console.log('GPS geolocation error message:', error.message);
                
                const errorMessages = {
                    1: 'Location access denied by user',
                    2: 'Location unavailable (network/GPS issue)',
                    3: 'Location request timeout'
                };
                const errorMsg = errorMessages[error.code] || error.message || 'Unknown location error';
                console.log('GPS Error Type:', errorMsg);
                
                // Try Geoapify IP-based geolocation as fallback
                console.log('GPS failed, trying Geoapify IP geolocation...');
                console.log('Geoapify API Key available for fallback:', !!geoapifyApiKey);
                
                if (geoapifyApiKey) {
                    getLocationFromGeoapify();
                } else {
                    // Use hardcoded API key as fallback
                    console.log('No API key in variable, using hardcoded key...');
                    const fallbackApiKey = 'ef57cac5bf2f4442814cda270033e98d';
                    
                    fetch(`https://api.geoapify.com/v1/ipinfo?apiKey=${fallbackApiKey}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.location) {
                                const lat = data.location.latitude;
                                const lng = data.location.longitude;
                                const city = data.city?.name || 'Unknown';
                                const country = data.country?.name || 'Unknown';
                                
                                console.log(`Fallback location found: ${lat}, ${lng} (${city}, ${country})`);
                                
                                // Update user location marker
                                userLocationMarker.setLatLng([lat, lng]);
                                map.setView([lat, lng], 12);
                                
                                // Update driver location in database
                                updateDriverLocation(lat, lng);
                                
                                // showLocationStatus(`Location detected: ${city}, ${country}`, 'info');
                            } else {
                                showLocationStatus('Unable to detect location automatically.', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Fallback geolocation error:', error);
                            showLocationStatus('Location service temporarily unavailable.', 'error');
                        });
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 300000 // 5 minutes
            }
        );
    } else {
        // Browser doesn't support geolocation, try Geoapify
        if (geoapifyApiKey) {
            getLocationFromGeoapify();
        } else {
            showLocationStatus('Geolocation not supported and no API key available.', 'error');
        }
    }
}

// Test Geoapify API connectivity
function testGeoapifyAPI() {
    console.log('Testing Geoapify API connectivity...');
    console.log('API URL:', `https://api.geoapify.com/v1/ipinfo?apiKey=${geoapifyApiKey}`);
    
    fetch(`https://api.geoapify.com/v1/ipinfo?apiKey=${geoapifyApiKey}`)
        .then(response => {
            console.log('Geoapify API Response Status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Geoapify API Test Success:', data);
            if (data.location) {
                console.log(`API detected location: ${data.city?.name || 'Unknown'}, ${data.country?.name || 'Unknown'}`);
            }
        })
        .catch(error => {
            console.error('Geoapify API Test Failed:', error);
            showLocationStatus('Geoapify API connection failed. Check API key.', 'error');
        });
}

// Get location using Geoapify IP-based geolocation
function getLocationFromGeoapify() {
    console.log('Trying Geoapify IP-based geolocation...');
    
    fetch(`https://api.geoapify.com/v1/ipinfo?apiKey=${geoapifyApiKey}`)
        .then(response => response.json())
        .then(data => {
            if (data.location) {
                const lat = data.location.latitude;
                const lng = data.location.longitude;
                const city = data.city?.name || 'Unknown';
                const country = data.country?.name || 'Unknown';
                
                console.log(`Geoapify location found: ${lat}, ${lng} (${city}, ${country})`);
                
                // Update user location marker
                userLocationMarker.setLatLng([lat, lng]);
                map.setView([lat, lng], 12);
                
                // Update driver location in database
                updateDriverLocation(lat, lng);
                
                // Show info message
                // showLocationStatus(`Location detected: ${city}, ${country}`, 'info');
            } else {
                showLocationStatus('Unable to detect location automatically.', 'error');
            }
        })
        .catch(error => {
            console.error('Geoapify geolocation error:', error);
            showLocationStatus('Location service temporarily unavailable.', 'error');
        });
}

// Show location status message
function showLocationStatus(message, type) {
    // Create or update status message
    let statusDiv = document.getElementById('locationStatus');
    if (!statusDiv) {
        statusDiv = document.createElement('div');
        statusDiv.id = 'locationStatus';
        statusDiv.className = 'fixed top-20 left-4 right-4 z-50 p-3 rounded-lg shadow-lg text-sm font-medium';
        document.body.appendChild(statusDiv);
    }
    
    // Set message and styling based on type
    statusDiv.textContent = message;
    statusDiv.className = 'fixed top-20 left-4 right-4 z-50 p-3 rounded-lg shadow-lg text-sm font-medium ';
    
    switch(type) {
        case 'success':
            statusDiv.className += 'bg-green-500 text-white';
            break;
        case 'error':
            statusDiv.className += 'bg-red-500 text-white';
            break;
        case 'info':
            statusDiv.className += 'bg-blue-500 text-white';
            break;
        default:
            statusDiv.className += 'bg-gray-500 text-white';
    }
    
    // Auto-hide after 4 seconds
    setTimeout(() => {
        if (statusDiv) {
            statusDiv.remove();
        }
    }, 4000);
}

// Update driver location in database
function updateDriverLocation(lat, lng) {
    fetch('<?= base_url('api/driver/location') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `latitude=${lat}&longitude=${lng}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Driver location updated in database');
        } else {
            console.error('Failed to update location:', data.error);
        }
    })
    .catch(error => {
        console.error('Error updating location:', error);
    });
}

// Debug location services
function debugLocationServices() {
    console.log('=== LOCATION DEBUG START ===');
    console.log('Geoapify API Key:', geoapifyApiKey);
    console.log('Driver Location:', driverLocation);
    console.log('Navigator Geolocation Available:', !!navigator.geolocation);
    
    // Test Geoapify API
    if (geoapifyApiKey) {
        console.log('Testing Geoapify API...');
        testGeoapifyAPI();
        
        // Force try Geoapify location
        setTimeout(() => {
            console.log('Forcing Geoapify location detection...');
            getLocationFromGeoapify();
        }, 2000);
    } else {
        console.error('No Geoapify API key found!');
        showLocationStatus('No Geoapify API key configured', 'error');
    }
    
    // Test browser geolocation
    if (navigator.geolocation) {
        console.log('Testing browser geolocation...');
        navigator.geolocation.getCurrentPosition(
            (position) => {
                console.log('Browser GPS Success:', {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                    accuracy: position.coords.accuracy
                });
                showLocationStatus(`GPS: ${position.coords.latitude.toFixed(4)}, ${position.coords.longitude.toFixed(4)}`, 'success');
            },
            (error) => {
                console.error('Browser GPS Error Code:', error.code);
                console.error('Browser GPS Error Message:', error.message);
                const errorMessages = {
                    1: 'Permission denied by user',
                    2: 'Position unavailable',
                    3: 'Timeout occurred'
                };
                const errorMsg = errorMessages[error.code] || error.message || 'Unknown GPS error';
                showLocationStatus(`GPS Error: ${errorMsg}`, 'error');
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }
    
    console.log('=== LOCATION DEBUG END ===');
}

// Locate user button functionality
function locateUser() {
    getCurrentLocation();
}

// Show merchant detail in bottom sheet
function showMerchantDetail(merchantId) {
    const merchant = merchantData.find(m => m.id == merchantId);
    if (merchant) {
        // Update bottom sheet with merchant info
        console.log('Showing merchant details:', merchant);
        document.getElementById('merchantSheet').classList.add('active');
    }
}

// Filter merchants by service categories
function filterMerchantsByCategories(selectedCategoryIds) {
    merchantMarkers.forEach(marker => {
        const location = marker.locationData;
        let shouldShow = false;

        if (selectedCategoryIds.length === 0) {
            // Show all locations if no categories selected
            shouldShow = true;
        } else {
            // Check if location has any of the selected service categories
            const locationCategoryIds = location.service_category_ids || [];
            shouldShow = selectedCategoryIds.some(catId => locationCategoryIds.includes(catId));
        }
        
        if (shouldShow) {
            map.addLayer(marker);
        } else {
            map.removeLayer(marker);
        }
    });
    
    // Update filter panel with count
    updateFilterCount(selectedCategoryIds);
}

// Show all merchants
function showAllMerchants() {
    merchantMarkers.forEach(marker => {
        map.addLayer(marker);
    });
    
    // Clear all checkboxes
    document.querySelectorAll('.category-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    updateFilterCount([]);
}

// Update filter count display
function updateFilterCount(selectedCategoryIds) {
    const visibleCount = merchantMarkers.filter(marker => map.hasLayer(marker)).length;
    const totalCount = merchantMarkers.length;

    // Update filter button appearance based on active filters
    const filterToggleBtn = document.getElementById('filterToggleBtn');
    const filterIcon = filterToggleBtn.querySelector('i');

    if (selectedCategoryIds.length > 0) {
        filterToggleBtn.classList.add('bg-blue-100');
        filterToggleBtn.classList.remove('bg-white');
        filterIcon.classList.add('text-blue-600');
        filterIcon.classList.remove('text-gray-600');
    } else {
        filterToggleBtn.classList.add('bg-white');
        filterToggleBtn.classList.remove('bg-blue-100');
        filterIcon.classList.add('text-gray-600');
        filterIcon.classList.remove('text-blue-600');
    }

    console.log(`Showing ${visibleCount} of ${totalCount} merchants`);
}

// Toggle filter panel
function toggleFilterPanel() {
    const filterPanel = document.getElementById('filterPanel');
    const filterToggleBtn = document.getElementById('filterToggleBtn');
    const filterIcon = filterToggleBtn.querySelector('i');
    
    if (filterPanel.classList.contains('hidden')) {
        filterPanel.classList.remove('hidden');
        filterIcon.classList.remove('fa-filter');
        filterIcon.classList.add('fa-filter-circle-xmark');
    } else {
        filterPanel.classList.add('hidden');
        filterIcon.classList.remove('fa-filter-circle-xmark');
        filterIcon.classList.add('fa-filter');
    }
}

// Category filtering functionality
function initializeCategoryFilters() {
    const categoryFilters = document.querySelectorAll('.category-filter');
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const categoryInput = document.getElementById('categoryInput');
    
    // Category filter click handlers
    categoryFilters.forEach(filter => {
        filter.addEventListener('click', function() {
            // Remove active class from all filters
            categoryFilters.forEach(f => {
                f.classList.remove('active', 'bg-green-500', 'text-white');
                f.classList.add('bg-gray-100', 'text-gray-700');
            });
            
            // Add active class to clicked filter
            this.classList.add('active', 'bg-green-500', 'text-white');
            this.classList.remove('bg-gray-100', 'text-gray-700');
            
            const categoryId = this.dataset.category;
        if (categoryInput) categoryInput.value = categoryId;
        filterServices(categoryId, searchInput.value);
        });
    });
    
    // Search functionality
    function handleSearch() {
        const activeCategory = document.querySelector('.category-filter.active').dataset.category;
        const searchTerm = searchInput.value;
        filterServices(activeCategory, searchTerm);
    }
    
    searchBtn.addEventListener('click', handleSearch);
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            handleSearch();
        }
    });
}

// Filter services based on category and search term
function filterServices(categoryId, searchTerm) {
    // Prefer form submit for consistent behavior
    const form = document.getElementById('searchForm');
    if (form) {
        if (categoryId && categoryId !== 'all') {
            document.getElementById('categoryInput').value = categoryId;
        } else {
            document.getElementById('categoryInput').value = 'all';
        }
        form.submit();
        return;
    }
    // Fallback: direct URL navigation
    const params = new URLSearchParams();
    if (categoryId && categoryId !== 'all') params.append('category', categoryId);
    if (searchTerm && searchTerm.trim() !== '') params.append('search', searchTerm.trim());
    const url = params.toString() ? `<?= base_url('search') ?>?${params.toString()}` : `<?= base_url('search') ?>`;
    window.location.href = url;
}

// Submit handler for search form to mirror search_results behavior
function submitSearchForm(e) {
    e.preventDefault();
    const activeCategoryBtn = document.querySelector('.category-filter.active');
    const categoryId = activeCategoryBtn ? activeCategoryBtn.dataset.category : 'all';
    document.getElementById('categoryInput').value = categoryId;
    const form = document.getElementById('searchForm');
    if (form) form.submit();
    return false;
}

// Toggle category filters section
function toggleCategoryFilters() {
    const filtersSection = document.getElementById('categoryFiltersSection');
    const toggleBtn = document.getElementById('categoryFilterToggle');
    const icon = toggleBtn.querySelector('i');
    
    if (filtersSection.classList.contains('hidden')) {
        // Show filters
        filtersSection.classList.remove('hidden');
        toggleBtn.classList.add('bg-blue-100', 'text-blue-600');
        toggleBtn.classList.remove('bg-gray-100', 'text-gray-600');
        icon.classList.remove('fa-filter');
        icon.classList.add('fa-filter-circle-xmark');
    } else {
        // Hide filters
        filtersSection.classList.add('hidden');
        toggleBtn.classList.add('bg-gray-100', 'text-gray-600');
        toggleBtn.classList.remove('bg-blue-100', 'text-blue-600');
        icon.classList.remove('fa-filter-circle-xmark');
        icon.classList.add('fa-filter');
    }
}

// Toggle merchant bottom sheet
function toggleMerchantSheet() {
    const merchantSheet = document.getElementById('merchantSheet');
    const isCollapsed = merchantSheet.classList.contains('collapsed');
    
    if (isCollapsed) {
        // Expand the sheet
        merchantSheet.classList.remove('collapsed');
        merchantSheet.style.transform = 'translateY(0)';
    } else {
        // Collapse the sheet (show only handle)
        merchantSheet.classList.add('collapsed');
        merchantSheet.style.transform = 'translateY(calc(100% - 32px))';
    }
}

// Show merchant sheet (expand)
function showMerchantSheet() {
    const merchantSheet = document.getElementById('merchantSheet');
    merchantSheet.classList.remove('collapsed');
    merchantSheet.style.transform = 'translateY(0)';
}

// Hide merchant sheet (collapse)
function hideMerchantSheet() {
    const merchantSheet = document.getElementById('merchantSheet');
    merchantSheet.classList.add('collapsed');
    merchantSheet.style.transform = 'translateY(calc(100% - 32px))';
}

// Center map on user's location
function locateUser() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                // Update user location marker and center map
                if (userLocationMarker) {
                    userLocationMarker.setLatLng([lat, lng]);
                    map.setView([lat, lng], 15);
                } else {
                    // Create user location marker if it doesn't exist
                    const userIcon = L.divIcon({
                        html: '<div style="background: #3b82f6; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
                        className: 'user-location-marker',
                        iconSize: [22, 22],
                        iconAnchor: [11, 11]
                    });
                    
                    userLocationMarker = L.marker([lat, lng], { icon: userIcon }).addTo(map);
                    map.setView([lat, lng], 15);
                }
                
                console.log(`Location centered: ${lat}, ${lng}`);
            },
            function(error) {
                console.error('Geolocation error:', error);
                alert('Unable to get your location. Please check your browser permissions.');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000
            }
        );
    } else {
        alert('Geolocation is not supported by this browser.');
    }
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map
    initializeMap();
    
    // Initialize category filters
    initializeCategoryFilters();
    
    // Event listeners for map controls
    const locateBtn = document.getElementById('locateBtn');
    const filterToggleBtn = document.getElementById('filterToggleBtn');
    const closeFilterBtn = document.getElementById('closeFilterBtn');
    const showAllBtn = document.getElementById('showAllBtn');
    const applyFiltersBtn = document.getElementById('applyFiltersBtn');
    const planRouteBtn = document.getElementById('planRouteBtn');
    const routeModal = document.getElementById('routeModal');
    const closeModal = document.getElementById('closeModal');
    const merchantSheet = document.getElementById('merchantSheet');
    const sheetHandle = document.getElementById('sheetHandle');
    const categoryFilterToggle = document.getElementById('categoryFilterToggle');

    // Map control event listeners
    locateBtn.addEventListener('click', locateUser);
    filterToggleBtn.addEventListener('click', toggleFilterPanel);
    closeFilterBtn.addEventListener('click', () => {
        document.getElementById('filterPanel').classList.add('hidden');
        document.getElementById('filterToggleBtn').querySelector('i').classList.remove('fa-filter-circle-xmark');
        document.getElementById('filterToggleBtn').querySelector('i').classList.add('fa-filter');
    });
    showAllBtn.addEventListener('click', showAllMerchants);
    applyFiltersBtn.addEventListener('click', () => {
        const selectedCategories = [];
        document.querySelectorAll('.category-checkbox:checked').forEach(checkbox => {
            selectedCategories.push(checkbox.value);
        });
        filterMerchantsByCategories(selectedCategories);
    });
    planRouteBtn.addEventListener('click', () => routeModal.classList.remove('hidden'));
    closeModal.addEventListener('click', () => routeModal.classList.add('hidden'));
    
    // Sheet handle toggle functionality
    sheetHandle.addEventListener('click', toggleMerchantSheet);
    
    // Category filter toggle functionality
    categoryFilterToggle.addEventListener('click', toggleCategoryFilters);

    // Initialize merchant sheet in collapsed state after a delay
    setTimeout(() => {
        merchantSheet.classList.add('active');
        hideMerchantSheet(); // Start collapsed, showing only the handle
    }, 2000);
});

</script>

<!-- Custom CSS for map markers -->
<style>
.user-location-marker {
    background: transparent;
    border: none;
}

.merchant-marker {
    background: transparent;
    border: none;
}

.leaflet-popup-content-wrapper {
    border-radius: 8px;
}

.leaflet-popup-content {
    margin: 0;
    padding: 0;
}

/* Ensure map controls are properly styled */
.leaflet-control-zoom {
    border: none !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
}

.leaflet-control-zoom a {
    background-color: white !important;
    color: #374151 !important;
    border: none !important;
}

.leaflet-control-zoom a:hover {
    background-color: #f9fafb !important;
}

/* Merchant sheet toggle styles */
#merchantSheet {
    transform: translateY(0);
}

#merchantSheet.collapsed {
    transform: translateY(calc(100% - 32px)) !important;
}

#merchantSheet.active {
    display: block;
}

/* Handle hover effect */
#sheetHandle:hover {
    background-color: #9ca3af;
}
</style>

<?= view('driver/templates/footer') ?>