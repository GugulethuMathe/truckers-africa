<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title) ?> - Truckers Africa</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/logo-icon.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo/Header -->
            <div class="text-center">
                <div class="flex justify-center mb-4">
                    <img src="<?= base_url('assets/images/logo-icon.png') ?>" alt="Truckers Africa" class="h-16 w-16">
                </div>
                <h2 class="text-3xl font-bold" style="color: rgb(14, 33, 64);">Reset Password</h2>
                <p class="mt-2 text-sm text-gray-600">Create a new password for your account</p>
            </div>

            <!-- Messages -->
            <?php if (session()->getFlashdata('error')): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <!-- Display validation errors -->
            <?php if (session()->get('errors')): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside">
                        <?php foreach (session()->get('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Reset Password Form -->
            <div class="bg-white shadow-lg rounded-lg p-8">
                <form action="<?= base_url('branch/reset-password') ?>" method="POST" class="space-y-6">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= esc($token) ?>">

                    <!-- Password Requirements Info -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">
                            <i class="fas fa-info-circle text-blue-600 mr-1"></i>
                            Password Requirements:
                        </h3>
                        <ul class="text-xs text-gray-600 space-y-1 ml-5">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                At least 8 characters long
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Use a mix of letters, numbers, and symbols for better security
                            </li>
                        </ul>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            New Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   required
                                   class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                                   placeholder="Enter new password"
                                   minlength="8">
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirm New Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password"
                                   id="confirm_password"
                                   name="confirm_password"
                                   required
                                   class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                                   placeholder="Confirm new password"
                                   minlength="8">
                        </div>
                        <p class="mt-2 text-xs text-gray-500" id="password-match-message"></p>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit"
                                id="submit-button"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors"
                                style="background-color: rgb(14, 33, 64);"
                                onmouseover="this.style.backgroundColor='rgb(10, 25, 48)'"
                                onmouseout="this.style.backgroundColor='rgb(14, 33, 64)'">
                            <i class="fas fa-key mr-2"></i>
                            Reset Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Additional Links -->
            <div class="text-center space-y-2">
                <p class="text-sm text-gray-600">
                    Remember your password?
                    <a href="<?= base_url('branch/login') ?>" class="font-medium text-green-600 hover:text-green-500">
                        <i class="fas fa-arrow-left mr-1"></i>Back to Login
                    </a>
                </p>
            </div>

            <!-- Footer -->
            <div class="text-center text-xs text-gray-500">
                <p>&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
            </div>
        </div>
    </div>

    <!-- Password Strength Indicator Script -->
    <script>
        const passwordField = document.getElementById('password');
        const confirmField = document.getElementById('confirm_password');
        const matchMessage = document.getElementById('password-match-message');
        const submitButton = document.getElementById('submit-button');

        // Password strength indicator
        passwordField.addEventListener('input', function(e) {
            const password = e.target.value;

            if (password.length >= 8) {
                e.target.classList.remove('border-red-300');
                e.target.classList.add('border-green-300');
            } else if (password.length > 0) {
                e.target.classList.remove('border-green-300');
                e.target.classList.add('border-red-300');
            } else {
                e.target.classList.remove('border-green-300', 'border-red-300');
            }

            checkPasswordMatch();
        });

        // Password match indicator
        confirmField.addEventListener('input', function(e) {
            checkPasswordMatch();
        });

        function checkPasswordMatch() {
            const password = passwordField.value;
            const confirm = confirmField.value;

            if (confirm.length === 0) {
                confirmField.classList.remove('border-green-300', 'border-red-300');
                matchMessage.textContent = '';
                matchMessage.className = 'mt-2 text-xs text-gray-500';
                return;
            }

            if (password === confirm && password.length >= 8) {
                confirmField.classList.remove('border-red-300');
                confirmField.classList.add('border-green-300');
                matchMessage.innerHTML = '<i class="fas fa-check-circle text-green-500 mr-1"></i>Passwords match';
                matchMessage.className = 'mt-2 text-xs text-green-600';
            } else {
                confirmField.classList.remove('border-green-300');
                confirmField.classList.add('border-red-300');
                matchMessage.innerHTML = '<i class="fas fa-times-circle text-red-500 mr-1"></i>Passwords do not match';
                matchMessage.className = 'mt-2 text-xs text-red-600';
            }
        }
    </script>
</body>
</html>
