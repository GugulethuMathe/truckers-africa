<?= view('admin/templates/header') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="<?= site_url('admin/email-marketing/campaigns') ?>" class="text-gray-600 hover:text-gray-900">
                <i class="ri-arrow-left-line text-2xl"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= esc($campaign['campaign_name']) ?></h1>
                <p class="text-gray-600 mt-1">Campaign Details</p>
            </div>
        </div>
        <?php
        $statusColors = [
            'draft' => 'bg-gray-100 text-gray-800',
            'scheduled' => 'bg-blue-100 text-blue-800',
            'sent' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800'
        ];
        $statusClass = $statusColors[$campaign['status']] ?? 'bg-gray-100 text-gray-800';
        ?>
        <span class="px-4 py-2 text-sm font-medium rounded-full <?= $statusClass ?>">
            <?= ucfirst(esc($campaign['status'])) ?>
        </span>
    </div>
</div>

<!-- Campaign Info Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Recipients</p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($campaign['total_recipients'] ?? 0) ?></p>
            </div>
            <div class="bg-blue-100 p-3 rounded-lg">
                <i class="ri-group-line text-2xl text-blue-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Emails Sent</p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($campaign['total_sent'] ?? 0) ?></p>
            </div>
            <div class="bg-green-100 p-3 rounded-lg">
                <i class="ri-mail-send-line text-2xl text-green-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Open Rate</p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($campaign['open_rate'] ?? 0, 1) ?>%</p>
            </div>
            <div class="bg-yellow-100 p-3 rounded-lg">
                <i class="ri-eye-line text-2xl text-yellow-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Click Rate</p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($campaign['click_rate'] ?? 0, 1) ?>%</p>
            </div>
            <div class="bg-purple-100 p-3 rounded-lg">
                <i class="ri-cursor-line text-2xl text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Campaign Content -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="border-b border-gray-200 px-6 py-4">
        <h2 class="text-lg font-semibold text-gray-900">Email Content</h2>
    </div>
    <div class="p-6">
        <div class="mb-4">
            <label class="text-sm font-medium text-gray-700">Subject:</label>
            <p class="text-gray-900"><?= esc($campaign['subject']) ?></p>
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Content:</label>
            <div class="mt-2 p-4 bg-gray-50 rounded-lg border">
                <?= nl2br(esc($campaign['email_content'])) ?>
            </div>
        </div>
    </div>
</div>

<!-- Recipients Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="border-b border-gray-200 px-6 py-4">
        <h2 class="text-lg font-semibold text-gray-900">Recipients (<?= count($recipients) ?>)</h2>
    </div>
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sent At</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($recipients)): ?>
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">No recipients found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($recipients as $recipient): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap"><?= esc($recipient['first_name'] . ' ' . $recipient['last_name']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= esc($recipient['email']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $emailStatusColors = ['pending' => 'bg-gray-100 text-gray-800', 'sent' => 'bg-green-100 text-green-800', 'failed' => 'bg-red-100 text-red-800', 'opened' => 'bg-blue-100 text-blue-800'];
                            $emailStatusClass = $emailStatusColors[$recipient['email_status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-2 py-1 text-xs font-medium rounded-full <?= $emailStatusClass ?>"><?= ucfirst($recipient['email_status']) ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $recipient['sent_at'] ? date('M d, Y H:i', strtotime($recipient['sent_at'])) : '-' ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= view('admin/templates/footer') ?>

