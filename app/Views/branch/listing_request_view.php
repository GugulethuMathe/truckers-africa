<?= view('branch/templates/header', ['page_title' => $page_title]) ?>

<div class="px-6 py-8">
    <div class="max-w-5xl mx-auto">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="<?= base_url('branch/listing-requests') ?>" class="text-green-600 hover:text-green-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back to Requests
            </a>
        </div>

        <!-- Request Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900"><?= esc($request['title']) ?></h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Submitted on <?= date('F d, Y \a\t h:i A', strtotime($request['created_at'])) ?>
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

            <!-- Status Messages -->
            <?php if ($request['status'] === 'approved'): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-blue-600 mt-1 mr-3"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-medium">Request Approved!</p>
                            <p class="mt-1">Your request has been approved by the merchant. The listing will be created soon.</p>
                            <?php if ($request['reviewed_at']): ?>
                                <p class="text-xs mt-1">Reviewed on <?= date('M d, Y \a\t h:i A', strtotime($request['reviewed_at'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php elseif ($request['status'] === 'converted'): ?>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-check-double text-green-600 mt-1 mr-3"></i>
                        <div class="text-sm text-green-800">
                            <p class="font-medium">Listing Created!</p>
                            <p class="mt-1">This request has been converted to an active listing.</p>
                            <?php if ($request['created_listing_id']): ?>
                                <a href="<?= base_url('driver/service/' . $request['created_listing_id']) ?>" 
                                   class="inline-block mt-2 text-green-700 hover:text-green-900 font-medium">
                                    View Listing <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php elseif ($request['status'] === 'rejected'): ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-times-circle text-red-600 mt-1 mr-3"></i>
                        <div class="text-sm text-red-800">
                            <p class="font-medium">Request Rejected</p>
                            <?php if ($request['rejection_reason']): ?>
                                <p class="mt-1"><strong>Reason:</strong> <?= esc($request['rejection_reason']) ?></p>
                            <?php endif; ?>
                            <?php if ($request['reviewed_at']): ?>
                                <p class="text-xs mt-1">Reviewed on <?= date('M d, Y \a\t h:i A', strtotime($request['reviewed_at'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-clock text-yellow-600 mt-1 mr-3"></i>
                        <div class="text-sm text-yellow-800">
                            <p class="font-medium">Pending Review</p>
                            <p class="mt-1">Your request is waiting for merchant review. You'll be notified once it's reviewed.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Request Details -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        <i class="fas fa-info-circle mr-2"></i>Request Details
                    </h2>

                    <!-- Description -->
                    <?php if ($request['description']): ?>
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Description</h3>
                            <p class="text-gray-900 whitespace-pre-wrap"><?= esc($request['description']) ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Images -->
                    <?php if (!empty($request['main_image']) || !empty($request['gallery_images'])): ?>
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">
                                <i class="fas fa-images mr-1"></i>Images
                            </h3>

                            <!-- Main Image -->
                            <?php if (!empty($request['main_image'])): ?>
                                <div class="mb-3">
                                    <p class="text-xs text-gray-600 mb-2">Main Image</p>
                                    <div class="relative group">
                                        <img src="<?= get_listing_request_image_url($request['main_image']) ?>"
                                             alt="Main Image"
                                             class="w-full h-64 object-cover rounded-lg border border-gray-200 shadow-sm cursor-pointer hover:shadow-md transition-shadow"
                                             onclick="openImageModal('<?= get_listing_request_image_url($request['main_image']) ?>')">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all rounded-lg flex items-center justify-center">
                                            <i class="fas fa-search-plus text-white text-2xl opacity-0 group-hover:opacity-100 transition-opacity"></i>
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
                                        <p class="text-xs text-gray-600 mb-2">Gallery Images (<?= count($galleryImages) ?>)</p>
                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                            <?php foreach ($galleryImages as $imageName): ?>
                                                <div class="relative group">
                                                    <img src="<?= get_listing_request_image_url($imageName) ?>"
                                                         alt="Gallery Image"
                                                         class="w-full h-32 object-cover rounded-lg border border-gray-200 shadow-sm cursor-pointer hover:shadow-md transition-shadow"
                                                         onclick="openImageModal('<?= get_listing_request_image_url($imageName) ?>')">
                                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all rounded-lg flex items-center justify-center">
                                                        <i class="fas fa-search-plus text-white opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Price -->
                    <?php if ($request['suggested_price']): ?>
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Suggested Price</h3>
                            <p class="text-2xl font-bold text-green-600">
                                <?= $request['currency_code'] ?> <?= number_format($request['suggested_price'], 2) ?>
                                <?php if ($request['unit']): ?>
                                    <span class="text-sm text-gray-600 font-normal">per <?= esc($request['unit']) ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Categories -->
                    <?php if ($request['suggested_categories']): ?>
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Suggested Categories</h3>
                            <div class="flex flex-wrap gap-2">
                                <?php
                                $categoryIds = json_decode($request['suggested_categories'], true);
                                if ($categoryIds):
                                    $categoryModel = new \App\Models\ServiceCategoryModel();
                                    foreach ($categoryIds as $catId):
                                        $category = $categoryModel->find($catId);
                                        if ($category):
                                ?>
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">
                                        <?= esc($category['name']) ?>
                                    </span>
                                <?php
                                        endif;
                                    endforeach;
                                endif;
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Justification -->
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Justification</h3>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-gray-900 whitespace-pre-wrap"><?= esc($request['justification']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Location Info -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-map-marker-alt mr-2"></i>Location
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Branch</p>
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

                <!-- Request Info -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-clock mr-2"></i>Timeline
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

<!-- Image Modal (Lightbox) -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4" onclick="closeImageModal()">
    <div class="relative max-w-4xl max-h-full">
        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors">
            <i class="fas fa-times text-3xl"></i>
        </button>
        <img id="modalImage" src="" alt="Full Size Image" class="max-w-full max-h-screen object-contain rounded-lg">
    </div>
</div>

<script>
function openImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal on ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeImageModal();
    }
});
</script>

<?= view('branch/templates/footer') ?>

