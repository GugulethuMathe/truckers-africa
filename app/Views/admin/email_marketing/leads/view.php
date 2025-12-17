<?= view('admin/templates/header') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="<?= site_url('admin/email-marketing/leads') ?>" class="text-gray-600 hover:text-gray-900">
                <i class="ri-arrow-left-line text-2xl"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-900"><?= esc($lead['first_name'] . ' ' . $lead['last_name']) ?></h2>
                <p class="text-gray-600 mt-1"><?= esc($lead['email']) ?></p>
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="<?= site_url('admin/email-marketing/leads/edit/' . $lead['id']) ?>" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="ri-edit-line mr-2"></i>
                Edit Lead
            </a>
        </div>
    </div>
</div>

                <!-- Lead Information Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Lead Details Card -->
                    <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Lead Information</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">First Name</p>
                                <p class="font-medium text-gray-900"><?= esc($lead['first_name']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Last Name</p>
                                <p class="font-medium text-gray-900"><?= esc($lead['last_name'] ?: 'N/A') ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Email</p>
                                <p class="font-medium text-gray-900"><?= esc($lead['email']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Phone Number</p>
                                <p class="font-medium text-gray-900"><?= esc($lead['phone_number'] ?: 'N/A') ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Company Name</p>
                                <p class="font-medium text-gray-900"><?= esc($lead['company_name'] ?: 'N/A') ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Lead Source</p>
                                <p class="font-medium text-gray-900"><?= esc($lead['lead_source'] ?: 'N/A') ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Country</p>
                                <p class="font-medium text-gray-900"><?= esc($lead['country'] ?: 'N/A') ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">City</p>
                                <p class="font-medium text-gray-900"><?= esc($lead['city'] ?: 'N/A') ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Lead Status</p>
                                <?php
                                $statusColors = [
                                    'new' => 'bg-blue-100 text-blue-800',
                                    'contacted' => 'bg-yellow-100 text-yellow-800',
                                    'qualified' => 'bg-green-100 text-green-800',
                                    'converted' => 'bg-purple-100 text-purple-800',
                                    'unsubscribed' => 'bg-red-100 text-red-800'
                                ];
                                $statusColor = $statusColors[$lead['lead_status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusColor ?>">
                                    <?= ucfirst($lead['lead_status']) ?>
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Subscription Status</p>
                                <?php if ($lead['is_subscribed']): ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="ri-checkbox-circle-line mr-1"></i> Subscribed
                                    </span>
                                <?php else: ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        <i class="ri-close-circle-line mr-1"></i> Unsubscribed
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($lead['tags']): ?>
                            <div class="mt-4">
                                <p class="text-sm text-gray-600 mb-2">Tags</p>
                                <div class="flex flex-wrap gap-2">
                                    <?php
                                    $tags = explode(',', $lead['tags']);
                                    foreach ($tags as $tag):
                                    ?>
                                        <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs rounded-full"><?= esc(trim($tag)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($lead['notes']): ?>
                            <div class="mt-4">
                                <p class="text-sm text-gray-600 mb-2">Notes</p>
                                <p class="text-gray-900 whitespace-pre-wrap"><?= esc($lead['notes']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Statistics Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Email Statistics</h2>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-600">Total Emails Sent</p>
                                <p class="text-2xl font-bold text-gray-900"><?= number_format($lead['email_sent_count'] ?? 0) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Last Email Sent</p>
                                <p class="font-medium text-gray-900">
                                    <?= $lead['last_email_sent_at'] ? date('M d, Y H:i', strtotime($lead['last_email_sent_at'])) : 'Never' ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Last Opened</p>
                                <p class="font-medium text-gray-900">
                                    <?= $lead['last_opened_at'] ? date('M d, Y H:i', strtotime($lead['last_opened_at'])) : 'Never' ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Last Clicked</p>
                                <p class="font-medium text-gray-900">
                                    <?= $lead['last_clicked_at'] ? date('M d, Y H:i', strtotime($lead['last_clicked_at'])) : 'Never' ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Added On</p>
                                <p class="font-medium text-gray-900"><?= date('M d, Y', strtotime($lead['created_at'])) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Campaign History -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Campaign History</h2>
                    <?php if (empty($campaign_history)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="ri-mail-line text-4xl mb-2"></i>
                            <p>No campaigns sent to this lead yet</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campaign Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent At</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opened At</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clicked At</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($campaign_history as $history): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="font-medium text-gray-900"><?= esc($history['campaign_name']) ?></div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900"><?= esc($history['subject']) ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php
                                                $emailStatusColors = [
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'sent' => 'bg-green-100 text-green-800',
                                                    'failed' => 'bg-red-100 text-red-800',
                                                    'bounced' => 'bg-red-100 text-red-800'
                                                ];
                                                $emailStatusColor = $emailStatusColors[$history['email_status']] ?? 'bg-gray-100 text-gray-800';
                                                ?>
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $emailStatusColor ?>">
                                                    <?= ucfirst($history['email_status']) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?= $history['sent_at'] ? date('M d, Y H:i', strtotime($history['sent_at'])) : 'N/A' ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php if ($history['opened_at']): ?>
                                                    <span class="text-green-600">
                                                        <i class="ri-checkbox-circle-line mr-1"></i>
                                                        <?= date('M d, Y H:i', strtotime($history['opened_at'])) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-gray-400">Not opened</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php if ($history['clicked_at']): ?>
                                                    <span class="text-blue-600">
                                                        <i class="ri-cursor-line mr-1"></i>
                                                        <?= date('M d, Y H:i', strtotime($history['clicked_at'])) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-gray-400">Not clicked</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

<?= view('admin/templates/footer') ?>