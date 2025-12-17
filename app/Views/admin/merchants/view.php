<?= view('admin/templates/header', ['page_title' => 'Merchant Details - ' . $merchant['business_name']]) ?>

<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
            <a href="<?= site_url('admin/merchants/all') ?>" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= esc($merchant['business_name']) ?></h1>
                <p class="text-gray-600">Merchant ID: <?= esc($merchant['id']) ?></p>
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="<?= site_url('admin/merchants/all') ?>" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                ← Back to All Merchants
            </a>
            <a href="#" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                📝 Quick Edit
            </a>
            <a href="mailto:<?= esc($merchant['email']) ?>" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">
                📧 Contact
            </a>
        </div>
    </div>

    <!-- Verification Status Alert -->
    <?php if (isset($verificationProgress) && $verificationProgress['approval_percentage'] === 100 && $merchant['is_verified'] !== 'verified'): ?>
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">
                            🎉 Ready for Final Verification!
                        </h3>
                        <div class="mt-2 text-sm text-green-700">
                            <p>All required documents have been approved. This merchant is ready to be verified and granted full platform access.</p>
                        </div>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <a href="<?= site_url('admin/merchants/verify/' . $merchant['id']) ?>"
                       class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 font-semibold"
                       onclick="return confirm('Mark <?= esc($merchant['business_name']) ?> as VERIFIED?\n\nThis action will:\n• Grant full platform access\n• Mark merchant as verified\n• Cannot be undone\n\nConfirm verification?')">
                        ✅ VERIFY NOW
                    </a>
                </div>
            </div>
        </div>
    <?php elseif (isset($verificationProgress) && $verificationProgress['uploaded'] > 0 && $verificationProgress['approval_percentage'] < 100): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        📋 Documents Awaiting Review
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>
                            <?= $verificationProgress['approved'] ?>/<?= $verificationProgress['total_required'] ?> documents approved.
                            <?= $verificationProgress['pending'] ?> pending review,
                            <?= $verificationProgress['rejected'] ?> rejected.
                            Review documents in the verification section below.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif (isset($verificationProgress) && $verificationProgress['uploaded'] === 0): ?>
        <div class="bg-gray-50 border-l-4 border-gray-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-gray-800">
                        📄 Awaiting Document Upload
                    </h3>
                    <div class="mt-2 text-sm text-gray-700">
                        <p>This merchant has not uploaded any verification documents yet. They need to submit <?= count($requiredDocuments) ?> documents for review.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Status and Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Status</p>
                    <?php
                    $statusClass = '';
                    switch ($merchant['verification_status']) {
                        case 'approved':
                            $statusClass = 'text-green-600';
                            break;
                        case 'pending':
                            $statusClass = 'text-yellow-600';
                            break;
                        case 'rejected':
                            $statusClass = 'text-red-600';
                            break;
                        default:
                            $statusClass = 'text-gray-600';
                    }
                    ?>
                    <p class="text-lg font-semibold <?= $statusClass ?>"><?= esc(ucfirst($merchant['verification_status'])) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Services</p>
                    <p class="text-lg font-semibold text-gray-900"><?= $stats['total_services'] ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Account Age</p>
                    <p class="text-lg font-semibold text-gray-900"><?= $stats['account_age_days'] ?> days</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Profile Complete</p>
                    <p class="text-lg font-semibold text-gray-900"><?= $stats['profile_completion'] ?>%</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-2 bg-indigo-100 rounded-lg">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Documents Verified</p>
                    <p class="text-lg font-semibold text-gray-900"><?= $stats['verification_completion'] ?>%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column - Main Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Business Information -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Business Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-center space-x-4">
                        <?php if (!empty($merchant['business_image_url'])): ?>
                            <img src="<?= esc($merchant['business_image_url']) ?>" alt="Business" class="w-20 h-20 rounded-lg object-cover">
                        <?php else: ?>
                            <div class="w-20 h-20 bg-gray-300 rounded-lg flex items-center justify-center">
                                <span class="text-2xl font-bold text-gray-600"><?= strtoupper(substr($merchant['business_name'], 0, 2)) ?></span>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900"><?= esc($merchant['business_name']) ?></h3>
                            <?php if (!empty($merchant['main_service'])): ?>
                                <p class="text-gray-600"><?= esc($merchant['main_service']) ?></p>
                            <?php endif; ?>
                            <div class="flex items-center mt-2">
                                <?php if ($merchant['is_visible']): ?>
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Visible</span>
                                <?php else: ?>
                                    <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">Hidden</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Registration Date</label>
                            <p class="text-gray-900"><?= date('F j, Y \a\t g:i A', strtotime($merchant['created_at'])) ?></p>
                        </div>
                        <?php if (!empty($merchant['last_login'])): ?>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Last Login</label>
                                <p class="text-gray-900"><?= date('F j, Y \a\t g:i A', strtotime($merchant['last_login'])) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($merchant['business_description'])): ?>
                    <div class="mt-6">
                        <label class="text-sm font-medium text-gray-600">Business Description</label>
                        <p class="text-gray-900 mt-1 leading-relaxed"><?= esc($merchant['business_description']) ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Owner Information -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Owner Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-center space-x-4">
                        <?php if (!empty($merchant['profile_image_url'])): ?>
                            <img src="<?= esc($merchant['profile_image_url']) ?>" alt="Profile" class="w-16 h-16 rounded-full object-cover">
                        <?php else: ?>
                            <div class="w-16 h-16 bg-gray-300 rounded-full flex items-center justify-center">
                                <span class="text-lg font-semibold text-gray-600"><?= strtoupper(substr($merchant['owner_name'], 0, 1)) ?></span>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900"><?= esc($merchant['owner_name']) ?></h3>
                            <p class="text-gray-600"><?= esc($merchant['email']) ?></p>
                            <?php if (!empty($merchant['google_id'])): ?>
                                <div class="flex items-center mt-1">
                                    <svg class="w-4 h-4 mr-1 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    </svg>
                                    <span class="text-xs text-blue-600">Google Account</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <?php if (!empty($merchant['business_contact_number'])): ?>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Business Phone</label>
                                <p class="text-gray-900"><?= esc($merchant['business_contact_number']) ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($merchant['business_whatsapp_number']) && $merchant['business_whatsapp_number'] != $merchant['business_contact_number']): ?>
                            <div>
                                <label class="text-sm font-medium text-gray-600">WhatsApp</label>
                                <p class="text-gray-900"><?= esc($merchant['business_whatsapp_number']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($merchant['profile_description'])): ?>
                    <div class="mt-6">
                        <label class="text-sm font-medium text-gray-600">Profile Description</label>
                        <p class="text-gray-900 mt-1 leading-relaxed"><?= esc($merchant['profile_description']) ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Location Information -->
            <?php if (!empty($merchant['physical_address'])): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Location Information</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Physical Address</label>
                            <p class="text-gray-900 mt-1"><?= esc($merchant['physical_address']) ?></p>
                        </div>
                        <?php if (!empty($merchant['latitude']) && !empty($merchant['longitude'])): ?>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-600">Latitude</label>
                                    <p class="text-gray-900 font-mono text-sm"><?= esc($merchant['latitude']) ?></p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600">Longitude</label>
                                    <p class="text-gray-900 font-mono text-sm"><?= esc($merchant['longitude']) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Services -->
            <?php if (!empty($merchant['services'])): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Services Offered</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($merchant['services'] as $service): ?>
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <div class="p-2 bg-blue-100 rounded-lg mr-3">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900"><?= esc($service['name']) ?></p>
                                    <?php if (!empty($service['category_name'])): ?>
                                        <p class="text-sm text-gray-600"><?= esc($service['category_name']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Document Verification -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Document Verification</h2>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Business Type:</span>
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                            <?= \App\Models\VerificationRequirementModel::getBusinessTypeDisplayName($businessType) ?>
                        </span>
                    </div>
                </div>

                <!-- Verification Progress -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Verification Progress</span>
                        <span class="text-sm text-gray-600"><?= $verificationProgress['approved'] ?>/<?= $verificationProgress['total_required'] ?> approved</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: <?= $verificationProgress['approval_percentage'] ?>%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1"><?= $verificationProgress['approval_percentage'] ?>% documents approved</p>
                </div>

                <!-- Document Status -->
                <div class="space-y-4">
                    <?php foreach ($verificationProgress['documents'] as $docType => $docInfo): ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start space-x-3">
                                    <div class="text-2xl">
                                        <?= \App\Models\VerificationRequirementModel::getDocumentIcon($docType) ?>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900"><?= esc($docInfo['display_name']) ?></h4>
                                        <p class="text-sm text-gray-600 mt-1"><?= esc($docInfo['description']) ?></p>

                                        <?php if (isset($docInfo['file_name'])): ?>
                                            <div class="mt-2">
                                                <p class="text-xs text-gray-500">
                                                    Uploaded: <?= date('M j, Y \a\t g:i A', strtotime($docInfo['upload_date'])) ?>
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    File: <?= esc($docInfo['file_name']) ?>
                                                    (<?= \App\Models\VerificationRequirementModel::formatFileSize($docInfo['file_size']) ?>)
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="flex flex-col items-end space-y-2">
                                    <?php if (isset($docInfo['is_verified'])): ?>
                                        <span class="<?= \App\Models\VerificationRequirementModel::getStatusColorClass($docInfo['is_verified']) ?> px-2 py-1 rounded-full text-xs font-medium">
                                            <?= esc(ucfirst($docInfo['is_verified'])) ?>
                                        </span>

                                        <?php if ($docInfo['is_verified'] === 'pending'): ?>
                                            <div class="flex space-x-1">
                                                <form method="POST" action="<?= site_url('admin/documents/approve/' . $docInfo['id']) ?>" class="inline">
                                                    <button type="submit"
                                                            class="bg-green-600 text-white px-2 py-1 rounded text-xs hover:bg-green-700"
                                                            onclick="return confirm('Approve this document?')">
                                                        ✓ Approve
                                                    </button>
                                                </form>
                                                <button type="button"
                                                        class="bg-red-600 text-white px-2 py-1 rounded text-xs hover:bg-red-700"
                                                        onclick="showRejectModal(<?= $docInfo['id'] ?>, '<?= esc($docInfo['display_name']) ?>')">
                                                    ✗ Reject
                                                </button>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (isset($docInfo['file_path'])): ?>
                                            <a href="<?= base_url($docInfo['file_path']) ?>"
                                               target="_blank"
                                               class="text-blue-600 hover:text-blue-800 text-xs">
                                                📄 View Document
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="<?= \App\Models\VerificationRequirementModel::getStatusColorClass('missing') ?> px-2 py-1 rounded-full text-xs font-medium">
                                            Not Uploaded
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if (isset($docInfo['rejection_reason']) && !empty($docInfo['rejection_reason'])): ?>
                                <div class="mt-3 p-2 bg-red-50 border border-red-200 rounded">
                                    <p class="text-xs text-red-700">
                                        <strong>Rejection Reason:</strong> <?= esc($docInfo['rejection_reason']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Overall Verification Status -->
                <?php if ($verificationProgress['approval_percentage'] === 100): ?>
                    <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-medium text-green-900">Ready for Verification</h4>
                                <p class="text-sm text-green-700">All required documents have been approved.</p>
                            </div>
                            <?php if ($merchant['is_verified'] !== 'verified'): ?>
                                <a href="<?= site_url('admin/merchants/verify/' . $merchant['id']) ?>"
                                   class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700"
                                   onclick="return confirm('Mark this merchant as verified? This action cannot be undone.')">
                                    ✓ Verify Merchant
                                </a>
                            <?php else: ?>
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                    ✓ Verified
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif ($verificationProgress['uploaded'] > 0): ?>
                    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <h4 class="font-medium text-yellow-900">Verification In Progress</h4>
                        <p class="text-sm text-yellow-700">
                            <?= $verificationProgress['pending'] ?> document(s) pending review,
                            <?= $verificationProgress['total_required'] - $verificationProgress['uploaded'] ?> document(s) still needed.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <h4 class="font-medium text-gray-900">Awaiting Documents</h4>
                        <p class="text-sm text-gray-700">Merchant has not uploaded any verification documents yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column - Subscription & Actions -->
        <div class="space-y-6">
            <!-- Current Subscription -->
            <?php if (!empty($merchant['plan_name'])): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Current Subscription</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Plan</span>
                            <span class="font-semibold text-gray-900"><?= esc($merchant['plan_name']) ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Price</span>
                            <span class="font-semibold text-gray-900">$<?= number_format($merchant['plan_price'], 2) ?>/month</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Status</span>
                            <?php
                            $subStatusClass = '';
                            switch ($merchant['subscription_status']) {
                                case 'active':
                                    $subStatusClass = 'bg-green-100 text-green-800';
                                    break;
                                case 'trial':
                                    $subStatusClass = 'bg-blue-100 text-blue-800';
                                    break;
                                case 'expired':
                                    $subStatusClass = 'bg-red-100 text-red-800';
                                    break;
                                default:
                                    $subStatusClass = 'bg-gray-100 text-gray-800';
                            }
                            ?>
                            <span class="<?= $subStatusClass ?> px-2 py-1 rounded-full text-xs font-medium">
                                <?= esc(ucfirst($merchant['subscription_status'])) ?>
                            </span>
                        </div>
                        <?php if (!empty($merchant['subscription_date'])): ?>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Started</span>
                                <span class="text-gray-900"><?= date('M j, Y', strtotime($merchant['subscription_date'])) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($merchant['subscription_status'] === 'trial' && !empty($merchant['trial_ends_at'])): ?>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Trial Ends</span>
                                <span class="text-gray-900"><?= date('M j, Y', strtotime($merchant['trial_ends_at'])) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Administrative Actions</h2>
                <div class="space-y-3">
                    <!-- Document Verification Actions -->
                    <?php if (isset($verificationProgress) && $verificationProgress['approval_percentage'] === 100 && $merchant['is_verified'] !== 'verified'): ?>
                        <div class="border-b border-gray-200 pb-3 mb-3">
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-3">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-medium text-green-800">Ready for Verification</h4>
                                        <p class="text-sm text-green-700">All required documents have been approved and verified.</p>
                                    </div>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-3">
                                <strong>Final Step:</strong> Mark this merchant as verified to grant full platform access.
                            </p>
                            <a href="<?= site_url('admin/merchants/verify/' . $merchant['id']) ?>"
                               class="w-full bg-green-600 text-white px-4 py-3 rounded-md hover:bg-green-700 text-center block font-semibold text-lg"
                               onclick="return confirm('Mark <?= esc($merchant['business_name']) ?> as VERIFIED?\n\nThis action will:\n• Grant full platform access\n• Mark merchant as verified\n• Cannot be undone\n\nConfirm verification?')">
                                ✅ VERIFY MERCHANT
                            </a>
                        </div>
                    <?php elseif (isset($verificationProgress) && $verificationProgress['uploaded'] > 0 && $verificationProgress['approval_percentage'] < 100): ?>
                        <div class="border-b border-gray-200 pb-3 mb-3">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-3">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-yellow-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-medium text-yellow-800">Documents Under Review</h4>
                                        <p class="text-sm text-yellow-700">
                                            <?= $verificationProgress['approved'] ?>/<?= $verificationProgress['total_required'] ?> documents approved.
                                            Review remaining documents below.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600">
                                <strong>Next Step:</strong> Review and approve all documents in the Document Verification section below.
                            </p>
                        </div>
                    <?php elseif (isset($verificationProgress) && $verificationProgress['uploaded'] === 0): ?>
                        <div class="border-b border-gray-200 pb-3 mb-3">
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-3">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-800">Awaiting Documents</h4>
                                        <p class="text-sm text-gray-700">Merchant has not uploaded verification documents yet.</p>
                                    </div>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600">
                                <strong>Status:</strong> Waiting for merchant to upload required documents.
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Approval Actions (for pending merchants) -->
                    <?php if ($merchant['verification_status'] === 'pending'): ?>
                        <div class="border-b border-gray-200 pb-3 mb-3">
                            <p class="text-sm text-gray-600 mb-2">Merchant Approval</p>
                            <a href="<?= site_url('admin/merchants/approve/' . $merchant['id']) ?>"
                               class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-center block mb-2"
                               onclick="return confirm('Are you sure you want to approve <?= esc($merchant['business_name']) ?>?')">
                                ✓ Approve Merchant
                            </a>
                            <a href="<?= site_url('admin/merchants/reject/' . $merchant['id']) ?>"
                               class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 text-center block"
                               onclick="return confirm('Are you sure you want to reject this application?')">
                                ✗ Reject Application
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- General Actions -->
                    <div class="space-y-2">
                        <a href="#" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-center block">
                            📝 Edit Merchant Profile
                        </a>
                        <a href="mailto:<?= esc($merchant['email']) ?>" class="w-full bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 text-center block">
                            📧 Send Email
                        </a>
                        <?php if (!empty($merchant['business_whatsapp_number'])): ?>
                            <a href="https://wa.me/<?= str_replace(['+', ' ', '-'], '', $merchant['business_whatsapp_number']) ?>"
                               target="_blank"
                               class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-center block">
                                💬 WhatsApp Contact
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Visibility Control -->
                    <div class="border-t border-gray-200 pt-3 mt-3">
                        <p class="text-sm text-gray-600 mb-2">Visibility Control</p>
                        <?php if ($merchant['is_visible']): ?>
                            <a href="#" class="w-full bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 text-center block"
                               onclick="return confirm('Hide <?= esc($merchant['business_name']) ?> from public listings?')">
                                👁‍🗨 Hide from Listings
                            </a>
                        <?php else: ?>
                            <a href="#" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-center block"
                               onclick="return confirm('Show <?= esc($merchant['business_name']) ?> in public listings?')">
                                👁 Show in Listings
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Account Management -->
                    <div class="border-t border-gray-200 pt-3 mt-3">
                        <p class="text-sm text-gray-600 mb-2">Account Management</p>
                        <?php if ($merchant['verification_status'] === 'approved'): ?>
                            <a href="#" class="w-full bg-orange-600 text-white px-4 py-2 rounded-md hover:bg-orange-700 text-center block"
                               onclick="return confirm('Are you sure you want to suspend <?= esc($merchant['business_name']) ?>? This will prevent them from accessing their account.')">
                                ⚠️ Suspend Account
                            </a>
                        <?php endif; ?>
                        <a href="<?= site_url('admin/merchants/analytics/' . $merchant['id']) ?>" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-center block mt-2">
                            📊 View Analytics
                        </a>
                        <a href="<?= site_url('admin/merchants/reset-password/' . $merchant['id']) ?>" class="w-full bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 text-center block mt-2"
                           onclick="return confirm('Send password reset email to <?= esc($merchant['owner_name']) ?>?')">
                            🔄 Reset Password
                        </a>
                    </div>

                    <!-- Subscription Management -->
                    <?php if (!empty($merchant['plan_name']) && !empty($merchant['subscription_id'])): ?>
                        <div class="border-t border-gray-200 pt-3 mt-3">
                            <p class="text-sm text-gray-600 mb-2">Subscription Management</p>
                            <a href="<?= site_url('admin/subscriptions/manage/' . $merchant['subscription_id']) ?>" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-center block">
                                💳 Manage Subscription
                            </a>
                            <?php if ($merchant['subscription_status'] === 'trial'): ?>
                                <a href="<?= site_url('admin/merchants/extend-trial/' . $merchant['id']) ?>" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-center block mt-2"
                                   onclick="return confirm('Extend trial period for <?= esc($merchant['business_name']) ?>?')">
                                    ⏰ Extend Trial
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Subscription History -->
            <?php if (!empty($subscriptionHistory)): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Subscription History</h2>
                    <div class="space-y-3">
                        <?php foreach ($subscriptionHistory as $subscription): ?>
                            <div class="border-l-4 border-blue-400 pl-4 py-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900"><?= esc($subscription['plan_name']) ?></span>
                                    <span class="text-sm text-gray-600"><?= date('M j, Y', strtotime($subscription['created_at'])) ?></span>
                                </div>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="text-sm text-gray-600">$<?= number_format($subscription['price'], 2) ?>/month</span>
                                    <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded"><?= esc(ucfirst($subscription['status'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Document Rejection Modal -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Document</h3>
            <form id="rejectForm" method="POST" action="">
                <div class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Reason for rejection:
                    </label>
                    <textarea id="rejection_reason"
                              name="rejection_reason"
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                              placeholder="Please provide a clear reason for rejecting this document..."
                              required></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button"
                            onclick="hideRejectModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Reject Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showRejectModal(documentId, documentName) {
    document.getElementById('rejectForm').action = '<?= site_url('admin/documents/reject/') ?>' + documentId;
    document.getElementById('rejection_reason').placeholder = 'Please provide a clear reason for rejecting the ' + documentName + '...';
    document.getElementById('rejectModal').classList.remove('hidden');
}

function hideRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejection_reason').value = '';
}

// Close modal when clicking outside
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideRejectModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideRejectModal();
    }
});
</script>

<?= view('admin/templates/footer') ?>
