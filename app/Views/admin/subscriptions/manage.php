<?= view('admin/templates/header', ['page_title' => 'Manage Subscription']) ?>

<div class="container mx-auto px-4 py-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="<?= site_url('admin/subscriptions') ?>" class="text-indigo-600 hover:underline flex items-center">
            <i class="ri-arrow-left-line mr-2"></i> Back to All Subscriptions
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Subscription Details -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Subscription Details</h2>
            
            <div class="space-y-3">
                <div class="flex justify-between border-b pb-2">
                    <span class="font-semibold">Subscription ID:</span>
                    <span><?= esc($subscription['id']) ?></span>
                </div>
                
                <div class="flex justify-between border-b pb-2">
                    <span class="font-semibold">Status:</span>
                    <span class="px-2 py-1 text-sm font-semibold leading-tight <?= $subscription['status'] === 'active' ? 'text-green-700 bg-green-100' : 'text-gray-700 bg-gray-100' ?> rounded-full">
                        <?= esc(ucfirst($subscription['status'])) ?>
                    </span>
                </div>
                
                <div class="flex justify-between border-b pb-2">
                    <span class="font-semibold">Plan:</span>
                    <span><?= esc($subscription['plan_name']) ?></span>
                </div>
                
                <div class="flex justify-between border-b pb-2">
                    <span class="font-semibold">Price:</span>
                    <span><?= esc($planCurrencySymbol) ?><?= number_format($subscription['plan_price'], 2) ?> / <?= esc($subscription['billing_interval']) ?></span>
                </div>
                
                <div class="flex justify-between border-b pb-2">
                    <span class="font-semibold">Current Period:</span>
                    <span><?= date('M d, Y', strtotime($subscription['current_period_starts_at'])) ?> - <?= date('M d, Y', strtotime($subscription['current_period_ends_at'])) ?></span>
                </div>
                
                <?php if (!empty($subscription['trial_ends_at'])): ?>
                <div class="flex justify-between border-b pb-2">
                    <span class="font-semibold">Trial Ends:</span>
                    <span><?= date('M d, Y', strtotime($subscription['trial_ends_at'])) ?></span>
                </div>
                <?php endif; ?>
                
                <div class="flex justify-between border-b pb-2">
                    <span class="font-semibold">Created:</span>
                    <span><?= date('M d, Y H:i', strtotime($subscription['created_at'])) ?></span>
                </div>
                
                <?php if (!empty($subscription['cancelled_at'])): ?>
                <div class="flex justify-between border-b pb-2">
                    <span class="font-semibold text-red-600">Cancelled:</span>
                    <span class="text-red-600"><?= date('M d, Y H:i', strtotime($subscription['cancelled_at'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Merchant Details -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Merchant Information</h2>
            
            <div class="space-y-3">
                <div class="flex justify-between border-b pb-2">
                    <span class="font-semibold">Business Name:</span>
                    <span><?= esc($subscription['business_name']) ?></span>
                </div>
                
                <div class="flex justify-between border-b pb-2">
                    <span class="font-semibold">Owner:</span>
                    <span><?= esc($subscription['owner_name']) ?></span>
                </div>
                
                <div class="flex justify-between border-b pb-2">
                    <span class="font-semibold">Email:</span>
                    <span><a href="mailto:<?= esc($subscription['merchant_email']) ?>" class="text-indigo-600 hover:underline"><?= esc($subscription['merchant_email']) ?></a></span>
                </div>
                
                <div class="flex justify-between border-b pb-2">
                    <span class="font-semibold">Contact:</span>
                    <span><?= esc($subscription['business_contact_number']) ?></span>
                </div>
                
                <div class="mt-4">
                    <a href="<?= site_url('admin/merchants/view/' . $subscription['merchant_id']) ?>" class="inline-block bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        View Full Merchant Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mt-6">
        <h2 class="text-xl font-bold mb-4">Subscription Actions</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php if ($subscription['status'] === 'active'): ?>
                <button onclick="if(confirm('Are you sure you want to suspend this subscription?')) { window.location.href='<?= site_url('admin/subscriptions/suspend/' . $subscription['id']) ?>'; }" 
                        class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                    <i class="ri-pause-circle-line mr-2"></i>Suspend Subscription
                </button>
                
                <button onclick="if(confirm('Are you sure you want to cancel this subscription?')) { window.location.href='<?= site_url('admin/subscriptions/cancel/' . $subscription['id']) ?>'; }" 
                        class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    <i class="ri-close-circle-line mr-2"></i>Cancel Subscription
                </button>
            <?php elseif ($subscription['status'] === 'suspended'): ?>
                <button onclick="if(confirm('Are you sure you want to reactivate this subscription?')) { window.location.href='<?= site_url('admin/subscriptions/reactivate/' . $subscription['id']) ?>'; }" 
                        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    <i class="ri-play-circle-line mr-2"></i>Reactivate Subscription
                </button>
            <?php endif; ?>
            
            <button onclick="document.getElementById('changePlanModal').classList.remove('hidden')" 
                    class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
                <i class="ri-refresh-line mr-2"></i>Change Plan
            </button>
            
            <button onclick="if(confirm('Are you sure you want to extend the current period by 30 days?')) { window.location.href='<?= site_url('admin/subscriptions/extend/' . $subscription['id']) ?>'; }" 
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                <i class="ri-time-line mr-2"></i>Extend Period
            </button>
        </div>
    </div>

    <!-- Payment History Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mt-6">
        <h2 class="text-xl font-bold mb-4">Payment History</h2>
        <p class="text-gray-600">Payment history will be displayed here once payment integration is complete.</p>
    </div>
</div>

<!-- Change Plan Modal -->
<div id="changePlanModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-bold mb-4">Change Subscription Plan</h3>
            <form action="<?= site_url('admin/subscriptions/change-plan/' . $subscription['id']) ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-2">Select New Plan:</label>
                    <select name="new_plan_id" class="w-full px-3 py-2 border rounded" required>
                        <option value="">-- Select Plan --</option>
                        <?php foreach ($available_plans as $plan): ?>
                            <?php if ($plan['id'] != $subscription['plan_id']): ?>
                                <option value="<?= $plan['id'] ?>">
                                    <?= esc($plan['name']) ?> - R<?= number_format($plan['price'], 2) ?>/<?= esc($plan['billing_interval']) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('changePlanModal').classList.add('hidden')" 
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        Change Plan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= view('admin/templates/footer') ?>
