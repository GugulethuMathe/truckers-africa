<?= view('admin/templates/header', ['page_title' => $page_title]) ?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Listing Details</h1>
            <p class="text-gray-600">View complete listing information</p>
        </div>
        <div class="flex space-x-2">
            <a href="<?= site_url('admin/listings/approved') ?>" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                Back to Approved
            </a>
            <a href="<?= site_url('admin/listings/all') ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Back to All Listings
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Listing Information -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <!-- Image Gallery Section -->
                <?php
                    $allImages = [];
                    // Add main image first
                    if (!empty($listing['main_image_path'])) {
                        $allImages[] = get_listing_image_url($listing['main_image_path']);
                    }
                    // Add gallery images
                    if (!empty($gallery_images)) {
                        foreach ($gallery_images as $image) {
                            if (!empty($image['image_path'])) {
                                $allImages[] = get_listing_image_url($image['image_path']);
                            }
                        }
                    }
                    $allImages = array_unique($allImages);
                    $firstImage = $allImages[0] ?? '';
                ?>

                <?php if (!empty($firstImage)): ?>
                    <div class="relative h-64 bg-gray-200">
                        <img id="mainImage" src="<?= $firstImage ?>" alt="<?= esc($listing['title']) ?>" class="w-full h-full object-cover">
                        <?php if (count($allImages) > 1): ?>
                            <div class="absolute bottom-4 right-4 bg-black bg-opacity-50 text-white px-2 py-1 rounded text-sm">
                                <span id="imageCounter">1 / <?= count($allImages) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (count($allImages) > 1): ?>
                        <div class="flex space-x-2 p-3 bg-white overflow-x-auto border-b border-gray-200">
                            <?php foreach ($allImages as $index => $image): ?>
                                <img onclick="changeMainImage('<?= $image ?>', <?= $index + 1 ?>)"
                                     src="<?= $image ?>"
                                     alt="Image <?= $index + 1 ?>"
                                     class="thumbnail w-16 h-16 object-cover rounded cursor-pointer border-2 <?= $index === 0 ? 'border-indigo-500' : 'border-gray-200' ?> hover:border-indigo-400 transition-colors">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="w-full h-64 bg-gray-200 flex items-center justify-center">
                        <svg class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                <?php endif; ?>

                <!-- Content Section -->
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h2 class="text-xl font-bold text-gray-900"><?= esc($listing['title']) ?></h2>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full 
                            <?php if ($listing['status'] === 'approved'): ?>bg-green-100 text-green-800
                            <?php elseif ($listing['status'] === 'pending'): ?>bg-yellow-100 text-yellow-800
                            <?php else: ?>bg-red-100 text-red-800<?php endif; ?>">
                            <?= ucfirst($listing['status']) ?>
                        </span>
                    </div>

                    <!-- Price -->
                    <div class="mb-4">
                        <?php
                            $currencySymbol = $currency['currency_symbol'] ?? '$';
                            $currencyName = $currency['currency_name'] ?? 'USD';
                            $currencyCode = $listing['currency_code'] ?? 'USD';
                        ?>
                        <h3 class="text-sm font-medium text-gray-700 mb-1">Price (<?= esc($currencyName) ?>)</h3>
                        <p class="text-2xl font-bold text-indigo-600">
                            <?php if ($listing['price'] > 0): ?>
                                <?= esc($currencySymbol) ?><?= number_format($listing['price'], 2) ?>
                            <?php else: ?>
                                Contact for price
                            <?php endif; ?>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Currency: <?= esc($currencyCode) ?></p>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Description</h3>
                        <div class="text-gray-900 whitespace-pre-wrap"><?= esc($listing['description']) ?></div>
                    </div>

                    <!-- Categories -->
                    <?php if (!empty($categories)): ?>
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Categories</h3>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($categories as $category): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                        <?= esc($category['name']) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Services (Subcategories) -->
                    <?php if (!empty($services)): ?>
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Services Offered</h3>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($services as $service): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <?= esc($service['name']) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Business Location/Branch -->
                    <?php if (!empty($location)): ?>
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Business Location</h3>
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <div class="flex items-start justify-between mb-2">
                                    <h4 class="font-semibold text-gray-900"><?= esc($location['location_name']) ?></h4>
                                    <?php if ($location['is_primary'] == 1): ?>
                                        <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded-full">Primary Location</span>
                                    <?php else: ?>
                                        <span class="inline-block text-xs px-2 py-0.5 rounded-full" style="background-color: #e6e8eb; color: #0e2140;">Branch</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($location['physical_address'])): ?>
                                    <p class="text-sm text-gray-600 mb-2">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        <?= esc($location['physical_address']) ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($location['contact_number'])): ?>
                                    <p class="text-sm text-gray-600 mb-1">
                                        <i class="fas fa-phone mr-1"></i>
                                        <?= esc($location['contact_number']) ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($location['latitude']) && !empty($location['longitude'])): ?>
                                    <a href="https://www.google.com/maps?q=<?= esc($location['latitude']) ?>,<?= esc($location['longitude']) ?>"
                                       target="_blank"
                                       class="inline-flex items-center mt-2 text-sm text-indigo-600 hover:text-indigo-800">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        View on Google Maps
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Timestamps -->
                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                        <div>
                            <h4 class="text-sm font-medium text-gray-700">Created</h4>
                            <p class="text-sm text-gray-900"><?= date('M j, Y g:i A', strtotime($listing['created_at'])) ?></p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-700">Last Updated</h4>
                            <p class="text-sm text-gray-900"><?= date('M j, Y g:i A', strtotime($listing['updated_at'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Merchant Information -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Merchant Information</h3>
                
                <div class="space-y-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700">Business Name</h4>
                        <p class="text-sm text-gray-900"><?= esc($listing['business_name']) ?></p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-700">Owner Name</h4>
                        <p class="text-sm text-gray-900"><?= esc($listing['owner_name']) ?></p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-700">Email</h4>
                        <p class="text-sm text-gray-900">
                            <a href="mailto:<?= esc($listing['email']) ?>" class="text-indigo-600 hover:text-indigo-800">
                                <?= esc($listing['email']) ?>
                            </a>
                        </p>
                    </div>
                    
                    <?php if (!empty($listing['business_contact_number'])) : ?>
                        <div>
                            <h4 class="text-sm font-medium text-gray-700">Business Contact</h4>
                            <p class="text-sm text-gray-900">
                                <a href="tel:<?= esc($listing['business_contact_number']) ?>" class="text-indigo-600 hover:text-indigo-800">
                                    <?= esc($listing['business_contact_number']) ?>
                                </a>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($listing['business_whatsapp_number'])) : ?>
                        <div>
                            <h4 class="text-sm font-medium text-gray-700">WhatsApp Number</h4>
                            <p class="text-sm text-gray-900">
                                <a href="https://wa.me/<?= esc($listing['business_whatsapp_number']) ?>" target="_blank" class="text-indigo-600 hover:text-indigo-800">
                                    <?= esc($listing['business_whatsapp_number']) ?>
                                </a>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($listing['physical_address'])) : ?>
                        <div>
                            <h4 class="text-sm font-medium text-gray-700">Physical Address</h4>
                            <p class="text-sm text-gray-900"><?= esc($listing['physical_address']) ?></p>
                        </div>
                    <?php endif; ?>

                    <div>
                        <h4 class="text-sm font-medium text-gray-700">Merchant ID</h4>
                        <p class="text-sm text-gray-900 font-mono"><?= esc($listing['merchant_id']) ?></p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <div class="flex flex-col space-y-2">
                        <?php if ($listing['status'] === 'pending'): ?>
                            <a href="<?= site_url('admin/listings/approve/' . $listing['id']) ?>" 
                               class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-center"
                               onclick="return confirm('Are you sure you want to approve this listing?')">
                                Approve Listing
                            </a>
                            <a href="<?= site_url('admin/listings/reject/' . $listing['id']) ?>" 
                               class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 text-center"
                               onclick="return confirm('Are you sure you want to reject this listing?')">
                                Reject Listing
                            </a>
                        <?php elseif ($listing['status'] === 'approved'): ?>
                            <a href="<?= site_url('admin/listings/reject/' . $listing['id']) ?>" 
                               class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 text-center"
                               onclick="return confirm('Are you sure you want to reject this listing?')">
                                Reject Listing
                            </a>
                        <?php elseif ($listing['status'] === 'rejected'): ?>
                            <a href="<?= site_url('admin/listings/relist/' . $listing['id']) ?>" 
                               class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-center"
                               onclick="return confirm('Are you sure you want to relist this listing? It will be set to pending for review.')">
                                Relist Listing
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?= site_url('admin/merchants/view/' . $listing['merchant_id']) ?>" 
                           class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-center">
                            View Merchant Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Listing Details</h3>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Listing ID:</span>
                        <span class="text-sm font-mono text-gray-900"><?= esc($listing['id']) ?></span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Categories:</span>
                        <div class="mt-1">
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $category): ?>
                                    <span class="inline-block text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded mr-1 mb-1">
                                        <?= esc($category['name']) ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-sm text-gray-400">No categories</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Services:</span>
                        <div class="mt-1">
                            <?php if (!empty($services)): ?>
                                <?php foreach ($services as $service): ?>
                                    <span class="inline-block text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded mr-1 mb-1">
                                        <?= esc($service['name']) ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-sm text-gray-400">No services</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Status:</span>
                        <span class="text-sm font-medium <?php if ($listing['status'] === 'approved'): ?>text-green-600
                            <?php elseif ($listing['status'] === 'pending'): ?>text-yellow-600
                            <?php else: ?>text-red-600<?php endif; ?>">
                            <?= ucfirst($listing['status']) ?>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Gallery Images:</span>
                        <span class="text-sm text-gray-900"><?= count($gallery_images) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Image gallery functionality
    function changeMainImage(imageSrc, imageNumber) {
        document.getElementById('mainImage').src = imageSrc;
        const counterElement = document.getElementById('imageCounter');
        if (counterElement) {
            counterElement.textContent = imageNumber + ' / <?= count($allImages) ?>';
        }

        // Update thumbnail borders
        document.querySelectorAll('.thumbnail').forEach((thumb, index) => {
            if (index === imageNumber - 1) {
                thumb.classList.add('border-indigo-500');
                thumb.classList.remove('border-gray-200');
            } else {
                thumb.classList.remove('border-indigo-500');
                thumb.classList.add('border-gray-200');
            }
        });
    }
</script>

<?= view('admin/templates/footer') ?>
