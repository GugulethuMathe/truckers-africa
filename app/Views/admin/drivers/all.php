<?= view('admin/templates/header', ['page_title' => 'All Drivers']) ?>

<div class="container mx-auto px-4 py-8">
    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p class="font-bold">Success</p>
            <p><?= session()->getFlashdata('success') ?></p>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p class="font-bold">Error</p>
            <p><?= session()->getFlashdata('error') ?></p>
        </div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">All Registered Drivers</h1>
        <div class="flex space-x-2">
            <a href="#" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Export CSV</a>
            <a href="<?= site_url('admin/drivers/add') ?>" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Add Driver</a>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <form method="GET" action="<?= site_url('admin/drivers/all') ?>" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-64">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Drivers</label>
                <input type="text"
                       id="search"
                       name="search"
                       value="<?= esc($search) ?>"
                       placeholder="Search by name, email, phone, license, or vehicle..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="min-w-48">
                <label for="vehicle_type" class="block text-sm font-medium text-gray-700 mb-2">Vehicle Type</label>
                <select id="vehicle_type"
                        name="vehicle_type"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Vehicle Types</option>
                    <?php foreach ($vehicleTypes as $type): ?>
                        <?php if (!empty($type['vehicle_type'])): ?>
                            <option value="<?= esc($type['vehicle_type']) ?>" <?= $selectedVehicleType == $type['vehicle_type'] ? 'selected' : '' ?>>
                                <?= esc(ucfirst($type['vehicle_type'])) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="min-w-40">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status"
                        name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Status</option>
                    <option value="Active" <?= $selectedStatus == 'Active' ? 'selected' : '' ?>>Active</option>
                    <option value="Inactive" <?= $selectedStatus == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="Dormant" <?= $selectedStatus == 'Dormant' ? 'selected' : '' ?>>Dormant</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Search
                </button>
                <a href="<?= site_url('admin/drivers/all') ?>" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Results Summary -->
    <div class="mb-4">
        <p class="text-gray-600">
            Showing <?= count($drivers) ?> of <?= $totalDrivers ?> drivers
            <?php if (!empty($search)): ?>
                for "<strong><?= esc($search) ?></strong>"
            <?php endif; ?>
            <?php if (!empty($selectedVehicleType)): ?>
                with vehicle type "<strong><?= esc(ucfirst($selectedVehicleType)) ?></strong>"
            <?php endif; ?>
            <?php if (!empty($selectedStatus)): ?>
                with status "<strong><?= esc($selectedStatus) ?></strong>"
            <?php endif; ?>
        </p>
    </div>

    <!-- Drivers Table -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">ID</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Name</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Email</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Phone</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Vehicle</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">License</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Status</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Joined</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (!empty($drivers)): ?>
                        <?php foreach ($drivers as $driver): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="text-left py-3 px-4 text-sm font-mono"><?= esc($driver['id']) ?></td>
                                <td class="text-left py-3 px-4">
                                    <div class="flex items-center">
                                        <?php if (!empty($driver['profile_image_url'])): ?>
                                            <img src="<?= esc($driver['profile_image_url']) ?>" alt="Profile" class="w-8 h-8 rounded-full mr-3">
                                        <?php else: ?>
                                            <div class="w-8 h-8 bg-gray-300 rounded-full mr-3 flex items-center justify-center">
                                                <span class="text-xs font-semibold text-gray-600"><?= strtoupper(substr($driver['name'], 0, 1)) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="font-medium"><?= esc($driver['name'] . ' ' . $driver['surname']) ?></div>
                                            <?php if (!empty($driver['country_of_residence'])): ?>
                                                <div class="text-sm text-gray-500"><?= esc($driver['country_of_residence']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-left py-3 px-4">
                                    <div class="text-sm"><?= esc($driver['email']) ?></div>
                                    <?php if (!empty($driver['google_id'])): ?>
                                        <div class="text-xs text-blue-600">Google Auth</div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-left py-3 px-4">
                                    <div class="text-sm"><?= esc($driver['contact_number']) ?></div>
                                    <?php if (!empty($driver['whatsapp_number']) && $driver['whatsapp_number'] != $driver['contact_number']): ?>
                                        <div class="text-xs text-green-600">WhatsApp: <?= esc($driver['whatsapp_number']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-left py-3 px-4">
                                    <?php if (!empty($driver['vehicle_type'])): ?>
                                        <div class="text-sm font-medium"><?= esc(ucfirst($driver['vehicle_type'])) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($driver['vehicle_registration'])): ?>
                                        <div class="text-xs text-gray-500 font-mono"><?= esc($driver['vehicle_registration']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-left py-3 px-4">
                                    <?php if (!empty($driver['license_number'])): ?>
                                        <span class="text-xs font-mono bg-gray-100 px-2 py-1 rounded"><?= esc($driver['license_number']) ?></span>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400">Not provided</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-left py-3 px-4">
                                    <?php
                                    $statusClass = '';
                                    $statusText = $driver['status'] ?? 'Unknown';
                                    switch ($statusText) {
                                        case 'Active':
                                            $statusClass = 'bg-green-100 text-green-800';
                                            break;
                                        case 'Inactive':
                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'Dormant':
                                            $statusClass = 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            $statusClass = 'bg-gray-100 text-gray-800';
                                    }
                                    ?>
                                    <span class="<?= $statusClass ?> py-1 px-3 rounded-full text-xs font-medium"><?= esc($statusText) ?></span>
                                    <?php if (!empty($driver['last_location_update'])): ?>
                                        <div class="text-xs text-gray-500 mt-1">
                                            Last seen: <?= date('M j, Y', strtotime($driver['last_location_update'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-left py-3 px-4 text-sm text-gray-500">
                                    <?= date('M j, Y', strtotime($driver['created_at'])) ?>
                                </td>
                                <td class="text-left py-3 px-4">
                                    <div class="flex space-x-2">
                                        <a href="#" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">View</a>
                                        <a href="<?= site_url('admin/drivers/edit/' . $driver['id']) ?>" class="text-blue-600 hover:text-blue-900 text-sm font-medium">Edit</a>
                                        <a href="<?= site_url('admin/drivers/delete/' . $driver['id']) ?>" class="text-red-600 hover:text-red-900 text-sm font-medium"
                                           onclick="return confirm('Are you sure you want to delete this driver?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-8 text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <p class="text-lg font-medium">No drivers found</p>
                                    <?php if (!empty($search) || !empty($selectedVehicleType) || !empty($selectedStatus)): ?>
                                        <p class="text-sm text-gray-400 mt-1">Try adjusting your search criteria</p>
                                    <?php else: ?>
                                        <p class="text-sm text-gray-400 mt-1">No drivers have registered yet</p>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="mt-6 flex items-center justify-between border-t border-gray-200 pt-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    <!-- Mobile pagination -->
                    <?php if ($currentPage > 1): ?>
                        <a href="<?= site_url('admin/drivers/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>"
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </a>
                    <?php endif; ?>
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="<?= site_url('admin/drivers/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>"
                           class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing page <span class="font-medium"><?= $currentPage ?></span> of <span class="font-medium"><?= $totalPages ?></span>
                            (<span class="font-medium"><?= $totalDrivers ?></span> total drivers)
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <!-- Previous Page Link -->
                            <?php if ($currentPage > 1): ?>
                                <a href="<?= site_url('admin/drivers/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>"
                                   class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Previous</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            <?php endif; ?>

                            <!-- Page Numbers -->
                            <?php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);

                            if ($startPage > 1): ?>
                                <a href="<?= site_url('admin/drivers/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>"
                                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>
                                <?php if ($startPage > 2): ?>
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <?php if ($i == $currentPage): ?>
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-indigo-50 text-sm font-medium text-indigo-600"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="<?= site_url('admin/drivers/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                                <?php endif; ?>
                                <a href="<?= site_url('admin/drivers/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>"
                                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"><?= $totalPages ?></a>
                            <?php endif; ?>

                            <!-- Next Page Link -->
                            <?php if ($currentPage < $totalPages): ?>
                                <a href="<?= site_url('admin/drivers/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>"
                                   class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Next</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Auto-submit form when filters change
document.getElementById('vehicle_type').addEventListener('change', function() {
    this.form.submit();
});

document.getElementById('status').addEventListener('change', function() {
    this.form.submit();
});

// Add keyboard shortcut for search (Ctrl+K or Cmd+K)
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.getElementById('search').focus();
    }
});

// Clear search when Escape is pressed in search field
document.getElementById('search').addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        this.value = '';
        this.form.submit();
    }
});

// Add loading state to search button
document.querySelector('form').addEventListener('submit', function() {
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Searching...';
    submitBtn.disabled = true;

    // Re-enable after a short delay in case of quick response
    setTimeout(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }, 2000);
});

// Add tooltips for status indicators
document.querySelectorAll('[data-tooltip]').forEach(function(element) {
    element.addEventListener('mouseenter', function() {
        // Simple tooltip implementation
        const tooltip = document.createElement('div');
        tooltip.className = 'absolute z-10 px-2 py-1 text-xs text-white bg-gray-900 rounded shadow-lg';
        tooltip.textContent = this.getAttribute('data-tooltip');
        document.body.appendChild(tooltip);

        const rect = this.getBoundingClientRect();
        tooltip.style.left = rect.left + 'px';
        tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';

        this._tooltip = tooltip;
    });

    element.addEventListener('mouseleave', function() {
        if (this._tooltip) {
            document.body.removeChild(this._tooltip);
            this._tooltip = null;
        }
    });
});
</script>

<?= view('admin/templates/footer') ?>
