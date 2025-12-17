<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
    
    <style>
      /* ... (your styles remain the same) ... */
      body {
        font-family: 'Inter', sans-serif;
        background-color: #0c111c;
        color: #e2e8f0;
      }
      .font-condensed { font-family: 'Roboto Condensed', sans-serif; }
    </style>
</head>
<body class="<?= esc($page_class) ?>">
    <!-- =================================== -->
    <!-- HEADER (Rugged & Clear)             -->
    <!-- =================================== -->
    <header class="w-full bg-gray-900/80 backdrop-blur-lg fixed top-0 z-50 border-b border-gray-700/50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <!-- CHANGED: Corrected link and image path -->
            <a href="<?= site_url('/') ?>" class="flex items-center space-x-3">
                <img src="<?= base_url('assets/images/logo-icon.png') ?>" alt="Truckers Africa Logo" class="h-12 w-12">
                <span class="text-2xl font-condensed tracking-wider text-white">TRUCKERS AFRICA</span>
            </a>
            <div class="hidden md:flex items-center space-x-6">
                <!-- CHANGED: Corrected links to use site_url() -->
                <a href="<?= site_url('login') ?>" class="text-gray-300 hover:text-secondary transition font-semibold">Login</a>
                <a href="<?= site_url('signup') ?>" class="bg-secondary text-gray-900 font-bold py-2 px-5 !rounded-button hover:bg-opacity-90 transition">Sign Up Free</a>
            </div>
             <div class="md:hidden">
                 <a href="<?= site_url('signup') ?>" class="bg-secondary text-gray-900 font-bold py-2 px-4 !rounded-button hover:bg-opacity-90 transition text-sm">Sign Up</a>
            </div>
        </div>
    </header>