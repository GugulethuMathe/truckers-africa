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
        .receipt-item {
            border-bottom: 1px dashed #e5e7eb;
        }
        .receipt-item:last-child {
            border-bottom: none;
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
        /* Notification animation */
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .animate-slide-in {
            animation: slideIn 0.3s ease-out;
            transition: opacity 0.3s, transform 0.3s;
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center space-x-3">
                <a href="<?= base_url('dashboard/driver') ?>" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-900"><?= esc($page_title) ?></h1>
            </div>

            <!-- Currency Dropdown -->
            <div class="relative">
                <button id="currencyDropdownBtn" class="flex items-center space-x-2 bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg transition-colors">
                    <i class="fas fa-coins text-green-600"></i>
                    <span id="current-currency" class="font-medium text-gray-700">ZAR</span>
                    <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                </button>

                <div id="currencyDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                    <div class="py-2">
                        <button onclick="changeCurrency('ZAR', 'R')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                            <span class="font-medium">R</span>
                            <span class="ml-2">South African Rand</span>
                        </button>
                        <button onclick="changeCurrency('USD', '$')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                            <span class="font-medium">$</span>
                            <span class="ml-2">US Dollar</span>
                        </button>
                        <button onclick="changeCurrency('BWP', 'P')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                            <span class="font-medium">P</span>
                            <span class="ml-2">Botswana Pula</span>
                        </button>
                        <button onclick="changeCurrency('NAD', 'N$')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                            <span class="font-medium">N$</span>
                            <span class="ml-2">Namibian Dollar</span>
                        </button>
                        <button onclick="changeCurrency('ZMW', 'ZK')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                            <span class="font-medium">ZK</span>
                            <span class="ml-2">Zambian Kwacha</span>
                        </button>
                        <button onclick="changeCurrency('KES', 'KSh')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                            <span class="font-medium">KSh</span>
                            <span class="ml-2">Kenyan Shilling</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pb-20">
        <div class="max-w-2xl mx-auto p-4 space-y-6">
            
            <!-- Success/Error Messages -->
            <?php if (session()->getFlashdata('message')): ?>
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= session()->getFlashdata('message') ?>
                </div>
            <?php endif; ?>
            
            <?php if (session()->getFlashdata('error')): ?>
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <!-- Order Receipt -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="text-center mb-6">
                    <i class="fas fa-receipt text-4xl text-green-600 mb-2"></i>
                    <h2 class="text-2xl font-bold text-gray-900">Order Receipt</h2>
                    <p class="text-gray-600">Review your order details</p>
                </div>

                <!-- Order Items -->
                <div id="receiptItems" class="space-y-3 mb-6">
                    <!-- Items will be populated by JavaScript -->
                </div>

                <!-- Order Total (Hidden) -->
                <div class="border-t-2 border-gray-200 pt-4 hidden">
                    <div class="flex justify-between items-center text-xl font-bold">
                        <span>Total:</span>
                        <span id="receiptTotal" class="text-green-600">R0.00</span>
                    </div>
                </div>
            </div>

            <!-- Vehicle Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-truck mr-2 text-green-600"></i>
                    Vehicle Information
                </h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="vehicle_info" class="block text-sm font-medium text-gray-700 mb-2">Vehicle Description</label>
                        <input type="text" id="vehicle_info" name="vehicle_info"
                               value="<?= esc($driver['vehicle_type'] ?? '') ?>"
                               placeholder="e.g., White Volvo Truck"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
            </div>

            <!-- Driver Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-user mr-2 text-green-600"></i>
                    Driver Information
                </h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Name:</span>
                        <span class="font-medium"><?= esc($driver['name'] . ' ' . $driver['surname']) ?></span>
                    </div>
                    <div>
                        <span class="text-gray-600">Contact:</span>
                        <span class="font-medium"><?= esc($driver['contact_number'] ?? 'Not provided') ?></span>
                    </div>
                    <div>
                        <span class="text-gray-600">Vehicle Reg:</span>
                        <span class="font-medium"><?= esc($driver['vehicle_registration'] ?? 'Not provided') ?></span>
                    </div>
                    <div>
                        <span class="text-gray-600">License:</span>
                        <span class="font-medium"><?= esc($driver['license_number'] ?? 'Not provided') ?></span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <button id="completeOrderBtn" class="w-full bg-green-500 text-white py-4 px-6 rounded-lg font-semibold hover:bg-green-600 transition-colors text-lg">
                    <i class="fas fa-check-circle mr-2"></i>
                    Complete Order
                </button>
                <a href="<?= base_url('dashboard/driver') ?>" class="w-full bg-gray-200 text-gray-700 py-3 px-6 rounded-lg font-semibold hover:bg-gray-300 transition-colors text-center block">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Shopping
                </a>
            </div>
        </div>
    </main>

    <?php 
    $current_page = 'orders';
    echo view('driver/templates/bottom_nav', ['current_page' => $current_page]); 
    ?>

    <!-- Loading Modal -->
    <div id="loadingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 text-center">
            <i class="fas fa-spinner fa-spin text-3xl text-green-600 mb-4"></i>
            <p class="text-gray-700">Processing your order...</p>
        </div>
    </div>

    <script>
        let cart = JSON.parse(localStorage.getItem('truckers_cart') || '[]');

        // Load cart items on page load
        document.addEventListener('DOMContentLoaded', function() {
            displayReceiptItems();
        });

        function displayReceiptItems() {
            const receiptItems = document.getElementById('receiptItems');
            const receiptTotal = document.getElementById('receiptTotal');

            if (cart.length === 0) {
                receiptItems.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">Your cart is empty</p>
                        <button onclick="history.back()" class="mt-4 bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600">
                            Continue Shopping
                        </button>
                    </div>
                `;
                document.getElementById('completeOrderBtn').disabled = true;
                document.getElementById('completeOrderBtn').classList.add('opacity-50', 'cursor-not-allowed');
                return;
            }

            // Group items by location (branch) - separate orders even for same merchant
            const itemsByLocation = {};
            cart.forEach(item => {
                const merchantId = item.merchant_id || 'unknown';
                const locationId = item.location_id || 'no_location';
                const merchantName = item.merchant_name || item.merchant || 'Unknown Merchant';
                const locationName = item.location_name || '';

                // Create unique key combining merchant and location
                const locationKey = merchantId + '_' + locationId;

                if (!itemsByLocation[locationKey]) {
                    // Display name: show only location name if it's a branch, otherwise show merchant name
                    let displayName;
                    if (locationName) {
                        // It's a branch - show only the location name
                        displayName = locationName;
                    } else {
                        // No location specified - show merchant name
                        displayName = merchantName;
                    }

                    itemsByLocation[locationKey] = {
                        name: displayName,
                        merchantName: merchantName,
                        locationName: locationName,
                        items: [],
                        total: 0
                    };
                }

                itemsByLocation[locationKey].items.push(item);
                itemsByLocation[locationKey].total += item.price * item.quantity;
            });

            const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const locationCount = Object.keys(itemsByLocation).length;

            // Check if any items are missing location data (old cart items)
            const hasOldCartItems = cart.some(item => !item.location_id && !item.location_name);

            let receiptHTML = `
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span class="text-sm font-medium">Your order will be split into ${locationCount} separate order(s) for different locations/branches</span>
                    </div>
                </div>
            `;

            // Show warning if old cart items detected
            if (hasOldCartItems) {
                receiptHTML += `
                    <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-start text-yellow-800">
                            <i class="fas fa-exclamation-triangle mr-2 mt-0.5"></i>
                            <div class="text-sm">
                                <p class="font-medium mb-1">Some items may not show correct branch information</p>
                                <p class="text-xs">Items added before the latest update may not display the correct branch name. For accurate branch information, please clear your cart and re-add the items.</p>
                            </div>
                        </div>
                    </div>
                `;
            }

            Object.entries(itemsByLocation).forEach(([locationKey, locationData], index) => {
                const orderLetter = String.fromCharCode(65 + index); // A, B, C, etc.
                // Get currency from first item in this location group
                const locationCurrency = locationData.items[0]?.currency || 'ZAR';
                const locationCurrencySymbol = getCurrencySymbol(locationCurrency);

                receiptHTML += `
                    <div class="border-2 border-gray-300 rounded-lg p-4 mb-4 bg-white shadow-sm">
                        <!-- Order Header -->
                        <div class="flex justify-between items-center mb-3 pb-3 border-b border-gray-200">
                            <h4 class="font-semibold text-gray-900">
                                <i class="fas fa-map-marker-alt mr-2 text-green-600"></i>
                                Order ${orderLetter}: ${locationData.name}
                            </h4>
                        </div>

                        <!-- Order Items -->
                        <div class="space-y-2 mb-4">
                            ${locationData.items.map((item, itemIndex) => {
                                const itemCurrency = item.currency || 'ZAR';
                                const itemSymbol = getCurrencySymbol(itemCurrency);
                                return `
                                <div class="receipt-item py-2" data-item-id="${item.id}">
                                    <div class="flex justify-between items-start gap-3">
                                        <div class="flex-1">
                                            <h5 class="font-medium text-gray-800">${item.title}</h5>
                                            <p class="text-sm text-gray-600"><span data-original-price="${item.price}" data-original-currency="${itemCurrency}">${itemSymbol}${item.price.toFixed(2)}</span> × ${item.quantity}</p>
                                        </div>
                                        <div class="text-right flex items-center gap-3">
                                            <span class="font-medium text-gray-800" data-original-price="${(item.price * item.quantity)}" data-original-currency="${itemCurrency}">${itemSymbol}${(item.price * item.quantity).toFixed(2)}</span>
                                            <button onclick="removeFromCart(${item.id})"
                                                    class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-lg transition-colors"
                                                    title="Remove from cart">
                                                <i class="fas fa-trash text-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `}).join('')}
                        </div>

                        <!-- Estimated Arrival (Embedded in order box) -->
                        <div class="mt-4 pt-4 border-t border-gray-200 bg-green-50 -mx-4 -mb-4 p-4 rounded-b-lg">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 mt-1">
                                    <i class="fas fa-clock text-green-600 text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <label for="estimated_arrival_${locationKey}" class="block text-sm font-semibold text-gray-900 mb-2">
                                        <i class="fas fa-calendar-check mr-1 text-green-600"></i>
                                        When will you arrive at this location? <span class="text-red-500">*</span>
                                    </label>
                                    <input type="datetime-local"
                                           id="estimated_arrival_${locationKey}"
                                           name="estimated_arrival_${locationKey}"
                                           data-location-key="${locationKey}"
                                           min="<?= date('Y-m-d\TH:i') ?>"
                                           required
                                           class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white estimated-arrival-input text-base font-medium">
                                    <p class="text-xs text-gray-600 mt-2 flex items-center">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Select the date and time you expect to arrive
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            receiptItems.innerHTML = receiptHTML;
            // Use the cart's primary currency for the total
            const cartCurrency = getCartCurrency();
            const cartSymbol = getCurrencySymbol(cartCurrency);
            receiptTotal.textContent = `${cartSymbol}${totalPrice.toFixed(2)}`;
        }

        // Remove item from cart
        function removeFromCart(itemId) {
            // Confirm removal
            if (!confirm('Are you sure you want to remove this item from your cart?')) {
                return;
            }

            // Find and remove the item from cart
            const itemIndex = cart.findIndex(item => item.id === itemId);

            if (itemIndex > -1) {
                const removedItem = cart[itemIndex];
                cart.splice(itemIndex, 1);

                // Update localStorage
                localStorage.setItem('truckers_cart', JSON.stringify(cart));

                // Show success message
                const itemTitle = removedItem.title || 'Item';

                // Refresh the display
                displayReceiptItems();

                // If cart is now empty, disable the complete order button
                if (cart.length === 0) {
                    document.getElementById('completeOrderBtn').disabled = true;
                    document.getElementById('completeOrderBtn').classList.add('opacity-50', 'cursor-not-allowed');
                }

                // Show notification
                showNotification('Item removed from cart: ' + itemTitle, 'success');
            }
        }

        // Show notification helper
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed top-20 right-4 z-50 px-6 py-3 rounded-lg shadow-lg ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } text-white flex items-center gap-2 animate-slide-in`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            document.body.appendChild(notification);

            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Complete order functionality
        document.getElementById('completeOrderBtn').addEventListener('click', function() {
            const vehicleInfo = document.getElementById('vehicle_info').value;

            if (cart.length === 0) {
                alert('Your cart is empty.');
                return;
            }

            // Get all estimated arrival inputs
            const arrivalInputs = document.querySelectorAll('.estimated-arrival-input');
            const estimatedArrivals = {};
            let hasEmptyArrival = false;
            let firstEmptyInput = null;

            // Validate all estimated arrival times are filled
            arrivalInputs.forEach(input => {
                const locationKey = input.dataset.locationKey;
                const arrivalTime = input.value;

                if (!arrivalTime) {
                    hasEmptyArrival = true;
                    if (!firstEmptyInput) {
                        firstEmptyInput = input;
                    }
                    // Highlight the field
                    input.classList.add('border-red-500', 'ring-2', 'ring-red-500');
                } else {
                    estimatedArrivals[locationKey] = arrivalTime;
                    // Remove error styling if it was there
                    input.classList.remove('border-red-500', 'ring-2', 'ring-red-500');
                }
            });

            if (hasEmptyArrival) {
                // Scroll to first empty input and show error
                if (firstEmptyInput) {
                    firstEmptyInput.focus();
                    firstEmptyInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                alert('⚠️ Missing Estimated Arrival Times!\n\nPlease select an estimated arrival time for ALL orders before completing your checkout.');

                // Remove error styling after 5 seconds
                setTimeout(() => {
                    arrivalInputs.forEach(input => {
                        input.classList.remove('border-red-500', 'ring-2', 'ring-red-500');
                    });
                }, 5000);
                return;
            }

            // Show loading modal
            document.getElementById('loadingModal').classList.remove('hidden');

            const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

            const orderData = {
                items: cart,
                total: totalPrice,
                estimated_arrivals: estimatedArrivals, // Send all arrival times keyed by location
                vehicle_info: vehicleInfo
            };

            // Send order to server
            fetch('<?= base_url('order/complete') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loadingModal').classList.add('hidden');
                
                if (data.success) {
                    // Clear cart
                    localStorage.removeItem('truckers_cart');
                    
                    // Show success message with multiple orders info
                    const ordersCount = data.orders_created || 1;
                    const message = `Orders completed successfully!\n` +
                                  `${ordersCount} order(s) created for different merchants`;

                    alert(message);
                    
                    // Redirect to multi-order receipt page
                    window.location.href = '<?= base_url('order/multi-receipt/') ?>' + data.checkout_session_id;
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                document.getElementById('loadingModal').classList.add('hidden');
                console.error('Error:', error);
                alert('An error occurred while processing your order. Please try again.');
            });
        });

        // Currency conversion functionality
        let currentDisplayCurrency = 'ZAR';
        let exchangeRates = {};

        // Load currency preference from localStorage
        function loadCurrencyPreference() {
            const savedCurrency = localStorage.getItem('driver_display_currency');
            if (savedCurrency) {
                currentDisplayCurrency = savedCurrency;
                document.getElementById('current-currency').textContent = savedCurrency;
                // Only convert prices if it's not ZAR (to avoid unnecessary processing)
                if (savedCurrency !== 'ZAR') {
                    convertAllPrices();
                }
            }
        }

        // Change currency and convert all prices
        function changeCurrency(currency, symbol) {
            currentDisplayCurrency = currency;
            document.getElementById('current-currency').textContent = currency;
            localStorage.setItem('driver_display_currency', currency);
            convertAllPrices();

            // Close dropdown
            document.getElementById('currencyDropdown').classList.add('hidden');
        }

        // Convert all prices on the page
        async function convertAllPrices() {
            if (currentDisplayCurrency === 'ZAR') {
                // Reset all prices to original ZAR values
                displayReceiptItems(); // Re-render with original prices
                return;
            }

            // Fetch exchange rates if needed
            if (!exchangeRates[currentDisplayCurrency]) {
                await fetchExchangeRates();
            }

            // Convert all price elements
            const priceElements = document.querySelectorAll('[data-original-price]');
            priceElements.forEach(element => {
                const originalPrice = parseFloat(element.dataset.originalPrice);
                const originalCurrency = element.dataset.originalCurrency || 'ZAR';

                if (originalPrice && originalCurrency !== currentDisplayCurrency) {
                    const convertedPrice = convertPrice(originalPrice, originalCurrency, currentDisplayCurrency);
                    if (convertedPrice) {
                        const symbol = getCurrencySymbol(currentDisplayCurrency);
                        element.innerHTML = `${symbol}${convertedPrice.toFixed(2)} <span class="text-xs text-gray-500">(R${originalPrice})</span>`;
                    }
                }
            });

            // Update total price
            updateTotalPrice();
        }

        // Fetch exchange rates from API
        async function fetchExchangeRates() {
            try {
                const response = await fetch(`https://api.exchangerate-api.com/v4/latest/ZAR`);
                const data = await response.json();
                exchangeRates = data.rates;
            } catch (error) {
                console.error('Failed to fetch exchange rates:', error);
            }
        }

        // Convert price between currencies
        function convertPrice(amount, fromCurrency, toCurrency) {
            if (fromCurrency === toCurrency) return amount;

            // Convert through ZAR as base
            let amountInZAR = amount;
            if (fromCurrency !== 'ZAR') {
                const fromRate = exchangeRates[fromCurrency];
                if (!fromRate) return null;
                amountInZAR = amount / fromRate;
            }

            if (toCurrency === 'ZAR') return amountInZAR;

            const toRate = exchangeRates[toCurrency];
            if (!toRate) return null;

            return amountInZAR * toRate;
        }

        // Get currency symbol
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

        // Get primary currency from cart items (first item's currency or ZAR as default)
        function getCartCurrency() {
            if (cart.length > 0 && cart[0].currency) {
                return cart[0].currency;
            }
            return 'ZAR';
        }

        // Update total price with currency conversion
        function updateTotalPrice() {
            const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const cartCurrency = getCartCurrency();
            const cartSymbol = getCurrencySymbol(cartCurrency);

            if (currentDisplayCurrency === cartCurrency) {
                document.getElementById('receiptTotal').textContent = `${cartSymbol}${totalPrice.toFixed(2)}`;
            } else {
                const convertedTotal = convertPrice(totalPrice, cartCurrency, currentDisplayCurrency);
                if (convertedTotal) {
                    const symbol = getCurrencySymbol(currentDisplayCurrency);
                    document.getElementById('receiptTotal').innerHTML = `${symbol}${convertedTotal.toFixed(2)} <span class="text-xs text-gray-500">(${cartSymbol}${totalPrice.toFixed(2)})</span>`;
                }
            }
        }

        // Currency dropdown toggle functionality
        document.getElementById('currencyDropdownBtn').addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = document.getElementById('currencyDropdown');
            dropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('currencyDropdown');
            const button = document.getElementById('currencyDropdownBtn');

            if (!dropdown.contains(e.target) && !button.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Initialize currency system
        document.addEventListener('DOMContentLoaded', function() {
            loadCurrencyPreference();
        });
    </script>
</body>
</html>
