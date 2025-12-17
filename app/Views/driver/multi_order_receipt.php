<?php
// Helper function to get currency symbol
function getCurrencySymbolPHP($currency) {
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title) ?> - Truckers Africa</title>
    
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/logo-icon-black.png') ?>">
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'safari-green': '#006400',
                        'sahara-sand': '#F1C40F',
                        'dark-grey': '#1F2937',
                        'medium-grey': '#374151'
                    }
                }
            }
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .receipt-container { box-shadow: none !important; margin: 0 !important; }
        }
        .receipt-item {
            border-bottom: 1px dashed #e5e7eb;
        }
        .receipt-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40 no-print">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center space-x-3">
                <a href="<?= base_url('order/my-orders') ?>" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-900"><?= esc($page_title) ?></h1>
            </div>
            <button onclick="window.print()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-print mr-2"></i>
                Print Receipt
            </button>
        </div>
    </header>

    <!-- Receipt Content -->
    <main class="container mx-auto px-4 py-6">
        <div class="receipt-container bg-white rounded-lg shadow-lg p-6 max-w-4xl mx-auto">
            <!-- Company Header -->
            <div class="text-center border-b border-gray-200 pb-6 mb-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Truckers Africa</h1>
                <p class="text-gray-600">Your Trusted Partner on the Road</p>
                <p class="text-sm text-gray-500 mt-2">Multi-Order Receipt</p>
            </div>

            <!-- Order Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Order Information</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Checkout Session:</span>
                            <span class="font-medium"><?= esc($checkout_session_id) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Orders Created:</span>
                            <span class="font-medium"><?= count($orders) ?> orders</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Order Date:</span>
                            <span class="font-medium"><?= date('d M Y, H:i', strtotime($order_date)) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">Pending</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Delivery Information</h3>
                    <div class="space-y-2 text-sm">
                        <?php if ($vehicle_model): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Vehicle:</span>
                            <span class="font-medium"><?= esc($vehicle_model) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($estimated_arrival): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Est. Arrival:</span>
                            <span class="font-medium"><?= date('d M Y, H:i', strtotime($estimated_arrival)) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Driver:</span>
                            <span class="font-medium"><?= esc(session()->get('name')) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders by Location/Branch -->
            <?php foreach ($orders as $index => $orderData):
                $currencyCode = $orderData['currency_code'] ?? 'ZAR';
                $currencySymbol = getCurrencySymbolPHP($currencyCode);
            ?>
            <div class="mb-8 border border-gray-200 rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">
                            <i class="fas fa-map-marker-alt mr-2 text-green-600"></i>
                            Order <?= chr(65 + $index) ?>: <?= esc($orderData['display_name']) ?>
                        </h3>
                        <?php if ($orderData['location_name']): ?>
                        <p class="text-sm text-gray-500 ml-7">Merchant: <?= esc($orderData['merchant_name']) ?></p>
                        <?php endif; ?>
                        <p class="text-sm text-gray-600 ml-7">Booking Reference: <span class="font-medium"><?= esc($orderData['order']['booking_reference']) ?></span></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500"><?= count($orderData['items']) ?> item(s)</p>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="space-y-3">
                    <?php foreach ($orderData['items'] as $item):
                        $itemCurrency = $item['currency_code'] ?? $currencyCode;
                        $itemSymbol = getCurrencySymbolPHP($itemCurrency);
                    ?>
                    <div class="receipt-item py-3">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900"><?= esc($item['listing_title']) ?></h4>
                                <p class="text-sm text-gray-600"><?= $itemSymbol ?><?= number_format($item['price'], 2) ?> × <?= $item['quantity'] ?></p>
                                <?php if ($item['status'] !== 'pending'): ?>
                                <p class="text-xs text-gray-500 mt-1">
                                    Status:
                                    <span class="<?= $item['status'] === 'accepted' ? 'text-green-600' : ($item['status'] === 'rejected' ? 'text-red-600' : 'text-yellow-600') ?>">
                                        <?= ucfirst($item['status']) ?>
                                    </span>
                                </p>
                                <?php endif; ?>
                            </div>
                            <div class="text-right">
                                <span class="font-medium text-gray-900"><?= $itemSymbol ?><?= number_format($item['total_cost'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Grand Total (Hidden) -->
            <div class="border-t-2 border-gray-300 pt-6 hidden">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">Grand Total</h3>
                        <p class="text-sm text-gray-600"><?= count($orders) ?> order(s) from <?= count($orders) ?> location(s)</p>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-bold text-green-600"><?= number_format($grand_total, 2) ?></p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-gray-200 text-center text-sm text-gray-500">
                <p class="mb-2">Thank you for choosing Truckers Africa!</p>
                <p>Each location/branch will process their order independently. You will receive separate notifications for each order.</p>
                <p class="mt-4">For support, contact us at support@truckersafrica.com</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-center space-x-4 mt-8 no-print">
            <a href="<?= base_url('order/my-orders') ?>" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition-colors">
                <i class="fas fa-list mr-2"></i>
                View All Orders
            </a>
            <a href="<?= base_url('dashboard/driver') ?>" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-home mr-2"></i>
                Back to Dashboard
            </a>
        </div>
    </main>

    <?php 
    $current_page = 'orders';
    echo view('driver/templates/bottom_nav', ['current_page' => $current_page]); 
    ?>

    <script>
        // Auto-focus print dialog if coming from checkout
        if (document.referrer.includes('/order/checkout')) {
            setTimeout(() => {
                // Optional: Auto-print after 2 seconds if user wants
                // window.print();
            }, 2000);
        }

        // Get currency symbol helper
        function getCurrencySymbol(currency) {
            const symbols = {
                'ZAR': 'R',
                'USD': '$',
                'BWP': 'P',
                'NAD': 'N$',
                'ZMW': 'ZK',
                'KES': 'KSh',
                'TZS': 'TSh',
                'UGX': 'USh',
                'EUR': '€',
                'GBP': '£',
                'NGN': '₦',
                'GHS': 'GH₵',
                'MZN': 'MT',
                'ZWL': 'Z$'
            };
            return symbols[currency] || currency;
        }
    </script>
</body>
</html>
