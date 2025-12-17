<?php
// Get current page to highlight active nav item
$current_page = $current_page ?? 'home';
?>
<style>
    #bottomNav {
     z-index: 1000 !important;   
    }
</style>
<!-- Bottom Navigation -->
<nav id="bottomNav" class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-30">
    <div class="flex justify-around py-2">
        <a href="<?= base_url('dashboard/driver') ?>" class="flex flex-col items-center p-2 <?= $current_page === 'home' ? '' : 'text-gray-400' ?>" <?= $current_page === 'home' ? 'style="color: #0e2140;"' : '' ?>>
            <i class="fas fa-home text-xl"></i>
            <span class="text-xs mt-1">Home</span>
        </a>
        <a href="<?= base_url('driver/routes') ?>" class="flex flex-col items-center p-2 <?= $current_page === 'routes' ? '' : 'text-gray-400' ?>" <?= $current_page === 'routes' ? 'style="color: #0e2140;"' : '' ?>>
            <i class="fas fa-route text-xl"></i>
            <span class="text-xs mt-1">Routes</span>
        </a>
        <a href="<?= base_url('driver/services') ?>" class="flex flex-col items-center p-2 <?= $current_page === 'services' ? '' : 'text-gray-400' ?>" <?= $current_page === 'services' ? 'style="color: #0e2140;"' : '' ?>>
            <i class="fas fa-store text-xl"></i>
            <span class="text-xs mt-1">Services</span>
        </a>
        <a href="<?= base_url('order/my-orders') ?>" class="flex flex-col items-center p-2 <?= $current_page === 'orders' ? '' : 'text-gray-400' ?>" <?= $current_page === 'orders' ? 'style="color: #0e2140;"' : '' ?>>
            <i class="fas fa-receipt text-xl"></i>
            <span class="text-xs mt-1">Orders</span>
        </a>
        <a href="<?= base_url('driver/notifications') ?>" class="flex flex-col items-center p-2 relative <?= $current_page === 'notifications' ? '' : 'text-gray-400' ?>" <?= $current_page === 'notifications' ? 'style="color: #0e2140;"' : '' ?>>
            <i class="fas fa-bell text-xl"></i>
            <span class="text-xs mt-1">Notifications</span>
            <span id="bottomNavNotificationBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center hidden">0</span>
        </a>
        <a href="<?= base_url('profile/driver') ?>" class="flex flex-col items-center p-2 <?= $current_page === 'account' ? '' : 'text-gray-400' ?>" <?= $current_page === 'account' ? 'style="color: #0e2140;"' : '' ?>>
            <i class="fas fa-user text-xl"></i>
            <span class="text-xs mt-1">Account</span>
        </a>
    </div>
</nav>

<style>
/* Add bottom padding to body to prevent content from being hidden behind fixed nav */
body {
    padding-bottom: 80px;
}

/* Ensure main content doesn't overlap with bottom nav */
main {
    margin-bottom: 20px;
}
</style>
