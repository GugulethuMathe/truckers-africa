<?= view('admin/templates/header', ['page_title' => 'Platform Settings']) ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-semibold mb-6">Platform Settings</h2>
    
    <form action="#" method="POST">
        <?= csrf_field() ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Max Driving Minutes -->
            <div>
                <label for="max_driving_minutes" class="block text-sm font-medium text-gray-700">Max Driving Minutes per Day</label>
                <input type="number" id="max_driving_minutes" name="max_driving_minutes" value="540" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <p class="mt-2 text-xs text-gray-500">Set the maximum number of minutes a driver can be on duty per day (e.g., 540 for 9 hours).</p>
            </div>

            <!-- Default Search Radius -->
            <div>
                <label for="default_search_radius" class="block text-sm font-medium text-gray-700">Default Search Radius (km)</label>
                <input type="number" id="default_search_radius" name="default_search_radius" value="100" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <p class="mt-2 text-xs text-gray-500">The default radius in kilometers for job searches.</p>
            </div>

            <!-- Other settings can be added here -->

        </div>

        <div class="mt-8 border-t border-gray-200 pt-6">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                Save Settings
            </button>
        </div>
    </form>
</div>

<?= view('admin/templates/footer') ?>
