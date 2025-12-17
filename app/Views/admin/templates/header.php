<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= esc($page_title ?? 'Admin Dashboard') ?> - Truckers Africa</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/logo-icon.png') ?>">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet">

    <style>
      body { font-family: "Inter", sans-serif; color: #374151; }
      input {
    color: black !important;
}
    </style>
</head>
<body class="bg-gray-100">

    <?php if (session()->get('isAdminLoggedIn')): ?>
    <div class="flex h-screen">
        <?= view('admin/templates/sidebar') ?>
        
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white border-b border-gray-200">
                <div class="flex items-center justify-between p-4">
                    <h1 class="text-xl font-bold text-gray-800"><?= esc($page_title ?? 'Dashboard') ?></h1>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm">Welcome, <?= esc(session()->get('admin_name')) ?></span>
                        <a href="<?= site_url('admin/logout') ?>" class="text-sm text-red-600 hover:underline">Logout</a>
                    </div>
                </div>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
    <?php else: ?>
    <main>
    <?php endif; ?>