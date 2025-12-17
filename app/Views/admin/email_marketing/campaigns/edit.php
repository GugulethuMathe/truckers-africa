<?= view('admin/templates/header') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center space-x-4">
        <a href="<?= site_url('admin/email-marketing/campaigns') ?>" class="text-gray-600 hover:text-gray-900">
            <i class="ri-arrow-left-line text-2xl"></i>
        </a>
        <div>
            <p class="text-gray-600 mt-1">Edit campaign: <?= esc($campaign['campaign_name']) ?></p>
        </div>
    </div>
</div>

<!-- Error Messages -->
<?php if (session()->has('errors')): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
        <ul class="list-disc list-inside">
            <?php foreach (session('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Edit Campaign Form -->
<div class="bg-white rounded-lg shadow p-6">
    <form action="<?= site_url('admin/email-marketing/campaigns/update/' . $campaign['id']) ?>" method="post">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 gap-6">
            <!-- Campaign Name -->
            <div>
                <label for="campaign_name" class="block text-sm font-medium text-gray-700 mb-2">Campaign Name *</label>
                <input type="text"
                       id="campaign_name"
                       name="campaign_name"
                       value="<?= old('campaign_name', $campaign['campaign_name']) ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
            </div>

            <!-- Email Subject -->
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Email Subject *</label>
                <input type="text"
                       id="subject"
                       name="subject"
                       value="<?= old('subject', $campaign['subject']) ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
            </div>

            <!-- Email Content -->
            <div>
                <label for="email_content" class="block text-sm font-medium text-gray-700 mb-2">Email Content *</label>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                    <p class="text-sm font-medium text-blue-800 mb-2"><i class="ri-magic-line mr-1"></i> Available Placeholders:</p>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs">
                        <code class="bg-white px-2 py-1 rounded border cursor-pointer hover:bg-blue-100" onclick="insertPlaceholder('{first_name}')">{first_name}</code>
                        <code class="bg-white px-2 py-1 rounded border cursor-pointer hover:bg-blue-100" onclick="insertPlaceholder('{last_name}')">{last_name}</code>
                        <code class="bg-white px-2 py-1 rounded border cursor-pointer hover:bg-blue-100" onclick="insertPlaceholder('{full_name}')">{full_name}</code>
                        <code class="bg-white px-2 py-1 rounded border cursor-pointer hover:bg-blue-100" onclick="insertPlaceholder('{email}')">{email}</code>
                        <code class="bg-white px-2 py-1 rounded border cursor-pointer hover:bg-blue-100" onclick="insertPlaceholder('{company_name}')">{company_name}</code>
                        <code class="bg-white px-2 py-1 rounded border cursor-pointer hover:bg-blue-100" onclick="insertPlaceholder('{country}')">{country}</code>
                        <code class="bg-white px-2 py-1 rounded border cursor-pointer hover:bg-blue-100" onclick="insertPlaceholder('{city}')">{city}</code>
                        <code class="bg-white px-2 py-1 rounded border cursor-pointer hover:bg-blue-100" onclick="insertPlaceholder('{unsubscribe_link}')">{unsubscribe_link}</code>
                    </div>
                    <p class="text-xs text-blue-600 mt-2">Click a placeholder to insert it at cursor position</p>
                </div>
                <textarea id="email_content"
                          name="email_content"
                          rows="10"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                          required><?= old('email_content', $campaign['email_content']) ?></textarea>
            </div>

            <!-- Select Recipients -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Recipients</label>
                
                <?php if (empty($leads)): ?>
                    <div class="text-center py-4 text-gray-500 bg-gray-50 rounded-lg">
                        <p>No subscribed leads available</p>
                    </div>
                <?php else: ?>
                    <div class="mb-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="select_all" class="form-checkbox h-4 w-4 text-blue-600">
                            <span class="ml-2 text-sm text-gray-700">Select All (<?= count($leads) ?> leads)</span>
                        </label>
                    </div>
                    <div class="max-h-60 overflow-y-auto border border-gray-300 rounded-lg p-3">
                        <?php foreach ($leads as $lead): ?>
                            <label class="flex items-center py-2 border-b border-gray-100 last:border-b-0">
                                <input type="checkbox"
                                       name="leads[]"
                                       value="<?= $lead['id'] ?>"
                                       <?= in_array($lead['id'], $selected_leads) ? 'checked' : '' ?>
                                       class="lead-checkbox form-checkbox h-4 w-4 text-blue-600">
                                <span class="ml-3">
                                    <span class="font-medium text-gray-900"><?= esc($lead['first_name'] . ' ' . $lead['last_name']) ?></span>
                                    <span class="text-gray-500 text-sm ml-2"><?= esc($lead['email']) ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="<?= site_url('admin/email-marketing/campaigns') ?>" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center">
                    <i class="ri-save-line mr-2"></i>
                    Update Campaign
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('select_all')?.addEventListener('change', function() {
    document.querySelectorAll('.lead-checkbox').forEach(cb => cb.checked = this.checked);
});

function insertPlaceholder(placeholder) {
    const textarea = document.getElementById('email_content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    textarea.value = text.substring(0, start) + placeholder + text.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
    textarea.focus();
}
</script>

<?= view('admin/templates/footer') ?>

