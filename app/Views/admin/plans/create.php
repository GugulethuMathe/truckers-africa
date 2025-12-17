<?= view('admin/templates/header', ['page_title' => 'Create New Plan']) ?>

<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8">
        <div>
            <h2 class="text-2xl font-semibold leading-tight">Create New Plan</h2>
        </div>
        <div class="-mx-4 sm:-mx-8 px-4 sm:px-8 py-4 overflow-x-auto">
            <div class="inline-block min-w-full shadow rounded-lg overflow-hidden">
                <div class="bg-white p-6">
                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Oops!</strong>
                            <span class="block sm:inline">There were some errors with your submission.</span>
                            <ul>
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li>- <?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= site_url('admin/plans/store') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="mb-4">
                            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Plan Name:</label>
                            <input type="text" id="name" name="name" value="<?= old('name') ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div class="mb-4">
                            <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Price (USD):</label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="text" id="price" name="price" value="<?= old('price') ?>" class="block w-full rounded-md border-gray-300 pl-7 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="0.00">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="billing_interval" class="block text-gray-700 text-sm font-bold mb-2">Billing Interval:</label>
                            <select id="billing_interval" name="billing_interval" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="monthly" <?= old('billing_interval') == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                <option value="yearly" <?= old('billing_interval') == 'yearly' ? 'selected' : '' ?>>Yearly</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
                            <textarea id="description" name="description" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?= old('description') ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Free Trial</label>
                            <label class="inline-flex items-center">
                                <input type="hidden" name="has_trial" value="0">
                                <input type="checkbox" id="has_trial" name="has_trial" value="1" class="form-checkbox h-5 w-5 text-indigo-600" <?= old('has_trial') ? 'checked' : '' ?> onchange="toggleTrialDays()">
                                <span class="ml-2 text-gray-700">Enable Free Trial</span>
                            </label>
                        </div>

                        <div class="mb-4" id="trial_days_container" style="display: <?= old('has_trial') ? 'block' : 'none' ?>;">
                            <label for="trial_days" class="block text-gray-700 text-sm font-bold mb-2">Trial Days:</label>
                            <input type="number" id="trial_days" name="trial_days" value="<?= old('trial_days', 0) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <!-- Plan Limitations Section -->
                        <div class="border-t border-gray-200 pt-6 mt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Plan Limitations</h3>
                            <p class="text-sm text-gray-600 mb-4">Set limits for this plan. Enter -1 for unlimited access.</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Max Locations -->
                                <div class="mb-4">
                                    <label for="max_locations" class="block text-gray-700 text-sm font-bold mb-2">Max Business Locations:</label>
                                    <input type="number" id="max_locations" name="max_locations"
                                           value="<?= old('max_locations', 1) ?>"
                                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                           min="-1">
                                    <p class="text-xs text-gray-500 mt-1">Number of business locations merchant can add (-1 = unlimited)</p>
                                </div>

                                <!-- Max Listings -->
                                <div class="mb-4">
                                    <label for="max_listings" class="block text-gray-700 text-sm font-bold mb-2">Max Service Listings:</label>
                                    <input type="number" id="max_listings" name="max_listings"
                                           value="<?= old('max_listings', 5) ?>"
                                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                           min="-1">
                                    <p class="text-xs text-gray-500 mt-1">Total number of service listings (-1 = unlimited)</p>
                                </div>

                                <!-- Max Categories -->
                                <div class="mb-4">
                                    <label for="max_categories" class="block text-gray-700 text-sm font-bold mb-2">Max Categories per Listing:</label>
                                    <input type="number" id="max_categories" name="max_categories"
                                           value="<?= old('max_categories', 2) ?>"
                                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                           min="-1">
                                    <p class="text-xs text-gray-500 mt-1">Categories merchant can select per listing (-1 = unlimited)</p>
                                </div>

                                <!-- Max Gallery Images -->
                                <div class="mb-4">
                                    <label for="max_gallery_images" class="block text-gray-700 text-sm font-bold mb-2">Max Gallery Images per Listing:</label>
                                    <input type="number" id="max_gallery_images" name="max_gallery_images"
                                           value="<?= old('max_gallery_images', 3) ?>"
                                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                           min="-1">
                                    <p class="text-xs text-gray-500 mt-1">Number of gallery images per listing (-1 = unlimited)</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <button type="submit" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded hover:bg-indigo-700 focus:outline-none focus:shadow-outline">
                                Create Plan
                            </button>
                            <a href="<?= site_url('admin/plans') ?>" class="inline-block align-baseline font-bold text-sm text-indigo-600 hover:text-indigo-800">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleTrialDays() {
        const hasTrialCheckbox = document.getElementById('has_trial');
        const trialDaysContainer = document.getElementById('trial_days_container');
        if (hasTrialCheckbox.checked) {
            trialDaysContainer.style.display = 'block';
        } else {
            trialDaysContainer.style.display = 'none';
        }
    }
</script>

<?= view('admin/templates/footer') ?>
