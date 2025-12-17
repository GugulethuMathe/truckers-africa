<?= view('admin/templates/header') ?>
<style>
    .header {
    background-color: #0e2140 !important;
    color: white;
    padding: 30px 20px;
    text-align: center;
    border-radius: 5px 5px 0 0;
}
</style>
<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center space-x-4">
        <a href="<?= site_url('admin/email-marketing/campaigns') ?>" class="text-gray-600 hover:text-gray-900">
            <i class="ri-arrow-left-line text-2xl"></i>
        </a>
        <div>
            <p class="text-gray-600 mt-1">Create a new email marketing campaign</p>
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

<?php if (session()->has('error')): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
        <?= session('error') ?>
    </div>
<?php endif; ?>

<!-- Create Campaign Form -->
<div class="bg-white rounded-lg shadow p-6">
    <form action="<?= site_url('admin/email-marketing/campaigns/store') ?>" method="post">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 gap-6">
            <!-- Campaign Name -->
            <div>
                <label for="campaign_name" class="block text-sm font-medium text-gray-700 mb-2">Campaign Name *</label>
                <input type="text"
                       id="campaign_name"
                       name="campaign_name"
                       value="<?= old('campaign_name') ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Enter campaign name"
                       required>
            </div>

            <!-- Email Subject -->
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Email Subject *</label>
                <input type="text"
                       id="subject"
                       name="subject"
                       value="<?= old('subject') ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Enter email subject line"
                       required>
            </div>

            <!-- Email Template Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Template</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 transition template-option" onclick="selectTemplate('blank')">
                        <div class="text-center">
                            <i class="ri-file-text-line text-3xl text-gray-400"></i>
                            <p class="font-medium mt-2">Blank</p>
                            <p class="text-xs text-gray-500">Start from scratch</p>
                        </div>
                    </div>
                    <div class="border-2 border-blue-500 rounded-lg p-4 cursor-pointer hover:border-blue-600 transition template-option selected" onclick="selectTemplate('professional')">
                        <div class="text-center">
                            <i class="ri-layout-4-line text-3xl text-blue-600"></i>
                            <p class="font-medium mt-2">Professional</p>
                            <p class="text-xs text-gray-500">Branded HTML template</p>
                        </div>
                    </div>
                    <div class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 transition template-option" onclick="selectTemplate('simple')">
                        <div class="text-center">
                            <i class="ri-mail-line text-3xl text-gray-400"></i>
                            <p class="font-medium mt-2">Simple</p>
                            <p class="text-xs text-gray-500">Plain text style</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Content -->
            <div>
                <label for="email_content" class="block text-sm font-medium text-gray-700 mb-2">Email Content (HTML) *</label>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                    <p class="text-sm font-medium text-blue-800 mb-2"><i class="ri-magic-line mr-1"></i> Available Placeholders:</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                        <code class="bg-white px-2 py-1 rounded border cursor-pointer hover:bg-blue-100" onclick="insertPlaceholder('{first_name}')">{first_name}</code>
                        <code class="bg-white px-2 py-1 rounded border cursor-pointer hover:bg-blue-100" onclick="insertPlaceholder('{last_name}')">{last_name}</code>
                        <code class="bg-white px-2 py-1 rounded border cursor-pointer hover:bg-blue-100" onclick="insertPlaceholder('{full_name}')">{full_name}</code>
                        <code class="bg-white px-2 py-1 rounded border cursor-pointer hover:bg-blue-100" onclick="insertPlaceholder('{email}')">{email}</code>
                        <code class="bg-white px-2 py-1 rounded border cursor-pointer hover:bg-blue-100" onclick="insertPlaceholder('{company_name}')">{company_name}</code>
                        <code class="bg-white px-2 py-1 rounded border cursor-pointer hover:bg-blue-100" onclick="insertPlaceholder('{country}')">{country}</code>
                        <code class="bg-white px-2 py-1 rounded border cursor-pointer hover:bg-blue-100" onclick="insertPlaceholder('{city}')">{city}</code>
                        <code class="bg-white px-2 py-1 rounded border cursor-pointer hover:bg-blue-100" onclick="insertPlaceholder('{register_link}')">{register_link}</code>
                        <code class="bg-white px-2 py-1 rounded border cursor-pointer hover:bg-blue-100" onclick="insertPlaceholder('{unsubscribe_link}')">{unsubscribe_link}</code>
                    </div>
                    <p class="text-xs text-blue-600 mt-2">Click a placeholder to insert it at cursor position</p>
                </div>
                <textarea id="email_content"
                          name="email_content"
                          rows="20"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                          required><?= old('email_content') ?></textarea>
                <div class="mt-2 flex justify-end">
                    <button type="button" onclick="previewEmail()" class="text-sm text-blue-600 hover:text-blue-800">
                        <i class="ri-eye-line mr-1"></i> Preview Email
                    </button>
                </div>
            </div>

            <!-- Select Recipients -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Recipients</label>
                <p class="text-xs text-gray-500 mb-2">Only subscribed leads are shown</p>
                
                <?php if (empty($leads)): ?>
                    <div class="text-center py-4 text-gray-500 bg-gray-50 rounded-lg">
                        <i class="ri-user-unfollow-line text-2xl mb-2"></i>
                        <p>No subscribed leads available</p>
                        <a href="<?= site_url('admin/email-marketing/leads/add') ?>" class="text-blue-600 hover:underline text-sm">Add leads first</a>
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
                                <input type="checkbox" name="leads[]" value="<?= $lead['id'] ?>" class="lead-checkbox form-checkbox h-4 w-4 text-blue-600">
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
                    Create Campaign
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Email Preview Modal -->
<div id="previewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-hidden">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold">Email Preview</h3>
            <button onclick="closePreview()" class="text-gray-500 hover:text-gray-700">
                <i class="ri-close-line text-xl"></i>
            </button>
        </div>
        <div class="overflow-auto max-h-[calc(90vh-80px)]">
            <iframe id="previewFrame" class="w-full h-[600px] border-0"></iframe>
        </div>
    </div>
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

