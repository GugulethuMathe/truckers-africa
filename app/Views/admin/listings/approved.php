<?= view('admin/templates/header', ['page_title' => 'Approved Listings']) ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Approved Service Listings</h1>
        <div class="flex space-x-2">
            <a href="#" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Export CSV</a>
            <a href="<?= site_url('admin/listings/pending') ?>" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700">View Pending</a>
            <a href="<?= site_url('admin/listings/all') ?>" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">View All</a>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <form method="GET" action="<?= site_url('admin/listings/approved') ?>" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-64">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Listings</label>
                <input type="text"
                       id="search"
                       name="search"
                       value="<?= esc($search) ?>"
                       placeholder="Search by title, description, or merchant name..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="min-w-48">
                <label for="merchant_id" class="block text-sm font-medium text-gray-700 mb-2">Merchant</label>
                <select id="merchant_id"
                        name="merchant_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Merchants</option>
                    <?php foreach ($merchants as $merchant): ?>
                        <option value="<?= esc($merchant['id']) ?>" <?= $selectedMerchantId == $merchant['id'] ? 'selected' : '' ?>>
                            <?= esc($merchant['business_name']) ?> (<?= esc($merchant['owner_name']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Search
                </button>
                <a href="<?= site_url('admin/listings/approved') ?>" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Approved</p>
                    <p class="text-2xl font-semibold text-gray-900"><?= number_format($totalListings) ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">This Month</p>
                    <p class="text-2xl font-semibold text-gray-900">-</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Revenue Impact</p>
                    <p class="text-2xl font-semibold text-gray-900">-</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Listings Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Approved Listings (<?= number_format($totalListings) ?> total)</h3>
        </div>
        
        <?php if (empty($listings)): ?>
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No approved listings found</h3>
                <p class="mt-1 text-sm text-gray-500">No listings match your current search criteria.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Merchant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approved Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($listings as $listing): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if (!empty($listing['image_url'])): ?>
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full object-cover" src="<?= base_url('uploads/listings/' . $listing['image_url']) ?>" alt="">
                                            </div>
                                        <?php else: ?>
                                            <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?= esc($listing['title']) ?></div>
                                            <div class="text-sm text-gray-500"><?= esc(substr($listing['description'], 0, 50)) ?>...</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?= esc($listing['business_name']) ?></div>
                                    <div class="text-sm text-gray-500"><?= esc($listing['owner_name']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Service
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php if ($listing['price'] > 0): ?>
                                        <div><?= number_format($listing['price'], 2) ?></div>
                                        <div class="text-xs text-gray-500"><?= esc($listing['currency_code'] ?? 'USD') ?></div>
                                    <?php else: ?>
                                        <span class="text-gray-500">Contact for price</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M j, Y', strtotime($listing['updated_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Approved
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="<?= site_url('admin/listings/view/' . $listing['id']) ?>" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        <a href="#" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                                        <a href="<?= site_url('admin/listings/reject/' . $listing['id']) ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to reject this listing?')">Reject</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <?php if ($currentPage > 1): ?>
                            <a href="<?= site_url('admin/listings/approved?page=' . ($currentPage - 1) . ($search ? '&search=' . urlencode($search) : '') . ($selectedMerchantId ? '&merchant_id=' . $selectedMerchantId : '')) ?>" 
                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="<?= site_url('admin/listings/approved?page=' . ($currentPage + 1) . ($search ? '&search=' . urlencode($search) : '') . ($selectedMerchantId ? '&merchant_id=' . $selectedMerchantId : '')) ?>" 
                               class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing <span class="font-medium"><?= ($currentPage - 1) * $perPage + 1 ?></span> to 
                                <span class="font-medium"><?= min($currentPage * $perPage, $totalListings) ?></span> of 
                                <span class="font-medium"><?= number_format($totalListings) ?></span> results
                            </p>
                        </div>
                        <div>
                            <?= $pager->links() ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= view('admin/templates/footer') ?>
