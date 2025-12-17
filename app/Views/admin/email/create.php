<?= view('admin/templates/header', ['page_title' => 'Create Email Campaign']) ?>

<style>
    .template-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .template-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .template-card.selected {
        border-color: #4F46E5;
        background-color: #EEF2FF;
    }
    .editor-toolbar button {
        transition: all 0.2s ease;
    }
    .editor-toolbar button:hover {
        background-color: #E5E7EB;
    }
    .editor-toolbar button.active {
        background-color: #4F46E5;
        color: white;
    }
    #visual-editor {
        min-height: 300px;
        outline: none;
    }
    #visual-editor:focus {
        border-color: #4F46E5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .email-preview {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 20px;
    }
    .email-preview-inner {
        background: white;
        border-radius: 8px;
        max-width: 600px;
        margin: 0 auto;
        overflow: hidden;
    }
    .header {
    background-color: #0e2140 !important;
    color: white;
    padding: 30px 20px;
    text-align: center;
    border-radius: 5px 5px 0 0;
}
</style>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Create New Campaign</h3>

    <?php if (session()->getFlashdata('errors')) : ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <ul class="mt-2 list-disc list-inside">
                <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Step 1: Choose Template -->
    <div id="step-template" class="mb-8">
        <h4 class="text-md font-semibold text-gray-600 mb-4 flex items-center">
            <span class="bg-indigo-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm mr-2">1</span>
            Choose a Template
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Scratch Template -->
            <div class="template-card border-2 border-gray-200 rounded-lg p-4" data-template="scratch" onclick="selectTemplate('scratch')">
                <div class="text-center mb-3">
                    <i class="fas fa-file-alt text-4xl text-gray-400"></i>
                </div>
                <h5 class="font-semibold text-gray-700 text-center">Start from Scratch</h5>
                <p class="text-xs text-gray-500 text-center mt-1">Plain text or custom HTML</p>
            </div>
            <!-- Professional Template -->
            <div class="template-card border-2 border-gray-200 rounded-lg p-4 selected" data-template="professional" onclick="selectTemplate('professional')">
                <div class="text-center mb-3">
                    <i class="fas fa-briefcase text-4xl text-indigo-500"></i>
                </div>
                <h5 class="font-semibold text-gray-700 text-center">Professional</h5>
                <p class="text-xs text-gray-500 text-center mt-1">Branded template with logo</p>
            </div>
            <!-- Promotional Template -->
            <div class="template-card border-2 border-gray-200 rounded-lg p-4" data-template="promotional" onclick="selectTemplate('promotional')">
                <div class="text-center mb-3">
                    <i class="fas fa-bullhorn text-4xl text-orange-500"></i>
                </div>
                <h5 class="font-semibold text-gray-700 text-center">Promotional</h5>
                <p class="text-xs text-gray-500 text-center mt-1">Eye-catching marketing email</p>
            </div>
        </div>
    </div>

    <form action="<?= site_url('admin/email-marketing/store') ?>" method="post" id="campaign-form">
        <?= csrf_field() ?>
        <input type="hidden" name="template_type" id="template_type" value="professional">

        <!-- Step 2: Campaign Details -->
        <div class="mb-6">
            <h4 class="text-md font-semibold text-gray-600 mb-4 flex items-center">
                <span class="bg-indigo-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm mr-2">2</span>
                Campaign Details
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700">Email Subject</label>
                    <input type="text" id="subject" name="subject" value="<?= old('subject') ?>"
                        placeholder="e.g., Exciting News from Truckers Africa!"
                        class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="target_group" class="block text-sm font-medium text-gray-700">Target Audience</label>
                    <select id="target_group" name="target_group" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        <option value="" disabled selected>Select audience...</option>
                        <option value="all_merchants" <?= old('target_group') == 'all_merchants' ? 'selected' : '' ?>>All Merchants</option>
                        <option value="all_drivers" <?= old('target_group') == 'all_drivers' ? 'selected' : '' ?>>All Drivers</option>
                        <option value="all_users" <?= old('target_group') == 'all_users' ? 'selected' : '' ?>>All Users</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Step 3: Email Content -->
        <div class="mb-6">
            <h4 class="text-md font-semibold text-gray-600 mb-4 flex items-center">
                <span class="bg-indigo-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm mr-2">3</span>
                Email Content
            </h4>

            <!-- Visual Editor (for Professional/Promotional templates) -->
            <div id="visual-editor-section">
                <!-- Headline -->
                <div class="mb-4">
                    <label for="headline" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-heading mr-1"></i> Headline
                    </label>
                    <input type="text" id="headline" name="headline" value="<?= old('headline', 'Welcome to Truckers Africa!') ?>"
                        placeholder="Main headline for your email"
                        class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <!-- Main Content with Toolbar -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-align-left mr-1"></i> Message Content
                    </label>

                    <!-- Simple Formatting Toolbar -->
                    <div class="editor-toolbar flex flex-wrap gap-1 p-2 bg-gray-100 border border-gray-300 border-b-0 rounded-t-md">
                        <button type="button" onclick="formatText('bold')" title="Bold" class="px-3 py-1 rounded text-sm font-bold">
                            <i class="fas fa-bold"></i>
                        </button>
                        <button type="button" onclick="formatText('italic')" title="Italic" class="px-3 py-1 rounded text-sm italic">
                            <i class="fas fa-italic"></i>
                        </button>
                        <button type="button" onclick="formatText('underline')" title="Underline" class="px-3 py-1 rounded text-sm underline">
                            <i class="fas fa-underline"></i>
                        </button>
                        <span class="border-l border-gray-300 mx-1"></span>
                        <button type="button" onclick="formatText('insertUnorderedList')" title="Bullet List" class="px-3 py-1 rounded text-sm">
                            <i class="fas fa-list-ul"></i>
                        </button>
                        <button type="button" onclick="formatText('insertOrderedList')" title="Numbered List" class="px-3 py-1 rounded text-sm">
                            <i class="fas fa-list-ol"></i>
                        </button>
                        <span class="border-l border-gray-300 mx-1"></span>
                        <button type="button" onclick="insertLink()" title="Insert Link" class="px-3 py-1 rounded text-sm">
                            <i class="fas fa-link"></i>
                        </button>
                        <span class="border-l border-gray-300 mx-1"></span>
                        <select onchange="formatText('fontSize', this.value)" class="px-2 py-1 rounded text-sm border-0 bg-transparent">
                            <option value="">Size</option>
                            <option value="1">Small</option>
                            <option value="3">Normal</option>
                            <option value="5">Large</option>
                            <option value="7">Huge</option>
                        </select>
                    </div>

                    <!-- Content Editable Area -->
                    <div id="visual-editor" contenteditable="true"
                        class="w-full px-3 py-3 bg-white border border-gray-300 rounded-b-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        style="min-height: 200px;"><?= old('content', 'Type your message here...') ?></div>
                    <input type="hidden" name="content" id="content">
                </div>

                <!-- Call to Action Button -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="cta_text" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-mouse-pointer mr-1"></i> Button Text
                        </label>
                        <input type="text" id="cta_text" name="cta_text" value="<?= old('cta_text', 'Visit Truckers Africa') ?>"
                            placeholder="e.g., Learn More, Sign Up Now"
                            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="cta_url" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-external-link-alt mr-1"></i> Button Link
                        </label>
                        <input type="url" id="cta_url" name="cta_url" value="<?= old('cta_url', 'https://truckersafrica.com/signup/merchant') ?>"
                            placeholder="https://truckersafrica.com/..."
                            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>
            </div>

            <!-- Raw HTML Editor (for Scratch template) -->
            <div id="raw-editor-section" class="hidden">
                <div class="mb-4">
                    <label for="raw_body" class="block text-sm font-medium text-gray-700">Email Body (HTML)</label>
                    <textarea id="raw_body" name="raw_body" rows="15"
                        class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm font-mono text-sm"
                        placeholder="Enter your HTML email content here..."><?= old('raw_body') ?></textarea>
                    <p class="mt-2 text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i> You can use HTML for formatting. Plain text will also work.
                    </p>
                </div>
            </div>

            <!-- Hidden field for final body -->
            <textarea name="body" id="body" class="hidden"><?= old('body') ?></textarea>
        </div>

        <!-- Live Preview -->
        <div class="mb-6">
            <h4 class="text-md font-semibold text-gray-600 mb-4 flex items-center">
                <span class="bg-indigo-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm mr-2">4</span>
                Preview
                <button type="button" onclick="updatePreview()" class="ml-auto text-sm text-indigo-600 hover:text-indigo-800">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh Preview
                </button>
            </h4>
            <div class="border border-gray-300 rounded-lg overflow-hidden">
                <div class="bg-gray-100 px-4 py-2 border-b flex items-center">
                    <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                    <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>
                    <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                    <span class="text-sm text-gray-500 ml-2">Email Preview</span>
                </div>
                <div id="email-preview" class="email-preview">
                    <!-- Preview will be generated here -->
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4">
            <a href="<?= site_url('admin/email-marketing') ?>" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300 transition">
                <i class="fas fa-times mr-1"></i> Cancel
            </a>
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                <i class="fas fa-save mr-1"></i> Save as Draft
            </button>
        </div>
    </form>
