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
        .receipt-border {
            border: 2px dashed #e5e7eb;
        }
        .receipt-item {
            border-bottom: 1px dashed #e5e7eb;
        }
        .receipt-item:last-child {
            border-bottom: none;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
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
</head>
<body class="bg-gray-50">

    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40 no-print">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center space-x-3">
                <a href="<?= base_url('dashboard/driver') ?>" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-900"><?= esc($page_title) ?></h1>
            </div>
            <div class="flex space-x-2">
                <button onclick="window.print()" class="px-3 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                    <i class="fas fa-print mr-1"></i>
                    Print
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pb-20">
        <div class="max-w-2xl mx-auto p-4">
            
            <!-- Success Message -->
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 no-print">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-2xl mr-3"></i>
                    <div>
                        <h3 class="font-semibold">Order Completed Successfully!</h3>
                        <p class="text-sm">Your booking reference is: <strong><?= esc($order['booking_reference']) ?></strong></p>
                    </div>
                </div>
            </div>

            <!-- Receipt -->
            <div class="bg-white receipt-border rounded-lg p-8 mb-6">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">TRUCKERS AFRICA</h2>
                    <p class="text-gray-600">Service Order Receipt</p>
                    <div class="w-20 h-1 bg-green-500 mx-auto mt-4"></div>
                </div>

                <!-- Order Information -->
                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Order Details</h3>
                        <div class="space-y-2 text-sm">
                            <div><span class="text-gray-600">Booking Ref:</span> <span class="font-medium"><?= esc($order['booking_reference']) ?></span></div>
                            <div><span class="text-gray-600">Order Date:</span> <span class="font-medium"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></span></div>
                            <div><span class="text-gray-600">Status:</span> 
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full 
                                    <?= $order['order_status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                        ($order['order_status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') ?>">
                                    <?= ucfirst($order['order_status']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Delivery Information</h3>
                        <div class="space-y-2 text-sm">
                            <div><span class="text-gray-600">Vehicle:</span> <span class="font-medium"><?= esc($order['vehicle_model'] ?: 'Not specified') ?></span></div>
                            <div><span class="text-gray-600">Est. Arrival:</span> <span class="font-medium"><?= $order['estimated_arrival'] ? date('d M Y, H:i', strtotime($order['estimated_arrival'])) : 'Not specified' ?></span></div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="mb-8">
                    <h3 class="font-semibold text-gray-900 mb-4">Order Items</h3>
                    <div class="space-y-3">
                        <?php foreach ($order_items as $item): ?>
                        <div class="receipt-item py-3">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">Service Item #<?= esc($item['listing_id']) ?></h4>
                                    <p class="text-sm text-gray-600">R<?= number_format($item['price'], 2) ?> × <?= $item['quantity'] ?></p>
                                </div>
                                <div class="text-right">
                                    <span class="font-medium text-gray-900">R<?= number_format($item['total_cost'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Total -->
                <div class="border-t-2 border-gray-200 pt-4 mb-8">
                    <div class="flex justify-between items-center text-2xl font-bold">
                        <span>Total Amount:</span>
                        <span class="text-green-600">R<?= number_format($order['grand_total'], 2) ?></span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center text-sm text-gray-600 border-t pt-6">
                    <p class="mb-2">Thank you for using Truckers Africa!</p>
                    <p>For support, contact us at support@truckersafrica.com</p>
                    <p class="mt-4 text-xs">This is a computer-generated receipt and does not require a signature.</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3 no-print">
                <a href="<?= base_url('order/my-orders') ?>" class="w-full bg-green-500 text-white py-3 px-6 rounded-lg font-semibold hover:bg-green-600 transition-colors text-center block">
                    <i class="fas fa-list mr-2"></i>
                    View All Orders
                </a>
                <a href="<?= base_url('dashboard/driver') ?>" class="w-full bg-gray-200 text-gray-700 py-3 px-6 rounded-lg font-semibold hover:bg-gray-300 transition-colors text-center block">
                    <i class="fas fa-home mr-2"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </main>

    <?php 
    $current_page = 'orders';
    echo view('driver/templates/bottom_nav', ['current_page' => $current_page]); 
    ?>

    <script>
        // Auto-focus print dialog after page loads (optional)
        // window.addEventListener('load', function() {
        //     setTimeout(() => window.print(), 1000);
        // });
    </script>
</body>
</html>
