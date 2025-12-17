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
                    <h1 class="text-xl font-semibold text-gray-900"><?= esc($page_title) ?></h1>
                </div>

                <button onclick="window.location.href='<?= base_url('order/checkout') ?>'" class="relative flex items-center space-x-2 px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                    <div class="relative">
                        <i class="fas fa-shopping-cart text-lg"></i>
                        <span id="cartCount" class="absolute -top-1 -right-1 bg-green-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                    </div>
                    <span class="text-sm font-medium">Complete Order</span>
                </button>

                <!-- Profile Dropdown -->
                <div class="relative ml-2" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open" class="flex items-center space-x-2 p-2 text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors focus:outline-none">
                        <?php
                            // Use the helper function to get driver's profile image
                            $profileSrc = get_driver_profile_image();
                        ?>
                        <img src="<?= esc($profileSrc) ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover hover:ring-2 hover:ring-green-500 transition-all" onerror="this.onerror=null;this.src='<?= base_url('assets/images/logo-icon-black.png') ?>';">
                        <i class="fas fa-chevron-down text-xs text-gray-500" :class="{'rotate-180': open}" style="transition: transform 0.2s;"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                         style="display: none;">

                        <a href="<?= base_url('profile/driver') ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-user mr-3 text-gray-400"></i>
                            My Profile
                        </a>

                        <a href="<?= base_url('profile/driver/change-password') ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-key mr-3 text-gray-400"></i>
                            Change Password
                        </a>

                        <div class="border-t border-gray-100 my-1"></div>

                        <a href="<?= base_url('logout') ?>" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                            <i class="fas fa-sign-out-alt mr-3 text-red-400"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Page Info Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-16 h-16 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-store text-green-600 text-2xl"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-900 mb-1">All Services & Products</h2>
                    <p class="text-gray-600 text-sm mb-2">Discover services and products from verified merchants across the platform</p>
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <span><i class="fas fa-box mr-1"></i><?= number_format($total_results) ?> services available</span>
                        <span><i class="fas fa-shield-alt mr-1"></i>All verified merchants</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Categories Section -->
        <div class="bg-white rounded-lg shadow-sm mb-6">
            <div class="p-6">
                <!-- Search Bar with Category Dropdown -->
                <form id="searchForm" method="GET" action="<?= base_url('driver/services') ?>">
                    <div class="flex gap-3 search-form-wrapper">
                        <!-- Category Dropdown -->
                        <div class="relative">
                            <select id="categorySelect" name="category"
                                    class="h-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white text-gray-700 appearance-none cursor-pointer min-w-[180px]">
                                <option value="all" <?= empty($selected_category) || $selected_category === 'all' ? 'selected' : '' ?>>All Categories</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= esc($category['id']) ?>" <?= $selected_category == $category['id'] ? 'selected' : '' ?>>
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
                            <input type="text" id="searchInput" name="search" placeholder="Search services, products, or merchants..."
                                   value="<?= esc($search_term) ?>"
                                   class="w-full pl-10 pr-20 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <button type="submit" class="absolute inset-y-0 right-0 pr-3 flex items-center text-green-600 hover:text-green-800 font-medium">
                                Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Services & Products Section -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Available Services & Products</h3>
                    <span class="text-sm text-gray-500"><?= number_format($total_results) ?> results</span>
                </div>
            </div>
            
            <?php if (!empty($listings)): ?>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 services-grid">
                        <?php foreach ($listings as $listing): ?>
                            <div class="service-item border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow overflow-hidden" data-category="<?= strtolower(esc($listing['category_name'] ?? 'general')) ?>">
                                <div class="w-full h-40 sm:h-48 bg-gray-100 rounded-md overflow-hidden mb-3">
                                    <?php
                                        $imagePathRaw = $listing['main_image_path'] ?? ($listing['image_url'] ?? '');
                                        if (!empty($imagePathRaw) && preg_match('#^https?://#', $imagePathRaw)) {
                                            // External URL, use as-is
                                            $imageSrc = $imagePathRaw;
                                        } else if (!empty($imagePathRaw)) {
                                            // Use helper function for proper path handling
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
                                <div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex-1">
                                                <h4 class="service-title text-lg font-semibold text-gray-900 mb-1"><?= esc($listing['title']) ?></h4>
                                                <p class="service-description text-gray-600 text-sm mb-2 line-clamp-2"><?= esc($listing['description']) ?></p>
                                                
                                                <!-- Branch/Location Info -->
                                                <?php if (!empty($listing['location_name'])): ?>
                                                    <div class="flex items-center gap-2 flex-wrap mb-2">
                                                        <span class="text-sm text-gray-700 font-medium"><?= esc($listing['location_name']) ?></span>
                                                        <?php if ($listing['is_primary'] == 1): ?>
                                                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-1.5 py-0.5 rounded-full">Primary</span>
                                                        <?php else: ?>
                                                            <span class="inline-block text-xs px-1.5 py-0.5 rounded-full" style="background-color: #e6e8eb; color: #0e2140;">BR</span>
                                                        <?php endif; ?>
                                                        <?php if (isset($listing['is_verified']) && $listing['is_verified']): ?>
                                                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-1.5 py-0.5 rounded-full">
                                                                <i class="fas fa-check-circle text-xs mr-0.5"></i>Verified
                                                            </span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($listing['category_name'])): ?>
                                                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full"><?= esc($listing['category_name']) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <!-- Fallback: Show merchant name if no location -->
                                                    <div class="flex items-center space-x-2 mb-2">
                                                        <span class="text-sm text-gray-700"><?= esc($listing['business_name']) ?></span>
                                                        <?php if (isset($listing['is_verified']) && $listing['is_verified']): ?>
                                                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-1.5 py-0.5 rounded-full">
                                                                <i class="fas fa-check-circle text-xs mr-0.5"></i>Verified
                                                            </span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($listing['category_name'])): ?>
                                                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full"><?= esc($listing['category_name']) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <!-- Location -->
                                                <?php
                                                    // Show branch address if available, otherwise show merchant address
                                                    $displayAddress = !empty($listing['location_address']) ? $listing['location_address'] : $listing['physical_address'];
                                                ?>
                                                <?php if (!empty($displayAddress)): ?>
                                                    <p class="text-xs text-gray-500 mb-2">
                                                        <i class="fas fa-map-marker-alt mr-1"></i><?= esc($displayAddress) ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            <!-- Price moved near buttons below -->
                                        </div>
                                        
                                        <div class="flex items-center space-x-3 mt-3">
                                            <button onclick="addToCart(<?= $listing['id'] ?>, '<?= esc($listing['title']) ?>', <?= $listing['price_numeric'] ?? 0 ?>, '<?= esc($listing['business_name']) ?>', <?= $listing['merchant_id'] ?>, '<?= $listing['currency_code'] ?? 'ZAR' ?>', <?= $listing['location_id'] ?? 'null' ?>, '<?= esc($listing['location_name'] ?? '') ?>')"
                                                    class="flex items-center px-2 py-1 sm:px-3 sm:py-1.5 bg-green-500 text-white text-xs sm:text-sm rounded-md hover:bg-green-600 transition-colors whitespace-nowrap">
                                                <i class="fas fa-plus mr-1"></i>Add to Cart
                                            </button>
                                            <?php $priceValue = $listing['price'] ?? ''; ?>
                                            <?php if ($priceValue !== ''): ?>
                                                <div class="flex items-baseline space-x-2">
                                                    <span class="text-sm sm:text-base font-bold text-green-600">
                                                        <?= display_listing_price($listing) ?>
                                                    </span>
                                                    <?php if (!empty($listing['unit'])): ?>
                                                        <span class="text-xs text-gray-500">per <?= esc($listing['unit']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            <button onclick="window.location.href='<?= base_url('driver/service/' . $listing['id']) ?>'"
                                                    class="px-2 py-1 sm:px-3 sm:py-1.5 border border-gray-300 text-gray-700 text-xs sm:text-sm rounded-md hover:bg-gray-50 transition-colors whitespace-nowrap">
                                                View Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="p-12 text-center">
                    <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">No services found</h4>
                    <p class="text-gray-500 mb-4">
                        <?php if (!empty($search_term)): ?>
                            No services match your search for "<?= esc($search_term) ?>".
                        <?php else: ?>
                            No services are currently available.
                        <?php endif; ?>
                    </p>
                    <div class="space-x-2">
                        <button onclick="clearSearch()" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors">
                            Clear Search
                        </button>
                        <button onclick="window.location.href='/dashboard/driver'" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                            Back to Dashboard
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cart Sidebar (similar to merchant profile) -->
    <div id="cartSidebar" class="cart-sidebar fixed top-0 right-0 h-full w-80 bg-white shadow-xl z-50 flex flex-col">
        <div class="p-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Your Cart</h3>
                <button onclick="toggleCart()" class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto p-4">
            <div id="cartItems">
                <!-- Cart items will be populated by JavaScript -->
            </div>
        </div>
        
        <div class="border-t border-gray-200 p-4">
            <div class="flex items-center justify-between mb-4">
                <span class="text-lg font-semibold text-gray-900">Total:</span>
                <span id="cartTotal" class="text-lg font-bold text-green-600">R0.00</span>
            </div>
            <button onclick="window.location.href='<?= base_url('order/checkout') ?>'" class="w-full bg-green-500 text-white py-3 rounded-lg font-medium hover:bg-green-600 transition-colors">
                Complete Order
            </button>
        </div>
    </div>

    <!-- Overlay -->
    <div id="cartOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" onclick="toggleCart()"></div>

    <script>
        // Cart functionality
        let cart = JSON.parse(localStorage.getItem('truckers_cart') || '[]');

        function saveCart() {
            localStorage.setItem('truckers_cart', JSON.stringify(cart));
        }

        function addToCart(listingId, title, price, merchantName, merchantId, currency = 'ZAR', locationId = null, locationName = '') {
            const existingItem = cart.find(item => item.id === listingId);

            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: listingId,
                    title: title,
                    price: parseFloat(price) || 0,
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
            
            // Show success message
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check mr-1"></i>Added!';
            button.classList.remove('bg-green-500', 'hover:bg-green-600');
            button.classList.add('bg-green-600');
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('bg-green-600');
                button.classList.add('bg-green-500', 'hover:bg-green-600');
            }, 1500);
        }

        function removeFromCart(listingId) {
            cart = cart.filter(item => item.id !== listingId);
            saveCart();
            updateCartUI();
        }

        function updateQuantity(listingId, newQuantity) {
            const item = cart.find(item => item.id === listingId);
            if (item) {
                if (newQuantity <= 0) {
                    removeFromCart(listingId);
                } else {
                    item.quantity = newQuantity;
                    saveCart();
                    updateCartUI();
                }
            }
        }

        function updateCartUI() {
            const cartCount = document.getElementById('cartCount');
            const cartItems = document.getElementById('cartItems');
            const cartTotal = document.getElementById('cartTotal');
            
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            cartCount.textContent = totalItems;
            cartTotal.textContent = 'R' + totalPrice.toFixed(2);
            
            if (cart.length === 0) {
                cartItems.innerHTML = '<p class="text-gray-500 text-center py-8">Your cart is empty</p>';
            } else {
                cartItems.innerHTML = cart.map(item => `
                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg mb-3">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900 text-sm">${item.title}</h4>
                            <p class="text-xs text-gray-500">${item.merchant}</p>
                            <p class="text-sm font-semibold text-green-600">R${item.price.toFixed(2)}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="updateQuantity(${item.id}, ${item.quantity - 1})" class="w-6 h-6 bg-gray-200 rounded text-xs">-</button>
                            <span class="text-sm font-medium">${item.quantity}</span>
                            <button onclick="updateQuantity(${item.id}, ${item.quantity + 1})" class="w-6 h-6 bg-gray-200 rounded text-xs">+</button>
                            <button onclick="removeFromCart(${item.id})" class="w-6 h-6 bg-red-500 text-white rounded text-xs">×</button>
                        </div>
                    </div>
                `).join('');
            }
        }

        function toggleCart() {
            const sidebar = document.getElementById('cartSidebar');
            const overlay = document.getElementById('cartOverlay');
            
            sidebar.classList.toggle('open');
            overlay.classList.toggle('hidden');
        }

        // Search and filter functionality
        let allListings = [];
        let filteredListings = [];
        let currentCategory = '<?= $selected_category ?: 'all' ?>';
        let currentSearchTerm = '<?= $search_term ?>';

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

        // Category dropdown change handler
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('categorySelect');
            if (categorySelect) {
                categorySelect.addEventListener('change', function() {
                    // Submit the form when category changes
                    document.getElementById('searchForm').submit();
                });
            }
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

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            document.getElementById('categorySelect').value = 'all';
            currentSearchTerm = '';
            currentCategory = 'all';

            // Submit form to reload with cleared filters
            document.getElementById('searchForm').submit();
        }

        // Update notification count in bottom nav
        function updateNotificationCount() {
            fetch('<?= base_url('notifications/count') ?>', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const bottomNavBadge = document.getElementById('bottomNavNotificationBadge');
                    
                    if (data.unread_count > 0) {
                        // Update bottom nav badge
                        if (bottomNavBadge) {
                            bottomNavBadge.textContent = data.unread_count;
                            bottomNavBadge.classList.remove('hidden');
                        }
                    } else {
                        // Hide badge
                        if (bottomNavBadge) {
                            bottomNavBadge.classList.add('hidden');
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching notification count:', error);
            });
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeListings();
            updateCartUI();
            updateNotificationCount();
        });

        // Handle search form submission
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            // Let the form submit naturally to the server
            // The form will handle the search and category parameters
        });
        
        // Add real-time search as user types (optional)
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            currentSearchTerm = e.target.value.toLowerCase();

            // Apply client-side filtering immediately
            applyFilters();

            // Optional: Auto-submit after user stops typing for 1 second
            // searchTimeout = setTimeout(() => {
            //     if (e.target.value.trim() !== '<?= esc($search_term) ?>') {
            //         document.getElementById('searchForm').submit();
            //     }
            // }, 1000);
        });

        // Show payment notification popup on page load
        document.addEventListener('DOMContentLoaded', function() {
            showPaymentNotification();
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
