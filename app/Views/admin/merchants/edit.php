<?= view('admin/templates/header', ['page_title' => 'Edit Merchant']) ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit Merchant: <?= esc($merchant['business_name']) ?></h1>
        <div class="flex space-x-2">
            <a href="<?= site_url('admin/merchants/view/' . $merchant['id']) ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                View Details
            </a>
            <a href="<?= site_url('admin/merchants/all') ?>" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                Back to Merchants
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p class="font-bold">Success</p>
            <p><?= session()->getFlashdata('success') ?></p>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p class="font-bold">Error</p>
            <p><?= session()->getFlashdata('error') ?></p>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="<?= site_url('admin/merchants/edit/' . $merchant['id']) ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Owner Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Owner Information</h3>
                    
                    <div>
                        <label for="owner_name" class="block text-sm font-medium text-gray-700 mb-2">Owner Name *</label>
                        <input type="text" id="owner_name" name="owner_name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= esc($merchant['owner_name']) ?>">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                        <input type="email" id="email" name="email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= esc($merchant['email']) ?>">
                    </div>
                </div>

                <!-- Business Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Business Information</h3>
                    
                    <div>
                        <label for="business_name" class="block text-sm font-medium text-gray-700 mb-2">Business Name *</label>
                        <input type="text" id="business_name" name="business_name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= esc($merchant['business_name']) ?>">
                    </div>

                    <div>
                        <label for="main_service" class="block text-sm font-medium text-gray-700 mb-2">Main Service</label>
                        <input type="text" id="main_service" name="main_service"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= esc($merchant['main_service']) ?>">
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Contact Information</h3>
                    
                    <div>
                        <label for="business_contact_number" class="block text-sm font-medium text-gray-700 mb-2">Business Phone</label>
                        <input type="text" id="business_contact_number" name="business_contact_number"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= esc($merchant['business_contact_number']) ?>">
                    </div>

                    <div>
                        <label for="business_whatsapp_number" class="block text-sm font-medium text-gray-700 mb-2">WhatsApp Number</label>
                        <input type="text" id="business_whatsapp_number" name="business_whatsapp_number"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= esc($merchant['business_whatsapp_number']) ?>">
                    </div>

                    <div>
                        <label for="physical_address" class="block text-sm font-medium text-gray-700 mb-2">Physical Address</label>
                        <textarea id="physical_address" name="physical_address" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"><?= esc($merchant['physical_address']) ?></textarea>
                    </div>
                </div>

                <!-- Status and Settings -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Status & Settings</h3>
                    
                    <div>
                        <label for="verification_status" class="block text-sm font-medium text-gray-700 mb-2">Verification Status</label>
                        <select id="verification_status" name="verification_status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="pending" <?= $merchant['verification_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="approved" <?= $merchant['verification_status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="rejected" <?= $merchant['verification_status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                            <option value="suspended" <?= $merchant['verification_status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        </select>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_visible" name="is_visible" value="1" 
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                               <?= $merchant['is_visible'] ? 'checked' : '' ?>>
                        <label for="is_visible" class="ml-2 block text-sm text-gray-900">
                            Make merchant visible to drivers
                        </label>
                    </div>

                    <div class="text-sm text-gray-600">
                        <p><strong>Created:</strong> <?= date('M j, Y g:i A', strtotime($merchant['created_at'])) ?></p>
                        <p><strong>Last Updated:</strong> <?= date('M j, Y g:i A', strtotime($merchant['updated_at'])) ?></p>
                        <?php if (!empty($merchant['google_id'])): ?>
                            <p><strong>Google Account:</strong> Yes</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Descriptions -->
            <div class="mt-8 space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Descriptions</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="profile_description" class="block text-sm font-medium text-gray-700 mb-2">Profile Description</label>
                        <textarea id="profile_description" name="profile_description" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Brief description about the owner/profile..."><?= esc($merchant['profile_description']) ?></textarea>
                    </div>

                    <div>
                        <label for="business_description" class="block text-sm font-medium text-gray-700 mb-2">Business Description</label>
                        <textarea id="business_description" name="business_description" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Detailed description about the business..."><?= esc($merchant['business_description']) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-8 flex justify-end space-x-4">
                <a href="<?= site_url('admin/merchants/all') ?>" 
                   class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Update Merchant
                </button>
            </div>
        </form>
    </div>
</div>

<?= view('admin/templates/footer') ?>
