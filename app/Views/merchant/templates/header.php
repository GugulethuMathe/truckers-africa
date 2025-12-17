<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= esc($page_title ?? 'Merchant Dashboard') ?> - Truckers Africa</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/logo-icon-black.png') ?>">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              'brand-blue': '#0D47A1',
              'brand-yellow': '#FFC107',
            }
          }
        }
      }
    </script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/geoapify/geoapify-autocomplete-css@v1/dist/geoapify-autocomplete.min.css">

    <style>
      body { font-family: "Inter", sans-serif; }
      select#business_type {
    color: black !important;
}
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-PCRPK4W4C5"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-PCRPK4W4C5');
</script>
<body class="bg-gray-100">

<script>
// Fetch and update notification count
function updateNotificationCount() {
    fetch('<?= site_url('notifications/count') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.unread_count > 0) {
                const badges = document.querySelectorAll('.notification-badge');
                badges.forEach(badge => {
                    badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                    badge.classList.remove('hidden');
                });
            }
        })
        .catch(error => console.error('Error fetching notification count:', error));
}

// Update on page load
document.addEventListener('DOMContentLoaded', updateNotificationCount);

// Update every 60 seconds
setInterval(updateNotificationCount, 60000);
</script>

    <div x-data="{ mobileMenuOpen: false }" class="flex h-screen">
        <!-- =================================== -->
        <!-- MOBILE MENU OVERLAY                 -->
        <!-- =================================== -->
        <div class="lg:hidden">
            <!-- Mobile menu overlay -->
            <div x-show="mobileMenuOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75" style="display: none;"></div>

            <!-- Mobile menu -->
            <div x-show="mobileMenuOpen" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 flex flex-col" style="display: none;">
                <!-- Mobile Logo -->
                <div class="h-16 flex items-center justify-between px-4 border-b border-gray-200">
                    <a href="<?= site_url('dashboard/merchant') ?>" class="flex items-center space-x-2">
                        <img src="<?= base_url('assets/images/logo-icon.png') ?>" alt="Truckers Africa Logo" class="h-8 w-8">
                        <span class="text-lg font-bold text-gray-900">Truckers Africa</span>
                    </a>
                    <button @click="mobileMenuOpen = false" class="p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Mobile Nav Links -->
                <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                    <a href="<?= site_url('merchant/dashboard') ?>" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-md">
                        <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h12M3.75 3h16.5M3.75 3v16.5M16.5 3c0 1.623-1.377 2.946-2.946 2.946l-1.054.012a2.946 2.946 0 01-2.946-2.946M16.5 3v16.5" /></svg>
                        Dashboard
                    </a>
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-md">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c.51 0 .962-.343 1.087-.835l1.823-6.812a.75.75 0 00-.66-1.114H4.878A2.25 2.25 0 002.628 6H2.25m5.25 8.25h10.5m-10.5 0a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <span>Manage Orders</span>
                            </span>
                            <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="mt-2 py-2 pl-8 pr-4 space-y-2 bg-gray-50 rounded-md" style="display: none;">
                            <a href="<?= site_url('merchant/orders/all') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">All Orders</a>
                            <a href="<?= site_url('merchant/orders/pending') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">Pending Orders</a>
                            <a href="<?= site_url('merchant/orders/approved') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">Approved Orders</a>
                            <a href="<?= site_url('merchant/orders/rejected') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">Rejected Orders</a>
                        </div>
                    </div>
                    <a href="<?= site_url('profile/merchant/edit') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-md">
                        <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                        My Business Profile
                    </a>
                    <a href="<?= site_url('merchant/locations') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-md">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        Branches
                    </a>
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-md">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" /></svg>
                                <span>My Services</span>
                            </span>
                            <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="mt-2 py-2 pl-8 pr-4 space-y-2 bg-gray-50 rounded-md" style="display: none;">
                            <a href="<?= site_url('merchant/listings') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">All Service Listings</a>
                            <a href="<?= site_url('merchant/listings/new') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">Add New Listing</a>
                            <a href="<?= site_url('merchant/listing-requests') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">Branch Requests</a>
                        </div>
                    </div>
                    <a href="<?= site_url('merchant/subscription') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-md">
                        <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0011.667 0l3.181-3.183m-4.991-2.696L7.985 5.644m11.667 0L19.5 7.985m-4.991-2.696L12 12.015" /></svg>
                        Subscription
                    </a>
                    <a href="<?= site_url('merchant/help') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-md">
                        <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg>
                        Help & Support
                    </a>

                    <!-- Mobile Logout -->
                    <div class="pt-4 border-t border-gray-200">
                        <a href="<?= site_url('logout') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-md">
                            <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                            Logout
                        </a>
                    </div>
                </nav>
            </div>
        </div>

        <!-- =================================== -->
        <!-- DESKTOP SIDEBAR NAVIGATION          -->
        <!-- =================================== -->
        <aside class="hidden lg:flex w-64 flex-shrink-0 bg-white border-r border-gray-200 flex-col">
            <!-- Logo -->
            <div class="h-16 flex items-center justify-center border-b border-gray-200">
                <a href="<?= site_url('dashboard/merchant') ?>" class="flex items-center space-x-2">
                    <img src="<?= base_url('assets/images/logo-icon.png') ?>" alt="Truckers Africa Logo" class="h-10 w-10">
                    <span class="text-lg font-bold text-gray-900">Truckers Africa</span>
                </a>
            </div>
            <!-- Nav Links -->
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="<?= site_url('merchant/dashboard') ?>" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
                    <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h12M3.75 3h16.5M3.75 3v16.5M16.5 3c0 1.623-1.377 2.946-2.946 2.946l-1.054.012a2.946 2.946 0 01-2.946-2.946M16.5 3v16.5" /></svg>
                    Dashboard
                </a>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-md">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c.51 0 .962-.343 1.087-.835l1.823-6.812a.75.75 0 00-.66-1.114H4.878A2.25 2.25 0 002.628 6H2.25m5.25 8.25h10.5m-10.5 0a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <span>Manage Orders</span>
                        </span>
                        <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="mt-2 py-2 pl-8 pr-4 space-y-2 bg-gray-50 rounded-md" style="display: none;">
                        <a href="<?= site_url('merchant/orders/all') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">All Orders</a>
                        <a href="<?= site_url('merchant/orders/pending') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">Pending Orders</a>
                        <a href="<?= site_url('merchant/orders/approved') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">Approved Orders</a>
                        <a href="<?= site_url('merchant/orders/rejected') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">Rejected Orders</a>
                    </div>
                </div>
                <a href="<?= site_url('profile/merchant/edit') ?>" class="flex items-center px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-md">
                    <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                    My Business Profile
                </a>
                <a href="<?= site_url('merchant/locations') ?>" class="flex items-center px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-md">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                    Branches
                </a>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-md">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" /></svg>
                            <span>My Services</span>
                        </span>
                        <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="mt-2 py-2 pl-8 pr-4 space-y-2 bg-gray-50 rounded-md" style="display: none;">
                        <a href="<?= site_url('merchant/listings') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">All Service Listings</a>
                        <a href="<?= site_url('merchant/listings/new') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">Add New Listing</a>
                        <a href="<?= site_url('merchant/listing-requests') ?>" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-md">Branch Requests</a>
                    </div>
                </div>
                 <a href="<?= site_url('merchant/subscription') ?>" class="flex items-center px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-md">
                    <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0011.667 0l3.181-3.183m-4.991-2.696L7.985 5.644m11.667 0L19.5 7.985m-4.991-2.696L12 12.015" /></svg>
                    Subscription
                </a>
                <a href="<?= site_url('merchant/help') ?>" class="flex items-center px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-md">
                    <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg>
                    Help & Support
                </a>
            </nav>
            <!-- User Profile / Logout -->
            <div class="px-4 py-4 border-t border-gray-200">
                <a href="<?= site_url('logout') ?>" class="flex items-center px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-md">
                    <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                    Logout
                </a>
            </div>
        </aside>

        <!-- =================================== -->
        <!-- MAIN CONTENT AREA                   -->
        <!-- =================================== -->
        <main class="flex-1 overflow-y-auto bg-gray-100">
            <!-- Top Header Bar -->
            <header class="bg-white shadow-sm border-b border-gray-200 px-4 lg:px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <!-- Mobile menu button -->
                        <button @click="mobileMenuOpen = true" class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        <h1 class="text-base lg:text-xl font-semibold text-gray-900"><?= esc($page_title ?? 'Merchant Dashboard') ?></h1>
                    </div>
                    
                    <!-- Profile Section -->
                    <div class="flex items-center space-x-2 lg:space-x-4">
                        <!-- Notifications -->
                        <a href="<?= site_url('merchant/notifications') ?>" class="relative p-2 text-gray-400 hover:text-gray-600 transition-colors">
                            <!-- Correct Bell Icon -->
                            <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <!-- Notification badge - dynamically updated -->
                            <span class="notification-badge absolute -top-1 -right-1 h-4 w-4 lg:h-5 lg:w-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center hidden">0</span>
                        </a>

                        <!-- Profile Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center space-x-2 lg:space-x-3 p-1 lg:p-2 rounded-lg hover:bg-gray-100 transition-colors">
                                <!-- Profile Image -->
                                <?php
                                    // Get merchant data from database to ensure we have the latest profile image
                                    $merchantId = session()->get('merchant_id') ?? session()->get('user_id');
                                    $merchantModel = new \App\Models\MerchantModel();
                                    $merchantData = $merchantModel->find($merchantId);

                                    // Try to get profile image from database first, then session
                                    $candidates = [
                                        $merchantData['profile_image_url'] ?? '',
                                        $merchantData['business_image_url'] ?? '',
                                        session()->get('merchant_profile_image_url') ?? '',
                                        session()->get('business_image_url') ?? '',
                                        session()->get('avatar') ?? '',
                                        session()->get('profile_image_url') ?? ''
                                    ];
                                    $rawSrc = '';
                                    foreach ($candidates as $c) { if (!empty($c)) { $rawSrc = $c; break; } }
                                    if (empty($rawSrc)) {
                                        $profileSrc = base_url('uploads/avatars/default.png');
                                    } else {
                                        if (strpos($rawSrc, 'http://') === 0 || strpos($rawSrc, 'https://') === 0 || strpos($rawSrc, '//') === 0) {
                                            $profileSrc = $rawSrc;
                                        } else {
                                            $profileSrc = base_url(ltrim($rawSrc, '/'));
                                        }
                                    }

                                    // Get merchant name - prioritize business name, then owner name, then session name
                                    $merchantName = $merchantData['business_name'] ?? session()->get('business_name') ?? '';
                                    if (empty($merchantName)) {
                                        $merchantName = $merchantData['owner_name'] ?? session()->get('name') ?? 'Merchant';
                                    }
                                    // If still empty, try first_name + last_name
                                    if (empty($merchantName) || $merchantName === 'Merchant') {
                                        $firstName = $merchantData['first_name'] ?? session()->get('first_name') ?? '';
                                        $lastName = $merchantData['last_name'] ?? session()->get('last_name') ?? '';
                                        if (!empty($firstName) || !empty($lastName)) {
                                            $merchantName = trim($firstName . ' ' . $lastName);
                                        }
                                    }
                                    // Final fallback
                                    if (empty($merchantName)) {
                                        $merchantName = 'Merchant';
                                    }
                                ?>
                                <img src="<?= esc($profileSrc) ?>" alt="Profile" class="w-7 h-7 lg:w-8 lg:h-8 rounded-full object-cover">
                                <div class="text-left hidden lg:block">
                                    <p class="text-sm font-medium text-gray-900"><?= esc($merchantName) ?></p>
                                    <p class="text-xs text-gray-500">Profile & Settings</p>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 hidden lg:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-48 lg:w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50" style="display: none;">
                                <a href="<?= site_url('profile/merchant/edit') ?>" class="flex items-center px-3 lg:px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    My Profile
                                </a>
                                <a href="<?= site_url('profile/merchant/change-password') ?>" class="flex items-center px-3 lg:px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                    </svg>
                                    Change Password
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <a href="<?= site_url('logout') ?>" class="flex items-center px-3 lg:px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4 mr-2 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
