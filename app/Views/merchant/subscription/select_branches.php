<div class="px-4 lg:px-6 py-6 lg:py-8">
    <div class="max-w-4xl mx-auto">
        
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Activate Your Branches</h1>
            <p class="text-gray-600 mt-2">
                Welcome back! Your subscription has been reactivated. Please select which branches you'd like to activate based on your plan limits.
            </p>
        </div>

        <!-- Plan Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h3 class="font-semibold text-blue-900">Your Plan: <?= esc($plan_name) ?></h3>
                    <p class="text-sm text-blue-800 mt-1">
                        You can activate up to <strong><?= $max_locations ?></strong> branch location(s) with your current plan.
                    </p>
                </div>
            </div>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (empty($locations)): ?>
            <!-- No Branches -->
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Branch Locations Found</h3>
                <p class="text-gray-600 mb-6">You don't have any branch locations to activate yet.</p>
                <a href="<?= site_url('merchant/locations') ?>" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                    Go to Locations
                </a>
            </div>
        <?php else: ?>
            <!-- Branch Selection Form -->
            <form action="<?= site_url('merchant/subscription/activate-branches') ?>" method="post" id="branch-form">
                <?= csrf_field() ?>

                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Select Branches to Activate</h2>
                        <p class="text-sm text-gray-600 mb-6">
                            Check the boxes next to the branches you want to activate. You can activate up to <?= $max_locations ?> branch(es).
                        </p>

                        <div class="space-y-4">
                            <?php foreach ($locations as $location): ?>
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                                    <label class="flex items-start cursor-pointer">
                                        <input type="checkbox" 
                                               name="branches[]" 
                                               value="<?= $location['id'] ?>"
                                               class="branch-checkbox mt-1 h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                               <?= $location['is_active'] ? 'checked' : '' ?>>
                                        
                                        <div class="ml-4 flex-1">
                                            <div class="flex items-center justify-between">
                                                <h3 class="font-semibold text-gray-900">
                                                    <?= esc($location['location_name']) ?>
                                                </h3>
                                                <?php if ($location['is_active']): ?>
                                                    <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">
                                                        Currently Active
                                                    </span>
                                                <?php else: ?>
                                                    <span class="px-2 py-1 text-xs font-semibold text-gray-600 bg-gray-100 rounded-full">
                                                        Inactive
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <p class="text-sm text-gray-600 mt-1">
                                                <?= esc($location['address']) ?>
                                            </p>

                                            <?php if (!empty($location['branch_user'])): ?>
                                                <div class="mt-2 text-sm text-gray-500">
                                                    <span class="font-medium">Branch Manager:</span> 
                                                    <?= esc($location['branch_user']['full_name']) ?>
                                                    (<?= esc($location['branch_user']['email']) ?>)
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Selection Counter -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">
                                    Selected: <strong id="selected-count">0</strong> / <?= $max_locations ?>
                                </span>
                                <span class="text-xs text-gray-500" id="limit-warning" style="display: none;">
                                    ⚠️ You've reached your plan limit
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <a href="<?= site_url('merchant/locations') ?>" 
                           class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition-colors text-center">
                            Skip for Now
                        </a>
                        <button type="submit" 
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                id="submit-btn">
                            Activate Selected Branches
                        </button>
                    </div>
                </div>
            </form>
        <?php endif; ?>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.branch-checkbox');
    const selectedCount = document.getElementById('selected-count');
    const limitWarning = document.getElementById('limit-warning');
    const submitBtn = document.getElementById('submit-btn');
    const maxLocations = <?= $max_locations ?>;

    function updateCounter() {
        const checked = document.querySelectorAll('.branch-checkbox:checked').length;
        selectedCount.textContent = checked;

        // Show warning if limit reached
        if (checked >= maxLocations) {
            limitWarning.style.display = 'block';
            // Disable unchecked checkboxes
            checkboxes.forEach(cb => {
                if (!cb.checked) {
                    cb.disabled = true;
                }
            });
        } else {
            limitWarning.style.display = 'none';
            // Enable all checkboxes
            checkboxes.forEach(cb => {
                cb.disabled = false;
            });
        }
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateCounter);
    });

    // Initialize counter
    updateCounter();
});
</script>

