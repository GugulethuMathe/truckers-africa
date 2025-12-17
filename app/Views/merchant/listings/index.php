<?= view('merchant/templates/header', ['page_title' => $page_title]) ?>
<?php helper('image'); ?>

<div class="px-4 lg:px-6 py-6 lg:py-8">
    <div class="max-w-7xl mx-auto">

        <?= view('merchant/components/notifications') ?>

        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-6 space-y-4 lg:space-y-0">
            <div>
                <h1 class="text-xl lg:text-3xl font-bold text-gray-900">My Service Listings</h1>
                <p class="text-gray-600 text-xs lg:text-base">Preview how your listings appear to truck drivers</p>
            </div>
            <?php if (isset($merchant) && $merchant['verification_status'] === 'approved'): ?>
                <div class="flex-shrink-0">
                    <a href="<?= site_url('merchant/listings/new') ?>" class="block w-full lg:w-auto text-center bg-brand-blue text-white font-semibold py-3 lg:py-2 px-6 lg:px-4 rounded-lg text-sm hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add New Listing
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (isset($merchant) && $merchant['verification_status'] !== 'approved'): ?>
            <div class="rounded-md bg-yellow-50 p-4 mb-6 border border-yellow-300">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Account Approval Required</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>Your account must be approved before you can create service listings.
                            <?php if ($merchant['verification_status'] === 'pending'): ?>
                                Your account is currently pending approval. Please wait for admin review.
                            <?php else: ?>
                                Please complete your verification or contact support for assistance.
                            <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session()->has('message')) : ?>
            <div class="rounded-md bg-green-50 p-4 mb-6 border border-green-300">
                <p class="text-sm font-medium text-green-800"><?= session('message') ?></p>
            </div>
        <?php endif; ?>
        <?php if (session()->has('error')) : ?>
            <div class="rounded-md bg-red-50 p-4 mb-6 border border-red-300">
                <p class="text-sm font-medium text-red-800"><?= session('error') ?></p>
            </div>
        <?php endif; ?>

        <!-- Driver Preview Info Banner -->
        <?php if (!empty($listings)): ?>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-eye text-blue-600 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-800">
                            <strong>Driver Preview Mode:</strong> This is how your listings appear to truck drivers searching for services.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-md">
            <?php if (empty($listings)): ?>
                <div class="text-center py-12 px-4">
                    <div class="mb-4">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-base lg:text-lg font-medium text-gray-900 mb-2">No service listings yet</h3>
                    <p class="text-gray-500 mb-6 text-xs lg:text-base">
                        <?php if (isset($merchant) && $merchant['verification_status'] === 'approved'): ?>
                            You haven't created any service listings yet. Start by creating your first listing to attract customers.
                        <?php else: ?>
                            Once your account is approved, you'll be able to create service listings to attract customers.
                        <?php endif; ?>
                    </p>
                    <?php if (isset($merchant) && $merchant['verification_status'] === 'approved'): ?>
                        <a href="<?= site_url('merchant/listings/new') ?>" class="inline-block bg-brand-blue text-white font-semibold py-3 px-6 rounded-lg text-sm hover:bg-blue-700 transition-colors">Create Your First Listing</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Listing Stats Summary -->
                <div class="p-4 lg:p-6 border-b border-gray-200">
                    <div class="flex flex-wrap items-center gap-4">
                        <span class="text-sm text-gray-600">
                            <i class="fas fa-box mr-1"></i><?= count($listings) ?> listing<?= count($listings) !== 1 ? 's' : '' ?>
                        </span>
                        <?php
                            $approvedCount = count(array_filter($listings, fn($l) => $l['status'] === 'approved'));
                            $pendingCount = count(array_filter($listings, fn($l) => $l['status'] === 'pending'));
                            $rejectedCount = count(array_filter($listings, fn($l) => $l['status'] === 'rejected'));
                        ?>
                        <?php if ($approvedCount > 0): ?>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                <i class="fas fa-check-circle mr-1"></i><?= $approvedCount ?> Approved
                            </span>
                        <?php endif; ?>
                        <?php if ($pendingCount > 0): ?>
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">
                                <i class="fas fa-clock mr-1"></i><?= $pendingCount ?> Pending
                            </span>
                        <?php endif; ?>
                        <?php if ($rejectedCount > 0): ?>
                            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">
                                <i class="fas fa-times-circle mr-1"></i><?= $rejectedCount ?> Rejected
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Driver-Style Listings Grid -->
                <div class="p-4 lg:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($listings as $listing): ?>
                            <div class="service-item border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow relative">
                                <!-- Status Badge (Top Right) -->
                                <div class="absolute top-3 right-3 z-10">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full shadow-sm
                                        <?php
                                            switch($listing['status']) {
                                                case 'approved': echo 'bg-green-500 text-white'; break;
                                                case 'pending': echo 'bg-yellow-500 text-white'; break;
                                                case 'rejected': echo 'bg-red-500 text-white'; break;
                                            }
                                        ?>">
                                        <?= ucfirst(esc($listing['status'])) ?>
                                    </span>
                                </div>

                                <!-- Listing Image -->
                                <div class="w-full h-48 bg-gray-100 overflow-hidden">
                                    <?php
                                        $imagePathRaw = $listing['main_image_path'] ?? ($listing['image_url'] ?? '');
                                        if (!empty($imagePathRaw) && preg_match('#^https?://#', $imagePathRaw)) {
                                            $imageSrc = $imagePathRaw;
                                        } else if (!empty($imagePathRaw)) {
                                            $imageSrc = get_listing_image_url($imagePathRaw);
                                        } else {
                                            $imageSrc = '';
                                        }
                                    ?>
                                    <?php if (!empty($imageSrc)): ?>
                                        <img src="<?= esc($imageSrc) ?>" alt="<?= esc($listing['title']) ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                            <i class="fas fa-box text-gray-400 text-4xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Listing Content -->
                                <div class="p-4">
                                    <!-- Title -->
                                    <h4 class="text-lg font-semibold text-gray-900 mb-2"><?= esc($listing['title']) ?></h4>

                                    <!-- Description -->
                                    <?php if (!empty($listing['description'])): ?>
                                        <p class="text-gray-600 text-sm mb-3 line-clamp-2"><?= esc($listing['description']) ?></p>
                                    <?php endif; ?>

                                    <!-- Location Info -->
                                    <?php if (!empty($listing['location_name'])): ?>
                                        <div class="flex items-center gap-2 flex-wrap mb-2">
                                            <span class="text-sm text-gray-700 font-medium">
                                                <i class="fas fa-store text-gray-400 mr-1"></i><?= esc($listing['location_name']) ?>
                                            </span>
                                            <?php if (!empty($listing['is_primary']) && $listing['is_primary'] == 1): ?>
                                                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-1.5 py-0.5 rounded-full">Primary</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Address -->
                                    <?php
                                        $displayAddress = !empty($listing['location_address']) ? $listing['location_address'] : ($listing['physical_address'] ?? '');
                                    ?>
                                    <?php if (!empty($displayAddress)): ?>
                                        <p class="text-xs text-gray-500 mb-3">
                                            <i class="fas fa-map-marker-alt mr-1"></i><?= esc($displayAddress) ?>
                                        </p>
                                    <?php endif; ?>

                                    <!-- Category Badge -->
                                    <?php if (!empty($listing['category_name'])): ?>
                                        <div class="mb-3">
                                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                                <?= esc($listing['category_name']) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Price -->
                                    <div class="flex items-baseline space-x-1 pt-3 border-t border-gray-100">
                                        <span class="text-lg font-bold text-green-600">
                                            <?= esc($listing['currency_symbol'] ?? 'R') ?><?= esc(number_format($listing['price'], 2)) ?>
                                        </span>
                                        <?php if (!empty($listing['unit'])): ?>
                                            <span class="text-xs text-gray-500">per <?= esc($listing['unit']) ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex items-center gap-2 mt-3">
                                        <a href="<?= site_url('merchant/listings/view/' . $listing['id']) ?>"
                                           class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                            <i class="fas fa-eye mr-2"></i>View
                                        </a>
                                        <a href="<?= site_url('merchant/listings/edit/' . $listing['id']) ?>"
                                           class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                                            <i class="fas fa-edit mr-2"></i>Edit
                                        </a>
                                        <a href="<?= site_url('merchant/listings/delete/' . $listing['id']) ?>"
                                           class="inline-flex items-center justify-center px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors"
                                           title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this listing?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if (isset($pager)): ?>
                    <?php $pageCount = $pager->getPageCount(); ?>
                    <?php if ($pageCount > 1): ?>
                        <div class="p-4 border-t border-gray-200 flex justify-center">
                            <nav class="flex items-center space-x-2">
                                <?= $pager->links('default', 'merchant_pagination') ?>
                            </nav>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?= view('merchant/templates/footer') ?>
