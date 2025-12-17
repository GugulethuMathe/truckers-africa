<?= view('merchant/templates/header', ['page_title' => $page_title]) ?>

<div class="px-6 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Branch Listing Requests</h1>
            <p class="text-gray-600 mt-2">Review and manage listing requests from your branches</p>
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
                        <p class="text-gray-500 text-sm font-medium">Pending Review</p>
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
                        <p class="text-3xl font-bold text-blue-600 mt-2"><?= $stats['approved'] ?></p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-check-circle text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Converted</p>
                        <p class="text-3xl font-bold text-green-600 mt-2"><?= $stats['converted'] ?></p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-check-double text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <a href="<?= base_url('merchant/listing-requests') ?>" 
                       class="<?= !$current_status ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                        All (<?= $stats['total'] ?>)
                    </a>
                    <a href="<?= base_url('merchant/listing-requests?status=pending') ?>" 
                       class="<?= $current_status === 'pending' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                        Pending (<?= $stats['pending'] ?>)
                    </a>
                    <a href="<?= base_url('merchant/listing-requests?status=approved') ?>" 
                       class="<?= $current_status === 'approved' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                        Approved (<?= $stats['approved'] ?>)
                    </a>
                    <a href="<?= base_url('merchant/listing-requests?status=rejected') ?>" 
                       class="<?= $current_status === 'rejected' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                        Rejected (<?= $stats['rejected'] ?>)
                    </a>
                    <a href="<?= base_url('merchant/listing-requests?status=converted') ?>" 
                       class="<?= $current_status === 'converted' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                        Converted (<?= $stats['converted'] ?>)
                    </a>
                </nav>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
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
                                            <?= esc(substr($request['description'] ?? '', 0, 50)) ?><?= strlen($request['description'] ?? '') > 50 ? '...' : '' ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= esc($request['location_name']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= esc($request['requester_name']) ?></div>
                                        <div class="text-xs text-gray-500"><?= esc($request['requester_email']) ?></div>
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
                                        <a href="<?= base_url('merchant/listing-requests/view/' . $request['id']) ?>" 
                                           class="text-green-600 hover:text-green-900">
                                            <i class="fas fa-eye mr-1"></i>Review
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
                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                    <?php if ($current_status): ?>
                        No <?= ucfirst($current_status) ?> Requests
                    <?php else: ?>
                        No Requests Yet
                    <?php endif; ?>
                </h3>
                <p class="text-gray-600">
                    <?php if ($current_status): ?>
                        There are no <?= $current_status ?> requests at the moment.
                    <?php else: ?>
                        Your branches haven't submitted any listing requests yet.
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-medium mb-1">About Listing Requests</p>
                    <p>Branch managers can submit requests for new services they'd like to offer. Review each request, approve or reject it, and convert approved requests into actual listings. This helps you maintain quality control while empowering your branches.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('merchant/templates/footer') ?>

