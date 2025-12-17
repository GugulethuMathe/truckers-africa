<?= $this->include('merchant/templates/header') ?>

<div class="p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Subscription Management</h1>
        <p class="text-gray-600 mt-2">Manage your subscription plan and billing information</p>
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

    <?php if (session()->getFlashdata('info')): ?>
        <div class="mb-6 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?= session()->getFlashdata('info') ?></span>
        </div>
    <?php endif; ?>

    <!-- Payment Required Alert for 'new' or 'trial_pending' status -->
    <?php if ($current_subscription && in_array($current_subscription['status'], ['new', 'trial_pending'])): ?>
        <div class="mb-6 bg-red-50 border-2 border-red-400 rounded-lg p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="text-lg font-bold text-red-800 mb-2">
                        🔒 Payment Required to Activate Your Subscription
                    </h3>
                    <p class="text-sm text-red-700 mb-4">
                        <?php if ($current_subscription['status'] === 'trial_pending'): ?>
                            Complete your payment setup to start your free trial and access all premium features. Your card will not be charged during the trial period.
                        <?php else: ?>
                            Your subscription is pending payment. Complete your payment to activate your account and access all premium features.
                        <?php endif; ?>
                    </p>
                    <form method="post" action="<?= site_url('merchant/subscription/process-payment') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="plan_id" value="<?= $current_subscription['plan_id'] ?>">
                        <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-lg">
                            <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            Complete Payment Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Current Subscription Card -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Current Subscription</h2>
                
                <?php if ($current_subscription): ?>
                    <!-- Plan Header -->
                    <div class="border-l-4 border-blue-500 pl-4 mb-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900"><?= esc($current_subscription['plan_name']) ?></h3>
                                <p class="text-gray-600"><?= esc($current_subscription['description']) ?></p>
                                <div class="mt-2">
                                    <span class="text-2xl font-bold text-gray-900">$<?= number_format($current_subscription['price'], 2) ?></span>
                                    <span class="text-gray-600">/month</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <?php
                                $statusColors = [
                                    'trial' => 'bg-yellow-100 text-yellow-800',
                                    'active' => 'bg-green-100 text-green-800',
                                    'past_due' => 'bg-red-100 text-red-800',
                                    'cancelled' => 'bg-gray-100 text-gray-800',
                                    'expired' => 'bg-red-100 text-red-800',
                                    'new' => 'bg-orange-100 text-orange-800',
                                    'trial_pending' => 'bg-orange-100 text-orange-800'
                                ];
                                $statusColor = $statusColors[$current_subscription['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $statusColor ?>">
                                    <?php if ($current_subscription['status'] === 'trial'): ?>
                                        <?php
                                        $trialDays = $current_subscription['trial_days'] ?? 30;
                                        $daysLeft = $current_subscription['trial_ends_at'] ?
                                            max(0, ceil((strtotime($current_subscription['trial_ends_at']) - time()) / (24 * 60 * 60))) :
                                            $trialDays;
                                        ?>
                                        <?= $trialDays ?>-Day Free Trial (<?= $daysLeft ?> days left)
                                    <?php elseif ($current_subscription['status'] === 'new'): ?>
                                        Payment Required
                                    <?php elseif ($current_subscription['status'] === 'trial_pending'): ?>
                                        Payment Setup Required
                                    <?php else: ?>
                                        <?= ucfirst($current_subscription['status']) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Subscription Information -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Subscription Details
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <!-- Subscription ID -->
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600 font-medium">Subscription ID:</span>
                                <span class="text-gray-900 font-mono">#<?= str_pad($current_subscription['id'], 6, '0', STR_PAD_LEFT) ?></span>
                            </div>

                            <!-- Status -->
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600 font-medium">Status:</span>
                                <span class="text-gray-900 font-semibold"><?= ucfirst($current_subscription['status']) ?></span>
                            </div>

                            <!-- Plan ID -->
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600 font-medium">Plan ID:</span>
                                <span class="text-gray-900 font-mono">#<?= $current_subscription['plan_id'] ?></span>
                            </div>

                            <!-- Merchant ID -->
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600 font-medium">Merchant ID:</span>
                                <span class="text-gray-900 font-mono">#<?= $current_subscription['merchant_id'] ?></span>
                            </div>

                            <!-- Trial Ends At -->
                            <?php if ($current_subscription['trial_ends_at']): ?>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600 font-medium">Trial Ends:</span>
                                <span class="text-gray-900"><?= date('M j, Y g:i A', strtotime($current_subscription['trial_ends_at'])) ?></span>
                            </div>
                            <?php endif; ?>

                            <!-- Current Period Starts At -->
                            <?php if ($current_subscription['current_period_starts_at']): ?>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600 font-medium">Period Started:</span>
                                <span class="text-gray-900"><?= date('M j, Y g:i A', strtotime($current_subscription['current_period_starts_at'])) ?></span>
                            </div>
                            <?php endif; ?>

                            <!-- Current Period Ends At -->
                            <?php if ($current_subscription['current_period_ends_at']): ?>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600 font-medium">
                                    <?php if ($current_subscription['status'] === 'past_due'): ?>
                                        Period Ended:
                                    <?php else: ?>
                                        Next Billing:
                                    <?php endif; ?>
                                </span>
                                <span class="text-gray-900 <?= $current_subscription['status'] === 'past_due' ? 'text-red-600 font-semibold' : '' ?>">
                                    <?= date('M j, Y g:i A', strtotime($current_subscription['current_period_ends_at'])) ?>
                                </span>
                            </div>
                            <?php endif; ?>

                            <!-- PayFast Token -->
                            <?php if ($current_subscription['payfast_token']): ?>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600 font-medium">PayFast Token:</span>
                                <span class="text-gray-900 font-mono text-xs"><?= substr($current_subscription['payfast_token'], 0, 20) ?>...</span>
                            </div>
                            <?php endif; ?>

                            <!-- Created At -->
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600 font-medium">Subscription Created:</span>
                                <span class="text-gray-900"><?= date('M j, Y g:i A', strtotime($current_subscription['created_at'])) ?></span>
                            </div>

                            <!-- Updated At -->
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600 font-medium">Last Updated:</span>
                                <span class="text-gray-900"><?= date('M j, Y g:i A', strtotime($current_subscription['updated_at'])) ?></span>
                            </div>

                            <!-- Days Active -->
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600 font-medium">Days Active:</span>
                                <span class="text-gray-900 font-semibold">
                                    <?php
                                    $daysActive = floor((time() - strtotime($current_subscription['created_at'])) / (24 * 60 * 60));
                                    echo $daysActive . ' ' . ($daysActive === 1 ? 'day' : 'days');
                                    ?>
                                </span>
                            </div>

                            <!-- Days Until Next Billing -->
                            <?php if ($current_subscription['current_period_ends_at'] && in_array($current_subscription['status'], ['trial', 'active'])): ?>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600 font-medium">Days Until Billing:</span>
                                <span class="text-gray-900 font-semibold">
                                    <?php
                                    $daysUntilBilling = max(0, ceil((strtotime($current_subscription['current_period_ends_at']) - time()) / (24 * 60 * 60)));
                                    echo $daysUntilBilling . ' ' . ($daysUntilBilling === 1 ? 'day' : 'days');
                                    ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Plan Features Section -->
                    <?php if (!empty($plan_features)): ?>
                    <div class="bg-blue-50 rounded-lg p-4 mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Plan Features
                        </h4>
                        <ul class="space-y-2">
                            <?php foreach ($plan_features as $feature): ?>
                                <li class="flex items-start text-sm">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <span class="text-gray-900 font-medium"><?= esc($feature['feature_name']) ?></span>
                                        <?php if (!empty($feature['description'])): ?>
                                            <p class="text-gray-600 text-xs mt-0.5"><?= esc($feature['description']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <!-- Plan Limitations Section -->
                    <?php if (!empty($plan_limitations)): ?>
                    <div class="bg-purple-50 rounded-lg p-4 mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Plan Limits
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <?php foreach ($plan_limitations as $limitType => $limitData): ?>
                                <div class="bg-white rounded-md p-3 border border-purple-200">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-700 text-sm font-medium"><?= esc($limitData['name']) ?>:</span>
                                        <span class="text-gray-900 font-bold text-sm <?= $limitData['value'] === -1 ? 'text-green-600' : '' ?>">
                                            <?= $limitData['display'] ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="flex space-x-3">
                        <?php if (in_array($current_subscription['status'], ['new', 'trial_pending'])): ?>
                            <!-- Payment Required - Show Complete Payment button -->
                            <form method="post" action="<?= site_url('merchant/subscription/process-payment') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="plan_id" value="<?= $current_subscription['plan_id'] ?>">
                                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors font-semibold">
                                    Complete Payment
                                </button>
                            </form>
                            <a href="<?= site_url('merchant/subscription/plans') ?>" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors">
                                Change Plan
                            </a>
                        <?php elseif ($current_subscription['status'] === 'past_due'): ?>
                            <a href="<?= site_url('merchant/subscription/renew/' . $current_subscription['id']) ?>" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                                Renew Subscription
                            </a>
                        <?php else: ?>
                            <a href="<?= site_url('merchant/subscription/plans') ?>" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                Change Plan
                            </a>
                        <?php endif; ?>

                        <?php if (!in_array($current_subscription['status'], ['cancelled', 'new', 'trial_pending'])): ?>
                            <button type="button"
                                    onclick="openCancelModal()"
                                    class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">
                                Cancel Subscription
                            </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No Active Subscription</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by choosing a subscription plan.</p>
                        <div class="mt-6">
                            <a href="<?= site_url('merchant/subscription/plans') ?>" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                Choose a Plan
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="space-y-6">
            <!-- Available Plans Preview -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Available Plans</h3>
                <div class="space-y-3">
                    <?php foreach (array_slice($available_plans, 0, 3) as $plan): ?>
                        <div class="border rounded-lg p-3">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 class="font-medium text-gray-900"><?= esc($plan['name']) ?></h4>
                                    <p class="text-sm text-gray-600">$<?= number_format($plan['price'], 2) ?>/month</p>
                                </div>
                                <?php if ($plan['has_free_trial']): ?>
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                        <?= $plan['trial_days'] ?> day trial
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4">
                    <a href="<?= site_url('merchant/subscription/plans') ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View All Plans →
                    </a>
                </div>
            </div>

            <!-- Billing Information -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Billing Information</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Payment Method:</span>
                        <span class="font-medium">PayFast</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Currency:</span>
                        <span class="font-medium">ZAR (South African Rand)</span>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="<?= site_url('merchant/subscription/update-payment-method') ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Update Payment Method
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscription History -->
    <?php if (!empty($subscription_history)): ?>
        <div class="mt-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Subscription History</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($subscription_history as $subscription): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?= esc($subscription['plan_name']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $statusColor = $statusColors[$subscription['status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $statusColor ?>">
                                            <?php if ($subscription['status'] === 'trial'): ?>
                                                <?php
                                                $trialDays = $subscription['trial_days'] ?? 30;
                                                ?>
                                                <?= $trialDays ?>-Day Free Trial
                                            <?php else: ?>
                                                <?= ucfirst($subscription['status']) ?>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php if ($subscription['current_period_starts_at'] && $subscription['current_period_ends_at']): ?>
                                            <?= date('M j', strtotime($subscription['current_period_starts_at'])) ?> - 
                                            <?= date('M j, Y', strtotime($subscription['current_period_ends_at'])) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('M j, Y', strtotime($subscription['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Cancel Subscription Modal -->
<div id="cancelModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4 border-b">
            <h3 class="text-xl font-semibold text-gray-900">Cancel Subscription</h3>
            <button type="button" onclick="closeCancelModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <form id="cancelForm" method="post" action="<?= site_url('merchant/subscription/cancel') ?>">
            <?= csrf_field() ?>

            <!-- Warning Message -->
            <div class="mt-4 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-semibold text-red-800">Important: No Refunds</h4>
                        <p class="mt-1 text-sm text-red-700">
                            Cancelling your subscription will not result in a refund for the current billing period.
                            You will continue to have access until <strong><?= isset($current_subscription['current_period_ends_at']) ? date('F j, Y', strtotime($current_subscription['current_period_ends_at'])) : 'the end of your billing period' ?></strong>.
                        </p>
                    </div>
                </div>
            </div>

            <!-- What Happens Section -->
            <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                <h4 class="text-sm font-semibold text-yellow-800 mb-2">What happens when you cancel:</h4>
                <ul class="text-sm text-yellow-700 space-y-1 ml-4 list-disc">
                    <li>Your business will no longer be visible to drivers</li>
                    <li>All your locations and branches will be hidden</li>
                    <li>You will not receive any new orders</li>
                    <li>Your data will be preserved for 90 days</li>
                    <li>You can reactivate anytime by selecting a new plan</li>
                </ul>
            </div>

            <!-- Cancellation Reason -->
            <div class="mt-6">
                <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Please tell us why you're cancelling <span class="text-red-500">*</span>
                </label>
                <select id="cancellation_reason"
                        name="cancellation_reason"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Select a reason --</option>
                    <option value="too_expensive">Too expensive</option>
                    <option value="not_enough_orders">Not getting enough orders</option>
                    <option value="missing_features">Missing features I need</option>
                    <option value="technical_issues">Technical issues</option>
                    <option value="switching_service">Switching to another service</option>
                    <option value="business_closed">Business closed/paused</option>
                    <option value="other">Other reason</option>
                </select>
            </div>

            <!-- Additional Comments -->
            <div class="mt-4">
                <label for="cancellation_comments" class="block text-sm font-medium text-gray-700 mb-2">
                    Additional comments (optional)
                </label>
                <textarea id="cancellation_comments"
                          name="cancellation_comments"
                          rows="3"
                          placeholder="Help us improve by sharing more details..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <!-- Confirmation Checkbox -->
            <div class="mt-6">
                <label class="flex items-start">
                    <input type="checkbox"
                           id="confirm_cancel"
                           name="confirm_cancel"
                           required
                           class="mt-1 h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">
                        I understand that cancelling my subscription will hide my business from drivers and I will not receive a refund for the current billing period.
                    </span>
                </label>
            </div>

            <!-- Modal Footer -->
            <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-end border-t pt-4">
                <button type="button"
                        onclick="closeCancelModal()"
                        class="px-6 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors font-medium">
                    Keep Subscription
                </button>
                <button type="submit"
                        class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors font-medium">
                    Cancel Subscription
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCancelModal() {
    document.getElementById('cancelModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

function closeCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
    document.body.style.overflow = 'auto'; // Restore scrolling
    // Reset form
    document.getElementById('cancelForm').reset();
}

// Close modal when clicking outside
document.getElementById('cancelModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCancelModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCancelModal();
    }
});
</script>

<?= $this->include('merchant/templates/footer') ?>
