<?= view('driver/templates/header', ['page_title' => $page_title]) ?>

<style>
    /* Mobile button sizing tweaks */
    @media (max-width: 480px) {
        button,
        a[class*="rounded"][class*="px-"][class*="py-"] {
            padding: 6px 10px !important;
            font-size: 12px !important;
            line-height: 1.2 !important;
            border-radius: 6px;
        }
    }
</style>

<!-- Main Content -->
<main class="pb-20">
        <div class="max-w-4xl mx-auto p-4">
            
            <!-- Success/Error Messages -->
            <?php if (session()->getFlashdata('message')): ?>
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= session()->getFlashdata('message') ?>
                </div>
            <?php endif; ?>
            
            <?php if (session()->getFlashdata('error')): ?>
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <!-- Orders List -->
            <?php if (empty($grouped_orders)): ?>
                <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                    <i class="fas fa-clipboard-list text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Orders Yet</h3>
                    <p class="text-gray-600 mb-6">You haven't placed any orders yet. Start exploring services!</p>
                    <a href="<?= base_url('dashboard/driver') ?>" class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 transition-colors">
                        <i class="fas fa-search mr-2"></i>
                        Browse Services
                    </a>
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($grouped_orders as $group): ?>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Group Header -->
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        Checkout Session: <?= esc($group['checkout_session_id']) ?>
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-calendar mr-1"></i>
                                        <?= date('d M Y, H:i', strtotime($group['order_date'])) ?>
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-store mr-1"></i>
                                        <?= count($group['orders']) ?> merchant(s)
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full 
                                        <?php 
                                        switch($group['overall_status']) {
                                            case 'pending':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'in_progress':
                                                echo 'bg-blue-100 text-blue-800';
                                                break;
                                            case 'completed':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'partially_rejected':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?= ucfirst(str_replace('_', ' ', $group['overall_status'])) ?>
                                    </span>
                                    <div class="text-xl font-bold text-green-600 mt-1">
                                        R<?= number_format($group['total_amount'], 2) ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Individual Orders -->
                        <div class="divide-y divide-gray-200">
                            <?php foreach ($group['orders'] as $index => $order): ?>
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <h4 class="font-semibold text-gray-900">
                                            Order <?= chr(65 + $index) ?>: <?= esc($order['business_name']) ?>
                                        </h4>
                                        <p class="text-sm text-gray-600">
                                            Booking: <?= esc($order['booking_reference']) ?>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full 
                                            <?php 
                                            switch($order['order_status']) {
                                                case 'pending':
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'accepted':
                                                    echo 'bg-blue-100 text-blue-800';
                                                    break;
                                                case 'completed':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'rejected':
                                                    echo 'bg-red-100 text-red-800';
                                                    break;
                                                default:
                                                    echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?= ucfirst($order['order_status']) ?>
                                        </span>
                                        <div class="text-lg font-bold text-gray-900 mt-1">
                                            R<?= number_format($order['grand_total'], 2) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <a href="<?= base_url('driver/orders/view/' . $order['id']) ?>"
                                       class="inline-flex items-center text-sm text-blue-600 hover:underline">
                                        <i class="fas fa-eye mr-1"></i>
                                        View Details
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Group Actions -->
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                            <div class="flex flex-wrap gap-2">
                                <a href="<?= base_url('order/multi-receipt/' . $group['checkout_session_id']) ?>" 
                                   class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors text-sm">
                                    <i class="fas fa-receipt mr-1"></i>
                                    View Full Receipt
                                </a>
                                
                                <?php if ($group['overall_status'] === 'pending'): ?>
                                <button class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors text-sm"
                                        onclick="alert('Order cancellation feature coming soon!')">
                                    <i class="fas fa-times mr-1"></i>
                                    Cancel Orders
                                </button>
                                <?php endif; ?>
                                
                                <?php if (in_array($group['overall_status'], ['in_progress', 'accepted'])): ?>
                                <button class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors text-sm"
                                        onclick="alert('Order tracking feature coming soon!')">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    Track Orders
                                </button>
                                <?php endif; ?>
                                
                                <button class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors text-sm"
                                        onclick="window.location.href='<?= base_url('driver/orders/view/' . ($group['orders'][0]['id'] ?? '')) ?>'">
                                    <i class="fas fa-eye mr-1"></i>
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination could be added here if needed -->
            <?php endif; ?>
        </div>
    </main>

<?php
$current_page = 'orders';
echo view('driver/templates/bottom_nav', ['current_page' => $current_page]);
?>

</div> <!-- Close pt-16 div from header -->

<script>
    function cancelOrder(orderId, bookingRef) {
        if (confirm(`Are you sure you want to cancel order ${bookingRef}?`)) {
            // Here you would implement the cancel order functionality
            alert('Order cancellation functionality will be implemented.');
            // You could make an AJAX call to cancel the order
            // fetch('/order/cancel/' + orderId, { method: 'POST' })...
        }
    }
</script>
</body>
</html>
