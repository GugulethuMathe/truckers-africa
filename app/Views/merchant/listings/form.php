<?= view('merchant/templates/header', ['page_title' => $page_title]) ?>

<div class="px-6 py-8">
    <div class="max-w-4xl mx-auto">
        
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900"><?= esc($page_title) ?></h1>
            <p class="text-gray-600">Fill out the details below for your new service.</p>
        </div>

        <?php
            $is_edit = isset($listing);
            $form_action = $is_edit ? site_url('merchant/listings/update/' . $listing['id']) : site_url('merchant/listings/create');
        ?>

        <?php if (session()->has('errors')) : ?>
            <div class="rounded-md bg-red-50 p-4 mb-6 border border-red-300">
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <?php foreach (session('errors') as $error) : ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <form action="<?= $form_action ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Custom Service Title -->
                    <div class="md:col-span-2">
                        <label for="title" class="block text-sm font-medium text-black">Service Title</label>
                        <input type="text" id="title" name="title" value="<?= old('title', $listing['title'] ?? '') ?>"
                               class="mt-1 block w-full rounded-md border-2 border-gray-400 px-3 py-2 shadow-sm focus:border-brand-blue focus:ring-brand-blue sm:text-sm"
                               placeholder="Enter your service title (e.g., 24/7 Truck Repairs, Premium Fuel Station)" required>
                        <p class="mt-1 text-xs text-gray-500">Enter a descriptive title for your service listing.</p>
                    </div>

                    <!-- Business Location -->
                    <?php if (!$is_edit): ?>
                    <div class="md:col-span-2">
                        <label for="location_id" class="block text-sm font-medium text-black">Business Location <span class="text-red-500">*</span></label>
                        <select id="location_id" name="location_id" required
                                class="mt-1 block w-full rounded-md border-2 border-gray-400 px-3 py-2 shadow-sm focus:border-brand-blue focus:ring-brand-blue sm:text-sm">
                            <option value="">Select a location</option>
                            <?php if (isset($locations) && !empty($locations)): ?>
                                <?php
                                // Set default to first location (primary branch) if no preselected or old value
                                $defaultLocationId = old('location_id', $preselected_location_id ?? $locations[0]['id']);
                                ?>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?= $location['id'] ?>"
                                            <?= ($defaultLocationId == $location['id']) ? 'selected' : '' ?>>
                                        <?= esc($location['location_name']) ?>
                                        <?php if ($location['is_primary']): ?> (Primary)<?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Select which business location this service is offered at.</p>
                    </div>
                    <?php endif; ?>



                    <!-- Categories (Collapsible) -->
                    <div class="md:col-span-2">
                        <button type="button"
                                onclick="toggleCategories()"
                                class="w-full flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 border border-gray-300 rounded-md transition-colors">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-700">Service Categories</span>
                                <span class="text-red-500">*</span>
                                <?php if (isset($maxCategories) && $maxCategories !== -1): ?>
                                    <span class="text-sm font-normal text-gray-600">(Max: <?= $maxCategories ?>)</span>
                                <?php endif; ?>
                                <?php if (isset($maxCategories) && $maxCategories !== -1): ?>
                                    <span id="categoryCount" class="text-xs font-medium text-green-600">(0/<?= $maxCategories ?> selected)</span>
                                <?php endif; ?>
                            </div>
                            <i id="categoryToggleIcon" class="fas fa-chevron-down text-gray-600 transition-transform duration-200"></i>
                        </button>

                        <div id="categoriesContainer" class="hidden mt-3 border border-gray-300 rounded-md p-4 bg-white max-h-96 overflow-y-auto">
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                <?php foreach ($categories as $category): ?>
                                    <?php
                                    $isChecked = false;
                                    if (isset($listing) && isset($listing['categories']) && !empty($listing['categories'])) {
                                        $selectedIds = array_column($listing['categories'], 'category_id');
                                        $isChecked = in_array($category['id'], $selectedIds);
                                    } elseif (old('categories')) {
                                        $isChecked = in_array($category['id'], old('categories'));
                                    }
                                    ?>
                                    <label class="flex items-center space-x-2 p-3 border border-gray-300 rounded-md hover:bg-gray-50 cursor-pointer category-checkbox">
                                        <input type="checkbox"
                                               name="categories[]"
                                               value="<?= $category['id'] ?>"
                                               <?= $isChecked ? 'checked' : '' ?>
                                               class="rounded text-green-600 focus:ring-green-500 category-input">
                                        <span class="text-sm text-gray-700"><?= esc($category['name']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <p class="text-xs text-gray-500 mt-3">
                                Select categories that best describe this service
                            </p>
                            <?php if (isset($maxCategories) && $maxCategories !== -1): ?>
                                <p id="categoryLimitWarning" class="text-xs text-red-500 mt-1 hidden">
                                    You can only select up to <?= $maxCategories ?> categories based on your subscription plan.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Services/Subcategories with Autocomplete (Optional) -->
                    <div class="md:col-span-2" style="display: none;">
                        <label class="block text-sm font-medium text-black">Subcategories (Optional)</label>
                        <div class="mt-1">
                            <!-- Selected Services -->
                            <div class="flex flex-wrap gap-2 mb-2" x-show="selectedServices.length > 0">
                                <template x-for="service in selectedServices" :key="service.id">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-green-800">
                                        <span x-text="service.name"></span>
                                        <button type="button" @click="removeService(service.id)" class="ml-2 text-green-600 hover:text-green-800">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                            </div>

                            <!-- Service Search Input -->
                            <div class="relative">
                                <input type="text" x-model="serviceSearch"
                                       @input="showServiceDropdown = serviceSearch.length >= 3"
                                       @focus="showServiceDropdown = serviceSearch.length >= 3"
                                       @click.away="showServiceDropdown = false"
                                       class="block w-full rounded-md border-2 border-gray-400 px-3 py-2 shadow-sm focus:border-brand-blue focus:ring-brand-blue sm:text-sm"
                                       placeholder="Type at least 3 characters to search subcategories...">
                                
                                <!-- Service Dropdown -->
                                <div x-show="showServiceDropdown && filteredServices.length > 0" 
                                     class="absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                                    <template x-for="service in filteredServices" :key="service.id">
                                        <div @click="addService(service)" 
                                             class="px-4 py-2 hover:bg-green-50 cursor-pointer border-b border-gray-100">
                                            <div class="font-medium text-gray-900" x-text="service.name"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Hidden inputs for selected services -->
                            <template x-for="service in selectedServices" :key="service.id">
                                <input type="hidden" name="services[]" :value="service.id">
                            </template>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Optional: Add specific subcategories to help drivers find your service more easily.</p>
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-black">Service Description</label>
                        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-2 border-gray-400 px-3 py-2 shadow-sm focus:border-brand-blue focus:ring-brand-blue sm:text-sm" placeholder="Describe your service in detail..."><?= old('description', $listing['description'] ?? '') ?></textarea>
                        <p class="mt-1 text-xs text-gray-500">Provide a detailed description of your service to help customers understand what you offer.</p>
                    </div>

                    <!-- Price and Currency Section -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-black">Pricing Information</label>
                        <div class="mt-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Currency Selection -->
                            <div>
                                <label for="currency_code" class="block text-xs font-medium text-gray-700 mb-1">Currency</label>
                                <select id="currency_code" name="currency_code" class="block w-full rounded-md border-2 border-gray-400 px-3 py-2 shadow-sm focus:border-brand-blue focus:ring-brand-blue sm:text-sm" required>
                                    <?php
                                    $selectedCurrency = old('currency_code', $listing['currency_code'] ?? $merchant['default_currency'] ?? 'ZAR');
                                    ?>
                                    <?php foreach ($currencies as $currency): ?>
                                        <option value="<?= esc($currency['currency_code']) ?>"
                                                <?= $selectedCurrency === $currency['currency_code'] ? 'selected' : '' ?>>
                                            <?= esc($currency['currency_symbol']) ?> - <?= esc($currency['currency_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Price Input -->
                            <div class="md:col-span-2">
                                <label for="price" class="block text-xs font-medium text-gray-700 mb-1">Price</label>
                                <input type="number" id="price" name="price"
                                       value="<?= old('price', $listing['price'] ?? '') ?>"
                                       placeholder="e.g., 50, 200, 1500.50"
                                       step="0.01"
                                       min="0"
                                       class="block w-full rounded-md border-2 border-gray-400 px-3 py-2 shadow-sm focus:border-brand-blue focus:ring-brand-blue sm:text-sm"
                                       required>
                                <p class="mt-1 text-xs text-red-600" id="price-error" style="display: none;">Please enter a valid numeric price</p>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Enter the numeric price for your service (e.g., 50, 200, 1500.50). Decimals are allowed.
                        </p>

                        <!-- Price Preview -->
                        <div class="mt-2 p-3 bg-blue-50 rounded-md border border-blue-200">
                            <div class="text-sm text-blue-800">
                                <strong>Price Preview:</strong>
                                <span id="price-preview">Enter a price to see preview</span>
                            </div>
                        </div>
                    </div>

                    <!-- Current Main Image -->
                    <?php if ($is_edit && !empty($listing['main_image_path'])): ?>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-black mb-2">
                                <i class="fas fa-image mr-1"></i>Current Main Image
                            </label>
                            <div class="relative group inline-block">
                                <img src="<?= get_listing_image_url($listing['main_image_path']) ?>"
                                     alt="Main Image"
                                     class="rounded-lg max-h-64 object-cover border-2 border-gray-300 shadow-sm">
                                <div class="absolute top-2 right-2 bg-green-600 text-white text-xs px-2 py-1 rounded-full">
                                    <i class="fas fa-star mr-1"></i>Featured
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                Upload a new image below to replace this one
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Upload New Main Image -->
                    <div class="md:col-span-2">
                        <label for="main_image" class="block text-sm font-medium text-black">
                            <?= $is_edit ? 'Upload New Main Image (Optional)' : 'Main Image (Featured)' ?>
                            <?php if (!$is_edit): ?>
                                <span class="text-red-500">*</span>
                            <?php endif; ?>
                        </label>
                        <div class="mt-2">
                            <input type="file"
                                   id="main_image"
                                   name="main_image"
                                   accept="image/jpeg,image/jpg,image/png,image/webp"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-blue file:text-white hover:file:bg-blue-700"
                                   <?= !$is_edit ? 'required' : '' ?>>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            This will be the featured image for your service listing. Maximum file size: 2MB. Supported formats: JPG, PNG, WebP.
                        </p>
                    </div>

                    <!-- Current Gallery Images -->
                    <?php if ($is_edit && !empty($listing['gallery_images'])): ?>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-black mb-3">
                                <i class="fas fa-images mr-1"></i>Current Gallery Images
                                <span class="text-xs font-normal text-gray-600 ml-1">(<?= count($listing['gallery_images']) ?> images)</span>
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <?php foreach ($listing['gallery_images'] as $image): ?>
                                    <div class="relative group">
                                        <img src="<?= get_listing_image_url($image['image_path']) ?>"
                                             alt="Gallery Image"
                                             class="w-full h-32 object-cover rounded-lg border-2 border-gray-300 shadow-sm">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all rounded-lg flex items-center justify-center">
                                            <button type="button"
                                                    onclick="deleteGalleryImage(<?= $image['id'] ?>, this)"
                                                    class="opacity-0 group-hover:opacity-100 bg-red-600 text-white px-3 py-1 rounded-full text-xs font-medium hover:bg-red-700 transition-all">
                                                <i class="fas fa-trash mr-1"></i>Delete
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <p class="mt-2 text-xs text-gray-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                Hover over an image and click Delete to remove it. Upload new images below to add more.
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Upload New Gallery Images -->
                    <div class="md:col-span-2">
                        <label for="gallery_images" class="block text-sm font-medium text-black">
                            <?= $is_edit ? 'Upload Additional Gallery Images (Optional)' : 'Gallery Images (Optional)' ?>
                        </label>
                        <div class="mt-2">
                            <input type="file"
                                   id="gallery_images"
                                   name="gallery_images[]"
                                   multiple
                                   accept="image/jpeg,image/jpg,image/png,image/webp"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-blue file:text-white hover:file:bg-blue-700">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Upload additional images to showcase your service. Maximum file size: 2MB each. Supported formats: JPG, PNG, WebP.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-4">
                    <a href="<?= site_url('merchant/listings') ?>" class="bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded-lg text-sm hover:bg-gray-300">Cancel</a>
                    <button type="submit" class="bg-brand-blue text-white font-semibold py-2 px-4 rounded-lg text-sm hover:bg-blue-700"><?= $is_edit ? 'Update Listing' : 'Create Listing' ?></button>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
// Toggle categories section
function toggleCategories() {
    const container = document.getElementById('categoriesContainer');
    const icon = document.getElementById('categoryToggleIcon');

    if (container.classList.contains('hidden')) {
        container.classList.remove('hidden');
        icon.classList.add('rotate-180');
    } else {
        container.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
}

// Price preview functionality
document.addEventListener('DOMContentLoaded', function() {
    const currencySelect = document.getElementById('currency_code');
    const priceInput = document.getElementById('price');
    const pricePreview = document.getElementById('price-preview');
    const priceError = document.getElementById('price-error');
    const form = priceInput.closest('form');

    // Currency symbols mapping (from PHP data)
    const currencySymbols = {
        <?php foreach ($currencies as $currency): ?>
        '<?= esc($currency['currency_code']) ?>': '<?= esc($currency['currency_symbol']) ?>',
        <?php endforeach; ?>
    };

    function updatePricePreview() {
        const currency = currencySelect.value;
        const price = priceInput.value.trim();
        const symbol = currencySymbols[currency] || currency;

        if (!price) {
            pricePreview.textContent = 'Enter a price to see preview';
            priceError.style.display = 'none';
            return;
        }

        // Check if price is numeric
        const numericPrice = parseFloat(price);
        if (!isNaN(numericPrice) && isFinite(numericPrice) && numericPrice >= 0) {
            // Format numeric price
            const formattedPrice = numericPrice.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
            pricePreview.textContent = `${symbol}${formattedPrice}`;
            priceError.style.display = 'none';
            priceInput.setCustomValidity('');
        } else {
            // Invalid numeric price
            pricePreview.textContent = 'Invalid price';
            priceError.style.display = 'block';
            priceInput.setCustomValidity('Please enter a valid numeric price');
        }
    }

    // Validate on input
    priceInput.addEventListener('input', updatePricePreview);

    // Update preview when currency changes
    currencySelect.addEventListener('change', updatePricePreview);

    // Form submission validation
    if (form) {
        form.addEventListener('submit', function(e) {
            const price = priceInput.value.trim();
            const numericPrice = parseFloat(price);

            if (!price || isNaN(numericPrice) || !isFinite(numericPrice) || numericPrice < 0) {
                e.preventDefault();
                priceError.style.display = 'block';
                priceInput.focus();
                priceInput.setCustomValidity('Please enter a valid numeric price');
                return false;
            }
        });
    }

    // Initial preview update
    updatePricePreview();
});

<?php if (isset($maxCategories) && $maxCategories !== -1): ?>
// Category limit functionality
document.addEventListener('DOMContentLoaded', function() {
    const maxCategories = <?= $maxCategories ?>;
    const categoryInputs = document.querySelectorAll('.category-input');
    const categoryCount = document.getElementById('categoryCount');
    const categoryLimitWarning = document.getElementById('categoryLimitWarning');

    function updateCategoryCount() {
        const checkedCount = document.querySelectorAll('.category-input:checked').length;

        // Update count display
        if (categoryCount) {
            categoryCount.textContent = `(${checkedCount}/${maxCategories} selected)`;

            // Change color based on limit
            if (checkedCount >= maxCategories) {
                categoryCount.classList.remove('text-green-600');
                categoryCount.classList.add('text-red-600');
            } else {
                categoryCount.classList.remove('text-red-600');
                categoryCount.classList.add('text-green-600');
            }
        }

        // Show/hide warning
        if (checkedCount >= maxCategories) {
            categoryLimitWarning.classList.remove('hidden');
        } else {
            categoryLimitWarning.classList.add('hidden');
        }

        // Disable unchecked checkboxes if limit reached
        categoryInputs.forEach(input => {
            if (!input.checked && checkedCount >= maxCategories) {
                input.disabled = true;
                input.parentElement.classList.add('opacity-50', 'cursor-not-allowed');
                input.parentElement.classList.remove('hover:bg-gray-50', 'cursor-pointer');
            } else if (!input.checked) {
                input.disabled = false;
                input.parentElement.classList.remove('opacity-50', 'cursor-not-allowed');
                input.parentElement.classList.add('hover:bg-gray-50', 'cursor-pointer');
            }
        });
    }

    // Add event listeners to all category checkboxes
    categoryInputs.forEach(input => {
        input.addEventListener('change', updateCategoryCount);
    });

    // Initial count update
    updateCategoryCount();
});
<?php endif; ?>

// Delete gallery image function
function deleteGalleryImage(imageId, button) {
    if (!confirm('Are you sure you want to delete this image? This action cannot be undone.')) {
        return;
    }

    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Deleting...';
    button.disabled = true;

    // Send AJAX request to delete the image
    fetch('<?= site_url('merchant/listings/delete-gallery-image') ?>/' + imageId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the image container from DOM
            const imageContainer = button.closest('.relative.group');
            imageContainer.style.opacity = '0';
            setTimeout(() => {
                imageContainer.remove();

                // Update image count
                const countElement = document.querySelector('.md\\:col-span-2 label span.text-xs');
                if (countElement) {
                    const remainingImages = document.querySelectorAll('.relative.group').length;
                    if (remainingImages === 0) {
                        // Hide the entire gallery section if no images left
                        const gallerySection = imageContainer.closest('.md\\:col-span-2');
                        if (gallerySection) {
                            gallerySection.remove();
                        }
                    } else {
                        countElement.textContent = `(${remainingImages} images)`;
                    }
                }
            }, 300);
        } else {
            alert(data.message || 'Failed to delete image. Please try again.');
            button.innerHTML = '<i class="fas fa-trash mr-1"></i>Delete';
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the image. Please try again.');
        button.innerHTML = '<i class="fas fa-trash mr-1"></i>Delete';
        button.disabled = false;
    });
}
</script>



<?= view('merchant/templates/footer') ?>
