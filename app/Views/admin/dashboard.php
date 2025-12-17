<?= view('admin/templates/header', ['page_title' => 'Dashboard Summary']) ?>

<!-- Key Stat Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-sm font-medium text-gray-500">Pending Merchant Approvals</h3>
        <div class="text-3xl font-bold text-indigo-600 mt-2"><?= esc($pending_merchants) ?></div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-sm font-medium text-gray-500">Total Active Merchants</h3>
        <div class="text-3xl font-bold text-green-600 mt-2"><?= esc($active_merchants) ?></div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-sm font-medium text-gray-500">Total Registered Drivers</h3>
        <div class="text-3xl font-bold text-blue-600 mt-2"><?= esc($registered_drivers) ?></div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-sm font-medium text-gray-500">Total Active Subscriptions</h3>
        <div class="text-3xl font-bold text-yellow-600 mt-2"><?= esc($active_subscriptions) ?></div>
    </div>
</div>

<!-- Quick Links -->
<div class="mb-6">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Quick Links</h3>
    <div class="flex space-x-4">
        <a href="<?= site_url('admin/merchants/pending') ?>" class="bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">Manage Pending Merchants</a>
        <a href="#" class="bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-800 transition">Manage Users</a>
    </div>
</div>

<!-- Recent Activity Feed -->
<div>
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Recent Activity</h3>
    <div class="bg-white p-4 rounded-lg shadow-md">
        <ul class="divide-y divide-gray-200">
            <li class="py-3 flex justify-between items-center">
                <p class="text-sm text-gray-800">New Merchant Sign-up: <span class="font-semibold">'City Movers Ltd'</span></p>
                <span class="text-xs text-gray-500">10 minutes ago</span>
            </li>
            <li class="py-3 flex justify-between items-center">
                <p class="text-sm text-gray-800">New Driver Sign-up: <span class="font-semibold">James K.</span></p>
                <span class="text-xs text-gray-500">35 minutes ago</span>
            </li>
            <li class="py-3 flex justify-between items-center">
                <p class="text-sm text-gray-800">New Driver Sign-up: <span class="font-semibold">Amina S.</span></p>
                <span class="text-xs text-gray-500">1 hour ago</span>
            </li>
            <li class="py-3 flex justify-between items-center">
                <p class="text-sm text-gray-800">New Merchant Sign-up: <span class="font-semibold">'Reliable Transporters'</span></p>
                <span class="text-xs text-gray-500">3 hours ago</span>
            </li>
             <li class="py-3 flex justify-between items-center">
                <p class="text-sm text-gray-800">New Driver Sign-up: <span class="font-semibold">David O.</span></p>
                <span class="text-xs text-gray-500">5 hours ago</span>
            </li>
        </ul>
    </div>
</div>

<?= view('admin/templates/footer') ?>

