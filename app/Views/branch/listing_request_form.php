<?= view('branch/templates/header', ['page_title' => $page_title]) ?>

<div class="px-6 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="<?= base_url('branch/listing-requests') ?>" class="text-green-600 hover:text-green-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back to Requests
            </a>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-8">Request New Listing</h1>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="<?= base_url('branch/listing-requests/submit') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <!-- Service Title -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Service Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           required
                           value="<?= old('title') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                           placeholder="e.g., Laundry Service, Tire Repair, etc.">
                    <?php if (isset($errors['title'])): ?>
                        <p class="text-red-500 text-xs mt-1"><?= $errors['title'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Service Description
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                              placeholder="Describe the service in detail..."><?= old('description') ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <p class="text-red-500 text-xs mt-1"><?= $errors['description'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Price and Currency -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="md:col-span-2">
                        <label for="suggested_price" class="block text-sm font-medium text-gray-700 mb-2">
                            Suggested Price
                        </label>
                        <input type="number" 
                               id="suggested_price" 
                               name="suggested_price" 
                               step="0.01"
                               min="0"
                               value="<?= old('suggested_price') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                               placeholder="0.00">
                        <?php if (isset($errors['suggested_price'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= $errors['suggested_price'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="currency_code" class="block text-sm font-medium text-gray-700 mb-2">
                            Currency
                        </label>
                        <select id="currency_code"
                                name="currency_code"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                            <?php
                            $selectedCurrency = old('currency_code', $merchant['default_currency'] ?? 'ZAR');
                            ?>
                            <?php foreach ($currencies as $currency): ?>
                                <option value="<?= esc($currency['currency_code']) ?>"
                                        <?= $selectedCurrency === $currency['currency_code'] ? 'selected' : '' ?>>
                                    <?= esc($currency['currency_symbol']) ?> - <?= esc($currency['currency_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Unit -->
                <div class="mb-6">
                    <label for="unit" class="block text-sm font-medium text-gray-700 mb-2">
                        Unit of Measurement
                    </label>
                    <input type="text" 
                           id="unit" 
                           name="unit" 
                           value="<?= old('unit') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                           placeholder="e.g., per hour, per kg, per load, etc.">
                    <p class="text-xs text-gray-500 mt-1">Optional: Specify the unit for pricing (e.g., "per hour", "per kg")</p>
                </div>

                <!-- Categories (Collapsible) -->
                <div class="mb-6">
                    <button type="button"
                            onclick="toggleCategories()"
                            class="w-full flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 border border-gray-300 rounded-md transition-colors">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-700">Suggested Categories</span>
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
                                <label class="flex items-center space-x-2 p-3 border border-gray-300 rounded-md hover:bg-gray-50 cursor-pointer category-checkbox">
                                    <input type="checkbox"
                                           name="categories[]"
                                           value="<?= $category['id'] ?>"
                                           <?= in_array($category['id'], old('categories') ?? []) ? 'checked' : '' ?>
                                           class="rounded text-green-600 focus:ring-green-500 category-input">
                                    <span class="text-sm text-gray-700"><?= esc($category['name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-3">
                            Select categories that best describe this service
                        </p>
                        <p id="categoryLimitWarning" class="text-xs text-red-500 mt-1 hidden">
                            You can only select up to <?= $maxCategories ?? 0 ?> categories based on your merchant's subscription plan.
                        </p>
                    </div>
                </div>

                <!-- Main Image Upload -->
                <div class="mb-6">
                    <label for="main_image" class="block text-sm font-medium text-gray-700 mb-2">
                        Main Image (Featured)
                    </label>
                    <div class="mt-2">
                        <input type="file"
                               id="main_image"
                               name="main_image"
                               accept="image/jpeg,image/png,image/jpg,image/webp"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-600 file:text-white hover:file:bg-green-700">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">This will be the featured image for your service listing. Maximum file size: 2MB. Supported formats: JPG, PNG, WebP.</p>
                    <?php if (isset($errors['main_image'])): ?>
                        <p class="text-red-500 text-xs mt-1"><?= $errors['main_image'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Gallery Images Upload -->
                <div class="mb-6">
                    <label for="gallery_images" class="block text-sm font-medium text-gray-700 mb-2">
                        Gallery Images (Optional)
                    </label>
                    <div class="mt-2">
                        <input type="file"
                               id="gallery_images"
                               name="gallery_images[]"
                               multiple
                               accept="image/jpeg,image/png,image/jpg,image/webp"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-600 file:text-white hover:file:bg-green-700">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Upload additional images to showcase your service. Maximum file size: 2MB each. Supported formats: JPG, PNG, WebP.</p>
                    <?php if (isset($errors['gallery_images'])): ?>
                        <p class="text-red-500 text-xs mt-1"><?= $errors['gallery_images'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Justification -->
                <div class="mb-6">
                    <label for="justification" class="block text-sm font-medium text-gray-700 mb-2">
                        Justification <span class="text-red-500">*</span>
                    </label>
                    <textarea id="justification"
                              name="justification"
                              rows="4"
                              required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                              placeholder="Explain why this service is needed at your branch. Include customer demand, market opportunity, or any other relevant information..."><?= old('justification') ?></textarea>
                    <?php if (isset($errors['justification'])): ?>
                        <p class="text-red-500 text-xs mt-1"><?= $errors['justification'] ?></p>
                    <?php endif; ?>
                    <p class="text-xs text-gray-500 mt-1">Minimum 10 characters. Help the merchant understand why this listing is important.</p>
                </div>

                <!-- Info Box -->
                <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-medium mb-1">What happens next?</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>Your request will be submitted to the main merchant for review</li>
                                <li>The merchant will evaluate your request and may approve or reject it</li>
                                <li>If approved, the merchant will create the actual listing</li>
                                <li>You'll be notified of the decision</li>
                                <li>Approved listings will appear in your branch's services</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-4">
                    <a href="<?= base_url('branch/listing-requests') ?>" 
                       class="px-6 py-3 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Request
                    </button>
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

<?php if (isset($maxCategories) && $maxCategories !== -1): ?>
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
</script>

<?= view('branch/templates/footer') ?>

