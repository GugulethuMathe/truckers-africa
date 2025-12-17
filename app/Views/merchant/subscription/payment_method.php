<?= view('merchant/templates/header', ['page_title' => $page_title]) ?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="<?= site_url('merchant/subscription') ?>" class="text-blue-600 hover:text-blue-800 flex items-center">
            <i class="ri-arrow-left-line mr-2"></i> Back to Subscription
        </a>
    </div>

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Update Payment Method</h1>
        <p class="text-gray-600">Manage your payment information for subscription billing</p>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('info')): ?>
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6">
            <?= session()->getFlashdata('info') ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Current Payment Method -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Current Payment Method</h2>
                
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg bg-gray-50">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="ri-bank-card-line text-2xl text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">PayFast</p>
                            <p class="text-sm text-gray-600">Secure payment gateway</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-green-100 text-green-700 text-sm font-medium rounded-full">Active</span>
                </div>

                <?php if ($current_subscription): ?>
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start">
                        <i class="ri-information-line text-blue-600 text-xl mr-3 mt-0.5"></i>
                        <div>
                            <p class="text-sm text-blue-900 font-medium mb-1">Current Subscription</p>
                            <p class="text-sm text-blue-800">
                                Your next billing date is <strong><?= date('F j, Y', strtotime($current_subscription['current_period_ends_at'])) ?></strong>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Update Payment Method Button -->
                <div class="mt-4">
                    <button onclick="showUpdatePaymentModal()" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center">
                        <i class="ri-refresh-line mr-2"></i>
                        Update Payment Method
                    </button>
                    <p class="text-xs text-gray-500 mt-2 text-center">
                        You'll be redirected to PayFast to securely update your payment details
                    </p>
                </div>
                <?php endif; ?>
            </div>

            <!-- PayFast Integration Notice -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">PayFast Integration</h2>
                
                <div class="space-y-4">
                    <div class="flex items-start">
                        <i class="ri-shield-check-line text-green-600 text-2xl mr-3 mt-1"></i>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Secure Payments</h3>
                            <p class="text-sm text-gray-600">All payments are processed securely through PayFast, South Africa's leading payment gateway.</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <i class="ri-lock-line text-green-600 text-2xl mr-3 mt-1"></i>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">PCI Compliant</h3>
                            <p class="text-sm text-gray-600">Your payment information is encrypted and stored securely according to PCI DSS standards.</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <i class="ri-customer-service-line text-green-600 text-2xl mr-3 mt-1"></i>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">24/7 Support</h3>
                            <p class="text-sm text-gray-600">Our support team is available to help with any payment-related questions.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-start">
                        <i class="ri-checkbox-circle-line text-green-600 text-xl mr-3 mt-0.5"></i>
                        <div>
                            <p class="text-sm text-green-900 font-medium mb-1">PayFast Integration Active</p>
                            <p class="text-sm text-green-800">
                                PayFast is fully integrated and operational. You can manage subscriptions, process payments securely, and view transaction history. All payment methods are supported including credit cards, EFT, and instant EFT.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="lg:col-span-1">
            <!-- Subscription Summary -->
            <?php if ($current_subscription): ?>
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Subscription Summary</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="font-medium capitalize"><?= esc($current_subscription['status']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Current Period:</span>
                        <span class="font-medium"><?= date('M j', strtotime($current_subscription['current_period_starts_at'])) ?> - <?= date('M j', strtotime($current_subscription['current_period_ends_at'])) ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="<?= site_url('merchant/subscription/transaction-history') ?>" class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="flex items-center">
                            <i class="ri-file-list-3-line text-xl text-gray-600 mr-3"></i>
                            <span class="text-sm font-medium text-gray-700">Transaction History</span>
                        </div>
                        <i class="ri-arrow-right-s-line text-gray-400"></i>
                    </a>
                    <a href="<?= site_url('merchant/subscription') ?>" class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="flex items-center">
                            <i class="ri-settings-3-line text-xl text-gray-600 mr-3"></i>
                            <span class="text-sm font-medium text-gray-700">Manage Subscription</span>
                        </div>
                        <i class="ri-arrow-right-s-line text-gray-400"></i>
                    </a>
                </div>
            </div>

            <!-- Help & Support -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Need Help?</h3>
                <p class="text-sm text-gray-600 mb-4">
                    If you have questions about billing or payments, our support team is here to help.
                </p>
                <a href="<?= site_url('merchant/support') ?>" class="block w-full text-center bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                    Contact Support
                </a>
            </div>

            <!-- Payment Methods Accepted -->
            <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Methods</h3>
                <p class="text-sm text-gray-600 mb-3">We accept the following payment methods through PayFast:</p>
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex items-center justify-center p-3 border border-gray-200 rounded-lg">
                        <i class="ri-bank-card-line text-2xl text-gray-600"></i>
                        <span class="ml-2 text-sm text-gray-700">Cards</span>
                    </div>
                    <div class="flex items-center justify-center p-3 border border-gray-200 rounded-lg">
                        <i class="ri-bank-line text-2xl text-gray-600"></i>
                        <span class="ml-2 text-sm text-gray-700">EFT</span>
                    </div>
                    <div class="flex items-center justify-center p-3 border border-gray-200 rounded-lg">
                        <i class="ri-smartphone-line text-2xl text-gray-600"></i>
                        <span class="ml-2 text-sm text-gray-700">Instant EFT</span>
                    </div>
                    <div class="flex items-center justify-center p-3 border border-gray-200 rounded-lg">
                        <i class="ri-wallet-line text-2xl text-gray-600"></i>
                        <span class="ml-2 text-sm text-gray-700">Debit</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Payment Method Modal -->
<div id="updatePaymentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-blue-100 rounded-full">
                <i class="ri-refresh-line text-2xl text-blue-600"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 text-center mt-4">Update Payment Method</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500 text-center">
                    You will be redirected to PayFast to securely update your payment details.
                </p>

                <!-- No Charge Notice -->
                <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center justify-center text-green-700">
                        <i class="ri-checkbox-circle-line text-lg mr-2"></i>
                        <p class="text-sm font-semibold">No charge will be made</p>
                    </div>
                </div>

                <p class="text-sm text-gray-700 font-medium text-center mt-4">
                    This process will:
                </p>
                <ul class="text-sm text-gray-600 mt-2 space-y-1">
                    <li class="flex items-start">
                        <i class="ri-check-line text-green-600 mr-2 mt-0.5"></i>
                        <span>Keep your current subscription active</span>
                    </li>
                    <li class="flex items-start">
                        <i class="ri-check-line text-green-600 mr-2 mt-0.5"></i>
                        <span>Update your payment information securely</span>
                    </li>
                    <li class="flex items-start">
                        <i class="ri-check-line text-green-600 mr-2 mt-0.5"></i>
                        <span>Process future payments with new details</span>
                    </li>
                    <li class="flex items-start">
                        <i class="ri-check-line text-green-600 mr-2 mt-0.5"></i>
                        <span>Not charge your account now</span>
                    </li>
                </ul>
            </div>
            <div class="flex gap-3 px-4 py-3">
                <button onclick="hideUpdatePaymentModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancel
                </button>
                <button onclick="proceedToUpdatePayment()" class="flex-1 px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Continue
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showUpdatePaymentModal() {
    document.getElementById('updatePaymentModal').classList.remove('hidden');
}

function hideUpdatePaymentModal() {
    document.getElementById('updatePaymentModal').classList.add('hidden');
}

function proceedToUpdatePayment() {
    window.location.href = '<?= site_url('merchant/subscription/update-payment-method') ?>';
}

// Close modal when clicking outside
document.getElementById('updatePaymentModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        hideUpdatePaymentModal();
    }
});
</script>

<?= view('merchant/templates/footer') ?>
