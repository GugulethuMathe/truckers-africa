<div class="px-4 lg:px-6 py-6 lg:py-8">
    <div class="max-w-3xl mx-auto">
        
        <!-- Page Header -->
        <div class="mb-8 text-center">
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Plan Upgrade Payment</h1>
            <p class="text-gray-600 mt-2">Complete your payment to upgrade to <?= esc($new_plan['name']) ?></p>
        </div>

        <!-- Payment Summary -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Payment Summary</h2>
            
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-700">Upgrading to:</span>
                    <span class="font-semibold text-gray-900"><?= esc($new_plan['name']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-700">Days Remaining in Cycle:</span>
                    <span class="font-semibold text-gray-900"><?= $breakdown['formatted']['days_remaining'] ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-700">Unused Credit:</span>
                    <span class="text-gray-900"><?= $breakdown['formatted']['unused_credit_zar'] ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-700">New Plan Prorata Cost:</span>
                    <span class="text-gray-900"><?= $breakdown['formatted']['new_plan_prorata_zar'] ?></span>
                </div>
                
                <div class="border-t-2 border-gray-300 pt-3 mt-3">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-900">Amount Due Today:</span>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-blue-600">
                                <?= $breakdown['formatted']['prorata_amount_zar'] ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                (<?= $breakdown['formatted']['prorata_amount_usd'] ?>)
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-gray-700">
                    <svg class="w-4 h-4 inline mr-1 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <strong>Note:</strong> This is a one-time prorata charge for the remaining days in your current billing cycle. 
                    Your next full billing at <?= $breakdown['formatted']['new_plan_price_zar'] ?>/month will occur on <?= $breakdown['formatted']['next_billing_date'] ?>.
                </p>
            </div>
        </div>

        <!-- PayFast Payment Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Payment Method</h2>
            
            <div class="mb-6">
                <div class="flex items-center justify-center mb-4">
                    <img src="<?= base_url('assets/images/payfast-logo.png') ?>" alt="PayFast" class="h-12">
                </div>
                <p class="text-sm text-gray-600 text-center">
                    You will be redirected to PayFast to complete your payment securely.
                </p>
            </div>

            <form action="<?= $payfast_url ?>" method="post" id="payfast-form">
                <?php foreach ($payfast_data as $key => $value): ?>
                    <input type="hidden" name="<?= esc($key) ?>" value="<?= esc($value) ?>">
                <?php endforeach; ?>

                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="<?= site_url('merchant/subscription') ?>" 
                       class="flex-1 px-6 py-3 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition-colors text-center">
                        Cancel
                    </a>
                    
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                        Proceed to PayFast
                    </button>
                </div>
            </form>

            <p class="text-xs text-gray-500 mt-4 text-center">
                Your payment is secured by PayFast. We do not store your payment information.
            </p>
        </div>

        <!-- Security Notice -->
        <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-green-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h3 class="font-semibold text-green-900">Secure Payment</h3>
                    <p class="text-sm text-green-800 mt-1">
                        All payments are processed securely through PayFast, South Africa's leading payment gateway. 
                        Your financial information is encrypted and never stored on our servers.
                    </p>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
// Auto-submit form after 3 seconds (optional)
// setTimeout(function() {
//     document.getElementById('payfast-form').submit();
// }, 3000);
</script>

