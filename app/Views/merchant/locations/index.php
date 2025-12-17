<?= view('merchant/templates/header', ['page_title' => $page_title ?? 'Branches']) ?>

<div class="container-fluid px-4 py-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Branches</h1>
            <p class="text-gray-600 mt-1">Manage your business branches</p>
        </div>
        <?php if ($can_add_location): ?>
            <a href="<?= site_url('merchant/locations/create') ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add New Branch
            </a>
        <?php endif; ?>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= session()->getFlashdata('success') ?></span>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <!-- Usage Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Branches -->
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Branches</p>
                    <p class="text-2xl font-bold text-gray-900">
                        <?= $usage_stats['locations']['current'] ?> / <?= $usage_stats['locations']['display_limit'] ?>
                    </p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
            </div>
            <?php if ($usage_stats['locations']['limit'] > 0): ?>
                <div class="mt-2">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: <?= $usage_stats['locations']['percentage'] ?>%"></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Service Listings -->
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Service Listings</p>
                    <p class="text-2xl font-bold text-gray-900">
                        <?= $usage_stats['listings']['current'] ?> / <?= $usage_stats['listings']['display_limit'] ?>
                    </p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
            <?php if ($usage_stats['listings']['limit'] > 0): ?>
                <div class="mt-2">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: <?= $usage_stats['listings']['percentage'] ?>%"></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Categories -->
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Categories Limit</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $usage_stats['categories']['display_limit'] ?></p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Gallery Images -->
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Images per Listing</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $usage_stats['gallery_images']['display_limit'] ?></p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Limit Message -->
    <?php if (!$can_add_location): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <?= $location_limit_message ?>
                        <?php if (isset($is_max_plan) && $is_max_plan): ?>
                            <a href="<?= site_url('merchant/help') ?>" class="font-medium underline hover:text-yellow-800">Contact Support</a>
                        <?php else: ?>
                            <a href="<?= site_url('merchant/subscription/plans') ?>" class="font-medium underline hover:text-yellow-800">Upgrade your plan</a>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Branches List -->
    <?php if (empty($locations)): ?>
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No Branches</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by adding your first branch.</p>
            <?php if ($can_add_location): ?>
                <div class="mt-6">
                    <a href="<?= site_url('merchant/locations/create') ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Branch
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($locations as $location): ?>
                <div class="bg-white rounded-lg shadow hover:shadow-lg transition <?= $location['is_active'] ? '' : 'opacity-60' ?>">
                    <div class="p-6">
                        <!-- Location Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <?= esc($location['location_name']) ?>
                                    <?php if ($location['is_primary']): ?>
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            Primary
                                        </span>
                                    <?php endif; ?>
                                </h3>
                                <?php if (!$location['is_active']): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mt-1">
                                        Inactive
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Location Details -->
                        <div class="space-y-2 text-sm text-gray-600 mb-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 mr-2 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span><?= esc($location['physical_address']) ?></span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <span><?= esc($location['contact_number']) ?></span>
                            </div>
                            <?php if (!empty($location['email'])): ?>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <span><?= esc($location['email']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between pt-4 border-t">
                            <div class="flex space-x-2">
                                <a href="<?= site_url('merchant/locations/edit/' . $location['id']) ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Edit
                                </a>
                                <?php if (!$location['is_primary']): ?>
                                    <button onclick="setPrimary(<?= $location['id'] ?>)" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                        Make Primary
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div>
                                <?php if ($location['is_active']): ?>
                                    <button onclick="deactivateLocation(<?= $location['id'] ?>)" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        Deactivate
                                    </button>
                                <?php else: ?>
                                    <button onclick="activateLocation(<?= $location['id'] ?>)" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                        Activate
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function setPrimary(locationId) {
    if (!confirm('Set this as your primary location?')) return;

    fetch(`<?= site_url('merchant/locations/set-primary/') ?>${locationId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to update primary location');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

function deactivateLocation(locationId) {
    if (!confirm('Deactivate this location? Listings associated with this location will remain but won\'t show this location.')) return;

    fetch(`<?= site_url('merchant/locations/deactivate/') ?>${locationId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to deactivate location');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

function activateLocation(locationId) {
    fetch(`<?= site_url('merchant/locations/activate/') ?>${locationId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to activate location. You may have reached your plan limit.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}
</script>

<?= view('merchant/templates/footer') ?>
