<?= view('admin/templates/header') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <p class="text-gray-600 mt-1">Create and manage your email marketing campaigns</p>
        </div>
        <a href="<?= site_url('admin/email-marketing/campaigns/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
            <i class="ri-add-line mr-2"></i>
            Create Campaign
        </a>
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

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Total Campaigns</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($stats['total']) ?></p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-lg">
                                <i class="ri-mail-line text-2xl text-blue-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Draft</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($stats['draft']) ?></p>
                            </div>
                            <div class="bg-gray-100 p-3 rounded-lg">
                                <i class="ri-draft-line text-2xl text-gray-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Scheduled</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($stats['scheduled']) ?></p>
                            </div>
                            <div class="bg-yellow-100 p-3 rounded-lg">
                                <i class="ri-calendar-line text-2xl text-yellow-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Sent</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($stats['sent']) ?></p>
                            </div>
                            <div class="bg-green-100 p-3 rounded-lg">
                                <i class="ri-send-plane-fill text-2xl text-green-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Failed</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($stats['failed']) ?></p>
                            </div>
                            <div class="bg-red-100 p-3 rounded-lg">
                                <i class="ri-error-warning-line text-2xl text-red-600"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Tabs -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <a href="<?= site_url('admin/email-marketing/campaigns') ?>" 
                               class="<?= !$current_status ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                                All Campaigns
                            </a>
                            <a href="<?= site_url('admin/email-marketing/campaigns?status=draft') ?>" 
                               class="<?= $current_status === 'draft' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                                Draft
                            </a>
                            <a href="<?= site_url('admin/email-marketing/campaigns?status=scheduled') ?>" 
                               class="<?= $current_status === 'scheduled' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                                Scheduled
                            </a>
                            <a href="<?= site_url('admin/email-marketing/campaigns?status=sent') ?>" 
                               class="<?= $current_status === 'sent' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                                Sent
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Campaigns Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campaign Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipients</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($campaigns)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        <i class="ri-inbox-line text-4xl mb-2"></i>
                                        <p>No campaigns found</p>
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
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $statusColors = [
                                                'draft' => 'bg-gray-100 text-gray-800',
                                                'scheduled' => 'bg-yellow-100 text-yellow-800',
                                                'sent' => 'bg-green-100 text-green-800',
                                                'failed' => 'bg-red-100 text-red-800'
                                            ];
                                            $statusColor = $statusColors[$campaign['status']] ?? 'bg-gray-100 text-gray-800';
                                            ?>
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusColor ?>">
                                                <?= ucfirst($campaign['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= date('M d, Y', strtotime($campaign['created_at'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <a href="<?= site_url('admin/email-marketing/campaigns/view/' . $campaign['id']) ?>" class="text-blue-600 hover:text-blue-900" title="View">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <?php if ($campaign['status'] === 'draft'): ?>
                                                <a href="<?= site_url('admin/email-marketing/campaigns/edit/' . $campaign['id']) ?>" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                                    <i class="ri-edit-line"></i>
                                                </a>
                                                <a href="<?= site_url('admin/email-marketing/campaigns/send/' . $campaign['id']) ?>" class="text-green-600 hover:text-green-900" title="Send" onclick="return confirm('Are you sure you want to send this campaign?')">
                                                    <i class="ri-send-plane-fill"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= site_url('admin/email-marketing/campaigns/delete/' . $campaign['id']) ?>" class="text-red-600 hover:text-red-900" title="Delete" onclick="return confirm('Are you sure you want to delete this campaign?')">
                                                <i class="ri-delete-bin-line"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

<?= view('admin/templates/footer') ?>

