<?= $this->include('merchant/templates/header') ?>

<div class="p-4 lg:p-6">
    <!-- Page Header -->
    <div class="mb-6 lg:mb-8 text-center">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Choose Your Plan</h1>
        <p class="text-gray-600 mt-2 text-sm lg:text-base">Select the perfect plan for your business needs</p>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?= session()->getFlashdata('success') ?></span>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <!-- Current Plan Alert -->
    <?php if ($current_subscription): ?>
        <div class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-blue-800">
                    You're currently on the <strong><?= esc($current_subscription['plan_name']) ?></strong> plan.
                    <?php if ($current_subscription['status'] === 'trial'): ?>
                        Your trial expires on <?= date('M j, Y', strtotime($current_subscription['trial_ends_at'])) ?>.
                    <?php endif; ?>
                </span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Pricing Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto mt-12">
        <?php foreach ($available_plans as $index => $plan): ?>
            <?php
            $isCurrentPlan = $current_subscription && $current_subscription['plan_id'] == $plan['id'];
            $isPopular = $index === 1; // Make the middle plan popular
            ?>

            <div class="relative bg-white rounded-lg shadow-lg overflow-visible <?= $isPopular ? 'ring-2 ring-blue-500' : 'border border-gray-200' ?>">
                <?php if ($isPopular): ?>
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                        <span class="bg-blue-500 text-white px-4 py-1 text-sm font-medium rounded-full whitespace-nowrap shadow-md">Most Popular</span>
                    </div>
                <?php endif; ?>

                <?php if ($isCurrentPlan): ?>
                    <div class="absolute top-0 right-0 bg-green-500 text-white px-3 py-1 text-xs font-medium rounded-bl-lg">
                        Current Plan
                    </div>
                <?php endif; ?>

                <div class="p-6">
                    <!-- Plan Header -->
                    <div class="text-center mb-6">
                        <h3 class="text-xl font-semibold text-gray-900"><?= esc($plan['name']) ?></h3>
                        <p class="text-gray-600 mt-2"><?= esc($plan['description']) ?></p>
                        
                        <div class="mt-4">
                            <span class="text-4xl font-bold text-gray-900"><?= $plan['formatted_price'] ?></span>
                            <span class="text-gray-600">/month</span>
                        </div>

                        <?php if ($plan['has_free_trial'] && !$has_subscription_history): ?>
                            <div class="mt-2">
                                <span class="bg-green-100 text-green-800 px-3 py-1 text-sm font-medium rounded-full">
                                    <?= $plan['trial_days'] ?> Day Free Trial (New Merchants Only)
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Plan Features -->
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">What's included:</h4>
                        <ul class="space-y-2">
                            <?php if (!empty($plan['features'])): ?>
                                <?php foreach ($plan['features'] as $feature): ?>
                                    <li class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        <?= esc($feature['feature_name']) ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    All basic features
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- Action Button -->
                    <div class="text-center">
                        <?php if ($isCurrentPlan): ?>
                            <button class="w-full bg-gray-100 text-gray-500 py-3 px-4 rounded-md font-medium cursor-not-allowed" disabled>
                                Current Plan
                            </button>
                        <?php elseif (!$current_subscription && !$has_subscription_history && $plan['has_free_trial']): ?>
                            <!-- Start Trial Button (ONLY for brand new merchants with NO subscription history) -->
                            <button onclick="startTrial(<?= $plan['id'] ?>)"
                                    class="w-full bg-blue-600 text-white py-3 px-4 rounded-md font-medium hover:bg-blue-700 transition-colors">
                                Start Free Trial
                            </button>
                        <?php elseif (!$current_subscription): ?>
                            <!-- Subscribe Button (for merchants without active subscription OR with subscription history) -->
                            <form method="post" action="<?= site_url('merchant/subscription/process-payment') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                                <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-md font-medium hover:bg-blue-700 transition-colors">
                                    Subscribe Now
                                </button>
                            </form>
                        <?php else: ?>
                            <!-- Change Plan Button (for merchants with existing subscriptions) -->
                            <form method="post" action="<?= site_url('merchant/subscription/change-plan-preview') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                                <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-md font-medium hover:bg-blue-700 transition-colors">
                                    Switch to <?= esc($plan['name']) ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- FAQ Section -->
    <div class="mt-16 max-w-4xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-900 text-center mb-8">Frequently Asked Questions</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Can I change my plan anytime?</h3>
                <p class="text-gray-600">Yes, you can upgrade or downgrade your plan at any time. Changes take effect immediately.</p>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">What payment methods do you accept?</h3>
                <p class="text-gray-600">We accept all major payment methods through PayFast, including credit cards and EFT.</p>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Can I cancel my subscription?</h3>
                <p class="text-gray-600">Yes, you can cancel your subscription at any time. You'll continue to have access until the end of your billing period.</p>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Is there a setup fee?</h3>
                <p class="text-gray-600">No, there are no setup fees. You only pay the monthly subscription fee for your chosen plan.</p>
            </div>
        </div>
    </div>

    <!-- Back to Dashboard -->
    <div class="mt-12 text-center">
        <a href="<?= site_url('merchant/subscription') ?>" class="text-blue-600 hover:text-blue-800 font-medium">
            ← Back to Subscription Dashboard
        </a>
    </div>
</div>

<script>
function startTrial(planId) {
    if (!confirm('Start your free trial now?')) {
        return;
    }

    fetch('<?= site_url('merchant/subscription/start-trial') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            'plan_id': planId,
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
</script>

<?= $this->include('merchant/templates/footer') ?>
