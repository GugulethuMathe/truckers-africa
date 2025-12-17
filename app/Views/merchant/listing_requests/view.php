<?= view('merchant/templates/header', ['page_title' => $page_title]) ?>

<div class="px-6 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="<?= base_url('merchant/listing-requests') ?>" class="text-green-600 hover:text-green-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back to Requests
            </a>
        </div>

        <!-- Request Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900"><?= esc($request['title']) ?></h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Requested by <?= esc($request['requester_name']) ?> on <?= date('F d, Y \a\t h:i A', strtotime($request['created_at'])) ?>
                    </p>
                </div>
                <div>
                    <?php
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'approved' => 'bg-blue-100 text-blue-800',
                        'rejected' => 'bg-red-100 text-red-800',
                        'converted' => 'bg-green-100 text-green-800'
                    ];
                    $statusColor = $statusColors[$request['status']] ?? 'bg-gray-100 text-gray-800';
                    ?>
                    <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full <?= $statusColor ?>">
                        <?= ucfirst($request['status']) ?>
                    </span>
                </div>
            </div>

            <!-- Action Buttons -->
            <?php if ($request['status'] === 'pending'): ?>
                <div class="border-t pt-4 mt-4 flex items-center space-x-4">
                    <!-- Approve Button -->
                    <form action="<?= base_url('merchant/listing-requests/approve/' . $request['id']) ?>" method="POST" class="inline">
                        <?= csrf_field() ?>
                        <button type="submit" 
                                onclick="return confirm('Are you sure you want to approve this request?')"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            <i class="fas fa-check mr-2"></i>Approve Request
                        </button>
                    </form>

                    <!-- Reject Button -->
                    <button onclick="showRejectModal()" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        <i class="fas fa-times mr-2"></i>Reject Request
                    </button>
                </div>
            <?php elseif ($request['status'] === 'approved'): ?>
                <div class="border-t pt-4 mt-4">
                    <form action="<?= base_url('merchant/listing-requests/convert/' . $request['id']) ?>" method="POST" class="inline">
                        <?= csrf_field() ?>
                        <button type="submit" 
                                onclick="return confirm('This will create a new listing from this request. Continue?')"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                            <i class="fas fa-magic mr-2"></i>Convert to Listing
                        </button>
                    </form>
                    <p class="text-sm text-gray-600 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        This will create a new listing and assign it to the requesting branch.
                    </p>
                </div>
            <?php elseif ($request['status'] === 'converted'): ?>
                <div class="border-t pt-4 mt-4 bg-green-50 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-check-double text-green-600 mt-1 mr-3"></i>
                        <div class="text-sm text-green-800">
                            <p class="font-medium">Listing Created Successfully!</p>
                            <?php if ($request['created_listing_id']): ?>
                                <a href="<?= base_url('merchant/listings/edit/' . $request['created_listing_id']) ?>" 
                                   class="inline-block mt-2 text-green-700 hover:text-green-900 font-medium">
                                    View Listing <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php elseif ($request['status'] === 'rejected'): ?>
                <div class="border-t pt-4 mt-4 bg-red-50 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-times-circle text-red-600 mt-1 mr-3"></i>
                        <div class="text-sm text-red-800">
                            <p class="font-medium">Request Rejected</p>
                            <?php if ($request['rejection_reason']): ?>
                                <p class="mt-1"><strong>Reason:</strong> <?= esc($request['rejection_reason']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Request Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Description -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        <i class="fas fa-file-alt mr-2"></i>Service Description
                    </h2>
                    <?php if ($request['description']): ?>
                        <p class="text-gray-900 whitespace-pre-wrap"><?= esc($request['description']) ?></p>
                    <?php else: ?>
                        <p class="text-gray-500 italic">No description provided</p>
                    <?php endif; ?>
                </div>

                <!-- Images -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        <i class="fas fa-images mr-2"></i>Service Images
                    </h2>

                    <?php if (!empty($request['main_image']) || !empty($request['gallery_images'])): ?>
                        <!-- Main Image -->
                        <?php if (!empty($request['main_image'])): ?>
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 mb-2 font-medium">Main Image (Featured)</p>
                                <div class="relative group">
                                    <img src="<?= get_listing_request_image_url($request['main_image']) ?>"
                                         alt="Main Image"
                                         class="w-full h-80 object-cover rounded-lg border border-gray-200 shadow-sm cursor-pointer hover:shadow-md transition-shadow"
                                         onclick="openImageModal('<?= get_listing_request_image_url($request['main_image']) ?>')">
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all rounded-lg flex items-center justify-center">
                                        <i class="fas fa-search-plus text-white text-3xl opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Gallery Images -->
                        <?php if (!empty($request['gallery_images'])): ?>
                            <?php
                            $galleryImages = json_decode($request['gallery_images'], true);
                            if ($galleryImages && is_array($galleryImages) && count($galleryImages) > 0):
                            ?>
                                <div>
                                    <p class="text-sm text-gray-600 mb-2 font-medium">
                                        Gallery Images <span class="text-xs font-normal">(<?= count($galleryImages) ?> images)</span>
                                    </p>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        <?php foreach ($galleryImages as $imageName): ?>
                                            <?php if (!empty($imageName)): ?>
                                                <div class="relative group">
                                                    <img src="<?= get_listing_request_image_url($imageName) ?>"
                                                         alt="Gallery Image"
                                                         class="w-full h-40 object-cover rounded-lg border border-gray-200 shadow-sm cursor-pointer hover:shadow-md transition-shadow"
                                                         onclick="openImageModal('<?= get_listing_request_image_url($imageName) ?>')">
                                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all rounded-lg flex items-center justify-center">
                                                        <i class="fas fa-search-plus text-white text-xl opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200">
                            <i class="fas fa-image text-gray-400 text-4xl mb-3"></i>
                            <p class="text-gray-500 italic">No images uploaded for this request</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pricing -->
                <?php if ($request['suggested_price']): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">
                            <i class="fas fa-tag mr-2"></i>Suggested Pricing
                        </h2>
                        <div class="flex items-baseline">
                            <span class="text-4xl font-bold text-green-600">
                                <?= $request['currency_code'] ?> <?= number_format($request['suggested_price'], 2) ?>
                            </span>
                            <?php if ($request['unit']): ?>
                                <span class="text-lg text-gray-600 ml-2">per <?= esc($request['unit']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Categories -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        <i class="fas fa-tags mr-2"></i>Suggested Categories
                    </h2>
                    <?php if (!empty($request['suggested_categories'])): ?>
                        <div class="flex flex-wrap gap-2">
                            <?php
                            $categoryIds = json_decode($request['suggested_categories'], true);
                            if ($categoryIds && is_array($categoryIds) && count($categoryIds) > 0):
                                $categoryModel = new \App\Models\ServiceCategoryModel();
                                $hasCategories = false;
                                foreach ($categoryIds as $catId):
                                    if (is_numeric($catId)):
                                        $category = $categoryModel->find($catId);
                                        if ($category):
                                            $hasCategories = true;
                            ?>
                                            <span class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-full border border-green-200">
                                                <i class="fas fa-tag mr-2 text-green-600"></i>
                                                <?= esc($category['name']) ?>
                                            </span>
                            <?php
                                        endif;
                                    endif;
                                endforeach;
                                if (!$hasCategories):
                            ?>
                                    <div class="text-center py-4 bg-gray-50 rounded-lg border border-gray-200 w-full">
                                        <i class="fas fa-tags text-gray-400 text-3xl mb-2"></i>
                                        <p class="text-gray-500 italic">No valid categories found</p>
                                    </div>
                            <?php
                                endif;
                            else:
                            ?>
                                <div class="text-center py-4 bg-gray-50 rounded-lg border border-gray-200 w-full">
                                    <i class="fas fa-tags text-gray-400 text-3xl mb-2"></i>
                                    <p class="text-gray-500 italic">No categories specified</p>
                                </div>
                            <?php
                            endif;
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200">
                            <i class="fas fa-tags text-gray-400 text-4xl mb-3"></i>
                            <p class="text-gray-500 italic">No categories selected for this request</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Justification -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        <i class="fas fa-lightbulb mr-2"></i>Business Justification
                    </h2>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-gray-900 whitespace-pre-wrap"><?= esc($request['justification']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Branch Info -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-map-marker-alt mr-2"></i>Branch Location
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Branch Name</p>
                            <p class="font-medium text-gray-900"><?= esc($request['location_name']) ?></p>
                        </div>
                        <?php if ($request['physical_address']): ?>
                            <div>
                                <p class="text-sm text-gray-600">Address</p>
                                <p class="text-sm text-gray-900"><?= esc($request['physical_address']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Requester Info -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-user mr-2"></i>Requested By
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Name</p>
                            <p class="font-medium text-gray-900"><?= esc($request['requester_name']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="text-sm text-gray-900"><?= esc($request['requester_email']) ?></p>
                        </div>
                        <?php if ($request['requester_phone']): ?>
                            <div>
                                <p class="text-sm text-gray-600">Phone</p>
                                <p class="text-sm text-gray-900"><?= esc($request['requester_phone']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-history mr-2"></i>Timeline
                    </h2>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check text-green-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Request Submitted</p>
                                <p class="text-xs text-gray-600">
                                    <?= date('M d, Y h:i A', strtotime($request['created_at'])) ?>
                                </p>
                            </div>
                        </div>

                        <?php if ($request['reviewed_at']): ?>
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 <?= $request['status'] === 'rejected' ? 'bg-red-100' : 'bg-blue-100' ?> rounded-full flex items-center justify-center">
                                        <i class="fas <?= $request['status'] === 'rejected' ? 'fa-times text-red-600' : 'fa-check text-blue-600' ?> text-sm"></i>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">
                                        <?= $request['status'] === 'rejected' ? 'Rejected' : 'Reviewed' ?>
                                    </p>
                                    <p class="text-xs text-gray-600">
                                        <?= date('M d, Y h:i A', strtotime($request['reviewed_at'])) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($request['status'] === 'converted'): ?>
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-check-double text-green-600 text-sm"></i>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Listing Created</p>
                                    <p class="text-xs text-gray-600">Active</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Request</h3>
            <form action="<?= base_url('merchant/listing-requests/reject/' . $request['id']) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Reason for Rejection <span class="text-red-500">*</span>
                    </label>
                    <textarea id="rejection_reason"
                              name="rejection_reason"
                              rows="4"
                              required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-red-500 focus:border-red-500"
                              placeholder="Explain why this request is being rejected..."></textarea>
                </div>
                <div class="flex items-center justify-end space-x-4">
                    <button type="button"
                            onclick="hideRejectModal()"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Reject Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Modal (Lightbox) -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4" onclick="closeImageModal()">
    <div class="relative max-w-5xl max-h-full">
        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors z-10">
            <i class="fas fa-times text-3xl"></i>
        </button>
        <img id="modalImage" src="" alt="Full Size Image" class="max-w-full max-h-screen object-contain rounded-lg">
    </div>
</div>

<script>
function showRejectModal() {
    document.getElementById('rejectModal').classList.remove('hidden');
}

function hideRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

// Close reject modal when clicking outside
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideRejectModal();
    }
});

// Image modal functions
function openImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close image modal on ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeImageModal();
    }
});
</script>

<?= view('merchant/templates/footer') ?>

