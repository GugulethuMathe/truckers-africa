<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title) ?> - Truckers Africa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .cart-sidebar {
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
        }
        .cart-sidebar.open {
            transform: translateX(0);
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
                    <h1 class="text-xl font-semibold text-gray-900"><?= esc($listing['title'] ?? 'Service Details') ?></h1>
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
    <?php
        $allImages = [];
        // Add main image first
        if (!empty($listing['main_image_path'])) {
            $allImages[] = get_listing_image_url($listing['main_image_path']);
        }
        // Add gallery images
        if (!empty($gallery_images)) {
            foreach ($gallery_images as $image) {
                // gallery_images returns arrays from the model
                if (!empty($image['image_path'])) {
                    $allImages[] = get_listing_image_url($image['image_path']);
                }
            }
        }
        $allImages = array_unique($allImages);
        $firstImage = $allImages[0] ?? base_url('assets/images/placeholder.png');
    ?>

        <!-- Service Image Gallery -->
        <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden">
            <div class="relative h-64 bg-gray-200">
                <img id="mainImage" src="<?= $firstImage ?>" alt="<?= esc($listing['title']) ?>" class="w-full h-full object-cover">
                <?php if (count($allImages) > 1): ?>
                    <div class="absolute bottom-4 right-4 bg-black bg-opacity-50 text-white px-2 py-1 rounded text-sm">
                        <span id="imageCounter">1 / <?= count($allImages) ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (count($allImages) > 1): ?>
                <div class="flex space-x-2 p-3 bg-white overflow-x-auto">
                    <?php foreach ($allImages as $index => $image): ?>
                        <img onclick="changeMainImage('<?= $image ?>', <?= $index + 1 ?>)" 
                             src="<?= $image ?>" 
                             alt="Service image <?= $index + 1 ?>" 
                             class="w-16 h-16 object-cover rounded cursor-pointer border-2 <?= $index === 0 ? 'border-green-500' : 'border-gray-200' ?> hover:border-green-400 transition-colors">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Service Information -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2"><?= esc($listing['title'] ?? 'Service Title') ?></h2>
                    <?php if (!empty($location)): ?>
                        <p class="text-gray-600 mb-3">
                            <?= esc($location['location_name'] ?? 'Branch Location') ?>
                            <?php if ($location['is_primary'] == 1): ?>
                                <span class="ml-2 bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs">Primary</span>
                            <?php else: ?>
                                <span class="ml-2 px-2 py-0.5 rounded-full text-xs" style="background-color: #e6e8eb; color: #0e2140;">BR</span>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <div class="flex items-center space-x-4 mb-3">
                        <div class="flex items-center text-yellow-400">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star text-sm"></i>
                            <?php endfor; ?>
                            <span class="text-gray-600 text-sm ml-2">4.8 (24 reviews)</span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-3xl font-bold text-green-600"><?= display_listing_price($listing) ?></p>
                    <p class="text-sm text-gray-500">Starting from</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-2 mb-4">
                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                    <i class="fas fa-circle text-xs mr-1"></i>Available Now
                </span>
                <?php if (isset($is_verified) && $is_verified): ?>
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                        <i class="fas fa-check-circle text-xs mr-1"></i>Verified
                    </span>
                <?php endif; ?>
                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">Featured</span>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex space-x-3 mt-6">
                <button onclick="addToCart(<?= $listing['id'] ?>, '<?= esc($listing['title']) ?>', <?= $listing['price_numeric'] ?? $listing['price'] ?? 0 ?>, '<?= esc($merchant['business_name']) ?>', <?= $listing['merchant_id'] ?>, '<?= esc($listing['currency_code'] ?? 'ZAR') ?>', <?= $listing['location_id'] ?? 'null' ?>, '<?= esc($location['location_name'] ?? '') ?>')" class="flex-1 bg-green-500 text-white px-6 py-3 rounded-md font-medium hover:bg-green-600 transition-colors">
                    <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                </button>
                <button id="saveBtn" onclick="toggleSaveListing(<?= $listing['id'] ?>, '<?= esc($listing['title']) ?>', '<?= esc($merchant['business_name']) ?>')" class="px-6 py-3 border border-green-500 text-green-600 rounded-md font-medium hover:bg-green-50 transition-colors">
                    <i id="saveIcon" class="far fa-heart mr-2"></i><span id="saveText">Save</span>
                </button>
                <button onclick="shareListing('<?= esc($listing['title']) ?>', '<?= esc($merchant['business_name']) ?>')" class="px-6 py-3 border border-gray-300 text-gray-600 rounded-md font-medium hover:bg-gray-50 transition-colors">
                    <i class="fas fa-share mr-2"></i>Share
                </button>
            </div>
        </div>

        <!-- Service Description -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">About This Service</h3>
            <p class="text-gray-700 leading-relaxed"><?= nl2br(esc($listing['description'] ?? 'No description available.')) ?></p>
        </div>

        <!-- Service Features -->
        <?php 
        $features = json_decode($listing['features'] ?? '[]', true);
        if (!empty($features)): 
        ?>
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">What's Included</h3>
            <ul class="space-y-3">
                <?php foreach ($features as $feature): ?>
                <li class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <span class="text-gray-700"><?= esc($feature) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Branch/Location Information -->
        <?php if (!empty($location)): ?>
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Branch Details</h3>
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-16 h-16 rounded-lg flex items-center justify-center" style="background-color: #e6e8eb;">
                        <i class="fas fa-map-marker-alt text-2xl" style="color: #0e2140;"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <h4 class="text-lg font-semibold text-gray-900"><?= esc($location['location_name'] ?? 'Branch Location') ?></h4>
                        <?php if ($location['is_primary'] == 1): ?>
                            <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs">Primary</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 rounded-full text-xs" style="background-color: #e6e8eb; color: #0e2140;">BR</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-gray-600 text-sm mb-2"><i class="fas fa-map-marker-alt mr-1"></i><?= esc($location['physical_address'] ?? 'Address not available') ?></p>
                    <div class="flex flex-col space-y-1 text-sm text-gray-500">
                        <?php if (!empty($location['contact_number'])): ?>
                            <span><i class="fas fa-phone mr-1"></i><?= esc($location['contact_number']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($location['email'])): ?>
                            <span><i class="fas fa-envelope mr-1"></i><?= esc($location['email']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($location['whatsapp_number'])): ?>
                            <span><i class="fab fa-whatsapp mr-1 text-green-500"></i><?= esc($location['whatsapp_number']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($location['whatsapp_number'])): ?>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <a href="https://wa.me/<?= esc(preg_replace('/[^0-9]/', '', $location['whatsapp_number'])) ?>" target="_blank" class="inline-flex items-center px-4 py-2 text-white rounded-md transition-colors" style="background-color: #25D366;" onmouseover="this.style.backgroundColor='#128C7E'" onmouseout="this.style.backgroundColor='#25D366'">
                    <i class="fab fa-whatsapp mr-2"></i>Contact on WhatsApp
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Reviews Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Reviews & Ratings</h3>
                <button class="text-green-600 font-medium hover:text-green-700">View All</button>
            </div>
            <div class="text-center py-8">
                <i class="fas fa-comment-slash text-4xl text-gray-300 mb-4"></i>
                <h4 class="text-lg font-medium text-gray-900 mb-2">No reviews yet</h4>
                <p class="text-gray-500">Be the first to review this service.</p>
            </div>
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

        // Image gallery functionality
        function changeMainImage(imageSrc, imageNumber) {
            document.getElementById('mainImage').src = imageSrc;
            document.getElementById('imageCounter').textContent = imageNumber + ' / <?= count($allImages) ?>';
            
            // Update thumbnail borders
            document.querySelectorAll('.w-16.h-16').forEach((thumb, index) => {
                if (index === imageNumber - 1) {
                    thumb.classList.add('border-green-500');
                    thumb.classList.remove('border-gray-200');
                } else {
                    thumb.classList.remove('border-green-500');
                    thumb.classList.add('border-gray-200');
                }
            });
        }

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
            }
        });

        // Initialize cart UI
        updateCartUI();

        // Show payment notification popup on page load
        showPaymentNotification();

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

        // Save/Unsave Listing functionality
        function toggleSaveListing(listingId, listingTitle, merchantName) {
            let savedListings = JSON.parse(localStorage.getItem('truckers_saved_listings') || '[]');
            const saveBtn = document.getElementById('saveBtn');
            const saveIcon = document.getElementById('saveIcon');
            const saveText = document.getElementById('saveText');

            const existingIndex = savedListings.findIndex(item => item.id === listingId);

            if (existingIndex !== -1) {
                // Remove from saved
                savedListings.splice(existingIndex, 1);
                localStorage.setItem('truckers_saved_listings', JSON.stringify(savedListings));

                // Update button appearance
                saveBtn.classList.remove('bg-green-500', 'text-white');
                saveBtn.classList.add('border-green-500', 'text-green-600');
                saveIcon.classList.remove('fas');
                saveIcon.classList.add('far');
                saveText.textContent = 'Save';

                showNotification('Removed from saved listings', 'info');
            } else {
                // Add to saved
                savedListings.push({
                    id: listingId,
                    title: listingTitle,
                    merchant: merchantName,
                    url: window.location.href,
                    saved_at: new Date().toISOString()
                });
                localStorage.setItem('truckers_saved_listings', JSON.stringify(savedListings));

                // Update button appearance
                saveBtn.classList.add('bg-green-500', 'text-white');
                saveBtn.classList.remove('border-green-500', 'text-green-600');
                saveIcon.classList.remove('far');
                saveIcon.classList.add('fas');
                saveText.textContent = 'Saved';

                showNotification('Added to saved listings', 'success');
            }
        }

        // Share Listing functionality
        function shareListing(listingTitle, merchantName) {
            const shareData = {
                title: listingTitle,
                text: `Check out ${listingTitle} from ${merchantName} on Truckers Africa`,
                url: window.location.href
            };

            // Check if Web Share API is supported
            if (navigator.share) {
                navigator.share(shareData)
                    .then(() => console.log('Shared successfully'))
                    .catch((error) => {
                        console.log('Error sharing:', error);
                        fallbackShare();
                    });
            } else {
                // Fallback: Copy to clipboard
                fallbackShare();
            }
        }

        // Fallback share function (copy to clipboard)
        function fallbackShare() {
            const url = window.location.href;

            // Create temporary input to copy URL
            const tempInput = document.createElement('input');
            tempInput.value = url;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);

            showNotification('Link copied to clipboard!', 'success');
        }

        // Show notification helper
        function showNotification(message, type = 'success') {
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'info' ? 'bg-blue-500' : 'bg-gray-500';
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 ${bgColor} text-white px-4 py-3 rounded-md shadow-lg z-50 flex items-center space-x-2`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'info' ? 'info-circle' : 'bell'}"></i>
                <span>${message}</span>
            `;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transition = 'opacity 0.3s';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Check if listing is already saved on page load
        document.addEventListener('DOMContentLoaded', function() {
            const listingId = <?= $listing['id'] ?>;
            const savedListings = JSON.parse(localStorage.getItem('truckers_saved_listings') || '[]');
            const isSaved = savedListings.some(item => item.id === listingId);

            if (isSaved) {
                const saveBtn = document.getElementById('saveBtn');
                const saveIcon = document.getElementById('saveIcon');
                const saveText = document.getElementById('saveText');

                saveBtn.classList.add('bg-green-500', 'text-white');
                saveBtn.classList.remove('border-green-500', 'text-green-600');
                saveIcon.classList.remove('far');
                saveIcon.classList.add('fas');
                saveText.textContent = 'Saved';
            }
        });
    </script>

    <?php 
    $current_page = 'services';
    echo view('driver/templates/bottom_nav', ['current_page' => $current_page]); 
    ?>
</body>
</html>
