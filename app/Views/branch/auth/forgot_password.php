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
                <h2 class="text-3xl font-bold" style="color: rgb(14, 33, 64);">Forgot Password</h2>
                <p class="mt-2 text-sm text-gray-600">Enter your email to receive a password reset link</p>
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

            <?php if (session()->getFlashdata('message')): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= session()->getFlashdata('message') ?>
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

            <!-- Forgot Password Form -->
            <div class="bg-white shadow-lg rounded-lg p-8">
                <form action="<?= base_url('branch/forgot-password') ?>" method="POST" class="space-y-6">
                    <?= csrf_field() ?>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   required
                                   value="<?= old('email') ?>"
                                   class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                                   placeholder="branch@example.com">
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Enter the email address associated with your branch account
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors"
                                style="background-color: rgb(14, 33, 64);"
                                onmouseover="this.style.backgroundColor='rgb(10, 25, 48)'"
                                onmouseout="this.style.backgroundColor='rgb(14, 33, 64)'">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Send Reset Link
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
                <p class="text-sm text-gray-600">
                    Not a branch manager?
                    <a href="<?= base_url('login') ?>" class="font-medium text-green-600 hover:text-green-500">
                        Merchant Login
                    </a>
                </p>
            </div>

            <!-- Footer -->
            <div class="text-center text-xs text-gray-500">
                <p>&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
