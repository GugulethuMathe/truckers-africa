<?= view('merchant/templates/header', ['page_title' => $page_title]) ?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="<?= site_url('merchant/subscription') ?>" class="text-blue-600 hover:text-blue-800 flex items-center">
            <i class="ri-arrow-left-line mr-2"></i> Back to Subscription
        </a>
    </div>

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Transaction History</h1>
        <p class="text-gray-600">View all your payment transactions and billing history</p>
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

    <!-- Current Subscription Summary -->
    <?php if ($current_subscription): ?>
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Current Subscription</h2>
                <p class="text-gray-600">
                    <span class="font-medium"><?= esc($current_subscription['plan_name']) ?></span> - 
                    <span class="capitalize"><?= esc($current_subscription['status']) ?></span>
                </p>
                <p class="text-sm text-gray-500 mt-1">
                    Next billing: <?= date('F j, Y', strtotime($current_subscription['current_period_ends_at'])) ?>
                </p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold text-gray-900">$<?= number_format($current_subscription['price'], 2) ?></p>
                <p class="text-sm text-gray-500">per month</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">All Transactions</h2>
        </div>

        <?php if (empty($transactions)): ?>
            <div class="p-8 text-center">
                <i class="ri-file-list-line text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">No transactions found</p>
                <p class="text-gray-400 text-sm mt-2">Your payment history will appear here once you make your first payment</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($transactions as $transaction): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= date('M j, Y', strtotime($transaction['created_at'])) ?>
                                    <br>
                                    <span class="text-xs text-gray-500"><?= date('g:i A', strtotime($transaction['created_at'])) ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">
                                    <?= esc(substr($transaction['transaction_id'], 0, 20)) ?>...
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    <?= esc($transaction['currency']) ?> <?= number_format($transaction['amount'], 2) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $statusColors = [
                                        'completed' => 'bg-green-100 text-green-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'failed' => 'bg-red-100 text-red-800',
                                        'cancelled' => 'bg-gray-100 text-gray-800',
                                        'refunded' => 'bg-blue-100 text-blue-800'
                                    ];
                                    $statusClass = $statusColors[$transaction['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                        <?= ucfirst(esc($transaction['status'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?= esc($transaction['payment_method'] ?? 'PayFast') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Summary Stats -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Total Transactions</p>
                        <p class="text-lg font-semibold text-gray-900"><?= count($transactions) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Successful Payments</p>
                        <p class="text-lg font-semibold text-green-600">
                            <?= count(array_filter($transactions, fn($t) => $t['status'] === 'completed')) ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Paid</p>
                        <p class="text-lg font-semibold text-gray-900">
                            $<?= number_format(array_sum(array_map(fn($t) => $t['status'] === 'completed' ? $t['amount'] : 0, $transactions)), 2) ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Help Section -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex items-start">
            <i class="ri-information-line text-blue-600 text-2xl mr-3 mt-0.5"></i>
            <div>
                <h3 class="text-lg font-semibold text-blue-900 mb-2">Need Help?</h3>
                <p class="text-sm text-blue-800 mb-3">
                    If you have questions about a transaction or need to request a refund, please contact our support team.
                </p>
                <a href="<?= site_url('merchant/support') ?>" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800">
                    Contact Support <i class="ri-arrow-right-line ml-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<?= view('merchant/templates/footer') ?>
