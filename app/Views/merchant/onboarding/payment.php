<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title) ?> - Truckers Africa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .brand-color { color: #0e2140; }
        .brand-bg { background-color: #0e2140; }
        .brand-border { border-color: #0e2140; }
        .brand-hover:hover { background-color: #1a3a5f; }
        .progress-bar { background-color: #0e2140; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <div class="bg-white shadow-sm border-b-2 brand-border">
            <div class="max-w-4xl mx-auto px-4 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <img src="<?= site_url('assets/images/logo-icon-black.png') ?>" alt="Truckers Africa" class="h-12 w-auto">
                        <div>
                            <h1 class="text-2xl font-bold brand-color">Complete Your Payment</h1>
                            <p class="text-gray-600 mt-1">Secure payment powered by PayFast</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Step <?= $step ?> of <?= $total_steps ?></p>
                        <?php $progressPercent = ($step / $total_steps) * 100; ?>
                        <div class="w-32 bg-gray-200 rounded-full h-2 mt-2">
                            <div class="progress-bar h-2 rounded-full" style="width: <?= $progressPercent ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 py-8">
            <div class="max-w-4xl mx-auto px-4">
                <!-- Success/Error Messages -->
                <?php if (session()->has('message')): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
                        <?= session('message') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->has('error')): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                        <?= session('error') ?>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>

                            <div class="space-y-3 mb-6">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Plan:</span>
                                    <span class="font-medium text-gray-900"><?= esc($plan['name']) ?></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Billing:</span>
                                    <span class="font-medium text-gray-900"><?= ucfirst(esc($plan['billing_interval'])) ?></span>
                                </div>
                                <div class="border-t pt-3 flex justify-between">
                                    <span class="font-semibold text-gray-900">Total:</span>
                                    <span class="font-bold text-xl text-gray-900"><?= esc($plan['formatted_price']) ?></span>
                                </div>
                            </div>

                            <div class="bg-blue-50 border brand-border rounded-lg p-4 text-sm brand-color">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 brand-color mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <p class="font-semibold mb-1">Secure Payment</p>
                                        <p>Your payment is processed securely through PayFast. We never store your card details.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 text-xs text-gray-500 text-center">
                                <p>Prices shown in USD</p>
                                <p class="mt-1">Payment processed in ZAR via PayFast</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-md p-8">
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">Payment Details</h2>

                            <form action="<?= site_url('payment/process') ?>" method="post" id="paymentForm">
                                <?= csrf_field() ?>
                                <input type="hidden" name="subscription_id" value="<?= esc($subscription['id']) ?>">
                                <input type="hidden" name="plan_id" value="<?= esc($plan['id']) ?>">
                                <input type="hidden" name="amount" value="<?= esc($plan['price']) ?>">
                                <input type="hidden" name="return_url" value="<?= site_url('merchant/onboarding/complete') ?>">
                                <input type="hidden" name="cancel_url" value="<?= site_url('merchant/onboarding/payment') ?>">

                                <div class="space-y-6">
                                    <!-- Payment Method Info -->
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                                        <div class="flex items-center mb-4">
                                            <svg class="w-6 h-6 text-gray-700 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                            </svg>
                                            <h3 class="text-lg font-semibold text-gray-900">Credit/Debit Card</h3>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-4">
                                            You'll be redirected to PayFast's secure payment page to complete your transaction.
                                        </p>
                                        <div class="flex items-center space-x-2">
                                            <img src="https://www.payfast.co.za/images/logo.png" alt="PayFast" class="h-6">
                                            <span class="text-xs text-gray-500">Powered by PayFast</span>
                                        </div>
                                    </div>

                                    <!-- Accepted Cards -->
                                    <div>
                                        <p class="text-sm text-gray-600 mb-3">We accept:</p>
                                        <div class="flex items-center space-x-4">
                                            <div class="bg-white border border-gray-300 rounded px-3 py-2">
                                                <span class="text-sm font-semibold text-gray-700">Visa</span>
                                            </div>
                                            <div class="bg-white border border-gray-300 rounded px-3 py-2">
                                                <span class="text-sm font-semibold text-gray-700">Mastercard</span>
                                            </div>
                                            <div class="bg-white border border-gray-300 rounded px-3 py-2">
                                                <span class="text-sm font-semibold text-gray-700">EFT</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Terms Agreement -->
                                    <div class="flex items-start">
                                        <input type="checkbox" id="terms" name="terms" required
                                               class="mt-1 h-4 w-4 brand-color focus:ring-1 brand-border border-gray-300 rounded">
                                        <label for="terms" class="ml-2 text-sm text-gray-700">
                                            I agree to the <a href="<?= site_url('terms') ?>" class="brand-color hover:underline">Terms of Service</a>
                                            and <a href="#" class="brand-color hover:underline">Privacy Policy</a>
                                        </label>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="flex items-center justify-between pt-6 border-t">
                                        <a href="<?= site_url('merchant/onboarding/plans') ?>"
                                           class="text-gray-600 hover:text-gray-700 font-medium">
                                            ← Change Plan
                                        </a>
                                        <button type="submit"
                                                class="px-8 py-3 brand-bg text-white font-semibold rounded-md brand-hover focus:outline-none focus:ring-2 brand-border focus:ring-offset-2 transition-colors">
                                            Proceed to Payment →
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Security Info -->
                        <div class="mt-6 bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Security Matters</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-gray-900">SSL Encrypted</p>
                                        <p class="text-gray-600">All data is encrypted</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-gray-900">PCI Compliant</p>
                                        <p class="text-gray-600">Industry standard security</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-gray-900">No Card Storage</p>
                                        <p class="text-gray-600">We never store card details</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</body>
</html>

