<?php
// Helper function to get currency symbol
function getCurrencySymbolMerchant($currency) {
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
<?php echo view('merchant/templates/header', ['page_title' => $page_title]); ?>

<div class="container mx-auto px-4 py-8">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="<?= base_url('merchant/orders/all') ?>" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition duration-300 ease-in-out">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Orders
        </a>
    </div>

    <h1 class="text-2xl font-bold text-gray-800 mb-6">Order #<?= esc($order['id']) ?> Details</h1>

    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Driver Details</h2>
        <p><span class="font-semibold">Name:</span> <?= esc($driver['name'] . ' ' . $driver['surname']) ?></p>
        <p><span class="font-semibold">Email:</span> <?= esc($driver['email']) ?></p>
        <p><span class="font-semibold">Phone:</span> <?= esc($driver['contact_number'] ?? ($driver['whatsapp_number'] ?? 'N/A')) ?></p>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
        <p><span class="font-semibold">Booking Reference:</span> <?= esc($order['booking_reference']) ?></p>
        <p><span class="font-semibold">Status:</span> <?= esc(ucfirst($order['order_status'])) ?></p>
        <p><span class="font-semibold">Placed On:</span> <?= date('M d, Y H:i', strtotime($order['created_at'])) ?></p>
        <?php if (!empty($order['estimated_arrival'])): ?>
            <p><span class="font-semibold">Estimated Arrival:</span> <?= date('M d, Y h:i A', strtotime($order['estimated_arrival'])) ?></p>
        <?php endif; ?>
        <?php if (!empty($order['vehicle_model'])): ?>
            <p><span class="font-semibold">Vehicle:</span> <?= esc($order['vehicle_model']) ?></p>
        <?php endif; ?>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Items (<?= count($order['items'] ?? []) ?>)</h2>
        <?php if (!empty($order['items'])): ?>
        <table class="min-w-full">
            <thead>
                <tr class="bg-gray-200 text-gray-700">
                    <th class="text-left py-2 px-4">Listing</th>
                    <th class="text-left py-2 px-4">Quantity</th>
                    <th class="text-left py-2 px-4">Price</th>
                    <th class="text-left py-2 px-4">Total</th>
                    <th class="text-left py-2 px-4">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order['items'] as $item):
                    $itemCurrency = $item['currency_code'] ?? 'ZAR';
                    $itemSymbol = getCurrencySymbolMerchant($itemCurrency);
                ?>
                <tr class="border-b">
                    <td class="py-2 px-4"><?= esc($item['listing_title']) ?></td>
                    <td class="py-2 px-4"><?= esc($item['quantity']) ?></td>
                    <td class="py-2 px-4"><?= $itemSymbol ?><?= number_format($item['price'], 2) ?></td>
                    <td class="py-2 px-4"><?= $itemSymbol ?><?= number_format($item['total_cost'], 2) ?></td>
                    <td class="py-2 px-4"><?= esc(ucfirst($item['item_status'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No items found for this order.</p>
        <?php endif; ?>
    </div>

    <!-- Back Button at Bottom -->
    <div class="mt-6">
        <a href="<?= base_url('merchant/orders/all') ?>" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition duration-300 ease-in-out">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Orders
        </a>
    </div>
</div>

<?php echo view('merchant/templates/footer'); ?>
