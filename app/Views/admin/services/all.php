<?= view('admin/templates/header', ['page_title' => 'All Services']) ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">All Services</h1>
        <a href="<?= site_url('admin/services/add') ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Add New Service</a>
    </div>

    <!-- Search and Filter Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <form method="GET" action="<?= site_url('admin/services/all') ?>" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-64">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Services</label>
                <input type="text"
                       id="search"
                       name="search"
                       value="<?= esc($search) ?>"
                       placeholder="Search by service name or category..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="min-w-48">
                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Filter by Category</label>
                <select id="category"
                        name="category"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= esc($cat['id']) ?>" <?= $selectedCategory == $cat['id'] ? 'selected' : '' ?>>
                            <?= esc($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Search
                </button>
                <a href="<?= site_url('admin/services/all') ?>" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Results Summary -->
    <div class="mb-4">
        <p class="text-gray-600">
            Showing <?= count($services) ?> of <?= $totalServices ?> services
            <?php if (!empty($search)): ?>
                for "<strong><?= esc($search) ?></strong>"
            <?php endif; ?>
            <?php if (!empty($selectedCategory)): ?>
                <?php
                $selectedCategoryName = '';
                foreach ($categories as $cat) {
                    if ($cat['id'] == $selectedCategory) {
                        $selectedCategoryName = $cat['name'];
                        break;
                    }
                }
                ?>
                in category "<strong><?= esc($selectedCategoryName) ?></strong>"
            <?php endif; ?>
        </p>
    </div>

    <!-- Services Table -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">ID</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Service Name</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Category</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (!empty($services)): ?>
                        <?php foreach ($services as $service): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="text-left py-3 px-4 text-sm"><?= esc($service['id']) ?></td>
                                <td class="text-left py-3 px-4 font-medium"><?= esc($service['name']) ?></td>
                                <td class="text-left py-3 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?= esc($service['category_name'] ?? 'Uncategorized') ?>
                                    </span>
                                </td>
                                <td class="text-left py-3 px-4">
                                    <div class="flex space-x-2">
                                        <a href="#" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Edit</a>
                                        <a href="#" class="text-red-600 hover:text-red-900 text-sm font-medium"
                                           onclick="return confirm('Are you sure you want to delete this service?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-8 text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-lg font-medium">No services found</p>
                                    <?php if (!empty($search) || !empty($selectedCategory)): ?>
                                        <p class="text-sm text-gray-400 mt-1">Try adjusting your search criteria</p>
                                    <?php else: ?>
                                        <p class="text-sm text-gray-400 mt-1">Get started by adding your first service</p>
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
                        <a href="<?= site_url('admin/services/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>"
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </a>
                    <?php endif; ?>
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="<?= site_url('admin/services/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>"
                           class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing page <span class="font-medium"><?= $currentPage ?></span> of <span class="font-medium"><?= $totalPages ?></span>
                            (<span class="font-medium"><?= $totalServices ?></span> total services)
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <!-- Previous Page Link -->
                            <?php if ($currentPage > 1): ?>
                                <a href="<?= site_url('admin/services/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>"
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
                                <a href="<?= site_url('admin/services/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>"
                                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>
                                <?php if ($startPage > 2): ?>
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <?php if ($i == $currentPage): ?>
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-indigo-50 text-sm font-medium text-indigo-600"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="<?= site_url('admin/services/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                                <?php endif; ?>
                                <a href="<?= site_url('admin/services/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>"
                                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"><?= $totalPages ?></a>
                            <?php endif; ?>

                            <!-- Next Page Link -->
                            <?php if ($currentPage < $totalPages): ?>
                                <a href="<?= site_url('admin/services/all') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>"
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
// Auto-submit form when category filter changes
document.getElementById('category').addEventListener('change', function() {
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
