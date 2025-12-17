<div class="px-4 lg:px-6 py-6 lg:py-8">
    <div class="max-w-4xl mx-auto">
        
        <!-- Back Button -->
        <div class="mb-6">
            <a href="<?= site_url('merchant/subscription') ?>" class="text-blue-600 hover:text-blue-800 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Subscription
            </a>
        </div>

        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Confirm Plan Change</h1>
            <p class="text-gray-600 mt-2">Review the prorata billing details before confirming your plan change.</p>
        </div>

        <!-- Plan Comparison -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Plan Change Summary</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Current Plan -->
                <div class="border-2 border-gray-300 rounded-lg p-4">
                    <div class="text-sm text-gray-500 mb-2">Current Plan</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2"><?= esc($breakdown['usd']['current_plan_name']) ?></h3>
                    <div class="text-2xl font-bold text-gray-700">
                        <?= $breakdown['formatted']['current_plan_price_usd'] ?>/month
                    </div>
                    <div class="text-sm text-gray-500">
                        (<?= $breakdown['formatted']['current_plan_price_zar'] ?>/month)
                    </div>
                </div>

                <!-- New Plan -->
                <div class="border-2 border-blue-500 rounded-lg p-4 bg-blue-50">
                    <div class="text-sm text-blue-600 mb-2">New Plan</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2"><?= esc($breakdown['usd']['new_plan_name']) ?></h3>
                    <div class="text-2xl font-bold text-blue-600">
                        <?= $breakdown['formatted']['new_plan_price_usd'] ?>/month
                    </div>
                    <div class="text-sm text-gray-600">
                        (<?= $breakdown['formatted']['new_plan_price_zar'] ?>/month)
                    </div>
                </div>
            </div>
        </div>

        <!-- Prorata Breakdown -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <?= $breakdown['usd']['is_upgrade'] ? '⬆️ Upgrade' : '⬇️ Downgrade' ?> Billing Details
            </h2>

            <div class="space-y-4">
                <!-- Billing Cycle Info -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-700">Current Billing Cycle:</span>
                        <span class="font-semibold text-gray-900">
                            <?= date('M j', strtotime($breakdown['usd']['period_start'])) ?> - 
                            <?= date('M j, Y', strtotime($breakdown['usd']['period_end'])) ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-700">Days Remaining:</span>
                        <span class="font-semibold text-gray-900"><?= $breakdown['formatted']['days_remaining'] ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Next Full Billing Date:</span>
                        <span class="font-semibold text-gray-900"><?= $breakdown['formatted']['next_billing_date'] ?></span>
                    </div>
                </div>

                <!-- Prorata Calculation -->
                <div class="border-t border-gray-200 pt-4">
                    <h3 class="font-semibold text-gray-900 mb-3">Prorata Calculation:</h3>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Unused credit from current plan:</span>
                            <span class="text-gray-900">
                                <?= $breakdown['formatted']['unused_credit_usd'] ?>
                                <span class="text-gray-500">(<?= $breakdown['formatted']['unused_credit_zar'] ?>)</span>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">New plan cost for remaining period:</span>
                            <span class="text-gray-900">
                                <?= $breakdown['formatted']['new_plan_prorata_usd'] ?>
                                <span class="text-gray-500">(<?= $breakdown['formatted']['new_plan_prorata_zar'] ?>)</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Amount Due/Credit -->
                <div class="border-t-2 border-gray-300 pt-4">
                    <?php if ($breakdown['usd']['prorata_amount'] > 0): ?>
                        <!-- Upgrade - Charge -->
                        <div class="bg-blue-50 border-2 border-blue-500 rounded-lg p-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-lg font-semibold text-gray-900">Amount Due Today:</span>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-blue-600">
                                        <?= $breakdown['formatted']['prorata_amount_usd'] ?>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        (<?= $breakdown['formatted']['prorata_amount_zar'] ?>)
                                    </div>
                                </div>
                            </div>
                            <p class="text-sm text-gray-700 mt-2">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <?= esc($breakdown['usd']['message']) ?>
                            </p>
                        </div>
                    <?php elseif ($breakdown['usd']['prorata_amount'] < 0): ?>
                        <!-- Downgrade - Credit -->
                        <div class="bg-green-50 border-2 border-green-500 rounded-lg p-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-lg font-semibold text-gray-900">Credit to Your Account:</span>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-green-600">
                                        <?= '$' . number_format(abs($breakdown['usd']['prorata_amount']), 2) ?>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        (R <?= number_format(abs($breakdown['zar']['prorata_amount']), 2) ?>)
                                    </div>
                                </div>
                            </div>
                            <p class="text-sm text-gray-700 mt-2">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <?= esc($breakdown['usd']['message']) ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <!-- No Change -->
                        <div class="bg-gray-50 border-2 border-gray-300 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-900">Amount Due Today:</span>
                                <div class="text-2xl font-bold text-gray-600">$0.00</div>
                            </div>
                            <p class="text-sm text-gray-700 mt-2">
                                <?= esc($breakdown['usd']['message']) ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Confirmation Buttons -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex flex-col sm:flex-row gap-4 justify-end">
                <a href="<?= site_url('merchant/subscription') ?>" 
                   class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition-colors text-center">
                    Cancel
                </a>
                
                <form method="post" action="<?= site_url('merchant/subscription/change-plan') ?>" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit" 
                            class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                        <?php if ($breakdown['usd']['prorata_amount'] > 0): ?>
                            Proceed to Payment
                        <?php else: ?>
                            Confirm Plan Change
                        <?php endif; ?>
                    </button>
                </form>
            </div>

            <p class="text-xs text-gray-500 mt-4 text-center">
                By confirming, you agree to the prorata billing terms and the new subscription plan.
            </p>
        </div>

    </div>
</div>

