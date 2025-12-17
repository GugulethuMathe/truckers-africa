<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title) ?> - Truckers Africa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .cart-sidebar {
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
        }
        .cart-sidebar.open {
            transform: translateX(0);
        }

        /* Mobile responsive layout */
        @media (max-width: 640px) {
            .search-form-wrapper {
                flex-direction: column;
            }
            #categorySelect {
                width: 100%;
                min-width: 100%;
            }
        }

        /* Mobile button sizing tweaks */
        @media (max-width: 480px) {
            button,
            .category-btn,
            .category-filter,
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
<?php helper('currency'); ?>
    <!-- Top Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <button onclick="history.back()" class="mr-4 p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </button>
                    <h1 class="text-xl font-semibold text-gray-900"><?= esc($merchant['business_name']) ?></h1>
                </div>
                <!-- Currency Selector -->
                <div class="relative mr-2" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open" class="flex items-center space-x-1 px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none border border-gray-300 rounded-md hover:bg-gray-50">
                        <i class="fas fa-coins text-gray-500"></i>
                        <span id="current-currency">ZAR</span>
                        <i class="fas fa-chevron-down text-xs" :class="{'rotate-180': open}"></i>
                    </button>

                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                         style="display: none;">

                        <div class="px-3 py-2 text-xs font-medium text-gray-500 border-b">
                            Select Currency
                        </div>

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

                        <div class="border-t mt-1 pt-1">
                            <a href="<?= base_url('driver/settings/currency') ?>" class="w-full text-left px-4 py-2 text-xs text-indigo-600 hover:bg-indigo-50 flex items-center">
                                <i class="fas fa-cog mr-2"></i>
                                More Settings
                            </a>
                        </div>
                    </div>
                </div>

                <button id="cartToggle" class="relative flex items-center space-x-2 px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                    <div class="relative">
                        <i class="fas fa-shopping-cart text-lg"></i>
                        <span id="cartCount" class="absolute -top-1 -right-1 bg-green-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                    </div>
                    <span class="text-sm font-medium">Complete Order</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Business Info Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-16 h-16 bg-green-100 rounded-lg flex items-center justify-center">
                        <?php if (!empty($merchant['business_image_url'])): ?>
                            <img src="<?= base_url($merchant['business_image_url']) ?>" alt="<?= esc($merchant['business_name']) ?>" class="w-full h-full object-cover rounded-lg">
                        <?php else: ?>
                            <span class="text-green-600 font-bold text-xl"><?= strtoupper(substr($merchant['business_name'], 0, 2)) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-900 mb-1"><?= esc($merchant['business_name']) ?></h2>
                    <p class="text-gray-600 text-sm mb-2"><?= esc($merchant['physical_address']) ?></p>
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <span><i class="fas fa-phone mr-1"></i><?= esc($merchant['business_contact_number']) ?></span>
                        <?php if (!empty($merchant['business_whatsapp_number'])): ?>
                            <span><i class="fab fa-whatsapp mr-1 text-green-500"></i><?= esc($merchant['business_whatsapp_number']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php if (!empty($merchant['business_description'])): ?>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-gray-700 text-sm"><?= esc($merchant['business_description']) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Search and Categories Section -->
        <div class="bg-white rounded-lg shadow-sm mb-6">
            <div class="p-6">
                <!-- Search Bar with Category Dropdown -->
                <div class="flex gap-3 search-form-wrapper">
                    <!-- Category Dropdown -->
                    <div class="relative">
                        <select id="categorySelect"
                                class="h-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white text-gray-700 appearance-none cursor-pointer min-w-[180px]">
                            <option value="all">All Categories</option>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= strtolower(esc($category['name'])) ?>">
                                        <?= esc($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <input type="text" id="searchInput" placeholder="Search services..."
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services & Products Section -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Services & Products</h3>
            </div>
            
            <?php if (!empty($listings)): ?>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 services-grid">
                        <?php foreach ($listings as $listing): ?>
                            <div class="service-item border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow overflow-hidden" data-category="<?= strtolower(esc($listing['category'] ?? 'general')) ?>">
                                <div class="w-full h-40 sm:h-48 bg-gray-100 rounded-md overflow-hidden mb-3">
                                    <?php
                                        $imagePathRaw = $listing['main_image_path'] ?? ($listing['image_url'] ?? '');
                                        if (!empty($imagePathRaw) && preg_match('#^https?://#', $imagePathRaw)) {
                                            $imageSrc = $imagePathRaw;
                                        } else if (!empty($imagePathRaw)) {
                                            $imageSrc = get_listing_image_url($imagePathRaw);
                                        } else {
                                            $imageSrc = '';
                                        }
                                    ?>
                                    <?php if (!empty($imageSrc)): ?>
                                        <img src="<?= esc($imageSrc) ?>" alt="<?= esc($listing['title']) ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center">
                                            <i class="fas fa-box text-gray-400 text-2xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <h4 class="service-title font-semibold text-gray-900 mb-1"><?= esc($listing['title']) ?></h4>
                                <p class="service-description text-gray-600 text-sm mb-3"><?= esc(substr($listing['description'], 0, 140)) ?><?= strlen($listing['description']) > 140 ? '...' : '' ?></p>

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <button onclick="addToCart(<?= $listing['id'] ?>, '<?= esc($listing['title']) ?>', <?= $listing['price_numeric'] ?? $listing['price'] ?? 0 ?>, '<?= esc($merchant['business_name']) ?>', <?= $listing['merchant_id'] ?>, '<?= esc($listing['currency_code'] ?? 'ZAR') ?>', <?= $listing['location_id'] ?? 'null' ?>, '<?= esc($listing['location_name'] ?? '') ?>')"
                                                class="flex items-center px-3 py-1.5 bg-green-500 text-white text-sm rounded-md hover:bg-green-600 transition-colors whitespace-nowrap">
                                            <i class="fas fa-plus mr-1"></i>Add to Cart
                                        </button>
                                        <?php $priceValue = $listing['price'] ?? ''; ?>
                                        <?php if ($priceValue !== ''): ?>
                                            <div class="flex items-baseline space-x-2">
                                                <span class="text-base font-bold text-green-600">
                                                    <?= display_listing_price($listing) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <a href="<?= base_url('driver/service/' . esc($listing['id'], 'url')) ?>" 
                                       class="px-3 py-1.5 border border-gray-300 text-gray-700 text-sm rounded-md hover:bg-gray-50 transition-colors whitespace-nowrap">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="p-12 text-center">
                    <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">No Services Available</h4>
                    <p class="text-gray-500">This merchant hasn't listed any services yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cart Sidebar -->
    <div id="cartSidebar" class="fixed inset-y-0 right-0 w-80 bg-white shadow-xl cart-sidebar z-50">
        <div class="flex flex-col h-full">
            <!-- Cart Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Your Cart</h3>
                <button id="closeCart" class="p-2 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-4">
                <div id="cartItems" class="space-y-3">
                    <!-- Cart items will be dynamically added here -->
                </div>
                <div id="emptyCart" class="text-center py-8">
                    <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">Your cart is empty</p>
                </div>
            </div>
            
            <!-- Cart Footer -->
            <div class="border-t border-gray-200 p-4">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-lg font-semibold text-gray-900">Total:</span>
                    <span id="cartTotal" class="text-lg font-bold text-green-600">R0.00</span>
                </div>
                <button id="checkoutBtn" class="w-full bg-green-500 text-white py-3 rounded-md font-medium hover:bg-green-600 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed" disabled>
                    Proceed to Checkout
                </button>
            </div>
        </div>
    </div>

    <!-- Cart Overlay -->
    <div id="cartOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40"></div>

    <script>
        // Cart persistence using localStorage
        let cart = JSON.parse(localStorage.getItem('truckers_cart') || '[]');
        let cartTotal = 0;

        // Cart functionality with localStorage persistence
        function saveCart() {
            localStorage.setItem('truckers_cart', JSON.stringify(cart));
        }

        function addToCart(id, title, price, merchantName, merchantId, currency = 'ZAR', locationId = null, locationName = '') {
            const existingItem = cart.find(item => item.id === id);

            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: id,
                    title: title,
                    price: price,
                    currency: currency,
                    merchant_name: merchantName,
                    merchant_id: merchantId,
                    location_id: locationId,
                    location_name: locationName,
                    quantity: 1
                });
            }
            
            saveCart();
            updateCartUI();
            // Update header cart count if function exists
            if (typeof updateHeaderCartCount === 'function') {
                updateHeaderCartCount();
            }
            showCartNotification(title);
        }

        function removeFromCart(id) {
            cart = cart.filter(item => item.id !== id);
            saveCart();
            updateCartUI();
            // Update header cart count if function exists
            if (typeof updateHeaderCartCount === 'function') {
                updateHeaderCartCount();
            }
        }

        function updateQuantity(id, quantity) {
            const item = cart.find(item => item.id === id);
            if (item) {
                if (quantity <= 0) {
                    removeFromCart(id);
                } else {
                    item.quantity = quantity;
                    saveCart();
                    updateCartUI();
                    // Update header cart count if function exists
                    if (typeof updateHeaderCartCount === 'function') {
                        updateHeaderCartCount();
                    }
                }
            }
        }

        function updateCartUI() {
            const cartCount = document.getElementById('cartCount');
            const cartItems = document.getElementById('cartItems');
            const cartTotal = document.getElementById('cartTotal');
            const emptyCart = document.getElementById('emptyCart');
            const checkoutBtn = document.getElementById('checkoutBtn');
            
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            cartCount.textContent = totalItems;
            cartTotal.textContent = `R${totalPrice.toFixed(2)}`;
            
            if (cart.length === 0) {
                cartItems.innerHTML = '';
                emptyCart.style.display = 'block';
                checkoutBtn.disabled = true;
            } else {
                emptyCart.style.display = 'none';
                checkoutBtn.disabled = false;
                
                cartItems.innerHTML = cart.map(item => `
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900 text-sm">${item.title}</h4>
                            <p class="text-green-600 font-semibold">R${item.price.toFixed(2)}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="updateQuantity(${item.id}, ${item.quantity - 1})" class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-300">
                                <i class="fas fa-minus text-xs"></i>
                            </button>
                            <span class="w-8 text-center text-sm font-medium">${item.quantity}</span>
                            <button onclick="updateQuantity(${item.id}, ${item.quantity + 1})" class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-300">
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                            <button onclick="removeFromCart(${item.id})" class="ml-2 text-red-500 hover:text-red-700">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            }
        }

        function showCartNotification(itemName) {
            // Simple notification - you can enhance this
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md shadow-lg z-50';
            notification.innerHTML = `<i class="fas fa-check mr-2"></i>${itemName} added to cart`;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Cart toggle - redirect to checkout
        document.getElementById('cartToggle').addEventListener('click', function() {
            if (cart.length > 0) {
                window.location.href = '<?= base_url('order/checkout') ?>';
            } else {
                alert('Your cart is empty. Add some items first!');
            }
        });

        document.getElementById('closeCart').addEventListener('click', function() {
            document.getElementById('cartSidebar').classList.remove('open');
            document.getElementById('cartOverlay').classList.add('hidden');
        });

        document.getElementById('cartOverlay').addEventListener('click', function() {
            document.getElementById('cartSidebar').classList.remove('open');
            document.getElementById('cartOverlay').classList.add('hidden');
        });

        // Checkout functionality
        document.getElementById('checkoutBtn').addEventListener('click', function() {
            if (cart.length > 0) {
                alert('Checkout functionality will be implemented soon!');
                // Here you would typically redirect to checkout page or open checkout modal
            }
        });

        // Initialize cart UI
        updateCartUI();

        // Search and Filter Functionality
        let allListings = [];
        let filteredListings = [];
        let currentCategory = 'all';
        let currentSearchTerm = '';

        // Initialize listings data
        function initializeListings() {
            const listingElements = document.querySelectorAll('.service-item');
            allListings = Array.from(listingElements).map(element => {
                return {
                    element: element,
                    title: element.querySelector('.service-title').textContent.toLowerCase(),
                    description: element.querySelector('.service-description').textContent.toLowerCase(),
                    category: element.dataset.category || 'general'
                };
            });
            filteredListings = [...allListings];
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            currentSearchTerm = e.target.value.toLowerCase();
            applyFilters();
        });

        // Category dropdown change handler
        document.getElementById('categorySelect').addEventListener('change', function() {
            currentCategory = this.value;
            applyFilters();
        });

        // Apply both search and category filters
        function applyFilters() {
            filteredListings = allListings.filter(listing => {
                const matchesSearch = currentSearchTerm === '' || 
                    listing.title.includes(currentSearchTerm) || 
                    listing.description.includes(currentSearchTerm);
                
                const matchesCategory = currentCategory === 'all' || 
                    listing.category === currentCategory;
                
                return matchesSearch && matchesCategory;
            });
            
            updateListingsDisplay();
        }

        // Update the display of listings
        function updateListingsDisplay() {
            allListings.forEach(listing => {
                if (filteredListings.includes(listing)) {
                    listing.element.style.display = 'block';
                } else {
                    listing.element.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            const noResultsMsg = document.getElementById('noResultsMessage');
            if (filteredListings.length === 0) {
                if (!noResultsMsg) {
                    const servicesGrid = document.querySelector('.services-grid');
                    const noResults = document.createElement('div');
                    noResults.id = 'noResultsMessage';
                    noResults.className = 'col-span-2 text-center py-8';
                    noResults.innerHTML = `
                        <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                        <h4 class="text-lg font-medium text-gray-900 mb-2">No services found</h4>
                        <p class="text-gray-500">Try adjusting your search or category filter.</p>
                    `;
                    servicesGrid.appendChild(noResults);
                }
            } else if (noResultsMsg) {
                noResultsMsg.remove();
            }
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeListings();
            updateCartUI(); // Initialize cart UI with persisted data
            showPaymentNotification(); // Show payment notification popup
        });

        // Show payment notification popup
        function showPaymentNotification() {
            // Check if user has already seen this notification in this session
            if (sessionStorage.getItem('paymentNotificationShown')) {
                return;
            }

            // Create and show the popup
            const popup = document.createElement('div');
            popup.id = 'paymentNotificationPopup';
            popup.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
            popup.innerHTML = `
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-medium text-gray-900">Payment Information</h3>
                            </div>
                        </div>
                        <div class="mb-6">
                            <p class="text-gray-600 text-sm leading-relaxed">
                                <strong>Important:</strong> You don't pay through the Truckers Africa app.
                                All payments are made directly to the merchant when you receive their services or products.
                            </p>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button onclick="closePaymentNotification()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                Got it
                            </button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(popup);

            // Add animation
            setTimeout(() => {
                popup.querySelector('div > div').classList.add('scale-100');
            }, 10);

            // Mark as shown in session storage
            sessionStorage.setItem('paymentNotificationShown', 'true');
        }

        // Close payment notification popup
        function closePaymentNotification() {
            const popup = document.getElementById('paymentNotificationPopup');
            if (popup) {
                popup.querySelector('div > div').classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    popup.remove();
                }, 200);
            }
        }

        // Close popup when clicking outside
        document.addEventListener('click', function(e) {
            const popup = document.getElementById('paymentNotificationPopup');
            if (popup && e.target === popup) {
                closePaymentNotification();
            }
        });
    </script>

    <?php 
    $current_page = 'services';
    echo view('driver/templates/bottom_nav', ['current_page' => $current_page]); 
    ?>
</body>
</html>
