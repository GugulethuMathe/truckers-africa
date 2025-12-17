<?php
// Helper function to get currency symbol
function getCurrencySymbolBranch($currency) {
    $symbols = [
        'ZAR' => 'R',
        'USD' => '$',
        'BWP' => 'P',
        'NAD' => 'N$',
        'ZMW' => 'ZK',
        'KES' => 'KSh',
        'TZS' => 'TSh',
        'UGX' => 'USh',
        'EUR' => '€',
        'GBP' => '£',
        'NGN' => '₦',
        'GHS' => 'GH₵',
        'MZN' => 'MT',
        'ZWL' => 'Z$'
    ];
    return $symbols[$currency] ?? $currency;
}
?>
<?= view('branch/templates/header', ['page_title' => $page_title]) ?>

<div class="px-6 py-8">
    <div class="max-w-5xl mx-auto">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="<?= base_url('branch/orders') ?>" class="text-green-600 hover:text-green-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back to Orders
            </a>
        </div>

        <!-- Order Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Order #<?= esc($order['booking_reference']) ?></h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Placed on <?= date('F d, Y \a\t h:i A', strtotime($order['created_at'])) ?>
                    </p>
                </div>
                <div>
                    <?php
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'accepted' => 'bg-blue-100 text-blue-800',
                        'completed' => 'bg-green-100 text-green-800',
                        'rejected' => 'bg-red-100 text-red-800'
                    ];
                    $statusColor = $statusColors[$order['order_status']] ?? 'bg-gray-100 text-gray-800';
                    ?>
                    <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full <?= $statusColor ?>">
                        <?= ucfirst($order['order_status']) ?>
                    </span>
                </div>
            </div>

            <!-- Update Status Form -->
            <?php if ($order['order_status'] !== 'completed' && $order['order_status'] !== 'rejected'): ?>
                <div class="border-t pt-4 mt-4">
                    <form action="<?= base_url('branch/orders/update-status/' . $order['id']) ?>" method="POST" class="flex items-center space-x-4">
                        <?= csrf_field() ?>
                        <label class="text-sm font-medium text-gray-700">Update Status:</label>
                        <select name="status" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                            <option value="pending" <?= $order['order_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="accepted" <?= $order['order_status'] === 'accepted' ? 'selected' : '' ?>>Accepted</option>
                            <option value="completed" <?= $order['order_status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="rejected" <?= $order['order_status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>Update
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Order Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        <i class="fas fa-shopping-bag mr-2"></i>Order Items
                    </h2>

                    <div class="space-y-4">
                        <?php foreach ($order_items as $item):
                            $itemCurrency = $item['currency_code'] ?? 'ZAR';
                            $itemSymbol = getCurrencySymbolBranch($itemCurrency);
                        ?>
                            <div class="flex items-start border-b border-gray-200 pb-4 last:border-0 last:pb-0">
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900"><?= esc($item['listing_title']) ?></h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Quantity: <?= $item['quantity'] ?> × <?= $itemSymbol ?><?= number_format($item['price'], 2) ?>
                                    </p>
                                    <?php if (!empty($item['special_instructions'])): ?>
                                        <p class="text-sm text-gray-500 mt-1">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            <?= esc($item['special_instructions']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">
                                        <?= $itemSymbol ?><?= number_format($item['price'] * $item['quantity'], 2) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Order Total (Hidden - currencies may differ) -->
                    <div class="border-t border-gray-200 mt-4 pt-4 hidden">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-900">Total</span>
                            <span class="text-2xl font-bold text-green-600">
                                <?= number_format($order['grand_total'], 2) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Details Sidebar -->
            <div class="space-y-6">
                <!-- Driver Information -->
                <?php if (!empty($driver)): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-user-circle mr-2"></i>Driver Information
                    </h2>

                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Name</p>
                            <p class="font-medium text-gray-900"><?= esc($driver['name']) ?> <?= esc($driver['surname'] ?? '') ?></p>
                        </div>

                        <?php if (!empty($driver['contact_number'])): ?>
                            <div>
                                <p class="text-sm text-gray-600">Contact Number</p>
                                <p class="font-medium text-gray-900">
                                    <a href="tel:<?= esc($driver['contact_number']) ?>" class="text-green-600 hover:text-green-700">
                                        <i class="fas fa-phone mr-1"></i><?= esc($driver['contact_number']) ?>
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($driver['whatsapp_number'])): ?>
                            <div>
                                <p class="text-sm text-gray-600">WhatsApp</p>
                                <p class="font-medium text-gray-900">
                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $driver['whatsapp_number']) ?>" target="_blank" class="text-green-600 hover:text-green-700">
                                        <i class="fab fa-whatsapp mr-1"></i><?= esc($driver['whatsapp_number']) ?>
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($driver['email'])): ?>
                            <div>
                                <p class="text-sm text-gray-600">Email</p>
                                <p class="font-medium text-gray-900">
                                    <a href="mailto:<?= esc($driver['email']) ?>" class="text-green-600 hover:text-green-700">
                                        <i class="fas fa-envelope mr-1"></i><?= esc($driver['email']) ?>
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Truck/Vehicle Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-truck mr-2"></i>Vehicle Information
                    </h2>

                    <div class="space-y-3">
                        <?php if (!empty($order['vehicle_model'])): ?>
                            <div>
                                <p class="text-sm text-gray-600">Vehicle Model</p>
                                <p class="font-medium text-gray-900"><?= esc($order['vehicle_model']) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($order['vehicle_license_plate'])): ?>
                            <div>
                                <p class="text-sm text-gray-600">License Plate</p>
                                <p class="font-medium text-gray-900"><?= esc($order['vehicle_license_plate']) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($driver['vehicle_type'])): ?>
                            <div>
                                <p class="text-sm text-gray-600">Vehicle Type</p>
                                <p class="font-medium text-gray-900"><?= esc($driver['vehicle_type']) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($driver['vehicle_registration'])): ?>
                            <div>
                                <p class="text-sm text-gray-600">Registration Number</p>
                                <p class="font-medium text-gray-900"><?= esc($driver['vehicle_registration']) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($driver['license_number'])): ?>
                            <div>
                                <p class="text-sm text-gray-600">Driver License Number</p>
                                <p class="font-medium text-gray-900"><?= esc($driver['license_number']) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($order['estimated_arrival'])): ?>
                            <div>
                                <p class="text-sm text-gray-600">Estimated Arrival</p>
                                <p class="font-medium text-gray-900">
                                    <?= date('M d, Y h:i A', strtotime($order['estimated_arrival'])) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Order Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-info-circle mr-2"></i>Order Information
                    </h2>

                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Order ID</p>
                            <p class="font-medium text-gray-900">#<?= $order['id'] ?></p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600">Booking Reference</p>
                            <p class="font-medium text-gray-900"><?= esc($order['booking_reference']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Order Timeline -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-history mr-2"></i>Order Timeline
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check text-green-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Order Placed</p>
                                <p class="text-xs text-gray-600">
                                    <?= date('M d, Y h:i A', strtotime($order['created_at'])) ?>
                                </p>
                            </div>
                        </div>

                        <?php if ($order['order_status'] === 'accepted' || $order['order_status'] === 'completed'): ?>
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-check text-blue-600 text-sm"></i>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Order Accepted</p>
                                    <p class="text-xs text-gray-600">
                                        <?= date('M d, Y h:i A', strtotime($order['updated_at'])) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($order['order_status'] === 'completed'): ?>
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-check-circle text-green-600 text-sm"></i>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Order Completed</p>
                                    <p class="text-xs text-gray-600">
                                        <?= date('M d, Y h:i A', strtotime($order['updated_at'])) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($order['order_status'] === 'rejected'): ?>
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-times text-red-600 text-sm"></i>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Order Rejected</p>
                                    <p class="text-xs text-gray-600">
                                        <?= date('M d, Y h:i A', strtotime($order['updated_at'])) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-cog mr-2"></i>Actions
                    </h2>
                    
                    <div class="space-y-2">
                        <button onclick="window.print()" class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                            <i class="fas fa-print mr-2"></i>Print Order
                        </button>
                        <a href="<?= base_url('branch/orders') ?>" class="block w-full px-4 py-2 text-center border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                            <i class="fas fa-list mr-2"></i>View All Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('branch/templates/footer') ?>

