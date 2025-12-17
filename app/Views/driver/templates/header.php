<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes" />
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title><?= esc($page_title) ?></title>
    
    <!-- CHANGED: Corrected the path to the icon using base_url() -->
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/logo-icon-black.png') ?>">

    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>
      // ... (your tailwind config remains the same) ...
      tailwind.config = {
        theme: {
          extend: {
            colors: { 
              primary: "#2563eb",
              secondary: "#fb923c",
            },
            borderRadius: {
              button: "8px",
            },
          },
        },
      };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="<?= base_url('assets/css/driver_dashboard.css') ?>">
    
    <!-- Leaflet CSS and JS for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <!-- Alpine.js - Core functionality for interactive components -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    
    <style>
      /* ... (your styles remain the same) ... */
      body {
        font-family: 'Inter', sans-serif;
        background-color: #0c111c;
        color: #e2e8f0;
      }
      .font-condensed { font-family: 'Roboto Condensed', sans-serif; }
      .text-dark-grey {
    color: black;
}
    </style>
</head>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-PCRPK4W4C5"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-PCRPK4W4C5');
</script>
<body class="bg-gray-50 font-sans">

<script>
// Cart functionality for header
function toggleCart() {
    // Check if cart has items
    const cart = JSON.parse(localStorage.getItem('truckers_cart') || '[]');
    
    if (cart.length === 0) {
        alert('Your cart is empty. Add some items first!');
        return;
    }
    
    // Redirect to checkout page
    window.location.href = '<?= base_url('order/checkout') ?>';
}

// Update cart count in header
function updateHeaderCartCount() {
    const cart = JSON.parse(localStorage.getItem('truckers_cart') || '[]');
    const cartCount = document.getElementById('cartCount');
    
    if (cart.length > 0) {
        cartCount.textContent = cart.length;
        cartCount.classList.remove('hidden');
    } else {
        cartCount.classList.add('hidden');
    }
}

// Update notification count in header
function updateHeaderNotificationCount() {
    fetch('<?= base_url('notifications/count') ?>', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notificationCount = document.getElementById('notificationCount');
            const bottomNavBadge = document.getElementById('bottomNavNotificationBadge');
            
            if (data.unread_count > 0) {
                // Update header badge
                if (notificationCount) {
                    notificationCount.textContent = data.unread_count;
                    notificationCount.classList.remove('hidden');
                }
                // Update bottom nav badge
                if (bottomNavBadge) {
                    bottomNavBadge.textContent = data.unread_count;
                    bottomNavBadge.classList.remove('hidden');
                }
            } else {
                // Hide both badges
                if (notificationCount) {
                    notificationCount.classList.add('hidden');
                }
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

// Initialize cart and notification counts on page load
document.addEventListener('DOMContentLoaded', function() {
    updateHeaderCartCount();
    updateHeaderNotificationCount();
    
    // Listen for cart updates from other pages
    window.addEventListener('storage', function(e) {
        if (e.key === 'truckers_cart') {
            updateHeaderCartCount();
        }
    });
});
</script>

<header id="header" class="bg-white shadow-lg fixed top-0 left-0 right-0 z-50">
    <div class="flex items-center justify-between px-4 py-3">
        <div class="flex items-center space-x-3">
            <img src="<?= base_url('assets/images/logo-icon-black.png') ?>" alt="Truckers Africa Logo" class="w-8 h-8">
            <h1 class="text-xl font-bold text-gray-800">Truckers Africa</h1>
        </div>
        <div class="flex items-center space-x-4">
            <a href="<?= base_url('driver/notifications') ?>" id="notificationBtn" class="relative">
                <i class="fas fa-bell text-gray-600 text-xl hover:text-gray-800 transition-colors"></i>
                <span id="notificationCount" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center hidden">0</span>
            </a>
            <button id="cartBtn" class="relative" onclick="toggleCart()">
                <i class="fas fa-shopping-cart text-gray-600 text-xl"></i>
                <span id="cartCount" class="absolute -top-1 -right-1 bg-green-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center hidden">0</span>
            </button>
            <!-- Profile Dropdown -->
            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none">
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

                    <a href="<?= base_url('driver/settings/currency') ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-coins mr-3 text-gray-400"></i>
                        Currency Settings
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
</header>

<div class="pt-16"> <!-- Padding to offset fixed header -->