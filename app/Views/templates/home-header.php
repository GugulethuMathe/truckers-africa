<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= esc($page_title) ?></title>
    
    <!-- CHANGED: Corrected the path to the icon using base_url() -->
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/logo-icon.png') ?>">

    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@geoapify/geocoder-autocomplete@2.0.1/styles/minimal.css" />
    
    <style>
      /* ... (your styles remain the same) ... */
      body {
        font-family: 'Inter', sans-serif;
        background-color: #0c111c;
        color: #e2e8f0;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
      }
      .text-2xl {
    font-size: 1.0rem !important;
    line-height: 2rem !important;
}
button#get-directions-btn {
    color: #fff;
}
h1 {
    font-size: 1.55rem !important;
}
      .font-condensed { font-family: 'Roboto Condensed', sans-serif; }
      input {
    color: black !important;
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
<body class="<?= esc($page_class) ?>">
    <!-- =================================== -->
    <!-- HEADER (Rugged & Clear)             -->
    <!-- =================================== -->
    <header class="w-full bg-gray-900/80 backdrop-blur-lg fixed top-0 z-50 border-b border-gray-700/50" x-data="{ mobileMenuOpen: false }">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <!-- CHANGED: Corrected link and image path -->
            <a href="<?= site_url('/') ?>" class="flex items-center space-x-3">
                <img src="<?= base_url('assets/images/logo-icon.png') ?>" alt="Truckers Africa Logo" class="h-12 w-12">
                <span class="text-2xl font-condensed tracking-wider text-white">TRUCKERS AFRICA</span>
            </a>
            <div class="hidden md:flex items-center space-x-6">
                <!-- CHANGED: Corrected links to use site_url() -->
                <a href="<?= site_url('/') ?>" class="text-gray-300 hover:text-secondary transition font-semibold">Home</a>
                <a href="<?= site_url('packages') ?>" class="text-gray-300 hover:text-secondary transition font-semibold">Pricing</a>
                <a href="<?= site_url('contact-us') ?>" class="text-gray-300 hover:text-secondary transition font-semibold">Contact Us</a>

                <!-- Login Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="text-gray-300 hover:text-secondary transition font-semibold flex items-center gap-1">
                        Login
                        <i class="ri-arrow-down-s-line text-lg"></i>
                    </button>
                    <div x-show="open"
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         class="absolute right-0 mt-2 w-48 bg-gray-800 rounded-lg shadow-lg border border-gray-700 py-2 z-50"
                         style="display: none;">
                        <a href="<?= site_url('login') ?>" class="block px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition">
                            <i class="ri-user-line mr-2"></i>Driver/Merchant Login
                        </a>
                        <a href="<?= site_url('branch/login') ?>" class="block px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition">
                            <i class="ri-building-line mr-2"></i>Branch Login
                        </a>
                    </div>
                </div>

                <a href="<?= site_url('signup') ?>" class="bg-secondary text-gray-900 font-bold py-2 px-5 !rounded-button hover:bg-opacity-90 transition">Sign Up</a>
            </div>
             <div class="md:hidden">
                 <!-- Mobile Menu Button -->
                 <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-300 hover:text-secondary transition">
                     <i class="ri-menu-line text-2xl"></i>
                 </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform -translate-y-2"
             class="md:hidden bg-gray-800 border-t border-gray-700"
             style="display: none;">
            <div class="container mx-auto px-4 py-4 space-y-3">
                <a href="<?= site_url('/') ?>" class="block text-gray-300 hover:text-secondary transition font-semibold py-2">Home</a>
                <a href="<?= site_url('packages') ?>" class="block text-gray-300 hover:text-secondary transition font-semibold py-2">Pricing</a>
                <a href="<?= site_url('contact-us') ?>" class="block text-gray-300 hover:text-secondary transition font-semibold py-2">Contact Us</a>

                <!-- Mobile Login Section -->
                <div class="border-t border-gray-700 pt-3">
                    <p class="text-sm text-gray-400 mb-2">Login As:</p>
                    <a href="<?= site_url('login') ?>" class="block text-gray-300 hover:bg-gray-700 hover:text-white transition py-2 px-3 rounded">
                        <i class="ri-user-line mr-2"></i>Driver/Merchant
                    </a>
                    <a href="<?= site_url('branch/login') ?>" class="block text-gray-300 hover:bg-gray-700 hover:text-white transition py-2 px-3 rounded">
                        <i class="ri-building-line mr-2"></i>Branch Manager
                    </a>
                </div>

                <a href="<?= site_url('signup') ?>" class="block bg-secondary text-gray-900 font-bold py-3 px-5 !rounded-button hover:bg-opacity-90 transition text-center">Sign Up</a>
            </div>
        </div>
    </header>
    <main class="flex-grow pt-20">