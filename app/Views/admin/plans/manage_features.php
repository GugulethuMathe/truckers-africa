<?= view('admin/templates/header', ['page_title' => 'Manage Features for ' . esc($plan['name'])]) ?>

<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8">
        <div>
            <h2 class="text-2xl font-semibold leading-tight">Manage Features for "<?= esc($plan['name']) ?>"</h2>
        </div>

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative my-4" role="alert">
                <span class="block sm:inline"><?= session()->getFlashdata('success') ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Current Features (Draggable) -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Current Features (Drag to Reorder)</h3>
                <div id="current-features-list" class="space-y-2 min-h-[100px]">
                    <?php if (!empty($current_features)) : ?>
                        <?php foreach ($current_features as $feature) : ?>
                            <div class="feature-item bg-gray-50 border border-gray-200 rounded p-3 cursor-move hover:bg-gray-100 transition-colors"
                                 data-feature-id="<?= $feature['id'] ?>">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                        </svg>
                                        <span class="font-medium"><?= esc($feature['name']) ?></span>
                                    </div>
                                    <button type="button" class="remove-feature text-red-500 hover:text-red-700"
                                            data-feature-id="<?= $feature['id'] ?>">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="text-gray-500 empty-message">No features assigned to this plan yet.</p>
                    <?php endif; ?>
                </div>
                <div class="mt-4">
                    <button type="button" id="save-order" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition-colors opacity-50" disabled>
                        Save Changes
                    </button>
                    <p class="text-sm text-gray-500 mt-2">Click "Save Changes" after adding, removing, or reordering features.</p>
                    <p id="unsaved-changes" class="text-sm text-orange-600 mt-1 hidden">⚠️ You have unsaved changes</p>
                </div>
            </div>

            <!-- Available Features -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Available Features</h3>
                <?php if (!empty($all_features)) : ?>
                    <div class="space-y-2">
                        <?php foreach ($all_features as $feature) : ?>
                            <?php if (!in_array($feature['id'], $current_feature_ids)) : ?>
                                <div class="feature-available bg-blue-50 border border-blue-200 rounded p-3 hover:bg-blue-100 transition-colors"
                                     data-feature-id="<?= $feature['id'] ?>">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium"><?= esc($feature['name']) ?></span>
                                        <button type="button" class="add-feature text-blue-600 hover:text-blue-800"
                                                data-feature-id="<?= $feature['id'] ?>"
                                                data-feature-name="<?= esc($feature['name']) ?>">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="text-gray-500">No features have been created yet. <a href="<?= site_url('admin/features/create') ?>" class="text-indigo-600">Create one now</a>.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-8 flex items-center justify-between">
            <a href="<?= site_url('admin/plans') ?>" class="inline-block align-baseline font-bold text-sm text-indigo-600 hover:text-indigo-800">
                Back to Plans
            </a>
        </div>
    </div>
</div>

