<?= view('merchant/templates/header', ['page_title' => 'Document Verification']) ?>

<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Document Verification</h1>
                <p class="text-gray-600 mt-1">Upload your documents to get verified and unlock full platform access</p>
            </div>
            <a href="<?= site_url('merchant/dashboard') ?>" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                ← Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Business Type Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
            <div>
                <h3 class="text-sm font-medium text-blue-800">Business Type: <?= \App\Models\VerificationRequirementModel::getBusinessTypeDisplayName($businessType) ?></h3>
                <p class="text-sm text-blue-700">Based on your business type, you need to upload <?= count($requiredDocuments) ?> documents for verification.</p>
            </div>
        </div>
    </div>

    <!-- Progress Overview -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Verification Progress</h2>
        
        <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Overall Progress</span>
                <span class="text-sm text-gray-600"><?= $verificationProgress['uploaded'] ?>/<?= $verificationProgress['total_required'] ?> documents uploaded</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: <?= $verificationProgress['completion_percentage'] ?>%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-1"><?= $verificationProgress['completion_percentage'] ?>% complete</p>
        </div>

        <!-- Status Summary -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center p-3 bg-blue-50 rounded-lg">
                <div class="text-2xl font-bold text-blue-600"><?= $verificationProgress['uploaded'] ?></div>
                <div class="text-xs text-blue-700">Uploaded</div>
            </div>
            <div class="text-center p-3 bg-green-50 rounded-lg">
                <div class="text-2xl font-bold text-green-600"><?= $verificationProgress['approved'] ?></div>
                <div class="text-xs text-green-700">Approved</div>
            </div>
            <div class="text-center p-3 bg-yellow-50 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600"><?= $verificationProgress['pending'] ?></div>
                <div class="text-xs text-yellow-700">Pending Review</div>
            </div>
            <div class="text-center p-3 bg-red-50 rounded-lg">
                <div class="text-2xl font-bold text-red-600"><?= $verificationProgress['rejected'] ?></div>
                <div class="text-xs text-red-700">Rejected</div>
            </div>
        </div>
    </div>

    <!-- Document Upload Section -->
    <div class="space-y-6">
        <?php foreach ($verificationProgress['documents'] as $docType => $docInfo): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-start space-x-4">
                            <div class="text-3xl"><?= \App\Models\VerificationRequirementModel::getDocumentIcon($docType) ?></div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900"><?= esc($docInfo['display_name']) ?></h3>
                                <p class="text-sm text-gray-600 mt-1"><?= esc($docInfo['description']) ?></p>
                                <div class="mt-2">
                                    <span class="text-xs text-gray-500">Accepted formats: <?= esc($docInfo['accepted_formats']) ?></span>
                                    <span class="text-xs text-gray-500 ml-4">Max size: <?= \App\Models\VerificationRequirementModel::formatFileSize($docInfo['max_file_size']) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Document Status -->
                        <div class="text-right">
                            <?php if (isset($docInfo['is_verified'])): ?>
                                <?php
                                $statusClass = \App\Models\VerificationRequirementModel::getStatusColorClass($docInfo['is_verified']);
                                $statusText = ucfirst($docInfo['is_verified']);
                                $statusIcon = '';
                                switch ($docInfo['is_verified']) {
                                    case 'approved':
                                        $statusIcon = '✅';
                                        break;
                                    case 'pending':
                                        $statusIcon = '⏳';
                                        break;
                                    case 'rejected':
                                        $statusIcon = '❌';
                                        break;
                                }
                                ?>
                                <span class="<?= $statusClass ?> px-3 py-1 rounded-full text-sm font-medium">
                                    <?= $statusIcon ?> <?= $statusText ?>
                                </span>
                                
                                <?php if (isset($docInfo['upload_date'])): ?>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Uploaded: <?= date('M j, Y', strtotime($docInfo['upload_date'])) ?>
                                    </p>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-medium">
                                    📋 Required
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Current Document Info -->
                    <?php if (isset($docInfo['file_name'])): ?>
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Current Document:</p>
                                    <p class="text-sm text-gray-600"><?= esc($docInfo['file_name']) ?></p>
                                    <p class="text-xs text-gray-500">
                                        Size: <?= \App\Models\VerificationRequirementModel::formatFileSize($docInfo['file_size']) ?>
                                    </p>
                                </div>
                                <?php if (isset($docInfo['file_path'])): ?>
                                    <a href="<?= base_url($docInfo['file_path']) ?>" target="_blank" 
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        📄 View Document
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (isset($docInfo['rejection_reason']) && !empty($docInfo['rejection_reason'])): ?>
                                <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded">
                                    <p class="text-sm font-medium text-red-800">Rejection Reason:</p>
                                    <p class="text-sm text-red-700"><?= esc($docInfo['rejection_reason']) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Upload Form -->
                    <?php if (!isset($docInfo['is_verified']) || $docInfo['is_verified'] === 'rejected'): ?>
                        <form action="<?= site_url('merchant/upload-document') ?>" method="post" enctype="multipart/form-data" class="space-y-4">
                            <?= csrf_field() ?>
                            <input type="hidden" name="document_type" value="<?= $docType ?>">

                            <div>
                                <label for="document_file_<?= $docType ?>" class="block text-sm font-medium text-gray-700 mb-2">
                                    <?= isset($docInfo['file_name']) ? 'Replace Document' : 'Upload Document' ?>
                                </label>
                                <div class="flex items-center space-x-4">
                                    <input type="file"
                                           name="document_file"
                                           id="document_file_<?= $docType ?>"
                                           accept=".<?= str_replace(',', ',.', $docInfo['accepted_formats']) ?>"
                                           required
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    <button type="submit"
                                            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 font-medium">
                                        <?= isset($docInfo['file_name']) ? 'Replace' : 'Upload' ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php elseif ($docInfo['is_verified'] === 'pending'): ?>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <p class="text-sm text-yellow-800">
                                <strong>Under Review:</strong> Your document is being reviewed by our team. You'll be notified once it's approved or if any changes are needed.
                            </p>
                        </div>
                    <?php elseif ($docInfo['is_verified'] === 'approved'): ?>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <p class="text-sm text-green-800">
                                <strong>Approved:</strong> This document has been verified and approved. Thank you!
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Completion Status -->
    <?php if ($verificationProgress['completion_percentage'] === 100): ?>
        <div class="mt-8 bg-green-50 border border-green-200 rounded-lg p-6">
            <div class="flex items-center">
                <svg class="w-8 h-8 text-green-400 mr-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-medium text-green-800">All Documents Uploaded!</h3>
                    <p class="text-green-700">
                        <?php if ($merchant['is_verified'] === 'verified'): ?>
                            🎉 Congratulations! Your business has been verified and you now have full access to the platform.
                        <?php else: ?>
                            📋 All required documents have been submitted. Our team is reviewing your verification and will notify you once complete.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Help Section -->
    <div class="mt-8 bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Need Help?</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-medium text-gray-800 mb-2">Document Requirements</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Documents must be clear and readable</li>
                    <li>• All text and details must be visible</li>
                    <li>• Documents should not be older than 3 months (where applicable)</li>
                    <li>• File size should not exceed 5MB</li>
                </ul>
            </div>
            <div>
                <h4 class="font-medium text-gray-800 mb-2">Verification Process</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Upload all required documents</li>
                    <li>• Our team reviews within 1-3 business days</li>
                    <li>• You'll be notified of approval or required changes</li>
                    <li>• Once verified, you gain full platform access</li>
                </ul>
            </div>
        </div>
        
        <div class="mt-4 pt-4 border-t border-gray-200">
            <p class="text-sm text-gray-600">
                <strong>Having trouble?</strong> Contact our support team at 
                <a href="mailto:support@truckers-africa.com" class="text-blue-600 hover:text-blue-800">support@truckers-africa.com</a>
                or call us at <a href="tel:+27123456789" class="text-blue-600 hover:text-blue-800">+27 12 345 6789</a>
            </p>
        </div>
    </div>
</div>

<script>
// File upload validation
document.querySelectorAll('input[type="file"]').forEach(function(input) {
    input.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            // Check file size (5MB limit)
            if (file.size > 5242880) {
                alert('File size must be less than 5MB. Please choose a smaller file.');
                this.value = '';
                return;
            }
            
            // Show file name
            const fileName = file.name;
            const submitBtn = this.parentElement.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.textContent = 'Upload ' + fileName.substring(0, 20) + (fileName.length > 20 ? '...' : '');
            }
        }
    });
});

// Form submission loading state
document.querySelectorAll('form').forEach(function(form) {
    form.addEventListener('submit', function() {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Uploading...';
        }
    });
});
</script>

<?= view('merchant/templates/footer') ?>
