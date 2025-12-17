<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= esc($page_title ?? 'Dashboard') ?> - Truckers Africa</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/logo-icon.png') ?>">

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

    <style>
      body { font-family: "Inter", sans-serif; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="container mx-auto p-4 pb-24">
      <!-- =================================== -->
      <!-- HEADER (LOGGED-IN VERSION)          -->
      <!-- =================================== -->
      <header class="flex justify-between items-center mb-4">
        <!-- Logo and Title Group -->
        <a href="<?= site_url('dashboard/driver') ?>" class="flex items-center space-x-3">
          <img src="<?= base_url('assets/images/logo-icon.png') ?>" alt="Truckers Africa Logo" class="h-14 w-14">
          <span class="text-xl font-bold text-gray-900">Truckers Africa</span>
        </a>

        <!-- Logged-in User Controls -->
        <div class="flex items-center space-x-6">
            <!-- Notification Bell -->
            <a href="#" class="relative text-gray-500 hover:text-gray-900">
                <span class="sr-only">Notifications</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                <!-- Notification Dot -->
                <span class="absolute -top-1 -right-1 flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span></span>
            </a>
            <!-- Profile Avatar -->
            <a href="<?= site_url('profile/driver/edit') ?>" class="flex items-center justify-center h-10 w-10 rounded-full bg-brand-blue text-white font-semibold text-sm">
                <?php 
                    $name = session()->get('name') ?? '';
                    $parts = explode(' ', $name);
                    $initials = strtoupper(substr($parts[0], 0, 1));
                    if (count($parts) > 1) {
                        $initials .= strtoupper(substr(end($parts), 0, 1));
                    }
                    echo esc($initials, 'html');
                ?>
            </a>
        </div>
      </header>
