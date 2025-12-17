<?= view('merchant/templates/header', ['page_title' => 'Route Planning']) ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Route Planning</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-1 bg-white p-4 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-3">Plan Your Route</h2>
            <div class="mb-4">
                <label for="start" class="block text-sm font-medium text-gray-700">Start Point</label>
                <input type="text" id="start" name="start" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., Johannesburg">
            </div>
            <div class="mb-4">
                <label for="end" class="block text-sm font-medium text-gray-700">End Point</label>
                <input type="text" id="end" name="end" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., Cape Town">
            </div>
            <button id="plan-route-btn" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">Plan Route</button>
        </div>

        <div id="map" class="md:col-span-2 h-96 md:h-full rounded-lg shadow"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize the map, centered on Africa
        const map = L.map('map').setView([2.8, 23.3], 4);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        const planRouteBtn = document.getElementById('plan-route-btn');
        planRouteBtn.addEventListener('click', function() {
            alert('Route planning functionality will be implemented here! You will need an API key from openrouteservice.org.');
            // We will add the API call to OpenRouteService here.
        });
    });
</script>

<?= view('merchant/templates/footer') ?>
