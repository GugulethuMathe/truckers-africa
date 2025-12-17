<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Up Your Password - Truckers Africa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .brand-color { color: #0e2140; }
        .brand-bg { background-color: #0e2140; }
        .brand-border { border-color: #0e2140; }
        .brand-hover:hover { background-color: #1a3a5f; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo/Header -->
            <div class="text-center">
                <img src="<?= site_url('assets/images/logo-icon-black.png') ?>" alt="Truckers Africa" class="mx-auto h-20 w-auto mb-6" />
                <h2 class="mt-6 text-3xl font-extrabold brand-color">
                    Set Up Your Password
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Welcome, <strong><?= esc($merchant['owner_name']) ?></strong>!<br>
                    Create a secure password for your merchant account.
                </p>
            </div>

            <!-- Success/Error Messages -->
            <?php if (session()->has('error')): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?= session('error') ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (session()->has('errors')): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <ul class="list-disc list-inside text-sm text-red-700">
                                <?php foreach (session('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Account Info -->
            <div class="bg-blue-50 border-l-4 brand-border p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 brand-color" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm brand-color">
                            <strong>Business:</strong> <?= esc($merchant['business_name']) ?><br>
                            <strong>Email:</strong> <?= esc($merchant['email']) ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Password Setup Form -->
            <form class="mt-8 space-y-6" action="<?= site_url('merchant/setup-password/process') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= esc($token) ?>">
                <input type="hidden" name="email" value="<?= esc($email) ?>">

                <div class="rounded-md shadow-sm space-y-4">
                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            New Password
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-1 brand-border focus:z-10 sm:text-sm"
                            placeholder="Enter your password"
                            style="focus:border-color: #0e2140;"
                        >
                        <p class="mt-1 text-xs text-gray-500">
                            Must be at least 8 characters long
                        </p>
                    </div>

                    <!-- Confirm Password Field -->
                    <div>
                        <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-1">
                            Confirm Password
                        </label>
                        <input
                            id="password_confirm"
                            name="password_confirm"
                            type="password"
                            required
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-1 brand-border focus:z-10 sm:text-sm"
                            placeholder="Confirm your password"
                            style="focus:border-color: #0e2140;"
                        >
                    </div>
                </div>

                <!-- Password Requirements -->
                <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                    <h3 class="text-sm font-medium text-gray-900 mb-2">Password Requirements:</h3>
                    <ul class="text-xs text-gray-600 space-y-1">
                        <li class="flex items-center">
                            <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            At least 8 characters long
                        </li>
                        <li class="flex items-center">
                            <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Use a mix of letters, numbers, and symbols for better security
                        </li>
                    </ul>
                </div>

                <!-- Submit Button -->
                <div>
                    <button
                        type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white brand-bg brand-hover focus:outline-none focus:ring-2 focus:ring-offset-2 brand-border"
                    >
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-white opacity-75 group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        Set Up Password & Continue
                    </button>
                </div>
            </form>

            <!-- Help Text -->
            <div class="text-center">
                <p class="text-xs text-gray-500">
                    Having trouble? <a href="mailto:support@truckersafrica.com" class="font-medium brand-color hover:underline">Contact Support</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Password Strength Indicator Script -->
    <script>
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const confirmField = document.getElementById('password_confirm');
            
            // Basic validation feedback
            if (password.length >= 8) {
                e.target.classList.remove('border-red-300');
                e.target.classList.add('border-green-300');
            } else {
                e.target.classList.remove('border-green-300');
                e.target.classList.add('border-red-300');
            }
        });

        document.getElementById('password_confirm').addEventListener('input', function(e) {
            const password = document.getElementById('password').value;
            const confirm = e.target.value;
            
            if (confirm && password === confirm) {
                e.target.classList.remove('border-red-300');
                e.target.classList.add('border-green-300');
            } else if (confirm) {
                e.target.classList.remove('border-green-300');
                e.target.classList.add('border-red-300');
            }
        });
    </script>
</body>
</html>

