<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title ?? 'Branch Dashboard') ?> - Truckers Africa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="<?= base_url('branch/dashboard') ?>" class="flex items-center">
                            <img src="<?= base_url('assets/images/logo-icon.png') ?>" alt="Truckers Africa" class="h-8 w-8 sm:h-10 sm:w-10">
                            <span class="ml-2 text-lg sm:text-xl font-bold hidden sm:inline" style="color: rgb(14, 33, 64);">Truckers Africa</span>
                        </a>
                    </div>

                    <!-- Navigation Links (Desktop) -->
                    <div class="hidden md:ml-6 md:flex md:space-x-4 lg:space-x-8">
                        <a href="<?= base_url('branch/dashboard') ?>"
                           class="<?= (uri_string() == 'branch/dashboard') ? 'border-green-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-home mr-2"></i>Dashboard
                        </a>
                        <a href="<?= base_url('branch/orders') ?>"
                           class="<?= (strpos(uri_string(), 'branch/orders') !== false) ? 'border-green-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-shopping-cart mr-2"></i>Orders
                        </a>
                        <a href="<?= base_url('branch/listing-requests') ?>"
                           class="<?= (strpos(uri_string(), 'branch/listing-requests') !== false) ? 'border-green-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-plus-circle mr-2"></i>Requests
                        </a>
                        <a href="<?= base_url('branch/profile') ?>"
                           class="<?= (uri_string() == 'branch/profile') ? 'border-green-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-user-cog mr-2"></i>Profile
                        </a>
                    </div>
                </div>

                <!-- User Menu (Desktop) -->
                <div class="hidden md:flex items-center">
                    <div class="flex items-center space-x-3 lg:space-x-4">
                        <span class="text-xs lg:text-sm text-gray-700 hidden lg:block">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            <?= esc(session()->get('location_name')) ?>
                        </span>
                        <div class="relative group">
                            <button class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900">
                                <i class="fas fa-user-circle text-xl lg:text-2xl mr-1 lg:mr-2"></i>
                                <span class="hidden lg:inline"><?= esc(session()->get('full_name')) ?></span>
                                <i class="fas fa-chevron-down ml-1 lg:ml-2 text-xs"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div class="hidden group-hover:block absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                                <div class="px-4 py-2 text-xs text-gray-500 border-b lg:hidden">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    <?= esc(session()->get('location_name')) ?>
                                </div>
                                <a href="<?= base_url('branch/profile') ?>"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>My Profile
                                </a>
                                <a href="<?= base_url('branch/settings') ?>"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-2"></i>Settings
                                </a>
                                <div class="border-t border-gray-100"></div>
                                <a href="<?= base_url('branch/logout') ?>"
                                   class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="flex items-center md:hidden">
                    <button type="button" id="mobile-menu-button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-green-500">
                        <span class="sr-only">Open main menu</span>
                        <!-- Hamburger icon -->
                        <i class="fas fa-bars text-xl" id="menu-icon-open"></i>
                        <!-- Close icon (hidden by default) -->
                        <i class="fas fa-times text-xl hidden" id="menu-icon-close"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="hidden md:hidden" id="mobile-menu">
            <!-- User info section (mobile) -->
            <div class="pt-4 pb-3 border-t border-gray-200">
                <div class="flex items-center px-4 mb-3">
                    <div class="flex-shrink-0">
                        <i class="fas fa-user-circle text-3xl text-gray-700"></i>
                    </div>
                    <div class="ml-3">
                        <div class="text-base font-medium text-gray-800"><?= esc(session()->get('full_name')) ?></div>
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            <?= esc(session()->get('location_name')) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Links (mobile) -->
            <div class="pt-2 pb-3 space-y-1 border-t border-gray-200">
                <a href="<?= base_url('branch/dashboard') ?>"
                   class="<?= (uri_string() == 'branch/dashboard') ? 'bg-green-50 border-green-500 text-green-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                <a href="<?= base_url('branch/orders') ?>"
                   class="<?= (strpos(uri_string(), 'branch/orders') !== false) ? 'bg-green-50 border-green-500 text-green-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-shopping-cart mr-2"></i>Orders
                </a>
                <a href="<?= base_url('branch/listing-requests') ?>"
                   class="<?= (strpos(uri_string(), 'branch/listing-requests') !== false) ? 'bg-green-50 border-green-500 text-green-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-plus-circle mr-2"></i>Requests
                </a>
                <a href="<?= base_url('branch/profile') ?>"
                   class="<?= (uri_string() == 'branch/profile') ? 'bg-green-50 border-green-500 text-green-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-user-cog mr-2"></i>Profile
                </a>
                <a href="<?= base_url('branch/settings') ?>"
                   class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-cog mr-2"></i>Settings
                </a>
                <a href="<?= base_url('branch/logout') ?>"
                   class="border-transparent text-red-600 hover:bg-gray-50 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIconOpen = document.getElementById('menu-icon-open');
        const menuIconClose = document.getElementById('menu-icon-close');

        if (mobileMenuButton) {
            mobileMenuButton.addEventListener('click', function() {
                const isHidden = mobileMenu.classList.contains('hidden');

                if (isHidden) {
                    mobileMenu.classList.remove('hidden');
                    menuIconOpen.classList.add('hidden');
                    menuIconClose.classList.remove('hidden');
                } else {
                    mobileMenu.classList.add('hidden');
                    menuIconOpen.classList.remove('hidden');
                    menuIconClose.classList.add('hidden');
                }
            });
        }
    </script>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?= session()->getFlashdata('error') ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>
                <?= session()->getFlashdata('success') ?>
            </div>
        </div>
    <?php endif; ?>

