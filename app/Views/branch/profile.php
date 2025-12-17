<?= view('branch/templates/header', ['page_title' => $page_title]) ?>

<div class="px-6 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Branch Profile</h1>

        <!-- Profile Update Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">
                <i class="fas fa-user mr-2"></i>Personal Information
            </h2>
            
            <form action="<?= base_url('branch/profile/update') ?>" method="POST">
                <?= csrf_field() ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Full Name -->
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="full_name" 
                               name="full_name" 
                               value="<?= esc($branch_user['full_name']) ?>"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="<?= esc($branch_user['email']) ?>"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                            Phone Number
                        </label>
                        <input type="text" 
                               id="phone_number" 
                               name="phone_number" 
                               value="<?= esc($branch_user['phone_number']) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>

                <h2 class="text-xl font-semibold text-gray-900 mb-6 mt-8">
                    <i class="fas fa-map-marker-alt mr-2"></i>Branch/Location Information
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Location Name -->
                    <div>
                        <label for="location_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Branch Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="location_name" 
                               name="location_name" 
                               value="<?= esc($branch_user['location_name']) ?>"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                    </div>

                    <!-- Contact Number -->
                    <div>
                        <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-2">
                            Branch Contact Number
                        </label>
                        <input type="text" 
                               id="contact_number" 
                               name="contact_number" 
                               value="<?= esc($branch_user['contact_number']) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                    </div>

                    <!-- WhatsApp Number -->
                    <div>
                        <label for="whatsapp_number" class="block text-sm font-medium text-gray-700 mb-2">
                            WhatsApp Number
                        </label>
                        <input type="text" 
                               id="whatsapp_number" 
                               name="whatsapp_number" 
                               value="<?= esc($branch_user['whatsapp_number']) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                    </div>

                    <!-- Location Email -->
                    <div>
                        <label for="location_email" class="block text-sm font-medium text-gray-700 mb-2">
                            Branch Email
                        </label>
                        <input type="email" 
                               id="location_email" 
                               name="location_email" 
                               value="<?= esc($branch_user['location_email']) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                    </div>

                    <!-- Physical Address -->
                    <div class="md:col-span-2">
                        <label for="physical_address" class="block text-sm font-medium text-gray-700 mb-2">
                            Physical Address
                        </label>
                        <textarea id="physical_address"
                                  name="physical_address"
                                  rows="3"
                                  readonly
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600 cursor-not-allowed"><?= esc($branch_user['physical_address']) ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Physical address cannot be changed. Contact the main merchant to update this field.
                        </p>
                    </div>

                    <!-- Operating Hours -->
                    <div class="md:col-span-2">
                        <label for="operating_hours" class="block text-sm font-medium text-gray-700 mb-2">
                            Operating Hours
                        </label>
                        <textarea id="operating_hours" 
                                  name="operating_hours" 
                                  rows="2"
                                  placeholder="e.g., Mon-Fri: 8:00 AM - 5:00 PM, Sat: 9:00 AM - 1:00 PM"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"><?= esc($branch_user['operating_hours']) ?></textarea>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" 
                            class="px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Change Password Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">
                <i class="fas fa-lock mr-2"></i>Change Password
            </h2>
            
            <form action="<?= base_url('branch/profile/change-password') ?>" method="POST">
                <?= csrf_field() ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Current Password -->
                    <div class="md:col-span-2">
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Current Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               id="current_password" 
                               name="current_password" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                            New Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               id="new_password" 
                               name="new_password" 
                               required
                               minlength="8"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                        <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirm New Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               required
                               minlength="8"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        <i class="fas fa-key mr-2"></i>Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= view('branch/templates/footer') ?>

