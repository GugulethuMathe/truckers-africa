<?= view('admin/templates/header', ['page_title' => 'All Merchants']) ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">All Registered Merchants</h1>
        <div class="flex space-x-2">
            <a href="#" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Export CSV</a>
            <a href="<?= site_url('admin/merchants/add') ?>" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Add Merchant</a>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <form method="GET" action="<?= site_url('admin/merchants/all') ?>" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-64">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Merchants</label>
                <input type="text"
                       id="search"
                       name="search"
                       value="<?= esc($search) ?>"
                       placeholder="Search by business name, owner, email, phone, or address..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="min-w-48">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Verification Status</label>
                <select id="status"
                        name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Status</option>
                    <option value="pending" <?= $selectedStatus == 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= $selectedStatus == 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= $selectedStatus == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    <option value="suspended" <?= $selectedStatus == 'suspended' ? 'selected' : '' ?>>Suspended</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Search
                </button>
                <a href="<?= site_url('admin/merchants/all') ?>" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p class="font-bold">Success</p>
            <p><?= session()->getFlashdata('success') ?></p>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p class="font-bold">Error</p>
            <p><?= session()->getFlashdata('error') ?></p>
        </div>
    <?php endif; ?>

    <!-- Results Summary -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-6">
        <div class="flex justify-between items-center">
            <div class="text-sm text-gray-600">
                Showing <?= count($merchants) ?> of <?= $totalMerchants ?> merchants
                <?php if (!empty($search) || !empty($selectedStatus)): ?>
                    (filtered)
                <?php endif; ?>
            </div>
            <div class="text-sm text-gray-600">
                Page <?= $currentPage ?> of <?= $totalPages ?>
            </div>
        </div>
    </div>

    <!-- Merchants Table -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">ID</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Business Name</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Owner</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Contact</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Status</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Visible</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Created</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (!empty($merchants)): ?>
                        <?php foreach ($merchants as $merchant): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="text-left py-3 px-4 text-sm font-mono"><?= esc($merchant['id']) ?></td>
                                <td class="text-left py-3 px-4">
                                    <div class="flex items-center space-x-3">
                                        <?php if (!empty($merchant['business_image_url'])): ?>
                                            <img src="<?= esc($merchant['business_image_url']) ?>" alt="Business" class="w-10 h-10 rounded-lg object-cover">
                                        <?php else: ?>
                                            <div class="w-10 h-10 bg-gray-300 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <span class="text-sm font-bold text-gray-600"><?= strtoupper(substr($merchant['business_name'], 0, 2)) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="max-w-xs">
                                            <div class="font-medium text-gray-900"><?= esc($merchant['business_name']) ?></div>
                                            <?php if (!empty($merchant['main_service'])): ?>
                                                <div class="text-xs text-gray-500"><?= esc($merchant['main_service']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-left py-3 px-4">
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900"><?= esc($merchant['owner_name']) ?></div>
                                        <div class="text-gray-500 text-xs"><?= esc($merchant['email']) ?></div>
                                    </div>
                                </td>
                                <td class="text-left py-3 px-4">
                                    <div class="text-sm text-gray-600">
                                        <?php if (!empty($merchant['business_contact_number'])): ?>
                                            <div><?= esc($merchant['business_contact_number']) ?></div>
                                        <?php else: ?>
                                            <span class="text-gray-400">N/A</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-left py-3 px-4">
                                    <?php
                                        $status = esc($merchant['verification_status']);
                                        $status_classes = [
                                            'approved' => 'bg-green-200 text-green-800',
                                            'pending' => 'bg-yellow-200 text-yellow-800',
                                            'rejected' => 'bg-red-200 text-red-800',
                                            'suspended' => 'bg-orange-200 text-orange-800'
                                        ];
                                        $class = $status_classes[$status] ?? 'bg-gray-200 text-gray-800';
                                    ?>
                                    <span class="<?= $class ?> py-1 px-3 rounded-full text-xs font-medium"><?= ucfirst($status) ?></span>
                                </td>
                                <td class="text-left py-3 px-4">
                                    <?php if ($merchant['is_visible']): ?>
                                        <span class="bg-green-200 text-green-800 py-1 px-3 rounded-full text-xs font-medium">Yes</span>
                                    <?php else: ?>
                                        <span class="bg-gray-200 text-gray-800 py-1 px-3 rounded-full text-xs font-medium">No</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-left py-3 px-4 text-sm text-gray-500">
                                    <?= !empty($merchant['created_at']) ? date('M j, Y', strtotime($merchant['created_at'])) : 'N/A' ?>
                                </td>
                                <td class="text-left py-3 px-4">
                                    <div class="flex flex-col space-y-1">
                                        <?php if ($merchant['verification_status'] === 'pending'): ?>
                                            <a href="<?= site_url('admin/merchants/approve/' . $merchant['id']) ?>"
                                               class="bg-green-500 text-white px-3 py-1 rounded text-xs hover:bg-green-600 transition text-center"
                                               onclick="return confirm('Are you sure you want to approve this merchant?')">
                                                Approve
                                            </a>
                                            <a href="<?= site_url('admin/merchants/reject/' . $merchant['id']) ?>"
                                               class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600 transition text-center"
                                               onclick="return confirm('Are you sure you want to reject this merchant?')">
                                                Reject
                                            </a>
                                        <?php elseif ($merchant['verification_status'] === 'approved'): ?>
                                            <a href="<?= site_url('admin/merchants/suspend/' . $merchant['id']) ?>"
                                               class="bg-orange-500 text-white px-3 py-1 rounded text-xs hover:bg-orange-600 transition text-center"
                                               onclick="return confirm('Are you sure you want to suspend this merchant?')">
                                                Suspend
                                            </a>
                                        <?php elseif ($merchant['verification_status'] === 'suspended'): ?>
                                            <a href="<?= site_url('admin/merchants/suspend/' . $merchant['id']) ?>"
                                               class="bg-green-500 text-white px-3 py-1 rounded text-xs hover:bg-green-600 transition text-center"
                                               onclick="return confirm('Are you sure you want to reactivate this merchant?')">
                                                Reactivate
                                            </a>
                                        <?php elseif ($merchant['verification_status'] === 'rejected'): ?>
                                            <a href="<?= site_url('admin/merchants/approve/' . $merchant['id']) ?>"
                                               class="bg-green-500 text-white px-3 py-1 rounded text-xs hover:bg-green-600 transition text-center"
                                               onclick="return confirm('Are you sure you want to approve this merchant?')">
                                                Approve
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= site_url('admin/merchants/view/' . $merchant['id']) ?>"
                                           class="text-indigo-600 hover:text-indigo-900 text-xs text-center">View</a>
                                        <a href="<?= site_url('admin/merchants/edit/' . $merchant['id']) ?>"
                                           class="text-blue-600 hover:text-blue-900 text-xs text-center">Edit</a>
                                        <a href="<?= site_url('admin/merchants/disable/' . $merchant['id']) ?>"
                                           class="text-orange-600 hover:text-orange-900 text-xs text-center"
                                           onclick="return confirm('Are you sure you want to disable this merchant? They will no longer be able to access the platform, but their data will be preserved.')">Disable</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-8 text-gray-500">
                                No merchants found.
                                <?php if (!empty($search) || !empty($selectedStatus)): ?>
                                    <a href="<?= site_url('admin/merchants/all') ?>" class="text-indigo-600 hover:text-indigo-900 ml-2">Clear filters</a>
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
        <div class="mt-8 flex items-center justify-between border-t border-gray-200 pt-6">
            <div class="flex-1 flex justify-between sm:hidden">
                <!-- Mobile pagination -->
                <?php if ($currentPage > 1): ?>
                    <a href="<?= site_url('admin/merchants/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>"
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Previous
                    </a>
                <?php endif; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= site_url('admin/merchants/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>"
                       class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Next
                    </a>
                <?php endif; ?>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing page <span class="font-medium"><?= $currentPage ?></span> of <span class="font-medium"><?= $totalPages ?></span>
                        (<span class="font-medium"><?= $totalMerchants ?></span> total merchants)
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <!-- Previous Page Link -->
                        <?php if ($currentPage > 1): ?>
                            <a href="<?= site_url('admin/merchants/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>"
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
                            <a href="<?= site_url('admin/merchants/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>"
                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>
                            <?php if ($startPage > 2): ?>
                                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <?php if ($i == $currentPage): ?>
                                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-indigo-50 text-sm font-medium text-indigo-600"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= site_url('admin/merchants/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($endPage < $totalPages): ?>
                            <?php if ($endPage < $totalPages - 1): ?>
                                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                            <?php endif; ?>
                            <a href="<?= site_url('admin/merchants/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>"
                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"><?= $totalPages ?></a>
                        <?php endif; ?>

                        <!-- Next Page Link -->
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="<?= site_url('admin/merchants/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>"
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

<script>
// Auto-submit form when status filter changes
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
</script>

<?= view('admin/templates/footer') ?>
