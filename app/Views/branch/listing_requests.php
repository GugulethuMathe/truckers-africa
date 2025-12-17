<?= view('branch/templates/header', ['page_title' => $page_title]) ?>

<div class="px-6 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Listing Requests</h1>
                <p class="text-gray-600 mt-2">Request new service listings for your branch</p>
            </div>
            <a href="<?= base_url('branch/listing-requests/new') ?>" 
               class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>New Request
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Requests</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= $stats['total'] ?></p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-list text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Pending</p>
                        <p class="text-3xl font-bold text-yellow-600 mt-2"><?= $stats['pending'] ?></p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Approved</p>
                        <p class="text-3xl font-bold text-green-600 mt-2"><?= $stats['approved'] + $stats['converted'] ?></p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Rejected</p>
                        <p class="text-3xl font-bold text-red-600 mt-2"><?= $stats['rejected'] ?></p>
                    </div>
                    <div class="bg-red-100 rounded-full p-3">
                        <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requests List -->
        <?php if (!empty($requests)): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($requests as $request): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900"><?= esc($request['title']) ?></div>
                                        <div class="text-xs text-gray-500">
                                            <?= esc(substr($request['description'] ?? '', 0, 60)) ?><?= strlen($request['description'] ?? '') > 60 ? '...' : '' ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($request['suggested_price']): ?>
                                            <div class="text-sm text-gray-900">
                                                <?= $request['currency_code'] ?> <?= number_format($request['suggested_price'], 2) ?>
                                            </div>
                                            <?php if ($request['unit']): ?>
                                                <div class="text-xs text-gray-500">per <?= esc($request['unit']) ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-sm text-gray-400">Not specified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= date('M d, Y', strtotime($request['created_at'])) ?></div>
                                        <div class="text-xs text-gray-500"><?= date('h:i A', strtotime($request['created_at'])) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'approved' => 'bg-blue-100 text-blue-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'converted' => 'bg-green-100 text-green-800'
                                        ];
                                        $statusColor = $statusColors[$request['status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusColor ?>">
                                            <?= ucfirst($request['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="<?= base_url('branch/listing-requests/view/' . $request['id']) ?>" 
                                           class="text-green-600 hover:text-green-900">
                                            <i class="fas fa-eye mr-1"></i>View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-clipboard-list text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Requests Yet</h3>
                <p class="text-gray-600 mb-6">You haven't submitted any listing requests</p>
                <a href="<?= base_url('branch/listing-requests/new') ?>" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Submit Your First Request
                </a>
            </div>
        <?php endif; ?>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-medium mb-1">About Listing Requests</p>
                    <p>Submit requests for new services you'd like to offer at your branch. The main merchant will review and approve your requests. Once approved, the listing will be created and assigned to your location.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('branch/templates/footer') ?>

