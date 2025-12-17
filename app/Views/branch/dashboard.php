<?= view('branch/templates/header', ['page_title' => $page_title]) ?>

<div class="px-6 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Subscription Warning -->
        <?= view('components/subscription_warning') ?>

        <!-- Merchant Subscription Payment Required Alert -->
        <?php if (isset($merchant_subscription['status']) && in_array($merchant_subscription['status'], ['new', 'trial_pending'])): ?>
        <div class="rounded-md bg-orange-50 p-4 mb-6 border-2 border-orange-400">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-semibold text-orange-800 mb-2">
                        🔒 Merchant Payment Required
                    </h3>
                    <p class="text-sm text-orange-700 mb-3">
                        <?php if ($merchant_subscription['status'] === 'trial_pending'): ?>
                            Your merchant account needs to provide payment method to start the free trial and access features. Please contact your business owner to complete the payment setup.
                        <?php else: ?>
                            Your merchant account needs to complete payment to activate the subscription. Please contact your business owner to complete the payment.
                        <?php endif; ?>
                    </p>
                    <div class="text-xs text-orange-600 font-medium">
                        <i class="fas fa-info-circle mr-1"></i>
                        Contact your business owner to resolve this issue
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Welcome Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Welcome, <?= esc($branch_user['full_name'] ?? 'Branch Manager') ?></h1>
            <p class="text-gray-600 mt-2">
                <i class="fas fa-map-marker-alt mr-2"></i>
                <?= esc($branch_user['location_name']) ?> - <?= esc($branch_user['business_name']) ?>
            </p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Orders -->
            <a href="<?= base_url('branch/orders') ?>" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow cursor-pointer">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Orders</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= $stats['total_orders'] ?></p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-shopping-cart text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </a>

            <!-- Pending Orders -->
            <a href="<?= base_url('branch/orders?status=pending') ?>" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow cursor-pointer">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Pending Orders</p>
                        <p class="text-3xl font-bold text-orange-600 mt-2"><?= $stats['pending_orders'] ?></p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-3">
                        <i class="fas fa-clock text-orange-600 text-2xl"></i>
                    </div>
                </div>
            </a>

            <!-- Completed Orders -->
            <a href="<?= base_url('branch/orders?status=completed') ?>" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow cursor-pointer">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Completed</p>
                        <p class="text-3xl font-bold text-green-600 mt-2"><?= $stats['completed_orders'] ?></p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                </div>
            </a>

            <!-- Total Revenue (Hidden - currencies may differ) -->
            <div class="bg-white rounded-lg shadow-md p-6 hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Revenue</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= number_format($stats['total_revenue'], 2) ?></p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-dollar-sign text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <a href="<?= base_url('branch/orders') ?>" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="bg-blue-100 rounded-full p-3 mr-4">
                        <i class="fas fa-list text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">View All Orders</h3>
                        <p class="text-sm text-gray-600">Manage your orders</p>
                    </div>
                </div>
            </a>

            <a href="<?= base_url('branch/listing-requests') ?>" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="bg-purple-100 rounded-full p-3 mr-4">
                        <i class="fas fa-plus-circle text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Listing Requests</h3>
                        <p class="text-sm text-gray-600">Request new services</p>
                    </div>
                </div>
            </a>

            <a href="<?= base_url('branch/profile') ?>" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="bg-green-100 rounded-full p-3 mr-4">
                        <i class="fas fa-user-cog text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Branch Profile</h3>
                        <p class="text-sm text-gray-600">Update your details</p>
                    </div>
                </div>
            </a>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-orange-100 rounded-full p-3 mr-4">
                        <i class="fas fa-box text-orange-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Service Listings</h3>
                        <p class="text-sm text-gray-600"><?= $listings_count ?> active listings</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Recent Orders</h2>
                <a href="<?= base_url('branch/orders') ?>" class="text-green-600 hover:text-green-700 text-sm font-medium">
                    View All <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <?php if (!empty($recent_orders)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($recent_orders as $order): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= esc($order['booking_reference']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?= date('M d, Y', strtotime($order['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?= count($order['items']) ?> item(s)
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'accepted' => 'bg-blue-100 text-blue-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800'
                                        ];
                                        $statusColor = $statusColors[$order['order_status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusColor ?>">
                                            <?= ucfirst($order['order_status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="<?= base_url('branch/orders/view/' . $order['id']) ?>"
                                           class="text-green-600 hover:text-green-900 font-medium">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                    <p class="text-gray-500">No orders yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= view('branch/templates/footer') ?>

