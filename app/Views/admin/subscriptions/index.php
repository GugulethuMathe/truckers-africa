<?= view('admin/templates/header', ['page_title' => 'All Subscriptions']) ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">All Subscriptions</h1>
        <div class="text-sm text-gray-600">
            Total: <?= $totalSubscriptions ?> subscription<?= $totalSubscriptions != 1 ? 's' : '' ?>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-6">
        <form method="get" action="<?= site_url('admin/subscriptions') ?>" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="search" class="block text-sm font-semibold mb-2">Search</label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="<?= esc($search) ?>" 
                       placeholder="Search by merchant or plan..." 
                       class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            
            <div>
                <label for="status" class="block text-sm font-semibold mb-2">Status</label>
                <select id="status" 
                        name="status" 
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="active" <?= $selectedStatus === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="trial" <?= $selectedStatus === 'trial' ? 'selected' : '' ?>>Trial</option>
                    <option value="suspended" <?= $selectedStatus === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    <option value="cancelled" <?= $selectedStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    <option value="expired" <?= $selectedStatus === 'expired' ? 'selected' : '' ?>>Expired</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 mr-2">
                    <i class="ri-search-line mr-1"></i>Filter
                </button>
                <a href="<?= site_url('admin/subscriptions') ?>" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Merchant</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Plan Type</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Status</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Current Period</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (!empty($subscriptions)): ?>
                        <?php foreach ($subscriptions as $subscription): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="text-left py-3 px-4"><?= esc($subscription['business_name'] ?? 'N/A') ?></td>
                                <td class="text-left py-3 px-4"><?= esc(ucfirst($subscription['plan_name'] ?? 'N/A')) ?></td>
                                <td class="text-left py-3 px-4">
                                    <?php
                                    $statusColors = [
                                        'active' => 'text-green-700 bg-green-100',
                                        'trial' => 'text-blue-700 bg-blue-100',
                                        'suspended' => 'text-yellow-700 bg-yellow-100',
                                        'cancelled' => 'text-red-700 bg-red-100',
                                        'expired' => 'text-gray-700 bg-gray-100'
                                    ];
                                    $statusColor = $statusColors[$subscription['status']] ?? 'text-gray-700 bg-gray-100';
                                    ?>
                                    <span class="px-2 py-1 text-xs font-semibold leading-tight <?= $statusColor ?> rounded-full">
                                        <?= esc(ucfirst($subscription['status'])) ?>
                                    </span>
                                </td>
                                <td class="text-left py-3 px-4">
                                    <?= date('M d, Y', strtotime($subscription['current_period_starts_at'])) ?> - 
                                    <?= date('M d, Y', strtotime($subscription['current_period_ends_at'])) ?>
                                </td>
                                <td class="text-left py-3 px-4">
                                    <a href="<?= site_url('admin/subscriptions/manage/' . $subscription['id']) ?>" class="text-indigo-600 hover:underline">Manage</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-500">
                                <i class="ri-inbox-line text-4xl mb-2"></i>
                                <p>No subscriptions found.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Info -->
        <?php if ($totalSubscriptions > 0): ?>
        <div class="mt-6 flex justify-between items-center">
            <div class="text-sm text-gray-600">
                Showing <?= ($currentPage - 1) * $perPage + 1 ?> to <?= min($currentPage * $perPage, $totalSubscriptions) ?> of <?= $totalSubscriptions ?> results
            </div>
            
            <?php if ($totalPages > 1): ?>
            
            <div class="flex space-x-2">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?= $currentPage - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($selectedStatus) ? '&status=' . urlencode($selectedStatus) : '' ?>" 
                       class="px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                        <i class="ri-arrow-left-s-line"></i> Previous
                    </a>
                <?php endif; ?>

                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                
                if ($startPage > 1): ?>
                    <a href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($selectedStatus) ? '&status=' . urlencode($selectedStatus) : '' ?>" 
                       class="px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">1</a>
                    <?php if ($startPage > 2): ?>
                        <span class="px-3 py-2">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($selectedStatus) ? '&status=' . urlencode($selectedStatus) : '' ?>" 
                       class="px-3 py-2 <?= $i === $currentPage ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?> rounded">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span class="px-3 py-2">...</span>
                    <?php endif; ?>
                    <a href="?page=<?= $totalPages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($selectedStatus) ? '&status=' . urlencode($selectedStatus) : '' ?>" 
                       class="px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"><?= $totalPages ?></a>
                <?php endif; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?= $currentPage + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($selectedStatus) ? '&status=' . urlencode($selectedStatus) : '' ?>" 
                       class="px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                        Next <i class="ri-arrow-right-s-line"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= view('admin/templates/footer') ?>