<!-- Include SortableJS for drag and drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const planId = <?= $plan['id'] ?>;
    const currentFeaturesList = document.getElementById('current-features-list');
    const saveOrderBtn = document.getElementById('save-order');
    const unsavedChangesIndicator = document.getElementById('unsaved-changes');

    // Function to enable save button and show unsaved changes indicator
    function markAsChanged() {
        if (saveOrderBtn) {
            saveOrderBtn.disabled = false;
            saveOrderBtn.classList.remove('opacity-50');
        }
        if (unsavedChangesIndicator) {
            unsavedChangesIndicator.classList.remove('hidden');
        }
    }

    // Initialize Sortable for drag and drop
    if (currentFeaturesList) {
        const sortable = Sortable.create(currentFeaturesList, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                // Enable save button when order changes
                markAsChanged();
            }
        });
    }

    // Save order functionality - This now saves both the features AND their order
    if (saveOrderBtn) {
        saveOrderBtn.addEventListener('click', function() {
            const featureItems = currentFeaturesList.querySelectorAll('.feature-item');
            const featureOrder = Array.from(featureItems).map(item => item.dataset.featureId);

            // Disable button during save
            saveOrderBtn.disabled = true;
            saveOrderBtn.textContent = 'Saving...';

            // Send AJAX request to save the complete feature list with order
            fetch('<?= site_url('admin/plans/update-feature-order/' . $plan['id']) ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                },
                body: JSON.stringify({
                    feature_order: featureOrder
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message and reload page to reflect changes
                    showMessage('Features updated successfully! Reloading...', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showMessage('Error updating features: ' + (data.message || 'Unknown error'), 'error');
                    saveOrderBtn.disabled = false;
                    saveOrderBtn.textContent = 'Save Changes';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Error updating features', 'error');
                saveOrderBtn.disabled = false;
                saveOrderBtn.textContent = 'Save Changes';
            });
        });
    }

    // Add feature functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.add-feature')) {
            const button = e.target.closest('.add-feature');
            const featureId = button.dataset.featureId;
            const featureName = button.dataset.featureName;

            // Add feature to current list
            addFeatureToList(featureId, featureName);

            // Remove from available list
            button.closest('.feature-available').remove();
        }

        if (e.target.closest('.remove-feature')) {
            const button = e.target.closest('.remove-feature');
            const featureId = button.dataset.featureId;
            const featureItem = button.closest('.feature-item');
            const featureName = featureItem.querySelector('span.font-medium').textContent;

            // Remove from current list
            featureItem.remove();

            // Check if list is now empty and show empty message
            const remainingFeatures = currentFeaturesList.querySelectorAll('.feature-item');
            if (remainingFeatures.length === 0) {
                currentFeaturesList.innerHTML = '<p class="text-gray-500 empty-message">No features assigned to this plan yet.</p>';
            }

            // Add back to available list
            addFeatureToAvailable(featureId, featureName);

            // Mark as changed
            markAsChanged();
        }
    });

    function addFeatureToList(featureId, featureName) {
        // Remove empty message if it exists
        const emptyMessage = currentFeaturesList.querySelector('.empty-message');
        if (emptyMessage) {
            emptyMessage.remove();
        }

        const newFeatureHtml = `
            <div class="feature-item bg-gray-50 border border-gray-200 rounded p-3 cursor-move hover:bg-gray-100 transition-colors"
                 data-feature-id="${featureId}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                        </svg>
                        <span class="font-medium">${featureName}</span>
                    </div>
                    <button type="button" class="remove-feature text-red-500 hover:text-red-700"
                            data-feature-id="${featureId}">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `;

        currentFeaturesList.insertAdjacentHTML('beforeend', newFeatureHtml);
        markAsChanged();
    }

    function addFeatureToAvailable(featureId, featureName) {
        const availableContainer = document.querySelector('.space-y-2');
        const newAvailableHtml = `
            <div class="feature-available bg-blue-50 border border-blue-200 rounded p-3 hover:bg-blue-100 transition-colors"
                 data-feature-id="${featureId}">
                <div class="flex items-center justify-between">
                    <span class="font-medium">${featureName}</span>
                    <button type="button" class="add-feature text-blue-600 hover:text-blue-800"
                            data-feature-id="${featureId}"
                            data-feature-name="${featureName}">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `;

        availableContainer.insertAdjacentHTML('beforeend', newAvailableHtml);
    }

    function showMessage(message, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `fixed top-4 right-4 px-4 py-3 rounded z-50 ${type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'}`;
        messageDiv.textContent = message;

        document.body.appendChild(messageDiv);

        setTimeout(() => {
            messageDiv.remove();
        }, 3000);
    }
});
</script>

<style>
.sortable-ghost {
    opacity: 0.4;
}

.sortable-chosen {
    transform: scale(1.02);
}

.sortable-drag {
    transform: rotate(5deg);
}

#save-order:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

<?= view('admin/templates/footer') ?>
