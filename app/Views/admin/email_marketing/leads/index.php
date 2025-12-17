<?= view('admin/templates/header') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <p class="text-gray-600 mt-1">Manage your email marketing contacts</p>
        </div>
        <a href="<?= site_url('admin/email-marketing/leads/add') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
            <i class="ri-add-line mr-2"></i>
            Add New Lead
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
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Total Leads</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($stats['total']) ?></p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-lg">
                                <i class="ri-group-line text-2xl text-blue-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">New Leads</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($stats['new']) ?></p>
                            </div>
                            <div class="bg-green-100 p-3 rounded-lg">
                                <i class="ri-user-add-line text-2xl text-green-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Qualified</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($stats['qualified']) ?></p>
                            </div>
                            <div class="bg-yellow-100 p-3 rounded-lg">
                                <i class="ri-star-line text-2xl text-yellow-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Subscribed</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($stats['subscribed']) ?></p>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-lg">
                                <i class="ri-mail-check-line text-2xl text-purple-600"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters and Search -->
                <div class="bg-white rounded-lg shadow p-4 mb-6">
                    <form method="get" action="<?= site_url('admin/email-marketing/leads') ?>" class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <input type="text"
                                   name="search"
                                   value="<?= esc($current_search ?? '') ?>"
                                   placeholder="Search by name, email, or company..."
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="min-w-[150px]">
                            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Statuses</option>
                                <option value="new" <?= ($current_status ?? '') === 'new' ? 'selected' : '' ?>>New</option>
                                <option value="contacted" <?= ($current_status ?? '') === 'contacted' ? 'selected' : '' ?>>Contacted</option>
                                <option value="qualified" <?= ($current_status ?? '') === 'qualified' ? 'selected' : '' ?>>Qualified</option>
                                <option value="converted" <?= ($current_status ?? '') === 'converted' ? 'selected' : '' ?>>Converted</option>
                                <option value="unsubscribed" <?= ($current_status ?? '') === 'unsubscribed' ? 'selected' : '' ?>>Unsubscribed</option>
                            </select>
                        </div>
                        <div class="min-w-[150px]">
                            <select name="subscribed" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Subscription</option>
                                <option value="1" <?= ($current_subscribed ?? '') === '1' ? 'selected' : '' ?>>Subscribed</option>
                                <option value="0" <?= ($current_subscribed ?? '') === '0' ? 'selected' : '' ?>>Unsubscribed</option>
                            </select>
                        </div>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                            <i class="ri-search-line mr-2"></i>Filter
                        </button>
                        <a href="<?= site_url('admin/email-marketing/leads') ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg">
                            Clear
                        </a>
                    </form>
                </div>

                <!-- Leads Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lead</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email Stats</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Added</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($leads)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        <i class="ri-inbox-line text-4xl mb-2"></i>
                                        <p>No leads found</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($leads as $lead): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="font-medium text-gray-900">
                                                    <?= esc($lead['first_name'] . ' ' . $lead['last_name']) ?>
                                                </div>
                                                <?php if ($lead['company_name']): ?>
                                                    <div class="text-sm text-gray-500"><?= esc($lead['company_name']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900"><?= esc($lead['email']) ?></div>
                                            <?php if ($lead['phone_number']): ?>
                                                <div class="text-sm text-gray-500"><?= esc($lead['phone_number']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $statusColors = [
                                                'new' => 'bg-blue-100 text-blue-800',
                                                'contacted' => 'bg-yellow-100 text-yellow-800',
                                                'qualified' => 'bg-green-100 text-green-800',
                                                'converted' => 'bg-purple-100 text-purple-800',
                                                'unsubscribed' => 'bg-red-100 text-red-800'
                                            ];
                                            $statusClass = $statusColors[$lead['lead_status']] ?? 'bg-gray-100 text-gray-800';
                                            ?>
                                            <span class="px-2 py-1 text-xs font-medium rounded-full <?= $statusClass ?>">
                                                <?= ucfirst(esc($lead['lead_status'])) ?>
                                            </span>
                                            <?php if (!$lead['is_subscribed']): ?>
                                                <span class="ml-1 px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                                    <i class="ri-mail-close-line"></i>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= esc($lead['lead_source'] ?: '-') ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex items-center space-x-2">
                                                <span title="Emails sent"><i class="ri-mail-send-line"></i> <?= $lead['email_sent_count'] ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= date('M d, Y', strtotime($lead['created_at'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <a href="<?= site_url('admin/email-marketing/leads/view/' . $lead['id']) ?>" class="text-blue-600 hover:text-blue-900" title="View">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <a href="<?= site_url('admin/email-marketing/leads/edit/' . $lead['id']) ?>" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                            <a href="<?= site_url('admin/email-marketing/leads/delete/' . $lead['id']) ?>" class="text-red-600 hover:text-red-900" title="Delete" onclick="return confirm('Are you sure you want to delete this lead?')">
                                                <i class="ri-delete-bin-line"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if ($pager): ?>
                        <div class="bg-white px-4 py-3 border-t border-gray-200 flex justify-center">
                            <?= $pager->links('default', 'tailwind_full') ?>
                        </div>
                    <?php endif; ?>
                </div>

<?= view('admin/templates/footer') ?>