// Email Templates
const templates = {
    blank: '',
    professional: `<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email from Truckers Africa</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #0e2140 !important; color: white; padding: 30px 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .header img { width: 60px; height: 60px; vertical-align: middle; }
        .header h1 { margin: 0; font-size: 24px; display: inline-block; vertical-align: middle; }
        .header .logo-title { display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 10px; }
        .header p { margin: 10px 0 0 0; }
        .content { background-color: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
        .welcome-box { background-color: #dbeafe; border-left: 4px solid #0e2140; padding: 20px; margin: 20px 0; border-radius: 3px; }
        .welcome-box h2 { margin-top: 0; color: #1e40af; }
        .next-steps { background-color: white; padding: 20px; margin: 20px 0; border-radius: 5px; border: 1px solid #e0e0e0; }
        .next-steps h3 { color: #0e2140; margin-top: 0; }
        .button { display: inline-block; padding: 12px 30px; background-color: #0e2140; color: white !important; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 0.9em; background-color: #f3f4f6; border-radius: 0 0 5px 5px; }
        .footer img { width: 40px; height: 40px; vertical-align: middle; }
        ul { margin: 10px 0; padding-left: 20px; }
        li { padding: 5px 0; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-title">
            <img src="https://truckersafrica.com/assets/images/logo-icon.png" alt="Truckers Africa Logo">
            <h1>Truckers Africa</h1>
        </div>
        <p>Your Trusted Logistics Partner</p>
    </div>

    <div class="content">
        <div class="welcome-box">
            <h2>Hello {first_name}!</h2>
            <p style="margin-bottom: 0;">We hope this message finds you well! We wanted to reach out with some exciting news.</p>
        </div>

        <p>Replace this section with your main content. You can highlight important information, special offers, or announcements.</p>

        <div class="next-steps">
            <h3>What You Can Do:</h3>
            <ul>
                <li>First action item - describe what you want the reader to do</li>
                <li>Second action item - add another call to action</li>
                <li>Third action item - include any additional information</li>
            </ul>
        </div>

        <center>
            <a href="https://truckersafrica.com/" class="button">Visit Truckers Africa</a>
        </center>

        <p>If you have any questions, our support team is here to help!</p>

        <p>Best regards,<br><strong>Truckers Africa Team</strong></p>
    </div>

    <div class="footer">
        <img src="https://truckersafrica.com/assets/images/logo-icon.png" alt="Truckers Africa">
        <p style="margin: 0 0 10px 0;"><a href="https://truckersafrica.com/" style="color: #000f25; text-decoration: none; font-weight: bold;">Visit Truckers Africa</a></p>
        <p style="margin: 0;">© <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
        <p style="margin: 10px 0 0 0;">{unsubscribe_link}</p>
    </div>
</body>
</html>`,
    simple: `Hi {first_name},

Thank you for being part of the Truckers Africa community!

We wanted to reach out to share some important updates with you.

[Your message here]

Best regards,
The Truckers Africa Team

---
{unsubscribe_link}`
};

// Select template
function selectTemplate(templateName) {
    document.querySelectorAll('.template-option').forEach(el => {
        el.classList.remove('selected', 'border-blue-500');
        el.classList.add('border-gray-200');
    });
    event.currentTarget.classList.add('selected', 'border-blue-500');
    event.currentTarget.classList.remove('border-gray-200');

    const textarea = document.getElementById('email_content');
    if (textarea.value.trim() === '' || confirm('Replace current content with template?')) {
        textarea.value = templates[templateName];
    }
}

// Preview email
function previewEmail() {
    const content = document.getElementById('email_content').value;
    const previewContent = content
        .replace(/{first_name}/g, 'John')
        .replace(/{last_name}/g, 'Smith')
        .replace(/{full_name}/g, 'John Smith')
        .replace(/{email}/g, 'john@example.com')
        .replace(/{company_name}/g, 'ABC Logistics')
        .replace(/{country}/g, 'South Africa')
        .replace(/{city}/g, 'Johannesburg')
        .replace(/{register_link}/g, '<a href="<?= site_url('register') ?>" style="color: #000f25; font-weight: bold;">Register Now</a>')
        .replace(/{unsubscribe_link}/g, '<a href="#">Unsubscribe</a>');

    const iframe = document.getElementById('previewFrame');
    iframe.srcdoc = previewContent;
    document.getElementById('previewModal').classList.remove('hidden');
}

function closePreview() {
    document.getElementById('previewModal').classList.add('hidden');
}

// Close modal on outside click
document.getElementById('previewModal')?.addEventListener('click', function(e) {
    if (e.target === this) closePreview();
});

// Load professional template by default on page load
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('email_content');
    if (!textarea.value.trim()) {
        textarea.value = templates.professional;
    }
});
</script>

<?= view('admin/templates/footer') ?>

