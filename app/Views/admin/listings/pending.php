<?= view('admin/templates/header', ['page_title' => 'Pending Listings']) ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Pending Service Listings</h1>
        <div class="flex space-x-2">
            <a href="<?= site_url('admin/listings/all') ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">View All</a>
            <a href="<?= site_url('admin/listings/approved') ?>" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">View Approved</a>
        </div>
    </div>

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

    <!-- Search and Filter Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <form method="GET" action="<?= site_url('admin/listings/pending') ?>" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-64">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Pending Listings</label>
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
                <a href="<?= site_url('admin/listings/pending') ?>" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Results Summary -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-6">
        <div class="flex justify-between items-center">
            <div class="text-sm text-gray-600">
                Showing <?= count($listings) ?> of <?= $totalListings ?> pending listings
                <?php if (!empty($search) || !empty($selectedMerchantId)): ?>
                    (filtered)
                <?php endif; ?>
            </div>
            <div class="text-sm text-gray-600">
                Page <?= $currentPage ?> of <?= $totalPages ?>
            </div>
        </div>
    </div>

    <!-- Pending Listings Table -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">ID</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Title</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Merchant</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Price</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Status</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Submitted</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (!empty($listings)): ?>
                        <?php foreach ($listings as $listing): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="text-left py-3 px-4 text-sm font-mono"><?= esc($listing['id']) ?></td>
                                <td class="text-left py-3 px-4">
                                    <div class="max-w-xs">
                                        <div class="font-medium text-gray-900 truncate"><?= esc($listing['title']) ?></div>
                                        <?php if (!empty($listing['description'])): ?>
                                            <div class="text-sm text-gray-500 truncate"><?= esc(substr($listing['description'], 0, 50)) ?>...</div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-left py-3 px-4">
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900"><?= esc($listing['business_name']) ?></div>
                                        <div class="text-gray-500"><?= esc($listing['owner_name']) ?></div>
                                    </div>
                                </td>
                                <td class="text-left py-3 px-4">
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900"><?= number_format($listing['price'], 2) ?></div>
                                        <div class="text-xs text-gray-500"><?= esc($listing['currency_code'] ?? 'USD') ?></div>
                                    </div>
                                </td>
                                <td class="text-left py-3 px-4">
                                    <span class="bg-yellow-200 text-yellow-800 py-1 px-3 rounded-full text-xs font-medium">Pending</span>
                                </td>
                                <td class="text-left py-3 px-4 text-sm text-gray-500">
                                    <?= date('M j, Y', strtotime($listing['created_at'])) ?>
                                </td>
                                <td class="text-left py-3 px-4">
                                    <div class="flex space-x-2">
                                        <a href="<?= site_url('admin/listings/approve/' . $listing['id']) ?>"
                                           class="bg-green-500 text-white px-3 py-1 rounded text-xs hover:bg-green-600 transition"
                                           onclick="return confirm('Are you sure you want to approve this listing?')">
                                            Approve
                                        </a>
                                        <a href="<?= site_url('admin/listings/reject/' . $listing['id']) ?>"
                                           class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600 transition"
                                           onclick="return confirm('Are you sure you want to reject this listing?')">
                                            Reject
                                        </a>
                                        <a href="<?= site_url('admin/listings/view/' . $listing['id']) ?>" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">
                                <?php if (!empty($search) || !empty($selectedMerchantId)): ?>
                                    No pending listings found matching your search criteria.
                                <?php else: ?>
                                    No listings are currently pending approval.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mt-6 rounded-lg shadow-md">
            <div class="flex-1 flex justify-between sm:hidden">
                <?php if ($currentPage > 1): ?>
                    <a href="<?= site_url('admin/listings/pending') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>"
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Previous
                    </a>
                <?php endif; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= site_url('admin/listings/pending') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>"
                       class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Next
                    </a>
                <?php endif; ?>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium"><?= ($currentPage - 1) * $perPage + 1 ?></span>
                        to
                        <span class="font-medium"><?= min($currentPage * $perPage, $totalListings) ?></span>
                        of
                        <span class="font-medium"><?= $totalListings ?></span>
                        results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php if ($currentPage > 1): ?>
                            <a href="<?= site_url('admin/listings/pending') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>"
                               class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Previous</span>
                                <i class="ri-arrow-left-s-line"></i>
                            </a>
                        <?php endif; ?>

                        <?php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);
                        ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <?php if ($i == $currentPage): ?>
                                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-indigo-50 text-sm font-medium text-indigo-600"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= site_url('admin/listings/pending') ?>?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <a href="<?= site_url('admin/listings/pending') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>"
                               class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Next</span>
                                <i class="ri-arrow-right-s-line"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= view('admin/templates/footer') ?>