</div>

<script>
const LOGO_URL = 'https://truckersafrica.com/assets/images/logo-icon.png';
const DEFAULT_CTA_URL = 'https://truckersafrica.com/signup/merchant';

let selectedTemplate = 'professional';

function selectTemplate(template) {
    selectedTemplate = template;
    document.getElementById('template_type').value = template;

    // Update UI
    document.querySelectorAll('.template-card').forEach(card => {
        card.classList.remove('selected');
    });
    document.querySelector(`[data-template="${template}"]`).classList.add('selected');

    // Show/hide editors
    if (template === 'scratch') {
        document.getElementById('visual-editor-section').classList.add('hidden');
        document.getElementById('raw-editor-section').classList.remove('hidden');
    } else {
        document.getElementById('visual-editor-section').classList.remove('hidden');
        document.getElementById('raw-editor-section').classList.add('hidden');
    }

    updatePreview();
}

function formatText(command, value = null) {
    document.execCommand(command, false, value);
    document.getElementById('visual-editor').focus();
}

function insertLink() {
    const url = prompt('Enter URL:', 'https://');
    if (url) {
        document.execCommand('createLink', false, url);
    }
}

function getEditorContent() {
    return document.getElementById('visual-editor').innerHTML;
}

function generateEmailHTML() {
    const template = selectedTemplate;
    const headline = document.getElementById('headline')?.value || '';
    const content = getEditorContent();
    const ctaText = document.getElementById('cta_text')?.value || 'Visit Truckers Africa';
    const ctaUrl = document.getElementById('cta_url')?.value || DEFAULT_CTA_URL;

    if (template === 'scratch') {
        return document.getElementById('raw_body')?.value || '';
    }

    const primaryColor = template === 'promotional' ? '#F97316' : '#0e2140';
    const bgColor = template === 'promotional' ? '#FFF7ED' : '#F3F4F6';

    return `<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${headline}</title>
    <style>
        .header { background-color: #0e2140 !important; color: white; padding: 30px 20px; text-align: center; border-radius: 5px 5px 0 0; }
    </style>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: ${bgColor};">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: ${bgColor};">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <!-- Header with Logo and Truckers Africa -->
                    <tr>
                        <td class="header" style="background-color: #0e2140 !important; padding: 30px; text-align: center; border-radius: 5px 5px 0 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 0 auto 15px;">
                                <tr>
                                    <td style="vertical-align: middle; padding-right: 12px;">
                                        <img src="${LOGO_URL}" alt="Truckers Africa Logo" width="50" height="50" style="display: block;">
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <span style="color: #ffffff; font-size: 24px; font-weight: 700;">Truckers Africa</span>
                                    </td>
                                </tr>
                            </table>
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 700;">${headline}</h1>
                        </td>
                    </tr>
                    <!-- Main Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <div style="color: #374151; font-size: 16px; line-height: 1.6;">
                                ${content}
                            </div>
                            ${ctaText ? `
                            <!-- CTA Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top: 30px;">
                                <tr>
                                    <td align="center">
                                        <a href="${ctaUrl}" style="display: inline-block; background-color: ${primaryColor}; color: #ffffff; text-decoration: none; padding: 14px 35px; border-radius: 8px; font-weight: 600; font-size: 16px;">${ctaText}</a>
                                    </td>
                                </tr>
                            </table>
                            ` : ''}
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #1F2937; padding: 30px; text-align: center;">
                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 0 auto 15px;">
                                <tr>
                                    <td style="vertical-align: middle; padding-right: 10px;">
                                        <img src="${LOGO_URL}" alt="Truckers Africa Logo" width="40" height="40" style="display: block;">
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <span style="color: #ffffff; font-size: 18px; font-weight: 600;">Truckers Africa</span>
                                    </td>
                                </tr>
                            </table>
                            <p style="color: #9CA3AF; margin: 0 0 10px; font-size: 14px;">Connecting Truckers Across Africa</p>
                            <p style="margin: 0;">
                                <a href="https://truckersafrica.com/signup/merchant" style="color: #60A5FA; text-decoration: none; font-size: 14px;">Visit Truckers Africa</a>
                            </p>
                            <p style="color: #6B7280; margin: 20px 0 0; font-size: 12px;">
                                © ${new Date().getFullYear()} Truckers Africa. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>`;
}

