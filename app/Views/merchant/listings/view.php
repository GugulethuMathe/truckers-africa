<?= view('merchant/templates/header', ['page_title' => $page_title]) ?>

<div class="px-4 lg:px-6 py-6 lg:py-8">
    <div class="max-w-4xl mx-auto">

        <?= view('merchant/components/notifications') ?>

        <!-- Header with Back Button -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6 space-y-4 lg:space-y-0">
            <div class="flex items-center space-x-4">
                <a href="<?= site_url('merchant/listings') ?>" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Listings
                </a>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="<?= site_url('merchant/listings/edit/' . $listing['id']) ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    Edit Listing
                </a>
                <a href="<?= site_url('merchant/listings/delete/' . $listing['id']) ?>" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition-colors" onclick="return confirm('Are you sure you want to delete this listing?');">
                    Delete Listing
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            
            <!-- Listing Header -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between space-y-4 lg:space-y-0">
                    <div class="flex-1">
                        <h1 class="text-xl lg:text-3xl font-bold text-gray-900 mb-2"><?= esc($listing['title']) ?></h1>
                        <div class="flex flex-wrap items-center gap-4">
                            <span class="text-xl lg:text-3xl font-bold text-brand-blue">
                                <?= esc($listing['currency_code']) ?> <?= esc(number_format($listing['price_numeric'], 2)) ?>
                            </span>
                            <span class="
                                <?php
                                    switch ($listing['status']) {
                                        case 'approved': echo 'bg-green-100 text-green-800'; break;
                                        case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'rejected': echo 'bg-red-100 text-red-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800'; break;
                                    }
                                ?>
                                py-2 px-3 rounded-full text-sm font-semibold">
                                <?= ucfirst(esc($listing['status'])) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Image -->
            <?php if (!empty($listing['main_image_path'])): ?>
                <div class="aspect-w-16 aspect-h-9 lg:aspect-h-6">
                    <img src="<?= get_listing_image_url($listing['main_image_path']) ?>"
                         alt="<?= esc($listing['title']) ?>"
                         class="w-full h-64 lg:h-80 object-cover">
                </div>
            <?php endif; ?>

            <!-- Content -->
            <div class="p-6">
                
                <!-- Description -->
                <?php if (!empty($listing['description'])): ?>
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Description</h3>
                        <div class="prose prose-sm lg:prose max-w-none text-gray-700">
                            <?= nl2br(esc($listing['description'])) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Additional Images -->
                <?php if (!empty($images)): ?>
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Additional Images</h3>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <?php foreach ($images as $image): ?>
                                <div class="aspect-w-1 aspect-h-1">
                                    <img src="<?= get_listing_image_url($image['image_path']) ?>"
                                         alt="Additional image"
                                         class="w-full h-32 object-cover rounded-lg cursor-pointer hover:opacity-75 transition-opacity"
                                         onclick="openImageModal('<?= get_listing_image_url($image['image_path']) ?>')">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Listing Details -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Listing Information</h3>
                        <dl class="space-y-2">
                            <?php if (!empty($location)): ?>
                                <div class="flex justify-between items-start">
                                    <dt class="text-sm text-gray-500">Location:</dt>
                                    <dd class="text-sm font-medium text-gray-900 text-right">
                                        <?= esc($location['location_name']) ?>
                                        <?php if ($location['is_primary'] == 1): ?>
                                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded-full ml-1">Primary</span>
                                        <?php else: ?>
                                            <span class="inline-block text-xs px-2 py-0.5 rounded-full ml-1" style="background-color: #e6e8eb; color: #0e2140;">Branch</span>
                                        <?php endif; ?>
                                    </dd>
                                </div>
                            <?php endif; ?>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Created:</dt>
                                <dd class="text-sm font-medium text-gray-900"><?= date('M j, Y', strtotime($listing['created_at'])) ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Last Updated:</dt>
                                <dd class="text-sm font-medium text-gray-900"><?= date('M j, Y', strtotime($listing['updated_at'])) ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Listing ID:</dt>
                                <dd class="text-sm font-medium text-gray-900">#<?= esc($listing['id']) ?></dd>
                            </div>
                        </dl>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Status Information</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Current Status:</dt>
                                <dd class="text-sm font-medium text-gray-900"><?= ucfirst(esc($listing['status'])) ?></dd>
                            </div>
                            <?php if ($listing['status'] === 'pending'): ?>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mt-3">
                                    <p class="text-sm text-yellow-800">
                                        <strong>Pending Review:</strong> Your listing is currently being reviewed by our team. You'll be notified once it's approved.
                                    </p>
                                </div>
                            <?php elseif ($listing['status'] === 'rejected'): ?>
                                <div class="bg-red-50 border border-red-200 rounded-lg p-3 mt-3">
                                    <p class="text-sm text-red-800">
                                        <strong>Rejected:</strong> This listing was rejected. Please edit and resubmit with the required changes.
                                    </p>
                                </div>
                            <?php elseif ($listing['status'] === 'approved'): ?>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3 mt-3">
                                    <p class="text-sm text-green-800">
                                        <strong>Approved:</strong> Your listing is live and visible to customers.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4" onclick="closeImageModal()">
    <div class="max-w-4xl max-h-full">
        <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain">
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

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});
</script>

<?= view('merchant/templates/footer') ?>
