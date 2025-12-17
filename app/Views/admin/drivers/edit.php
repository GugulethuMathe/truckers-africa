<?= view('admin/templates/header', ['page_title' => 'Edit Driver']) ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit Driver</h1>
        <a href="<?= site_url('admin/drivers/all') ?>" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
            Back to Drivers
        </a>
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
        <form method="POST" action="<?= site_url('admin/drivers/edit/' . $driver['id']) ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Personal Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Personal Information</h3>
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                        <input type="text" id="name" name="name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= old('name', $driver['name']) ?>">
                    </div>

                    <div>
                        <label for="surname" class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                        <input type="text" id="surname" name="surname" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= old('surname', $driver['surname']) ?>">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                        <input type="email" id="email" name="email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= old('email', $driver['email']) ?>">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password (Optional)</label>
                        <input type="password" id="password" name="password"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Leave blank to keep current password">
                        <p class="text-xs text-gray-500 mt-1">Leave blank to keep current password</p>
                    </div>

                    <div>
                        <label for="country_of_residence" class="block text-sm font-medium text-gray-700 mb-2">Country of Residence</label>
                        <input type="text" id="country_of_residence" name="country_of_residence"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= old('country_of_residence', $driver['country_of_residence']) ?>">
                    </div>
                </div>

                <!-- Contact & Vehicle Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Contact & Vehicle Information</h3>
                    
                    <div>
                        <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-2">Contact Number *</label>
                        <input type="text" id="contact_number" name="contact_number" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= old('contact_number', $driver['contact_number']) ?>">
                    </div>

                    <div>
                        <label for="whatsapp_number" class="block text-sm font-medium text-gray-700 mb-2">WhatsApp Number</label>
                        <input type="text" id="whatsapp_number" name="whatsapp_number"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= old('whatsapp_number', $driver['whatsapp_number']) ?>">
                    </div>

                    <div>
                        <label for="vehicle_type" class="block text-sm font-medium text-gray-700 mb-2">Vehicle Type</label>
                        <input type="text" id="vehicle_type" name="vehicle_type"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="e.g., Truck, Van, Motorcycle, Pickup, Trailer"
                               value="<?= old('vehicle_type', $driver['vehicle_type']) ?>">
                    </div>

                    <div>
                        <label for="vehicle_registration" class="block text-sm font-medium text-gray-700 mb-2">Vehicle Registration</label>
                        <input type="text" id="vehicle_registration" name="vehicle_registration"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= old('vehicle_registration', $driver['vehicle_registration']) ?>">
                    </div>

                    <div>
                        <label for="license_number" class="block text-sm font-medium text-gray-700 mb-2">License Number</label>
                        <input type="text" id="license_number" name="license_number"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= old('license_number', $driver['license_number']) ?>">
                    </div>

                    <div>
                        <label for="preferred_search_radius_km" class="block text-sm font-medium text-gray-700 mb-2">Preferred Search Radius (km)</label>
                        <input type="number" id="preferred_search_radius_km" name="preferred_search_radius_km" min="1" max="500"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="<?= old('preferred_search_radius_km', $driver['preferred_search_radius_km'] ?? 50) ?>">
                        <p class="text-xs text-gray-500 mt-1">Default: 50 km</p>
                    </div>
                </div>
            </div>

            <!-- Driver Stats -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Driver Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div class="bg-gray-50 p-3 rounded">
                        <span class="font-medium text-gray-600">Driver ID:</span>
                        <span class="text-gray-900"><?= $driver['id'] ?></span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded">
                        <span class="font-medium text-gray-600">Joined:</span>
                        <span class="text-gray-900"><?= date('M j, Y', strtotime($driver['created_at'])) ?></span>
                    </div>
                    <?php if (!empty($driver['last_location_update'])): ?>
                    <div class="bg-gray-50 p-3 rounded">
                        <span class="font-medium text-gray-600">Last Seen:</span>
                        <span class="text-gray-900"><?= date('M j, Y', strtotime($driver['last_location_update'])) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                <a href="<?= site_url('admin/drivers/all') ?>" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Update Driver
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const requiredFields = ['name', 'surname', 'email', 'contact_number'];
    let hasErrors = false;
    
    requiredFields.forEach(function(fieldName) {
        const field = document.getElementById(fieldName);
        if (!field.value.trim()) {
            field.classList.add('border-red-500');
            hasErrors = true;
        } else {
            field.classList.remove('border-red-500');
        }
    });
    
    if (hasErrors) {
        e.preventDefault();
        alert('Please fill in all required fields.');
    }
});

// Clear error styling when user starts typing
document.querySelectorAll('input[required]').forEach(function(field) {
    field.addEventListener('input', function() {
        this.classList.remove('border-red-500');
    });
});
</script>

<?= view('admin/templates/footer') ?>
