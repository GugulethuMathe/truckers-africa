<?= view('admin/templates/header') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center space-x-4">
        <a href="<?= site_url('admin/email-marketing/campaigns') ?>" class="text-gray-600 hover:text-gray-900">
            <i class="ri-arrow-left-line text-2xl"></i>
        </a>
        <div>
            <p class="text-gray-600 mt-1">View all successfully sent email campaigns</p>
        </div>
    </div>
</div>

                <!-- Success/Error Messages -->
                <?php if (session()->has('success')): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
                        <?= session('success') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->has('error')): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                        <?= session('error') ?>
                    </div>
                <?php endif; ?>

                <!-- Campaigns Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campaign Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipients</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opened</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clicked</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($campaigns)): ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                        <i class="ri-inbox-line text-4xl mb-2"></i>
                                        <p>No sent campaigns found</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($campaigns as $campaign): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium text-gray-900"><?= esc($campaign['campaign_name']) ?></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900"><?= esc($campaign['subject']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= number_format($campaign['total_recipients'] ?? 0) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= number_format($campaign['total_sent'] ?? 0) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php
                                            $openRate = ($campaign['total_sent'] > 0) ? round(($campaign['total_opened'] / $campaign['total_sent']) * 100, 1) : 0;
                                            ?>
                                            <?= number_format($campaign['total_opened'] ?? 0) ?> (<?= $openRate ?>%)
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php
                                            $clickRate = ($campaign['total_sent'] > 0) ? round(($campaign['total_clicked'] / $campaign['total_sent']) * 100, 1) : 0;
                                            ?>
                                            <?= number_format($campaign['total_clicked'] ?? 0) ?> (<?= $clickRate ?>%)
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= $campaign['sent_at'] ? date('M d, Y H:i', strtotime($campaign['sent_at'])) : 'N/A' ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <a href="<?= site_url('admin/email-marketing/campaigns/view/' . $campaign['id']) ?>" class="text-blue-600 hover:text-blue-900" title="View Details">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <a href="<?= site_url('admin/email-marketing/campaigns/report/' . $campaign['id']) ?>" class="text-green-600 hover:text-green-900" title="View Report">
                                                <i class="ri-bar-chart-line"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

<?= view('admin/templates/footer') ?>