function updatePreview() {
    const html = generateEmailHTML();
    const previewContainer = document.getElementById('email-preview');

    if (selectedTemplate === 'scratch') {
        previewContainer.innerHTML = `<div class="email-preview-inner p-4">${html || '<p class="text-gray-400 text-center py-8">Enter content to see preview</p>'}</div>`;
    } else {
        // Create an iframe for safe HTML preview
        previewContainer.innerHTML = `<iframe id="preview-iframe" style="width: 100%; min-height: 500px; border: none; background: white;" sandbox="allow-same-origin"></iframe>`;
        const iframe = document.getElementById('preview-iframe');
        iframe.srcdoc = html;
    }

    // Update hidden body field
    document.getElementById('body').value = html;
    document.getElementById('content').value = getEditorContent();
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Set up auto-update on content changes
    const visualEditor = document.getElementById('visual-editor');
    if (visualEditor) {
        visualEditor.addEventListener('input', debounce(updatePreview, 500));
        visualEditor.addEventListener('focus', function() {
            if (this.innerHTML === 'Type your message here...') {
                this.innerHTML = '';
            }
        });
    }

    document.getElementById('headline')?.addEventListener('input', debounce(updatePreview, 500));
    document.getElementById('cta_text')?.addEventListener('input', debounce(updatePreview, 500));
    document.getElementById('cta_url')?.addEventListener('input', debounce(updatePreview, 500));
    document.getElementById('raw_body')?.addEventListener('input', debounce(updatePreview, 500));

    // Initial preview
    updatePreview();
});

// Form submission handler
document.getElementById('campaign-form').addEventListener('submit', function(e) {
    // Ensure body is updated before submission
    document.getElementById('body').value = generateEmailHTML();
});

// Debounce helper
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>

<?= view('admin/templates/footer') ?>
