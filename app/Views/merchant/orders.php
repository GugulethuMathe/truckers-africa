<?= view('merchant/templates/header', ['page_title' => $page_title]) ?>

<div class="container mx-auto px-4 lg:px-6 py-6 lg:py-8">

    <?= view('merchant/components/notifications') ?>

    <h1 class="text-lg lg:text-2xl font-bold text-gray-800 mb-6">Manage Your Orders</h1>

    <?php if (!empty($orders)): ?>
        <!-- Desktop Table View -->
        <div class="hidden lg:block bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-xs lg:text-sm">Order ID</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-xs lg:text-sm">Listing</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-xs lg:text-sm">Driver</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-xs lg:text-sm">Date</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-xs lg:text-sm">ETA</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-xs lg:text-sm">Status</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-xs lg:text-sm">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="text-left py-3 px-4">#<?= esc($order['id']) ?></td>
                                <td class="text-left py-3 px-4">
                                    <?= esc($order['listing_title']) ?>
                                    <?php if ($order['item_count'] > 1): ?>
                                        <span class="text-xs text-gray-500">(<?= $order['item_count'] ?> items)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-left py-3 px-4"><?= esc($order['driver_name'] ?? 'Unknown Driver') ?></td>
                                <td class="text-left py-3 px-4"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                <td class="text-left py-3 px-4">
                                    <?php if (!empty($order['estimated_arrival'])): ?>
                                        <div><?= date('M d', strtotime($order['estimated_arrival'])) ?></div>
                                        <div class="text-xs text-gray-500"><?= date('h:i A', strtotime($order['estimated_arrival'])) ?></div>
                                    <?php else: ?>
                                        <span class="text-gray-400">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-left py-3 px-4">
                                    <?php 
                                        $status_classes = [
                                            'accepted' => 'bg-green-200 text-green-800',
                                            'pending' => 'bg-yellow-200 text-yellow-800',
                                            'rejected' => 'bg-red-200 text-red-800',
                                            'completed' => 'bg-blue-200 text-blue-800',
                                            'cancelled' => 'bg-gray-200 text-gray-800',
                                            'processing' => 'bg-purple-200 text-purple-800',
                                        ];
                                        $class = $status_classes[$order['order_status']] ?? 'bg-gray-200 text-gray-800';
                                    ?>
                                    <span class="<?= $class ?> py-1 px-3 rounded-full text-xs font-semibold"><?= esc(ucfirst($order['order_status'])) ?></span>
                                </td>
                                <td class="text-left py-3 px-4">
                                    <a href="<?= site_url('merchant/orders/view/' . $order['id']) ?>" class="text-blue-600 hover:underline">View Order</a>
                                    <?php if ($order['order_status'] === 'pending'): ?>
                                        <a href="<?= site_url('order/accept/' . $order['id']) ?>" class="text-green-600 hover:underline ml-4">Accept</a>
                                        <a href="<?= site_url('order/reject/' . $order['id']) ?>" class="text-red-600 hover:underline ml-4">Reject</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="lg:hidden space-y-4">
            <?php foreach ($orders as $order): ?>
                <div class="bg-white rounded-lg shadow-md p-4 border border-gray-200">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="text-sm lg:text-base font-semibold text-gray-900">Order #<?= esc($order['id']) ?></h3>
                            <p class="text-xs lg:text-sm text-gray-600"><?= esc($order['listing_title']) ?></p>
                        </div>
                        <div class="text-right">
                            <?php
                                $status_classes = [
                                    'pending' => 'bg-yellow-200 text-yellow-800',
                                    'accepted' => 'bg-green-200 text-green-800',
                                    'rejected' => 'bg-red-200 text-red-800',
                                    'completed' => 'bg-blue-200 text-blue-800'
                                ];
                                $class = $status_classes[$order['order_status']] ?? 'bg-gray-200 text-gray-800';
                            ?>
                            <span class="<?= $class ?> py-1 px-2 rounded-full text-xs font-semibold"><?= esc(ucfirst($order['order_status'])) ?></span>
                        </div>
                    </div>

                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Driver:</span>
                            <span class="font-medium"><?= esc($order['driver_name'] ?? 'Unknown Driver') ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Date:</span>
                            <span><?= date('M j, Y', strtotime($order['created_at'])) ?></span>
                        </div>
                        <?php if (!empty($order['estimated_arrival'])): ?>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">ETA:</span>
                                <span class="font-medium text-blue-600"><?= date('M j, h:i A', strtotime($order['estimated_arrival'])) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Items:</span>
                            <span><?= esc($order['item_count']) ?> item(s)</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="<?= site_url('merchant/orders/view/' . $order['id']) ?>" class="flex-1 text-center bg-blue-600 text-white py-2 px-3 rounded text-sm font-medium hover:bg-blue-700 transition-colors">
                            View Order
                        </a>
                        <?php if ($order['order_status'] === 'pending'): ?>
                            <a href="<?= site_url('order/accept/' . $order['id']) ?>" class="flex-1 text-center bg-green-600 text-white py-2 px-3 rounded text-sm font-medium hover:bg-green-700 transition-colors">
                                Accept
                            </a>
                            <a href="<?= site_url('order/reject/' . $order['id']) ?>" class="flex-1 text-center bg-red-600 text-white py-2 px-3 rounded text-sm font-medium hover:bg-red-700 transition-colors">
                                Reject
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
            <div class="mt-6 flex justify-center">
                <nav class="flex items-center space-x-2">
                    <?= $pager->links('orders', 'merchant_pagination') ?>
                </nav>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <div class="mb-4">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No orders found</h3>
            <p class="text-gray-500 text-sm lg:text-base">You don't have any orders yet. Orders will appear here when customers place them.</p>
        </div>
    <?php endif; ?>
</div>

<?= view('merchant/templates/footer') ?>
